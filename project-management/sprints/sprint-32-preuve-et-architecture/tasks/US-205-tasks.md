# Tâches : US-205 : Refonte de la page « Preuve technique » dans la continuité de la home

## Informations US

- **Epic** : EPIC-12
- **Persona** : Camille (recruteuse IT) ; Didier (DSI / CTO)
- **Story Points** : 3 (Should, **sacrifice : n°2**, proposition)
- **Sprint** : sprint-32-preuve-et-architecture
- **Worker proposé** : w205 (orchestrateur pour la maquette)
- **Story** : [US-205](../../../backlog/user-stories/US-205.md)

## Résumé de la US

**En tant que** visiteuse de la démo  
**Je veux** une page « Preuve technique » claire, aérée et dans la même charte que l'accueil  
**Afin de** comprendre en quelques minutes ce qu'elle me prouve et de pouvoir la parcourir sans effort

## Règles et contraintes

- **Maquette d'abord** : maquette Claude Design (bureau 1440 px et téléphone 390 px, vraies données) dans une nouvelle page du canevas unique G2StRQigRKD6SqNRoyHpkW, publiée par l'orchestrateur et **validée par Thibaut (T-205-04) avant toute intégration Twig**. Les tâches de code T-205-05 à T-205-11 sont 📅 tant que son GO n'est pas noté au registre.
- **Contenu inchangé** : la page garde ses faits actuels (pas de nouvelles données ; l'indexation est US-207, sprint 33) ; la route `/{_locale}/preuve` et le nom `app_demo_proof` ne changent pas. À travailler dans la maquette : chiffres clés en tête (tests, PHPStan, CI, nombre de plugins, tous lus dans le rapport de preuve, **jamais écrits en dur**), faits groupés (qualité, compatibilité, versions), pastilles d'état avec texte (jamais la couleur seule), lien de vérification pour chaque fait.
- **Invariants des tests existants** (`tests/Functional/Demo/ProofPageTest.php`, 10 méthodes, **non modifié**) : `table[data-proof-table] tbody tr[data-plugin]` (une ligne par plugin du catalogue), cellules `[data-proof-cell="version|sylius|ci|phpstan|tests|coverage"]` avec leur lien source et `time[datetime]`, `[data-proof-stale]`, `[data-proof-state]`, `[data-proof-delivery]` et `[data-proof-delivery-item]`, un seul `h1`, aucune requête sortante, description propre de la page. Le tableau reste un vrai tableau (en-têtes, légende) ; l'affichage en cartes à 320 px passe du `<style>` en ligne au partial `_proof.scss`.
- **Fait sans valeur** : un état « non disponible » (ou « non publié », « non mesuré ») explicite remplace la valeur, sans bloc vide et sans estimation (`_fact.html.twig` garde ce rôle).
- Principes d'US-189, d'US-200 et d'US-204 : jetons de `b2b.scss`, classes `b2b-home-*` de `assets/shop/styles/_home.scss`, partial `_plugins.scss` comme référence de style, polices auto-hébergées, AAA, cibles de 44 px, `prefers-reduced-motion`, 320 et 1280 px sans défilement horizontal.
- **Disque** : aucun test ni rendu local avant 5 Go libres (6 Go pour la suite complète) ; avant, seuls le cadrage, la rédaction des tests et du code sont faits, les exécutions sont 📅 « disque ».
- Fichiers communs (règle 21) : `templates/demo/proof.html.twig`, `templates/demo/proof/_fact.html.twig`, `ProofController` (si les données doivent être groupées), `assets/shop/styles/_proof.scss` (nouveau) et une ligne d'import dans `b2b.scss`, domaine de traduction `proof` : US-205 seule ; `docs/captures-vitrine.md` : sa section seulement, `git diff` relu avant le commit ; `config/demo/proof.json` : orchestrateur (`bin/proof-rebase`).
- Ton neutre, aucune comparaison avec Sylius Plus ; aucun détail de sécurité dans le dépôt public.
- Tests uniquement par `WORKER_ID=w205 TEST_DB_NAME=b2b_w205 TEST_CACHE_DIR=var/cache-w205 bin/run-tests`, dans l'arbre partagé (règle 22) ; preuve : `bin/proof-rebase`.

## Lieu de vérification

**Local, image reconstruite et production** : page publique, sans compte. Mesures de production **le jour du déploiement** (pa11y WCAG2AAA FR et EN, 320, 375 et 1280 px, `bin/measure-mobile`).

## Fichiers principaux touchés

- `templates/demo/proof.html.twig`, `templates/demo/proof/_fact.html.twig`, `src/Demo/Controller/ProofController.php` (si les données doivent être groupées)
- `assets/shop/styles/_proof.scss` (nouveau) et une ligne d'import dans `b2b.scss`, `translations/proof.{fr,en}.yaml`
- `tests/Functional/Demo/` (nouveaux fichiers ; `ProofPageTest` relu, non modifié)
- `docs/captures-vitrine.md` (section), `docs/images/proof-*`, `config/demo/proof.json` (par `bin/proof-rebase`)

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-205-01 | [DOC] | Cadrage : `proof.html.twig` (103 lignes), `_fact.html.twig` et `ProofController` relus ; faits du rapport (livraison : tests, PHPStan, ECS, audit, image, SBOM, CI ; une ligne par plugin) et leurs états ; invariants de `ProofPageTest` listés ; style inline à déplacer ; classes `b2b-home-*` et `_plugins.scss` réutilisables ; plan des tests | 0,5 h | - | Claude | ✅ |
| T-205-02 | [TEST] | État de référence « avant » : `ProofPageTest` relu et, dès 5 Go libres, lancé sur la branche (10 méthodes) ; captures de production (sans connexion) de la page FR et EN en attendant le rendu local | 0,25 h | T-205-01 | Claude | ✅ |
| T-205-03 | [FE-WEB] | Maquette Claude Design (bureau 1440 px et téléphone 390 px), nouvelle page « Preuve technique » du canevas : en-tête, chiffres clés, faits groupés (qualité, compatibilité, versions), pastilles d'état avec texte, lien de vérification par fait, état « non disponible », tableau par plugin lisible à 390 px ; vraies données du rapport ; table « Acquis » mise à jour | 1,25 h | T-205-01 | Orchestrateur | ✅ |
| T-205-04 | [OPS] | 📅 Thibaut valide la maquette (ou demande des retouches) : **aucun gabarit Twig avant son GO**, noté au registre des décisions | 0,5 h | T-205-03 | Thibaut | ✅ |
| T-205-05 | [TEST] | 📅 Tests rouges, commités seuls et cités dans la PR : chiffres clés lus dans le rapport (aucun compteur en dur), faits groupés avec leur lien source, pastilles avec texte, état « non disponible » sans bloc vide, classes de l'accueil, anglais complet, `prefers-reduced-motion`, cibles de 44 px, 320 px sans défilement | 0,5 h | T-205-04 | Claude | ✅ |
| T-205-06 | [FE-WEB] | 📅 Gabarit et styles de la page (partial `_proof.scss`, classes `b2b-home-*`), chiffres clés, groupes, pastilles, tableau par plugin (cartes à 320 px sans `<style>` en ligne), AAA, `prefers-reduced-motion`, route et sélecteurs inchangés | 1,75 h | T-205-05 | Claude | ✅ |
| T-205-07 | [DOC] | 📅 Textes FR et EN (domaine `proof`), charte `PublicTextCharter` ; texte relu contre la maquette validée | 0,25 h | T-205-06 | Claude | ✅ |
| T-205-08 | [TEST] | 📅 Suite ciblée par `bin/run-tests` (`ProofPageTest` **non modifié**, nouveaux tests), PHPStan max sur tout le dépôt, ECS, Rector, lint Twig et YAML, pa11y local FR et EN, `bin/measure-mobile` à 320 et 375 px ; commande exacte et dernière ligne citées | 0,75 h | T-205-06, T-205-07 | Claude | 🔄 fait : pa11y AAA FR/EN 1280 et 320, 320/375/1280 sans défilement ; reste : suite complète (orchestrateur) |
| T-205-09 | [DOC] | 📅 Captures `docs/images/proof-*` par `bin/render-page` (FR et EN, bureau et 320 px), section de `docs/captures-vitrine.md` | 0,5 h | T-205-08 | Claude | ✅ |
| T-205-10 | [TEST] | Image reconstruite et conteneur neuf (règle 16 : rien hors base) ; production **le jour du déploiement**, lecture seule : pa11y WCAG2AAA FR et EN, 320, 375 et 1280 px, tests de fumée de la page ; mesures consignées | 0,5 h | merge de la PR | Claude | ✅ 2026-10-28 |
| T-205-11 | [REV] | Revue (ton neutre, parité FR et EN, contenu inchangé, aucune valeur estimée) et table « scénario → test ou preuve » remplie | 0,25 h | T-205-10 | Claude | ✅ 2026-10-28 |

**Total estimé : 7 h** (11 tâches ; 6,5 h pour Claude et l'orchestrateur, 0,5 h de Thibaut).

## Table « scénario → test ou preuve »

| Scénario | Tâche | Test ou preuve |
|----------|-------|----------------|
| Lecture dans la charte de la home (nominal) | T-205-05, T-205-09 | test fonctionnel des blocs de faits (à nommer) ; capture `docs/images/proof-*` |
| Lecture sur téléphone (alternatif) | T-205-08 | `bin/measure-mobile` à 320 et 375 px ; cibles de 44 px |
| Anglais et mouvement réduit (alternatif) | T-205-05, T-205-08 | test de traduction et de `prefers-reduced-motion` (à nommer) |
| Contenu inchangé (erreur) | T-205-08 | les 10 méthodes de `ProofPageTest` non modifiées et vertes |
| Fait sans valeur disponible (erreur) | T-205-05 | test de l'état « non disponible » sans bloc vide (à nommer) ; `testAFigureWithoutSourceIsNotDisplayed` et `testAMissingOrBrokenFileGivesANormalPageWithNothingAvailable` non modifiés |
| Régression d'accessibilité (erreur) | T-205-08, T-205-10 | pa11y WCAG2AAA FR et EN, en local puis en production le jour du déploiement |
