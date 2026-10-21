# Dépendances : Sprint 018 (repositionnement)

## Entre stories et avec les sprints précédents

```mermaid
graph TD
    S17["Sprint 17 (conversion) : rétro, revue, dettes"]
    PHP["Faille PHP du 2026-10-21 + image FrankenPHP 8.5 corrigée"]
    URL["URL du profil LinkedIn (Thibaut)"]
    US161["US-161 Actions de la rétro S17 (11 h)"]
    US160["US-160 Dettes du sprint 17 (10 h)"]
    US158["US-158 Repositionnement de la démo (12 h, reste 4 h)"]
    US147["US-147 Démo en PHP 8.5 (8 h)"]
    US148["US-148 Plugins RateLimiterFactoryInterface (9 h)"]
    US159["US-159 Calendrier et posts de suite (9 h)"]
    US124["US-124 Posts, lot 2 (10,75 h)"]
    US111["US-111 Preuve technique (7,5 h)"]
    US103["US-103 Assistants IA (7,25 h)"]
    US068["US-068 Liens dans 24 README (6,5 h)"]
    S19["Sprint 19 : US-121 (kit média 2/2), company-insights"]
    S17 --> US161
    S17 --> US160
    US161 -->|"verrou, check-disk, isolation"| US160
    US161 -->|"fiabilité des tests"| US158
    PHP --> US147
    US147 -->|"composer.lock"| US148
    US158 -->|"fichiers partagés libérés"| US160
    US158 -->|"libellés et CTA neutres"| US111
    US158 -->|"libellé du CTA"| US068
    URL --> US158
    URL --> US068
    US161 -->|"registre INDEX"| US159
    US159 -->|"calendrier réaligné"| US124
    US103 -->|"avant"| S19
```

## Entre tâches (vue transversale)

```mermaid
graph TD
    T15808["T-158-08 revue US-158"] --> T16003["T-160-03 tests rouges GET"]
    T15808 --> T16005["T-160-05 tests rouges menu compte"]
    T15808 --> T11103["T-111-03 service et page de preuve"]
    T15809["T-158-09 URL du profil (Thibaut)"] --> T06802["T-068-02 outil des README"]
    T14703["T-147-03 composer.lock en PHP 8.5"] --> T14811["T-148-11 démo : plugins montés"]
    T14810["T-148-10 tags des 5 plugins"] --> T14811
    T16002["T-160-02 verrou atomique"] --> T16102["T-161-02 check-disk dans run-tests"]
    T16102 --> T16108["T-161-08 option --wait"]
    T16111["T-161-11 registre INDEX"] --> T15909["T-159-09 INDEX à jour"]
    T15907["T-159-07 calendrier réaligné"] --> T12401["T-124-01 angles du lot 2"]
    T15905["T-159-05 post 1 publié avant le 2026-10-26 (Thibaut)"] -.->|"retours"| T12405["T-124-05 chiffres semaines 1 et 2 (Thibaut)"]
    T14705["T-147-05 image reconstruite"] --> TTECH01["T-TECH-01 image avant la revue"]
```

## Chemin critique

Trois chemins. **Calendaire (2026-10-26)** : T-159-01 → T-159-02 → T-159-03 → T-159-04 → T-159-05 (post 1 : 3,75 h dont 1 h de Thibaut), à faire dès la décision de Thibaut. **Plateforme (faille de PHP)** : T-147-01 (veille) → T-147-03 → T-147-05 → T-147-08 ; la veille programmée réalise T-147-01 à T-147-04 dès que l'image corrigée est publiée. **Développement** : T-160-01 → T-160-02 → T-161-02 → T-161-08 (outillage, ≈ 4,5 h en série) puis T-158-05 → T-158-08 → T-160-03 → T-160-04 → T-160-07 (≈ 6 h) ; US-111 et US-103 en fin de piste, sacrifiables. Les actions de Thibaut (publication, GO, URL, Railway) sortent du chemin critique de développement mais pas du chemin calendaire.

## Ressources partagées

- `bin/run-tests` : T-160-02, puis T-161-02, puis T-161-08 (un seul écrivain à la fois) ; `bin/clean-test-caches`, `bin/check-disk` et `docs/tests-locaux.md` : US-161 seule.
- Code non commité d'US-158 : `src/Demo/Site/`, `templates/shared/_contact_cta.html.twig`, `templates/demo/integrate.html.twig`, `config/demo/journeys.yaml`, traductions `integrate`, `journeys`, `messages`, `seo`, `shop_layout`, `theme_demo`, tests `ContactCta`, `IntegratePage`, `Journey` : US-158 seule jusqu'à T-158-08, puis US-160 (parcours, menu compte) et US-111 (page de preuve), un worker à la fois.
- `composer.json`, `composer.lock`, `docker/Dockerfile`, `.github/workflows/ci.yaml` : US-147 puis US-148 (T-148-11) ; l'image n'est reconstruite que par l'orchestrateur, un build à la fois.
- Dépôts de plugins : US-148 (cinq dépôts, un worker par dépôt) puis US-068 (24 dépôts, README seulement) ; deux workers ne touchent jamais le même dépôt en même temps ; `git fetch` et branche courante vérifiés.
- `bin/vitrine-readme` et `src/Demo/Vitrine/` : US-068 seule.
- Page et contrôleur Assistants IA, traductions communes : US-103 après US-111 (un seul worker sur les traductions partagées).
- `/Users/tmonier/Projects/sylius_plugins/communication/sprint-18/` : US-159 puis US-124 (calendrier lu par US-124 après sa réécriture) ; `communication/INDEX.md` : US-161 (création), puis US-159 et US-124 (mise à jour).
- `.bmad/sprint-status.yaml` : orchestrateur seulement (écrivain unique).
- Actions de Thibaut : URL du profil, relectures, publications, GO (plugins, README, merges), Railway, conversation avec l'assistant.
