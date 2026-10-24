<?php

declare(strict_types=1);

namespace App\Tests\Unit\Demo\Packs;

/**
 * US-178: a small pack, as its repository would publish it (composer.json and the two READMEs), and the data file entry
 * the refresh must produce from them. Shared by the unit tests and by the tests of the page.
 *
 * @phpstan-type Localized array{en: string, fr: string}
 * @phpstan-type PackShape array{composer: string, command: string, title: Localized, summary: Localized, plugins: list<array{slug: string, bound: string, role: Localized}>, suggests: list<array{slug: string, reason: Localized}>, steps: list<array{slug: string, bundles: list<string>, config: list<string>, routes: list<string>, migrations: bool, other: Localized}>, source: string, composer_source: string, checked_at: string, fingerprint: string}
 */
final class PackSamples
{
    public const REPOSITORY = 'sylius-pack-ai-headless';

    public const OTHER_REPOSITORY = 'sylius-pack-compliance-tooling';

    public const FEEDS = 'sylius-agentic-feeds-plugin';

    public const OUTBOX = 'sylius-webhook-outbox-plugin';

    public const AUDIT = 'sylius-audit-log-plugin';

    public static function composer(): string
    {
        return (string) json_encode([
            'name' => 'tmonier/sylius-pack-ai-headless',
            'type' => 'metapackage',
            'require' => ['tmonier/sylius-agentic-feeds-plugin' => '^1.0', 'tmonier/sylius-webhook-outbox-plugin' => '^1.0'],
            'suggest' => ['tmonier/sylius-audit-log-plugin' => 'Journal of admin changes'],
        ], \JSON_UNESCAPED_SLASHES);
    }

