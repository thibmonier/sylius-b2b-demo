# Audit inter-sprint (entre sprint 4 et sprint 5) — score global : 60/100

- Date : 2026-10-16 · Commande : `/team:audit` · Base : `main` @ `a833061` (sprints 1–4 mergés)
- Stack : Symfony / Sylius 2.3 (seul stack ; front = 4 JS Stimulus + 2 SCSS). Projet mono-stack : audit parallélisé **par dimension** (4 auditeurs, lecture seule) plutôt que par stack.
- Détail par dimension : `architecture/result.json`, `code-quality/result.json`, `testing/result.json`, `security/result.json`.

| Dimension | Score | Constats | Lecture |
|-----------|------:|---------:|---------|
| Architecture | 17/25 | 13 | Saine pour une démo (ajouts par twig hooks, fixtures via interfaces des plugins, décorateur Factur-X). Pénalisée par des contournements à retirer et un ADR décalé. |
| Qualité du code | 10/25 | 14 | **Outillage 100 % vert** (PHPStan niveau 9, ECS, lint twig/container/yaml, FR/EN à parité, strict_types partout). Le score vient du barème : 15 méthodes > 30 lignes (−15), surtout fixtures et import CSV. |
| Tests | 15/25 | 9 | 107 tests unitaires (2 s) + 47 smoke. **Couverture jamais mesurée** (aucun driver, ni local ni CI) : estimée 45–55 %. |
| Sécurité | 18/25 | 13 | Aucun secret, 0 CVE PHP, `DEMO_MODE` à 0 par défaut. 1 CVE JS haute de build uniquement. En-têtes et durcissement restants = sprint 6. |
| **Total** | **60/100** | 49 | |

## Constats majeurs

**Architecture**
- `resolve_target_entities` (config/packages/doctrine.yaml:13) et lecture des notes de commande (src/PunchOut/PunchOutPoReferenceSubscriber.php:77) : contournements d'un défaut du plugin punchout → retirés par le **patch punchout du sprint 7**.
- ADR-0005 (badges) en décalage avec l'implémentation → à mettre à jour.

**Qualité du code**
- Méthodes longues : `ProductCsvFixture::createOrUpdateVariant` (74 lignes, cc≈17, classe 294 lignes), `OrderPoReferenceTypeExtension::buildForm` (62), `CsvProductReader::parseHandle` (52, cc≈13), + 12 autres.

**Tests**
- Couverture non mesurée (ci.yaml:30 `coverage: none`).
- Classes de logique sans test (ex. `ChannelSwitchController`).
- Tests de configuration plutôt que de rendu restants (ex. `tests/Unit/Theme/ThemeHooksTest.php`).
- Smoke tests non hermétiques (données de fixtures en dur).
- CI : pas de `doctrine:schema:validate`, `composer install --no-scripts`, PHP 8.4 en CI/image vs 8.5 en local.

**Sécurité**
- [Haute, build seulement] `braces` (ReDoS, sans correctif) via `@sylius-ui/admin > sass > chokidar` : pas exécuté en production ; surveiller la mise à jour amont.
- [Haute] En-têtes CSP / COOP / COEP / CORP absents (docker/Caddyfile:9).
- [Moyenne] `remember_me` d'un an, pas de limitation des tentatives de login ; hasher `auto` (Argon2id implicite).
- [Moyenne] Identifiants admin publics (garde = `DEMO_MODE`) ; inscription/checkout publics sans anti-abus ni mention RGPD.
- [Moyenne] Images Docker non épinglées par digest ; actions GitHub par tag ; pas de scan d'image, de SBOM ni de signature.

## Top 5 des actions prioritaires

| # | Priorité | Action | Impact | Effort | Où |
|---|----------|--------|--------|--------|----|
| 1 | Élevée | Mesurer la couverture : driver `pcov` en CI (`coverage: pcov`) et seuil affiché ; ajouter `doctrine:schema:validate --skip-sync` en CI | Tests +5 | Faible | **Sprint 5 (tâche technique)** |
| 2 | Élevée | En-têtes CSP/COOP/COEP/CORP, remember-me 30 j, limitation des tentatives de login, admin de démo restreint, avertissement RGPD | Sécurité +5 | Moyen | Sprint 6 (US-033/035/036) |
| 3 | Moyenne | Découper les méthodes > 30 lignes (fixtures, CSV reader, form extension) | Qualité +10 | Moyen | Au fil de l'eau (règle du boy-scout) |
| 4 | Moyenne | Retirer les contournements punchout (patch plugin : cibler les interfaces, événement à la création de commande) | Architecture +3 | Moyen | Sprint 7 |
| 5 | Moyenne | Tests de rendu à la place des tests de configuration ; smoke tests hermétiques ; aligner PHP 8.4/8.5 | Tests +3 | Moyen | Sprint 5–6 |

## Métriques d'exécution

| Métrique | Valeur |
|----------|--------|
| Auditeurs | 4 (parallèles, lecture seule), 4/4 terminés, 0 échec |
| Durée | ~2,5 min (le plus long : sécurité, 133 s) |
| Coût estimé | ~2,15 $ (vs ~2,31 $ séquentiel, estimateur AgentTeams) |
