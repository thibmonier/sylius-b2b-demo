<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Mail;

use App\Demo\Account\DemoAccountCatalog;
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
        self::assertStringContainsString('Invoice generated', $html);
        self::assertStringNotContainsString('Reset your password', $html);

        $this->expectException(NotFoundHttpException::class);
        $controller->show('reset1');
    }

    public function testAMailSentToAVisitorAddressIsNeitherListedNorReadableById(): void
    {
        $controller = $this->controller();

        self::assertStringNotContainsString('Invoice generated visitor', (string) $controller->list()->getContent());

        $this->expectException(NotFoundHttpException::class);
        $controller->show('visitor1');
    }

    public function testAMailToAnAdministratorIsNeitherListedNorReadableById(): void
    {
        $controller = $this->controller();

        self::assertStringNotContainsString('Admin copy', (string) $controller->list()->getContent());

        $this->expectException(NotFoundHttpException::class);
        $controller->show('admin1');
    }

    public function testAMailCopiedToAnAddressOutsideTheListIsNeitherListedNorReadableById(): void
    {
        $controller = $this->controller(messageIds: ['cc1', 'bcc1', 'cc2']);

        self::assertSame(1, substr_count((string) $controller->list()->getContent(), 'Invoice generated'), 'only the mail copied to a shop account stays');
        self::assertSame(200, $controller->show('cc2')->getStatusCode());
        foreach (['cc1', 'bcc1'] as $id) {
            try {
                $controller->show($id);
                self::fail($id . ' must be refused');
            } catch (NotFoundHttpException) {
                self::addToAssertionCount(1);
            }
        }
    }

    public function testAMailOfAnUnlistedTypeIsNeitherListedNorReadableById(): void
    {
        $controller = $this->controller();

        self::assertStringNotContainsString('Brand new mail type', (string) $controller->list()->getContent());

        $this->expectException(NotFoundHttpException::class);
        $controller->show('type1');
    }

    public function testTheMailboxIsEmptyWhenNoMailIsWhitelisted(): void
    {
        $content = (string) $this->controller(messageIds: ['admin1', 'type1', 'visitor1'])->list()->getContent();

        self::assertStringNotContainsString('Admin copy', $content);
        self::assertStringNotContainsString('Brand new mail type', $content);
        self::assertStringNotContainsString('Invoice generated', $content);
    }

    public function testAHarmlessMailIsShownAsEscapedText(): void
    {
        $content = (string) $this->controller()->show('ok1')->getContent();

        self::assertStringContainsString('Invoice generated', $content);
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

    /** @param list<string>|null $messageIds */
    private function controller(bool $demoMode = true, string $apiUrl = 'http://mailpit:8025', bool $down = false, ?array $messageIds = null): DemoMailsController
    {
        $messages = [
            'reset1' => ['Subject' => 'Reset your password', 'Text' => 'hi', 'To' => [['Name' => '', 'Address' => 'claire@acme.example']]],
            'ok1' => ['Subject' => 'Invoice generated', 'Text' => '<script>alert(1)</script>', 'To' => [['Name' => 'Claire', 'Address' => 'Claire@Acme.example']]],
            'admin1' => ['Subject' => 'Admin copy', 'Text' => 'hi', 'To' => [['Name' => '', 'Address' => 'sophie@fournitures-pro.example']]],
            'cc1' => ['Subject' => 'Invoice generated', 'Text' => 'hi', 'To' => [['Name' => '', 'Address' => 'claire@acme.example']], 'Cc' => [['Name' => '', 'Address' => 'sophie@fournitures-pro.example']]],
            'bcc1' => ['Subject' => 'Invoice generated', 'Text' => 'hi', 'To' => [['Name' => '', 'Address' => 'claire@acme.example']], 'Bcc' => [['Name' => '', 'Address' => 'visitor@real-company.test']]],
            'cc2' => ['Subject' => 'Invoice generated', 'Text' => 'hi', 'To' => [['Name' => '', 'Address' => 'claire@acme.example']], 'Cc' => [['Name' => '', 'Address' => 'claire@acme.example']]],
            'type1' => ['Subject' => 'Brand new mail type', 'Text' => 'hi', 'To' => [['Name' => '', 'Address' => 'claire@acme.example']]],
            'visitor1' => ['Subject' => 'Invoice generated visitor', 'Text' => 'hello', 'To' => [['Name' => '', 'Address' => 'visitor@real-company.test']]],
        ];
        $http = new MockHttpClient(static function (string $method, string $url) use ($messages, $down, $messageIds): MockResponse {
            if ($down) {
                return new MockResponse('', ['error' => 'refused']);
            }
            if (str_contains($url, '/api/v1/messages')) {
                return new MockResponse((string) json_encode(['messages' => array_map(static fn (string $id): array => ['ID' => $id], $messageIds ?? array_keys($messages))]));
            }
            $id = basename($url);

            return isset($messages[$id]) ? new MockResponse((string) json_encode($messages[$id])) : new MockResponse('{}', ['http_code' => 404]);
        });

        $twig = new Environment(new ArrayLoader([
            'demo/mails.html.twig' => '{% if unavailable %}UNAVAILABLE{% endif %}{% for m in messages %}{{ m.subject }}{% endfor %}',
            'demo/mail.html.twig' => '{{ message.subject }} {{ message.displayText }}',
        ]), ['autoescape' => 'html']);
        $twig->addFunction(new TwigFunction('path', static fn (): string => '/x'));

        return new DemoMailsController($twig, new DemoMailbox(new MailpitClient($http, $apiUrl), new SensitiveMailFilter(), self::catalog(), ['/^Invoice generated/', '/^La commande n° \\d+ attend votre approbation$/']), $demoMode);
    }

    private static function catalog(): DemoAccountCatalog
    {
        return new DemoAccountCatalog(
            'pw',
            [['code' => 'ACME', 'name' => 'Acme', 'members' => [['email' => 'claire@acme.example', 'first_name' => 'Claire', 'last_name' => 'D', 'role' => 'buyer']]]],
            [],
            [['email' => 'sophie@fournitures-pro.example', 'password' => 'x', 'first_name' => 'Sophie', 'last_name' => 'M']],
        );
    }
}
