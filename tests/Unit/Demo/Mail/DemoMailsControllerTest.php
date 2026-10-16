<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Mail;

use App\Demo\Controller\DemoMailsController;
use App\Demo\Mail\DemoMailbox;
use App\Demo\Mail\MailpitClient;
use App\Demo\Mail\SensitiveMailFilter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\TwigFunction;

/** US-028: the demo mailbox controller (404 off, 404 excluded mail, unavailable message, no raw HTML). */
final class DemoMailsControllerTest extends TestCase
{
    public function testItIs404WhenTheDemoModeIsOff(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->controller(demoMode: false)->list();
    }

    public function testItIs404WhenMailpitIsNotConfigured(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->controller(apiUrl: '')->show('abc');
    }

    public function testASensitiveMailIsNotListedAndRefusedById(): void
    {
        $controller = $this->controller();

        $html = (string) $controller->list()->getContent();
        self::assertStringContainsString('Facture FA-1', $html);
        self::assertStringNotContainsString('Reset your password', $html);

        $this->expectException(NotFoundHttpException::class);
        $controller->show('reset1');
    }

    public function testAHarmlessMailIsShownAsEscapedText(): void
    {
        $content = (string) $this->controller()->show('ok1')->getContent();

        self::assertStringContainsString('Facture FA-1', $content);
        self::assertStringContainsString('&lt;script&gt;', $content);
        self::assertStringNotContainsString('<script>', $content);
    }

    public function testAnUnknownMailIs404(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->controller()->show('missing');
    }

    public function testMailpitDownGivesAClearMessageNotAnError(): void
    {
        $response = $this->controller(down: true)->list();

        self::assertSame(503, $response->getStatusCode());
        self::assertStringContainsString('UNAVAILABLE', (string) $response->getContent());
        self::assertSame(503, $this->controller(down: true)->show('ok1')->getStatusCode());
    }

    private function controller(bool $demoMode = true, string $apiUrl = 'http://mailpit:8025', bool $down = false): DemoMailsController
    {
        $messages = [
            'reset1' => ['Subject' => 'Reset your password', 'Text' => 'hi'],
            'ok1' => ['Subject' => 'Facture FA-1', 'Text' => '<script>alert(1)</script>'],
        ];
        $http = new MockHttpClient(static function (string $method, string $url) use ($messages, $down): MockResponse {
            if ($down) {
                return new MockResponse('', ['error' => 'refused']);
            }
            if (str_contains($url, '/api/v1/messages')) {
                return new MockResponse((string) json_encode(['messages' => array_map(static fn (string $id): array => ['ID' => $id], array_keys($messages))]));
            }
            $id = basename($url);

            return isset($messages[$id]) ? new MockResponse((string) json_encode($messages[$id])) : new MockResponse('{}', ['http_code' => 404]);
        });

        $twig = new Environment(new ArrayLoader([
            'demo/mails.html.twig' => '{% if unavailable %}UNAVAILABLE{% endif %}{% for m in messages %}{{ m.subject }}{% endfor %}',
            'demo/mail.html.twig' => '{{ message.subject }} {{ message.displayText }}',
        ]), ['autoescape' => 'html']);
        $twig->addFunction(new TwigFunction('path', static fn (): string => '/x'));

        return new DemoMailsController($twig, new DemoMailbox(new MailpitClient($http, $apiUrl), new SensitiveMailFilter()), $demoMode);
    }
}
