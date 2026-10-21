<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Site;

use App\Demo\Site\ContactCta;
use App\Demo\Site\SiteLinks;
use PHPUnit\Framework\TestCase;

/** US-142: the two call-to-action targets, tagged with the placement they come from, hidden when not configured. */
final class ContactCtaTest extends TestCase
{
    public function testBothTargetsCarryTheUtmOfTheirPlacement(): void
    {
        $cta = $this->cta('https://exemple.test/profil', 'https://exemple.test/contact');

        self::assertSame([
            ['kind' => 'profile', 'url' => 'https://exemple.test/profil?utm_source=demo-b2b&utm_medium=cta&utm_content=home'],
            ['kind' => 'contact', 'url' => 'https://exemple.test/contact?utm_source=demo-b2b&utm_medium=cta&utm_content=home'],
        ], $cta->targets('home'));
    }

    public function testExistingQueryAndFragmentAreKept(): void
    {
        $targets = $this->cta('https://exemple.test/profil?a=1#top', '')->targets('plugins-top');

        self::assertSame('https://exemple.test/profil?a=1&utm_source=demo-b2b&utm_medium=cta&utm_content=plugins-top#top', $targets[0]['url']);
    }

    public function testASingleConfiguredTargetIsTheOnlyOne(): void
    {
        $targets = $this->cta('', 'https://exemple.test/contact')->targets('about');

        self::assertCount(1, $targets);
        self::assertSame('contact', $targets[0]['kind']);
    }

    public function testNothingConfiguredMeansNoTargetAndNoError(): void
    {
        self::assertSame([], $this->cta('', '')->targets('home'));
    }

    public function testAnInvalidUrlIsHidden(): void
    {
        $targets = $this->cta('http://exemple.test/profil', 'https://user:pass@exemple.test/')->targets('home');

        self::assertSame([], $targets);
    }

    public function testThePlacementCannotInjectIntoTheQuery(): void
    {
        $url = $this->cta('https://exemple.test/profil', '')->targets('a&b=c d')[0]['url'];

        self::assertSame('https://exemple.test/profil?utm_source=demo-b2b&utm_medium=cta&utm_content=abcd', $url);
    }

    private function cta(string $profile, string $contact): ContactCta
    {
        return new ContactCta(new SiteLinks('', $contact, $profile));
    }
}