    public static function readmeEn(): string
    {
        return <<<'MD'
            # Sylius Pack: AI and headless

            **Two Sylius 2 plugins for AI assistants, installed with one `composer require`.**

            ## Installation

            ```bash
            composer require tmonier/sylius-pack-ai-headless
            ```

            ## Plugins in the pack

            | Plugin | Bound | Role |
            |--------|-------|------|
            | [sylius-agentic-feeds-plugin](https://github.com/thibmonier/sylius-agentic-feeds-plugin) | `^1.0` | Product feeds for AI assistants |
            | [sylius-webhook-outbox-plugin](https://github.com/thibmonier/sylius-webhook-outbox-plugin) | `^1.0` | Outgoing webhooks with retries |

            Suggested, not installed (`suggest`):

            | Plugin | Why |
            |--------|-----|
            | [sylius-audit-log-plugin](https://github.com/thibmonier/sylius-audit-log-plugin) | Records the product change made when a suggestion is accepted. |

            ## What is left to do by hand

            | Plugin | Bundles to register in `config/bundles.php` | Configuration (`config/packages/`) | Routes (`config/routes/`) | Migrations | Other |
            |--------|----------------------------------------------|------------------------------------|---------------------------|------------|-------|
            | agentic-feeds | `Tmonier\SyliusAgenticFeedsPlugin\TmonierSyliusAgenticFeedsPlugin` | `@TmonierSyliusAgenticFeedsPlugin/config/config.yaml` | `@TmonierSyliusAgenticFeedsPlugin/config/routes.yaml` | yes | schedule `tmonier:feeds:generate` (cron) |
            | webhook-outbox | `Tmonier\SyliusWebhookOutboxPlugin\TmonierSyliusWebhookOutboxPlugin` | `@TmonierSyliusWebhookOutboxPlugin/config/config.yaml` | `@TmonierSyliusWebhookOutboxPlugin/config/routes.yaml` | no | none |
            MD;
    }

    public static function readmeFr(): string
    {
        return <<<'MD'
            # Sylius Pack : IA et headless

            **Deux plugins Sylius 2 pour les assistants IA, installés avec un seul `composer require`.**

            ## Installation

            ```bash
            composer require tmonier/sylius-pack-ai-headless
            ```

            ## Plugins du pack

            | Plugin | Borne | Rôle |
            |--------|-------|------|
            | [sylius-agentic-feeds-plugin](https://github.com/thibmonier/sylius-agentic-feeds-plugin) | `^1.0` | Flux produits pour les assistants IA |
            | [sylius-webhook-outbox-plugin](https://github.com/thibmonier/sylius-webhook-outbox-plugin) | `^1.0` | Webhooks sortants avec nouvelles tentatives |

            Suggéré, non installé (`suggest`) :

            | Plugin | Pourquoi |
            |--------|----------|
            | [sylius-audit-log-plugin](https://github.com/thibmonier/sylius-audit-log-plugin) | Consigne la modification faite à l'acceptation d'une suggestion. |

            ## Ce qui reste à faire à la main

            | Plugin | Bundles à déclarer dans `config/bundles.php` | Configuration (`config/packages/`) | Routes (`config/routes/`) | Migrations | Autre |
            |--------|-----------------------------------------------|------------------------------------|---------------------------|------------|-------|
            | agentic-feeds | `Tmonier\SyliusAgenticFeedsPlugin\TmonierSyliusAgenticFeedsPlugin` | `@TmonierSyliusAgenticFeedsPlugin/config/config.yaml` | `@TmonierSyliusAgenticFeedsPlugin/config/routes.yaml` | oui | planifier `tmonier:feeds:generate` (cron) |
            | webhook-outbox | `Tmonier\SyliusWebhookOutboxPlugin\TmonierSyliusWebhookOutboxPlugin` | `@TmonierSyliusWebhookOutboxPlugin/config/config.yaml` | `@TmonierSyliusWebhookOutboxPlugin/config/routes.yaml` | non | aucun |
            MD;
    }

    /** @return PackShape the entry of the data file for the sample pack, as the refresh writes it */
    public static function pack(): array
    {
        return [
            'composer' => 'tmonier/sylius-pack-ai-headless',
            'command' => 'composer require tmonier/sylius-pack-ai-headless',
            'title' => ['en' => 'AI and headless', 'fr' => 'IA et headless'],
            'summary' => [
                'en' => 'Two Sylius 2 plugins for AI assistants, installed with one `composer require`.',
                'fr' => 'Deux plugins Sylius 2 pour les assistants IA, installés avec un seul `composer require`.',
            ],
            'plugins' => [
                ['slug' => self::FEEDS, 'bound' => '^1.0', 'role' => ['en' => 'Product feeds for AI assistants', 'fr' => 'Flux produits pour les assistants IA']],
                ['slug' => self::OUTBOX, 'bound' => '^1.0', 'role' => ['en' => 'Outgoing webhooks with retries', 'fr' => 'Webhooks sortants avec nouvelles tentatives']],
            ],
            'suggests' => [
                ['slug' => self::AUDIT, 'reason' => ['en' => 'Records the product change made when a suggestion is accepted.', 'fr' => "Consigne la modification faite à l'acceptation d'une suggestion."]],
            ],
            'steps' => [
                [
                    'slug' => self::FEEDS,
                    'bundles' => ['Tmonier\SyliusAgenticFeedsPlugin\TmonierSyliusAgenticFeedsPlugin'],
                    'config' => ['@TmonierSyliusAgenticFeedsPlugin/config/config.yaml'],
                    'routes' => ['@TmonierSyliusAgenticFeedsPlugin/config/routes.yaml'],
                    'migrations' => true,
                    'other' => ['en' => 'schedule `tmonier:feeds:generate` (cron)', 'fr' => 'planifier `tmonier:feeds:generate` (cron)'],
                ],
                [
                    'slug' => self::OUTBOX,
                    'bundles' => ['Tmonier\SyliusWebhookOutboxPlugin\TmonierSyliusWebhookOutboxPlugin'],
                    'config' => ['@TmonierSyliusWebhookOutboxPlugin/config/config.yaml'],
                    'routes' => ['@TmonierSyliusWebhookOutboxPlugin/config/routes.yaml'],
                    'migrations' => false,
                    'other' => ['en' => 'none', 'fr' => 'aucun'],
                ],
            ],
            'source' => 'https://github.com/thibmonier/sylius-pack-ai-headless/blob/main/README.md',
            'composer_source' => 'https://github.com/thibmonier/sylius-pack-ai-headless/blob/main/composer.json',
            'checked_at' => '2026-10-24',
            'fingerprint' => 'a',
        ];
    }

    /** @return PackShape a second pack, so that the page lists more than one */
    public static function otherPack(): array
    {
        $pack = self::pack();
        $pack['composer'] = 'tmonier/sylius-pack-compliance-tooling';
        $pack['command'] = 'composer require tmonier/sylius-pack-compliance-tooling';
        $pack['title'] = ['en' => 'Compliance and tooling', 'fr' => 'Conformité et outillage'];
        $pack['plugins'] = [['slug' => 'sylius-gpsr-plugin', 'bound' => '^1.0', 'role' => ['en' => 'Product safety information', 'fr' => 'Informations de sécurité des produits']]];
        $pack['suggests'] = [];
        $pack['steps'] = [[
            'slug' => 'sylius-gpsr-plugin',
            'bundles' => ['Tmonier\SyliusGpsrPlugin\TmonierSyliusGpsrPlugin'],
            'config' => ['@TmonierSyliusGpsrPlugin/config/config.yaml'],
            'routes' => ['@TmonierSyliusGpsrPlugin/config/routes.yaml'],
            'migrations' => true,
            'other' => ['en' => 'none', 'fr' => 'aucun'],
        ]];
        $pack['source'] = 'https://github.com/thibmonier/sylius-pack-compliance-tooling/blob/main/README.md';
        $pack['composer_source'] = 'https://github.com/thibmonier/sylius-pack-compliance-tooling/blob/main/composer.json';
        $pack['fingerprint'] = 'b';

        return $pack;
    }

    /** @return array{generated_at: string, packs: array<string, PackShape>} the whole data file for the two sample packs */
    public static function data(): array
    {
        return [
            'generated_at' => '2026-10-24T10:00:00Z',
            'packs' => [self::REPOSITORY => self::pack(), self::OTHER_REPOSITORY => self::otherPack()],
        ];
    }
}
