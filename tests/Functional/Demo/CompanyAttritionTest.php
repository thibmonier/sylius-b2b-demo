<?php

declare(strict_types=1);

namespace App\Tests\Functional\Demo;

/**
 * US-220 (T-220-04): the predictive-analytics plugin is wired in the demo (README step 4) and the fixtures give it one
 * company at risk, whatever the day: IndusPro orders every 30 days and has been silent for about 70.
 */
final class CompanyAttritionTest extends DemoAdminWebTestCase
{
    public function testTheCompanyPageOfIndusProShowsAnAtRiskStatusAndItsReason(): void
    {
        $client = $this->sophieClient('fr_FR');
        $companyId = $this->intValue("SELECT id FROM tmonier_company WHERE code = 'INDUSPRO'");

        $crawler = $client->request('GET', '/admin/companies/' . $companyId . '/edit');

        self::assertResponseIsSuccessful();
        self::assertSame('À risque', trim($crawler->filter('[data-test="attrition-status"]')->text()));
        self::assertMatchesRegularExpression('/commande tous les 30 jours, rien depuis (69|70|71) jours/u', $this->text($crawler->filter('[data-test="attrition-reason"]')));
        self::assertStringContainsString('Indicatif', $crawler->filter('[data-test="attrition-indicative"]')->text());
    }

    public function testTheOrderingRhythmFilterOfTheCompanyListReturnsIndusPro(): void
    {
        $client = $this->sophieClient('fr_FR');

        $crawler = $client->request('GET', '/admin/companies/', ['criteria' => ['attrition' => 'at_risk']]);

        self::assertResponseIsSuccessful();
        $text = $this->text($crawler);
        self::assertStringContainsString('IndusPro SARL', $text);
        self::assertStringNotContainsString('Acme Industries', $text);
        self::assertStringNotContainsString('TechParts SAS', $text);
    }

    public function testTheFilterOffersTheThreeStatuses(): void
    {
        $client = $this->sophieClient('fr_FR');

        $crawler = $client->request('GET', '/admin/companies/');

        self::assertResponseIsSuccessful();
        $options = $crawler->filter('select[name="criteria[attrition]"] option')->each(static fn ($option): string => trim($option->text()));
        self::assertContains('À risque', $options);
        self::assertContains('Critique', $options);
        self::assertContains('Actif', $options);
    }
}
