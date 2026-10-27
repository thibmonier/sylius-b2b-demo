# Tâches : US-203 : Pages de compte des plugins (devis, paiements à terme, assistants IA) alignées sur la refonte « Mon compte »

## Informations US

- **Epic** : EPIC-12
- **Persona** : Claire (acheteuse), Paul (approbateur) et Isabelle (administratrice) d'Acme Industries
- **Story Points** : 5 (Should, **sacrifice : n°2**)
- **Sprint** : sprint-31-chaine-approvisionnement-et-comptes
- **Worker proposé** : w203 (orchestrateur pour la planche de maquette)
- **Story** : [US-203](../../../backlog/user-stories/US-203.md)

## Résumé de la US

**En tant qu'** utilisatrice B2B connectée  
**Je veux** que « Mes devis », « Paiements à terme » et « Assistants IA » aient le même cadre, le même menu latéral et la même charte que « Mon compte »  
**Afin de** naviguer dans mon compte sans impression de changer de site

## Règles et contraintes

- **Surcharge dans la démo** (décision de Thibaut du 2026-10-27) : sous `templates/bundles/<Bundle>/…`, **plugins intacts**, aucune release, `composer.lock` inchangé pour les plugins. Cause : ces templates étendent `@SyliusShop/shared/layout/base.html.twig` avec leur propre menu `col-md-3` au lieu de `@SyliusShop/account/common/{index,show}.html.twig` ; les hooks `sylius_shop.account.common.*.content.menu` (fichier `demo_theme_b2b_account.yaml`, `nav.html.twig`) ne se déclenchent donc pas.
- **Maquette** : les devis (liste et détail) et les assistants IA reprennent la maquette validée de « Mon compte » (US-199) : aucune nouvelle maquette. **Une seule planche nouvelle** : le relevé de paiements à terme (tableau de relevé et tranches d'ancienneté), dans le canevas unique G2StRQigRKD6SqNRoyHpkW, **validée par Thibaut (T-203-04) avant le Twig du relevé** (T-203-09, T-203-10 📅).
- **Page de consentement OAuth** : cadre B2B seul, **sans menu du compte** (décision de Thibaut, registre du 2026-10-27) ; sa logique de consentement ne change pas. T-203-08 est **négociable** (−1 h si elle sort).
- **Garde contre la dérive** : test unitaire qui compare l'empreinte (hash) de chaque template d'origine du `vendor/` à une valeur versionnée ; message d'échec explicite nommant le template à relire ; `data-test` des plugins vérifiés dans le rendu.
- Droits inchangés : devis visibles de Claire et d'Isabelle, paiements à terme et connexions selon les droits actuels, **sans élargir aucun accès** ; refus d'accès identique à celui d'avant la surcharge ; canal B2C inchangé (hooks conditionnés par `not is_granted('DEMO_CONSUMER_CHANNEL')`).
- Reprise d'US-199 : jetons de `b2b.scss`, polices auto-hébergées, AAA, cibles de 44 px, `prefers-reduced-motion`, 320 et 1280 px sans défilement horizontal de la page ; `bin/measure-mobile` à 320 et 375 px.
- Fichiers communs (règle 21) : `templates/bundles/` des trois plugins (quote-request, trade-credit, b2b-mcp) : US-203 seule ; fichier de hooks et partial SCSS propres (`config/packages/demo_theme_b2b_account_plugins.yaml`, `_account-plugins.scss`, une ligne d'import dans `b2b.scss`), domaine de traduction `account_plugins` ; `docs/theme-b2b.md` : seulement la section des pages de compte des plugins ; `docs/captures-vitrine.md` : section propre.
- Ton neutre, aucune comparaison avec Sylius Plus ; aucun identifiant saisi en production ; aucun détail de sécurité dans le dépôt public.
- Tests uniquement par `WORKER_ID=w203 TEST_DB_NAME=b2b_w203 TEST_CACHE_DIR=var/cache-w203 bin/run-tests`, dans l'arbre partagé (règle 22) ; preuve : `bin/proof-rebase`.

## Lieu de vérification

**Local et image reconstruite** pour Claire, Paul, Isabelle et Léa (B2C) : pa11y WCAG2AAA FR et EN, 320, 375 et 1280 px. **Production** (lecture seule, sans connexion) : sans objet pour les pages connectées ; relevé que les adresses renvoient vers la connexion.

## Fichiers principaux touchés

- `templates/bundles/` : `templates/bundles/<QuoteRequest|TradeCredit|B2bMcp>…` (surcharges, nouveaux fichiers), `config/packages/demo_theme_b2b_account_plugins.yaml` (nouveau)
- `assets/shop/styles/_account-plugins.scss` (nouveau) et une ligne d'import dans `b2b.scss`, `translations/account_plugins.{fr,en}.yaml`
- `tests/Unit/Demo/` (test de dérive et empreintes versionnées), `tests/Functional/Demo/` (nouveaux fichiers)
- `docs/theme-b2b.md` (section), `docs/captures-vitrine.md`, `docs/images/account-quotes-*`, `account-statement-*`, `account-connections-*`, `config/demo/proof.json` (par `bin/proof-rebase`)

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-203-01 | [DOC] | Cadrage : relire contre le code les quatre templates du `vendor/` (devis liste et détail, relevé, connexions) et la page de consentement : blocs, variables, `data-test`, hooks du compte déjà posés par US-199 ; droits par rôle ; structure du relevé (tableau, tranches d'ancienneté) ; plan des tests | 0,75 h | - | Claude | 🔲 |
| T-203-02 | [TEST] | État de référence « avant » : tests du compte et des menus verts sur `main` ; captures connectées locales (bureau et 320 px) des quatre pages et de la page de consentement pour Claire et Isabelle | 0,5 h | T-203-01, T-TECH-31-04 | Claude | 🔲 |
| T-203-03 | [FE-WEB] | **Planche de maquette du relevé** dans le canevas unique : tableau de relevé et tranches d'ancienneté dans le cadre de « Mon compte », bureau 1440 px et téléphone 390 px (et 320 px lisible sans défilement de la page), vraies données des fixtures ; table « Acquis » mise à jour | 1,25 h | T-203-01, T-TECH-31-04 | Orchestrateur | 🔲 |
| T-203-04 | [OPS] | 📅 Thibaut valide la planche (ou demande des retouches) : **aucun Twig du relevé avant son GO**, noté au registre des décisions | 0,5 h | T-203-03 | Thibaut | 📅 Thibaut |
| T-203-05 | [TEST] | **Test de dérive** : empreinte de chaque template d'origine du `vendor/` comparée à une valeur versionnée, message d'échec explicite nommant le template à relire ; `data-test` des plugins vérifiés dans le rendu ; **vérifié rouge sur une modification simulée** | 0,5 h | T-203-01 | Claude | 🔲 |
| T-203-06 | [TEST] | Tests rouges, commités seuls et cités dans la PR : devis (liste, détail) et assistants IA dans le cadre du compte (menu latéral avec l'entrée active, en-tête de la charte, pas de menu d'origine), `data-test` conservés, page de consentement sans menu du compte, B2C inchangé, refus d'accès identique, 320 px sans défilement de la page, cibles de 44 px | 0,75 h | T-203-02, T-203-05 | Claude | 🔲 |
| T-203-07 | [FE-WEB] | Coquille commune et surcharges des devis (liste, détail) et des assistants IA sous `templates/bundles/` : menu latéral sur bureau, accès compact sur téléphone, en-tête de page de la charte ; fichier de hooks et `_account-plugins.scss` propres ; AAA, `prefers-reduced-motion` ; canal B2B seulement ; `data-test` conservés | 2 h | T-203-06 | Claude | 🔲 |
| T-203-08 | [FE-WEB] | ⚠️ **Négociable** : page de consentement du plugin MCP, cadre B2B seul (en-tête, charte, polices, contrastes), sans menu latéral ; logique de consentement intacte | 0,75 h | T-203-07 | Claude | 🔲 |
| T-203-09 | [TEST] | 📅 Tests rouges du relevé, commités seuls : tableau et tranches d'ancienneté lisibles à 320 px sans défilement horizontal de la page, `data-test` conservés, droits inchangés | 0,25 h | T-203-04, T-203-07 | Claude | 📅 GO |
| T-203-10 | [FE-WEB] | 📅 Surcharge du relevé de paiements à terme selon la planche validée : tableau, tranches d'ancienneté, cadre du compte ; AAA ; 320 px | 1,25 h | T-203-09 | Claude | 📅 GO |
| T-203-11 | [DOC] | Textes FR et EN (domaine `account_plugins`), charte `PublicTextCharter` ; texte relu contre la maquette validée | 0,5 h | T-203-07 | Claude | 🔲 |
| T-203-12 | [TEST] | Suite ciblée par `bin/run-tests` (compte, menus, devis, relevé, MCP, dérive), PHPStan max sur tout le dépôt, ECS, Rector, lint Twig et YAML, pa11y local FR et EN pour les trois comptes, `bin/measure-mobile` à 320 et 375 px ; commande exacte et dernière ligne citées | 0,75 h | T-203-08, T-203-10, T-203-11 | Claude | 🔲 |
| T-203-13 | [DOC] | Captures `docs/images/account-quotes-*`, `account-statement-*`, `account-connections-*` par `bin/render-page` (connecté, local, bureau et 320 px), section de `docs/theme-b2b.md`, `docs/captures-vitrine.md` | 0,5 h | T-203-12 | Claude | 🔲 |
| T-203-14 | [TEST] | Image reconstruite et conteneur neuf : les quatre pages et la page de consentement pour Claire, Paul et Isabelle (règle 16 : rien hors base) ; production après le déploiement, lecture seule : les adresses renvoient vers la connexion ; mesures consignées le jour du déploiement | 0,5 h | merge de la PR | Claude | 📅 production |
| T-203-15 | [REV] | Revue (ton neutre, parité FR et EN, B2C inchangé, plugins intacts, aucun accès élargi) et table « scénario → test ou preuve » remplie | 0,25 h | T-203-14 | Claude | 📅 production |

**Total estimé : 11 h** (15 tâches ; 10,5 h pour Claude et l'orchestrateur, 0,5 h de Thibaut).

## Table « scénario → test ou preuve »

| Scénario | Tâche | Test ou preuve |
|----------|-------|----------------|
| Liste des devis dans le cadre du compte (nominal) | T-203-06, T-203-13 | test fonctionnel des devis (à nommer) ; capture `docs/images/account-quotes-*` |
| Détail d'un devis (alternatif) | T-203-05, T-203-06 | `data-test` conservés (à nommer) |
| Paiements à terme et assistants IA (alternatif) | T-203-06, T-203-09, T-203-12 | tests du relevé et des connexions (à nommer) ; `bin/measure-mobile` à 320 px |
| Canal B2C (alternatif) | T-203-06 | test « B2C inchangé » (à nommer) |
| Mise à jour d'un plugin (erreur) | T-203-05 | test de dérive, rouge sur modification simulée |
| Page sans droit d'accès (erreur) | T-203-06 | test de refus identique (à nommer) |
| Régression d'accessibilité (erreur) | T-203-12 | pa11y WCAG2AAA local, FR et EN, trois comptes |
