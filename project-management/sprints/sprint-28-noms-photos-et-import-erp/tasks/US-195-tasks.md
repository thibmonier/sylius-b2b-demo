# Tâches : US-195 : Noms de produits sans marque réelle

## Informations US

- **Epic** : EPIC-12
- **Persona** : Camille (recruteuse IT) ; lead développeur d'une agence
- **Story Points** : 2 (Should, **protégée : non sacrifiable**)
- **Sprint** : sprint-28-noms-photos-et-import-erp
- **Worker proposé** : w195
- **Story** : [US-195](../../../backlog/user-stories/US-195.md)

## Résumé de la US

**En tant que** prospect ou évaluateur de la démo  
**Je veux** voir des noms de produits génériques et descriptifs  
**Afin de** ne pas lire de marques réelles associées à des produits, des prix et des fiches fictifs

## Règles et contraintes

- **77 produits sur 210** portent une marque réelle dans leur nom (inventaire T-189-01 du 2026-10-24) ; familles EPI-CASK, EPI-CHAU, EPI-GANT, EPI-VEST, OUTI-017/018, PAPE-013. Les descriptions et la référence fabricant (`mpn`) sont relues pour les mêmes marques.
- **Codes produits et slugs inchangés** : les slugs sont dérivés du SKU (`strtolower(sku)` + `-fr` ou `-en`, `ProductCsvFixture.php`), donc aucun lien cassé ni redirection (à confirmer en T-195-01).
- Liste noire des marques dans **un fichier unique**, utilisée par le test de garde ; la liste elle-même est le seul endroit du dépôt qui cite ces marques.
- Noms distincts au sein d'une famille (matière, couleur, norme, usage) ; le tableau ancien → nouveau est relu avant application (T-195-03). Le volume (77 produits, deux langues, descriptions) est la partie coûteuse de la story.
- **Ordre** : US-195 d'abord ; US-196 en dépend pour les noms affichés sur sa planche.
- Tests uniquement par `WORKER_ID=w195 TEST_DB_NAME=b2b_w195 TEST_CACHE_DIR=var/cache-w195 bin/run-tests` ; preuve : `php bin/proof-refresh` puis `php bin/proof-check`.
- Règles du sprint et consigne de worker : voir [README.md](./README.md) et [../consigne-worker.md](../consigne-worker.md).

## Lieu de vérification

**Pages publiques en production** (lecture seule, sans connexion) : une page du catalogue, la fiche `EPI-CASK-001` en FR et EN. Mesures consignées le jour du déploiement.

## Fichiers principaux touchés

- `fixtures/csv/epi.csv`, `fixtures/csv/fournitures.csv`, `fixtures/csv/quincaillerie.csv`, `fixtures/csv/SCHEMA.md` (lu), `src/Demo/DataFixtures/ProductCsvFixture.php` (lu)
- fichier de la liste noire (nouveau, emplacement fixé en T-195-03), `tests/` (garde, nouveaux fichiers), `config/demo/proof.json` (rafraîchi)

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-195-01 | [DOC] | Cadrage : inventaire T-189-01 relu contre `fixtures/csv/*.csv` (77 produits sur 210), colonnes `name_fr`, `name_en`, `description_fr`, `description_en`, `mpn`, `ProductCsvFixture`, `fixtures/csv/SCHEMA.md` ; confirmation que les slugs sont dérivés du SKU (`strtolower(sku)` + `-fr`/`-en`) donc inchangés ; recherche et filtres du catalogue relus | 0,5 h | - | Claude | 🔲 |
| T-195-02 | [TEST] | Tests rouges, commités seuls et cités dans la PR : garde de liste noire (nom, `description_fr`, `description_en`, `mpn` ; échec nommant fichier, ligne et marque), noms distincts au sein d'une famille, URL et code produit de `EPI-CASK-001` inchangés | 0,75 h | T-195-01 | Claude | 🔲 |
| T-195-03 | [DOC] | Liste noire dans un fichier unique, relue avec l'inventaire T-189-01 ; tableau ancien → nouveau pour les 77 produits en FR et EN (matière, couleur, norme, usage ; noms distincts par famille), relu avant application ; dernier état communiqué à US-196 (noms de la planche) | 1 h | T-195-02 | Claude | 🔲 |
| T-195-04 | [BE] | Application du tableau dans `epi.csv`, `fournitures.csv` et `quincaillerie.csv` (noms, descriptions, `mpn`) ; SKU et slugs inchangés ; `ProductCsvFixture` modifié seulement si un test l'exige | 0,5 h | T-195-03 | Claude | 🔲 |
| T-195-05 | [TEST] | Suite ciblée par `bin/run-tests` (garde, catalogue, recherche, filtres), PHPStan max sur tout le dépôt (`src` et `tests`), ECS, Rector, lint Twig et YAML ; commande exacte et dernière ligne citées ; recherche et filtres vérifiés avec les nouveaux noms | 0,5 h | T-195-04 | Claude | 🔲 |
| T-195-06 | [TEST] | Vérification après déploiement : URL de `EPI-CASK-001` en 200 avec le même slug, aucun nom de la liste noire dans le rendu public FR et EN, pa11y sur une page du catalogue, largeur à 360 et 1280 px ; mesures consignées le jour du déploiement | 0,5 h | merge de la PR | Claude | 🔲 |
| T-195-07 | [REV] | Revue (ton neutre, aucune marque réelle dans le dépôt public hors la liste noire elle-même) et table « scénario → test ou preuve » remplie ; `php bin/proof-refresh` puis `php bin/proof-check` | 0,25 h | T-195-06 | Claude | 🔲 |

**Total estimé : 4 h** (7 tâches ; 4 h pour Claude). La mesure du LCP de la home (action 5) est consignée ici par T-TECH-28-05.

## Scénarios et preuves

Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Noms génériques après chargement (nominal) | |
| Codes produits et liens conservés (alternatif) | |
| Noms distincts au sein d'une famille (alternatif) | |
| Marque réintroduite dans une fixture (erreur) | |
| Marque dans une description ou une référence fabricant (erreur) | |

## Mesures de production (jour du déploiement)

_À consigner par T-195-06 et T-TECH-28-05 (LCP de la home)._
