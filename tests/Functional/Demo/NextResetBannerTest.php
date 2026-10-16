<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Reset\NextResetProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\MockClock;
use Twig\Environment;

/** US-041 / T-033-05: the demo banner announces the next reset, in French and in English. */
final class NextResetBannerTest extends WebTestCase
{
    /** @return iterable<string, array{string, string, string}> */
    public static function moments(): iterable
    {
        yield 'before 03:00' => ['2026-10-16 01:15:00 Europe/Paris', "aujourd'hui 03:00 (heure de Paris)", 'today 03:00 (Paris time)'];
        yield 'after 03:00' => ['2026-10-16 09:00:00 Europe/Paris', 'demain 03:00 (heure de Paris)', 'tomorrow 03:00 (Paris time)'];
    }

    #[DataProvider('moments')]
    public function testThePartialAnnouncesTheNextResetInBothLanguages(string $now, string $french, string $english): void
    {
        self::bootKernel();
        $twig = self::getContainer()->get('twig');
        self::assertInstanceOf(Environment::class, $twig);

        $html = $twig->render('demo/_next_reset.html.twig', ['reset' => (new NextResetProvider(new MockClock($now)))->next()]);

        $text = trim((string) preg_replace('/\s+/', ' ', strip_tags($html)));
        self::assertStringContainsString('Prochaine remise à zéro : ' . $french, $text);
        self::assertStringContainsString('Next reset: ' . $english, $text);
    }

    public function testTheBannerOfTheShopCarriesIt(): void
    {
        $client = self::createClient();

        $client->request('GET', '/fr_FR/');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('[data-demo-banner] [data-demo-next-reset]');
        self::assertSelectorTextContains('[data-demo-next-reset]', '03:00');
    }
}
