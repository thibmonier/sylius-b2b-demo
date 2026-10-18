<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

use App\Demo\Reset\NextResetProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Clock\MockClock;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Twig\Environment;

/** US-041 / T-033-05: the demo banner announces the next reset, in the visitor's language only (US-076). */
final class NextResetBannerTest extends WebTestCase
{
    /** @return iterable<string, array{string, string, string, string}> */
    public static function moments(): iterable
    {
        yield 'French before 03:00' => ['fr_FR', '2026-10-16 01:15:00 Europe/Paris', "Prochaine remise à zéro : aujourd'hui 03:00 (heure de Paris)", 'Next reset'];
        yield 'French after 03:00' => ['fr_FR', '2026-10-16 09:00:00 Europe/Paris', 'Prochaine remise à zéro : demain 03:00 (heure de Paris)', 'Next reset'];
        yield 'English before 03:00' => ['en_US', '2026-10-16 01:15:00 Europe/Paris', 'Next reset: today 03:00 (Paris time)', 'Prochaine'];
        yield 'English after 03:00' => ['en_US', '2026-10-16 09:00:00 Europe/Paris', 'Next reset: tomorrow 03:00 (Paris time)', 'Prochaine'];
    }

    #[DataProvider('moments')]
    public function testThePartialAnnouncesTheNextResetInOneLanguage(string $locale, string $now, string $expected, string $forbidden): void
    {
        self::bootKernel();
        $translator = self::getContainer()->get('translator');
        self::assertInstanceOf(LocaleAwareInterface::class, $translator);
        $translator->setLocale($locale);
        $twig = self::getContainer()->get('twig');
        self::assertInstanceOf(Environment::class, $twig);

        $html = $twig->render('demo/_next_reset.html.twig', ['reset' => (new NextResetProvider(new MockClock($now)))->next()]);

        $text = trim((string) preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html), \ENT_QUOTES)));
        self::assertSame($expected, $text);
        self::assertStringNotContainsString($forbidden, $text);
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
