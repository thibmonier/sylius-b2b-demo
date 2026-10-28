# Tâches : US-204 : Refonte de la page « Plugins » dans la continuité de la home

## Informations US

- **Epic** : EPIC-12
- **Persona** : Hugo (intégrateur Sylius) ; Camille (recruteuse IT)
- **Story Points** : 3 (Should, **sacrifice : n°1**)
- **Sprint** : sprint-31-chaine-approvisionnement-et-comptes
- **Worker proposé** : w204 (orchestrateur pour la maquette)
- **Story** : [US-204](../../../backlog/user-stories/US-204.md)

## Résumé de la US

**En tant que** visiteuse de la démo  
**Je veux** une page « Plugins » claire, aérée et dans la même charte que l'accueil  
**Afin de** comprendre en quelques minutes ce qu'elle me prouve et de pouvoir la parcourir sans effort

## Règles et contraintes

- **Maquette d'abord** : maquette Claude Design (bureau et téléphone) dans une nouvelle page du canevas unique G2StRQigRKD6SqNRoyHpkW, publiée par l'orchestrateur et **validée par Thibaut (T-204-04) avant toute intégration Twig**. Les tâches de code T-204-05 à T-204-11 sont 📅 tant que son GO n'est pas noté au registre.
- **Contenu inchangé** : la page garde ses faits actuels (pas de nouvelles données ; l'indexation est US-207, après ce sprint) ; les routes ne changent pas. En-tête de page, cartes groupées par famille, badges (version, CI, compatibilité) lisibles, note de compatibilité conservée, liens vers les dépôts, appel à l'action neutre vers le profil (US-158).
- **Plugins en recoupement avec l'offre de l'éditeur** : présentation neutre actuelle conservée, sans mise en avant, sans comparaison (politique Sylius Plus).
- Principes d'US-189 et d'US-200 : jetons de `b2b.scss`, classes `b2b-home-*` de `assets/shop/styles/_home.scss`, polices auto-hébergées, AAA, cibles de 44 px, `prefers-reduced-motion`, 320 et 1280 px sans défilement horizontal.
- **Tests existants à garder verts, non modifiés** : `tests/Functional/Demo/PluginsPageCompatibilityNoteTest.php`, `tests/Smoke/PluginsAndHomepageTest.php`, `tests/Smoke/PluginBadgesSmokeTest.php`, `tests/Smoke/PluginCountSmokeTest.php`.
- Fichiers communs (règle 21) : `templates/shop/plugins/index.html.twig` et `PluginsController` : US-204 seule ; partial `_plugins.scss` (une ligne d'import dans `b2b.scss`), domaine de traduction `plugins` ; `docs/captures-vitrine.md` : section de la page « Plugins » seulement, `git diff` relu avant le commit (w203 y a sa section).
- Ton neutre, aucune comparaison avec Sylius Plus ; aucun détail de sécurité dans le dépôt public.
- Tests uniquement par `WORKER_ID=w204 TEST_DB_NAME=b2b_w204 TEST_CACHE_DIR=var/cache-w204 bin/run-tests`, dans l'arbre partagé (règle 22) ; preuve : `bin/proof-rebase`.

## Lieu de vérification

**Local, image reconstruite et production** : page publique, sans compte. Mesures de production **le jour du déploiement** (pa11y WCAG2AAA FR et EN, 320, 375 et 1280 px, `bin/measure-mobile`).

## Fichiers principaux touchés

- `templates/shop/plugins/index.html.twig`, `src/…/PluginsController` (si les données doivent être groupées par famille)
- `assets/shop/styles/_plugins.scss` (nouveau) et une ligne d'import dans `b2b.scss`, `translations/plugins.{fr,en}.yaml`
- `tests/Functional/Demo/` (nouveaux fichiers ; les quatre tests existants relus, non modifiés)
- `docs/captures-vitrine.md` (section), `docs/images/plugins-*`, `config/demo/proof.json` (par `bin/proof-rebase`)

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-204-01 | [DOC] | Cadrage : gabarit (61 lignes) et contrôleur relus ; données et familles de plugins disponibles ; badges (version, CI, compatibilité) et leur source ; note de compatibilité ; plugins en recoupement ; classes `b2b-home-*` réutilisables ; plan des tests | 0,5 h | - | Claude | ✅ |
| T-204-02 | [TEST] | État de référence « avant » : les quatre tests existants verts sur `main` ; captures locales (bureau 1280 px et 320 px) de la page FR et EN | 0,5 h | T-204-01, T-TECH-31-04 | Claude | ✅ |
| T-204-03 | [FE-WEB] | Maquette Claude Design (bureau 1440 px et téléphone 390 px), nouvelle page « Plugins » du canevas : en-tête, cartes groupées par famille, badges lisibles, note de compatibilité, liens vers les dépôts, appel à l'action neutre ; plugins en recoupement inchangés ; vraies données ; table « Acquis » mise à jour | 1,25 h | T-204-01, T-TECH-31-04 | Orchestrateur | ✅ |
| T-204-04 | [OPS] | 📅 Thibaut valide la maquette (ou demande des retouches) : **aucun gabarit Twig avant son GO**, noté au registre des décisions | 0,5 h | T-204-03 | Thibaut | ✅ |
| T-204-05 | [TEST] | 📅 Tests rouges, commités seuls et cités dans la PR : cartes avec nom, rôle, badges et liens vers le code, classes de l'accueil, groupes par famille, note de compatibilité conservée, plugins en recoupement neutres, anglais complet, `prefers-reduced-motion`, cibles de 44 px, 320 px sans défilement | 0,5 h | T-204-04 | Claude | ✅ |
| T-204-06 | [FE-WEB] | 📅 Gabarit et styles de la page (partial `_plugins.scss`, classes `b2b-home-*`), regroupement par famille, AAA, `prefers-reduced-motion`, routes inchangées | 1,75 h | T-204-05 | Claude | ✅ |
| T-204-07 | [DOC] | 📅 Textes FR et EN (domaine `plugins`), charte `PublicTextCharter` ; texte relu contre la maquette validée | 0,25 h | T-204-06 | Claude | ✅ |
| T-204-08 | [TEST] | 📅 Suite ciblée par `bin/run-tests` (les quatre tests existants **non modifiés**, nouveaux tests), PHPStan max sur tout le dépôt, ECS, Rector, lint Twig et YAML, pa11y local FR et EN, `bin/measure-mobile` à 320 et 375 px ; commande exacte et dernière ligne citées | 0,75 h | T-204-06, T-204-07 | Claude | 📅 déploiement |
| T-204-09 | [DOC] | 📅 Captures `docs/images/plugins-*` par `bin/render-page` (FR et EN, bureau et 320 px), section de `docs/captures-vitrine.md` | 0,5 h | T-204-08 | Claude | 📅 déploiement |
| T-204-10 | [TEST] | Image reconstruite et conteneur neuf (règle 16 : rien hors base) ; production **le jour du déploiement**, lecture seule : pa11y WCAG2AAA FR et EN, 320, 375 et 1280 px, tests de fumée de la page ; mesures consignées | 0,5 h | merge de la PR | Claude | 📅 déploiement |
| T-204-11 | [REV] | Revue (ton neutre, parité FR et EN, contenu inchangé, plugins en recoupement neutres) et table « scénario → test ou preuve » remplie | 0,25 h | T-204-10 | Claude | 📅 déploiement |

**Total estimé : 7,25 h** (11 tâches ; 6,75 h pour Claude et l'orchestrateur, 0,5 h de Thibaut).

## Table « scénario → test ou preuve »

| Scénario | Tâche | Test ou preuve |
|----------|-------|----------------|
| Lecture dans la charte de la home (nominal) | T-204-05, T-204-09 | test fonctionnel des cartes (à nommer) ; capture `docs/images/plugins-*` |
| Lecture sur téléphone (alternatif) | T-204-08 | `bin/measure-mobile` à 320 et 375 px ; cibles de 44 px |
| Anglais et mouvement réduit (alternatif) | T-204-05, T-204-08 | test de traduction et de `prefers-reduced-motion` (à nommer) |
| Contenu inchangé (erreur) | T-204-08 | `PluginsPageCompatibilityNoteTest`, `PluginsAndHomepageTest`, `PluginBadgesSmokeTest`, `PluginCountSmokeTest` non modifiés et verts |
| Plugins en recoupement (erreur) | T-204-05 | test de présentation neutre et de la note de compatibilité (à nommer) |
| Régression d'accessibilité (erreur) | T-204-08, T-204-10 | pa11y WCAG2AAA FR et EN, en local puis en production le jour du déploiement |
