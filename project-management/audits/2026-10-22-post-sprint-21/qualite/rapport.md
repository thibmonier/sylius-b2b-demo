# Audit qualité après le sprint 21 (2026-10-22)

Base : `main` @ `4a4dcfc` (Sylius 2.3.2, Symfony 8.1, PHP 8.5). Lecture seule. Comparaison : audit du 2026-10-18.

## Score : 67/100 (50/75)

| Catégorie | Score |
|---|---|
| Architecture | 15/25 |
| Qualité du code | 15/25 |
| Tests | 20/25 |

Le 2026-10-18 : 45/100 au barème. Le gain vient surtout de la preuve de tests et de l'absence de régression d'outillage ; la plupart des dettes d'architecture n'ont pas bougé.

## Mesures

- PHPStan niveau 9 : 0 erreur. ECS : 0 erreur. `lint:container`, `lint:twig` (98 fichiers), `lint:yaml` (94 fichiers) : OK. 871 lignes de `debug:router`.
- Rector en dry-run : 78 fichiers modifiables (61 le 18/10), toujours absent de la CI.
- 11 classes de plus de 200 lignes ; 22 méthodes de plus de 30 lignes (83 de plus de 20).
- Tests exécutés une fois (`bin/run-tests`, worker `wa`, disque à 9,6 Go) : 1787 tests, 12483 assertions, 1 skipped, 212 deprecations, OK en 6 min 30. CI de main verte. Nettoyage fait avec `bin/clean-test-caches`.

## Dettes du 2026-10-18

| Dette | État |
|---|---|
| flush() dans CompanyWishlistController | Corrigée (US-165) |
| Simulateur PunchOut dépendant de PunchoutDemoFixture | Corrigée (US-165, codes acheteurs ; le secret de démo reste lu dans la fixture) |
| Logique de réception dans WebhookReceiverController | Partielle (29 lignes, 101 au total) |
| cartFromState dans le contrôleur du simulateur | Non corrigée |
| Vitrine et Images/Fetch dans src/ avec exclusions | Non corrigée |
| Rector hors CI | Non corrigée |
| Méthodes de plus de 30 lignes | Quasi inchangé (23 puis 22) |
| Wishlist sans test dédié | Corrigée (US-165, test du service d’écriture) |
| Couverture sans seuil | Non corrigée |
| usleep dans les tests | Non corrigée (7) |
| Test légal sauté sans variable | Non corrigée |

## Autres constats

- Code mort : `StandardBlock::isInDemo()` et la branche « absent de la démo » (`ReadmeApplier`, `ReadmeChecker`, `render($inDemo)`).
- Cohérence vitrine : `plugins.yaml`, `proof.json`, `composer.json` listent les mêmes 25 plugins. Le compteur de tests de `proof.json` (161) est en retard sur la réalité (188 fichiers).
- Duplication des fixtures : lookups répétés dans 20 fixtures, sans résolveur partagé.
- Scripts `bin/` : les scripts shell utilisent `set -eu` et sont testés ; les scripts PHP et Node n'ont pas de garde équivalente.
- Code des sprints 18 à 21 : bien testé (Proof, Mcp, BulkOrderWriter, scripts bin, parc d'équipements).

## Actions prioritaires

1. Sortir `flush()` du contrôleur wishlist et ajouter son test (0,5 j ; ferme deux dettes, architecture et tests).
2. Extraire les codes acheteurs du simulateur vers une constante neutre (0,25 j ; corrige l'inversion de dépendance).
3. Supprimer `isInDemo` et les branches mortes de Vitrine (0,25 j ; code mort).
4. Découper les 6 méthodes de plus de 45 lignes, puis ProofCollector et BulkOrderWriter (1 à 2 j ; qualité).
5. Rector en CI et seuil de couverture bloquant ; relancer `bin/proof-refresh` (0,5 j ; garde-fous).

## Commandes refusées ou en échec

Aucune refusée. `lint:twig`, `lint:yaml` et `debug:router` ont d'abord échoué sur la limite mémoire PHP par défaut (128 Mo), relancées avec `memory_limit=1G`. `bfs`/`rtk ls` a refusé deux commandes mal formées (sans conséquence).
