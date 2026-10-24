# Task Board : Sprint 028 (noms, photos et import ERP)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Dépend d'une action de Thibaut | ⚠️ Sacrifiable | 🟡 Faite avec écart | ⊘ Sans objet

**Sprint planifié le 2026-10-25.** Total décomposé : **48 tâches, 34,2 h** pour 13 points engagés. État au planning : **2 ✅, 39 🔲, 7 📅, 0 🔄, 0 🚫**. Ordre de sacrifice **proposé** : tranche B d'US-187, puis US-196 (US-195 protégée), à confirmer par Thibaut.

Règle (action 5 de la rétro du sprint 22) : une tâche passe en ✅ dans le commit qui la livre.

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-195-01 | US-195 | DOC | Cadrage : inventaire T-189-01 relu contre `fixtures/csv/*.csv` (77 produits sur 210), colonnes `name_fr`, `name_en`, … | 0,5 h | Claude |
| T-195-02 | US-195 | TEST | Tests rouges, commités seuls et cités dans la PR : garde de liste noire (nom, `description_fr`, `description_en`, `mpn` ; échec … | 0,75 h | Claude |
| T-195-03 | US-195 | DOC | Liste noire dans un fichier unique, relue avec l'inventaire T-189-01 ; tableau ancien → nouveau pour les 77 produits en FR et EN … | 1 h | Claude |
| T-195-04 | US-195 | BE | Application du tableau dans `epi.csv`, `fournitures.csv` et `quincaillerie.csv` (noms, descriptions, `mpn`) ; SKU et slugs … | 0,5 h | Claude |
| T-195-05 | US-195 | TEST | Suite ciblée par `bin/run-tests` (garde, catalogue, recherche, filtres), PHPStan max sur tout le dépôt (`src` et `tests`), ECS, … | 0,5 h | Claude |
| T-195-06 | US-195 | TEST | Vérification après déploiement : URL de `EPI-CASK-001` en 200 avec le même slug, aucun nom de la liste noire dans le rendu public … | 0,5 h | Claude |
| T-195-07 | US-195 | REV | Revue (ton neutre, aucune marque réelle dans le dépôt public hors la liste noire elle-même) et table « scénario → test ou preuve … | 0,25 h | Claude |
| T-196-01 | US-196 | DOC | Cadrage : audit des images du 2026-10-24 relu (64 fichiers pour 210 produits, 37 partagés), liste des familles à couvrir (environ … | 0,5 h | Claude |
| T-196-02 | US-196 | TEST | Tests rouges, commités seuls et cités dans la PR : manifeste (ligne sans licence libre sans attribution, produit sans image : … | 0,75 h | Claude |
| T-196-03 | US-196 | DOC | Recherche sur Pexels et Unsplash : 2 à 3 candidats par famille, **licence relue sur le site pour chacun** (usage sans attribution … | 1,25 h | Claude |
| T-196-05 | US-196 | BE | Import à partir de la sélection : adapter ou remplacer `bin/fetch-product-images` (Openverse seulement) par un script qui lit un … | 1 h | Claude |
| T-196-07 | US-196 | BE | Chargeur et crédits : rattachement par famille (`ProductImageAttacher`), lecture du manifeste (`ImageManifest`), page Crédits … | 0,5 h | Claude |
| T-196-08 | US-196 | TEST | Revue visuelle famille par famille (rendu du noyau en mode prod par `bin/render-page`, **un seul cache de rendu**, … | 0,5 h | Claude |
| T-196-09 | US-196 | TEST | Vérification après déploiement : pa11y et largeurs (catalogue, une fiche, page Crédits), LCP mobile du catalogue **après** … | 0,5 h | Claude |
| T-196-10 | US-196 | REV | Revue (licences, ton neutre, aucune marque visible) et table « scénario → test ou preuve » remplie ; `php bin/proof-refresh` puis … | 0,25 h | Claude |
| T-187-01 | US-187 | DOC | [A] Cadrage : ADR 0017 (contrat, questions 3, 4 et 6), plugin `sylius-erp-flatfile-plugin` v0.1.0 (`OrderLinesCsv`, … | 0,5 h | Claude |
| T-187-03 | US-187 | DOC | [A] Affinage de la fiche : scénarios (1 nominal, 2 alternatifs, 2 erreurs pour le plugin ; 1 nominal et 1 erreur pour la … | 0,5 h | Claude |
| T-187-04 | US-187 | TEST | [A] Tests rouges du plugin, commités seuls et cités dans la PR du plugin : lot de prix valide, lot de stock partiellement valide, … | 1,75 h | Claude |
| T-187-05 | US-187 | BE | [A] Plugin, lecture et validation : encodage UTF-8, séparateur `;`, en-tête, total de contrôle ; prix (code de variante connu, … | 2 h | Claude |
| T-187-06 | US-187 | DB | [A] Plugin, journal des lots : entité et migration (nom, total de contrôle, date, lignes lues, appliquées, rejetées, rapport), … | 1,5 h | Claude |
| T-187-07 | US-187 | BE | [A] Plugin, application et commande : prix en tout ou rien dans une transaction (aucune écriture partielle), stock : lignes … | 2 h | Claude |
| T-187-08 | US-187 | DOC | [A] Plugin : README FR et EN (contrat des deux fichiers, journal, idempotence, ce qui n'est pas fait), CHANGELOG, mention de la … | 0,75 h | Claude |
| T-187-09 | US-187 | TEST | [A] Plugin : ECS, PHPStan max, Rector, tests sur la matrice CI du dépôt (Docker, MySQL de la démo, base supprimée à la fin) ; … | 0,5 h | Claude |
| T-187-13 | US-187 | TEST | [B] Tests rouges de la démo, commités seuls et cités dans la PR : simulateur qui dépose un fichier de prix et de stock d'exemple … | 0,75 h | Claude |
| T-187-15 | US-187 | BE | [B] Simulateur « ERP Démo (fictif) » : produit un fichier de prix et de stock d'exemple à partir du catalogue de la démo (codes … | 1,25 h | Claude |
| T-187-16 | US-187 | BE | [B] Import dans la démo : commande du plugin lancée après la simulation, au chargement des fixtures et à la remise à zéro … | 1 h | Claude |
| T-187-17 | US-187 | FE-WEB | [B] Page « Échanges ERP (fictif) » étendue : lots entrants d'import (nom, total de contrôle, lignes lues, appliquées, rejetées), … | 1,25 h | Claude |
| T-187-18 | US-187 | DOC | [B] `docs/erp-demo.md` (schéma, fichiers de prix et de stock, ce qui est simulé), lien depuis `/echange-fichier`, ADR 0017, … | 0,5 h | Claude |
| T-187-19 | US-187 | TEST | [B] Suite ciblée par `bin/run-tests`, PHPStan max sur tout le dépôt (`src` et `tests`), ECS, Rector, lint Twig et YAML ; image … | 0,75 h | Claude |
| T-187-20 | US-187 | TEST | [B] Vérification après déploiement : pages publiques en production (`/echange-fichier`, documentation : pa11y, 360 px) ; page du … | 0,5 h | Claude |
| T-187-21 | US-187 | REV | [B] Revue (ton neutre, « ERP Démo (fictif) » partout, aucun éditeur nommé, aucune comparaison avec Sylius Plus) et table « … | 0,5 h | Claude |
| T-TECH-28-01 | Transverse | OPS | **Action 1** : `bin/render-page` réutilise **un seul cache de rendu par worker** et appelle `bin/check-disk` avant chaque rendu … | 1,25 h | Orchestrateur |
| T-TECH-28-02 | Transverse | OPS | **Action 2** : `bin/proof-rebase` (ou option de `bin/proof-refresh`) : rebase sur `origin/main`, régénère … | 1 h | Orchestrateur |
| T-TECH-28-05 | Transverse | TEST | **Action 5** : LCP mobile de la **home** remesuré en production au premier lot (protocole de w186, 5 passes) ; au-dessus de 1456 … | 0,75 h | Claude |
| T-TECH-28-06 | Transverse | OPS | **Action 1 (processus)** : avant chaque lot `df -h .`, `bin/check-disk`, mémoire libre et swap ; après chaque lot aucun run … | 0,5 h | Orchestrateur |
| T-TECH-28-07 | Transverse | OPS | ECS (`src tests`), PHPStan max sur **tout le dépôt** et Rector rejoués sur l'arbre exact avant chaque push ; CI verte (« Quality … | 0,75 h | Orchestrateur |
| T-TECH-28-08 | Transverse | REV | Avant chaque passage en done : table « scénario → test ou preuve » complète, commit de tests rouges cité dans la PR, mesures de … | 0,5 h | Orchestrateur |
| T-TECH-28-09 | Transverse | OPS | Registre des GO de Thibaut (réponses aux questions 3, 4 et 6, planche et téléchargement d'US-196, push, tag et release du plugin, … | 0,25 h | Orchestrateur |
| T-TECH-28-10 | Transverse | DOC | Revue et rétrospective du sprint 28 : indicateurs de la rétro du sprint 27 (espace libre minimal d'au moins 5 Go, 0 conflit de … | 1 h | Orchestrateur |

## 🔄 En Cours

_Aucune tâche._

## 📅 En attente d'une action de Thibaut

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-196-04 | US-196 | OPS | 📅 Thibaut choisit une photo par famille sur la planche (une photo avec marque est écartée et remplacée) et donne le **GO … | 0,5 h | Thibaut |
| T-196-06 | US-196 | DOC | 📅 **Après le GO (T-196-04) seulement** : téléchargement des seules photos choisies, optimisation WebP, … | 0,75 h | Claude |
| T-187-02 | US-187 | OPS | [A] 📅 Thibaut tranche les questions 3, 4 et 6 de l'ADR (propositions : prix par canal, sans groupe de clients ; stock en valeur … | 0,25 h | Thibaut |
| T-187-10 | US-187 | OPS | [A] 📅 **GO de Thibaut pour push, tag et release** du plugin (branche, PR, tag `v0.2.0`, release) ; aucun push, tag ni release … | 0,1 h | Thibaut |
| T-187-11 | US-187 | OPS | [A] 📅 Après le GO : push de la branche, PR du plugin, CI verte (run terminé), merge sur GO, tag `v0.2.0` et release ; concordance … | 0,5 h | Claude |
| T-187-12 | US-187 | OPS | [A] 📅 Thibaut soumet ou met à jour le paquet sur Packagist s'il le faut (Claude ne soumet jamais) et confirme que `v0.2.0` y est … | 0,1 h | Thibaut |
| T-187-14 | US-187 | BE | [B] 📅 Montée de version : `tmonier/sylius-erp-flatfile-plugin` en `^0.2`, `composer.lock`, migration du plugin, configuration ; … | 0,25 h | Claude |

## 🚫 Bloqué ou écarté

_Aucune tâche._

## 🟡 Faite avec écart

_Aucune tâche._

## ⊘ Sans objet

_Aucune tâche._

## ✅ Terminé

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-TECH-28-03 | Transverse | DOC | **Action 3** : « que devient cet état après un déploiement ? » dans `project-management/templates/consigne-worker.md` (règle 16) … | 0,25 h | Orchestrateur |
| T-TECH-28-04 | Transverse | DOC | **Action 4** : règle 13 complétée (numéro de story du message de commit relu contre la branche et le titre de la PR par `git show … | 0,25 h | Orchestrateur |
