# Task Board : Sprint 026 (vitrine et flux)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Dépend d'une action de Thibaut | ⚠️ Sacrifiable

Total décomposé : **70 tâches, 54,5 h** pour 23 points engagés ; **53 h pour Claude**, 1,5 h d'actions de Thibaut (5 tâches). État à la clôture (2026-10-24) : **63 ✅, 7 🔲**, 0 📅. Les 7 tâches restantes sont des vérifications en production ou des contrôles transverses sans trace consignée (voir la note sous le tableau).

Règle (action 5 de la rétro du sprint 22) : une tâche passe en ✅ dans le commit qui la livre. Sacrifices : US-185 (n°1), puis US-184 (n°2) ; leurs tâches portent ⚠️ dans leur fichier si le sacrifice est décidé.

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-186-14 | US-186 | TEST | Vérification en production après le déploiement automatique de `main` (lecture seule) : pa11y WCAG2AAA en FR et en EN, 360 px et 1280 px, LCP mobil … | 1,25 h | Claude |
| T-186-15 | US-186 | REV | Revue (ton neutre, parité FR et EN, crédits complets, aucune comparaison avec une offre commerciale) et table « scénario → test ou preuve » remplie | 0,5 h | Claude |
| T-182-05 | US-182 | TEST | Contrôle en production après déploiement : largeur des pages à tableau à 360 px et 1280 px (lecture seule), 0 défaut d'affichage | 0,25 h | Claude |
| T-TECH-26-06 | Transverse | OPS | ECS (`src tests`), PHPStan max et Rector rejoués sur l'arbre exact avant chaque push de la démo ; contrôles du dépôt rejoués avant chaque push des … | 0,75 h | Orchestrateur |
| T-TECH-26-08 | Transverse | REV | Avant chaque passage en done : table « scénario → test ou preuve » complète, commit de tests rouges cité dans la PR (orchestrateur compris), lieu d … | 0,75 h | Orchestrateur |
| T-TECH-26-09 | Transverse | DOC | Statuts : chaque tâche cochée dans le commit qui la livre ; tâches transverses revues à chaque merge | 0,25 h | Orchestrateur |
| T-TECH-26-11 | Transverse | REV | Texte public relu contre sa source avant le commit (README des plugins, page de la PA fictive, textes de la home, crédits des photos) et concordanc … | 0,5 h | Orchestrateur |

> Note : T-182-05 reste ouverte (`/demo/accounts` déborde à 360 px, `scrollWidth` 412 ; correctif en cours par w185) ; T-186-14 est faite pour pa11y et les largeurs en production (2026-10-24), mais le LCP mobile « après » n'a pas été remesuré (voir `tasks/US-186-tasks.md`) ; T-186-15 (revue, table de preuves) n'est pas consignée ; T-TECH-26-06 (ECS, PHPStan et Rector sur l'arbre exact) n'a pas été tenue en totalité (trois CI rouges malgré des tests ciblés verts, voir la rétro) ; T-TECH-26-08, 09 et 11 n'ont pas de trace datée à citer. Rien n'est marqué ✅ sans preuve.

## 🔄 En Cours

_Aucune tâche._

## 📅 En attente d'une action de Thibaut

_Aucune tâche._ Les trois GO de publication et la soumission à Packagist ont été donnés (tags `v1.2.3`, `v0.2.0`, `v0.1.0` posés ; paquet du fichier plat sur Packagist selon Thibaut).

## 🚫 Bloqué ou écarté

_Aucune tâche._

## ✅ Terminé

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-186-01 | US-186 | DOC | Cadrage : inventaire de la home relu contre le code (hooks, gabarits, canal B2C, menu catalogue du cœur, tests existants `PriceBreaksAndHomeSmokeTe … | 1 h | Claude |
| T-186-02 | US-186 | TEST | Mesure « avant » en production (lecture seule) : LCP mobile (médiane de 5 passes, même protocole que l'après), poids et nombre de requêtes de la ho … | 0,75 h | Claude |
| T-186-03 | US-186 | DOC | Photos : sélection de 8 à 10 photos de banques libres (Unsplash ou Pexels), licence relue page par page, fichier des crédits (source, auteur, licen … | 1,25 h | Claude |
| T-186-04 | US-186 | FE-WEB | Maquette Claude Design (desktop et mobile) : hero éditorial, sections au défilement, en-tête et menu « La démo » ; publiée en artefact privé | 1,5 h | Orchestrateur |
| T-186-06 | US-186 | TEST | Tests rouges, commités seuls et cités dans la PR : sections et ordre, liens de présentation (200), connecté (blocs produits conservés), B2C inchang … | 1,25 h | Claude |
| T-186-07 | US-186 | FE-WEB | Images optimisées : conversion (WebP, AVIF si pris en charge), tailles responsives, dimensions explicites, chargement différé hors première vue, im … | 1 h | Claude |
| T-186-08 | US-186 | FE-WEB | Hero éditorial et système visuel : palette, typographie, fond coloré, photo, bouton principal et secondaire (`b2b.scss`, `home_hero.html.twig`) ; c … | 2 h | Claude |
| T-186-09 | US-186 | FE-WEB | Sections au défilement : parcours, plugins, s'intégrer, preuve par le code, auteur, rôles ou produits ; réécriture et suppression des gabarits `hom … | 2 h | Claude |
| T-186-10 | US-186 | FE-WEB | En-tête et menu : entrée « La démo » (liens de présentation) et « Essayer la démo », accessibles au clavier et à 360 px ; liens du hero ; pied cons … | 1 h | Claude |
| T-186-11 | US-186 | DOC | Textes FR et EN (domaine de traduction dédié), charte `PublicTextCharter`, légendes et `alt` des photos ; texte relu contre la maquette validée | 1 h | Claude |
| T-186-12 | US-186 | TEST | Suite ciblée par `bin/run-tests` (`WORKER_ID=w186 …`), `bin/measure-mobile` à 360 px en local, ECS (`src tests`), PHPStan max, Rector, lint Twig et … | 1 h | Claude |
| T-186-13 | US-186 | DOC | Captures `docs/images/home-*` refaites, `docs/theme-b2b.md` et README mis à jour (accueil, crédits des photos) | 0,5 h | Claude |
| T-181-01 | US-181 | DOC | Cadrage : dépôt `sylius-b2b-mcp-plugin` (branche, matrice de CI, `composer.json`, README), recette Flex de `sylius/mcp-server-plugin`, gestes actue … | 0,25 h | Claude |
| T-181-02 | US-181 | TEST | Tests rouges, commités seuls et cités dans la PR : `symfony/lock` dans `require`, README sans import des routes en double, geste du README cohérent … | 0,5 h | Claude |
| T-181-03 | US-181 | BE | `symfony/lock` ajouté au `require` ; README FR et EN corrigés (import des routes une fois, cas avec et sans Flex) ; CHANGELOG | 0,5 h | Claude |
| T-181-05 | US-181 | OPS | Après GO : push, CI verte sur toute la matrice (run terminé), concordance de la version avant le tag, tag et release | 0,5 h | Claude |
| T-181-06 | US-181 | OPS | Pack « IA et headless » : geste `symfony/lock` retiré si la borne couvre la version corrigée, CI d'installation verte (PR du dépôt du pack, GO couv … | 0,5 h | Claude |
| T-182-01 | US-182 | DOC | Cadrage : pages de `templates/demo/` dont un tableau contient du `code`, règles `overflow-wrap` actuelles (en ligne, dans `<style>`, hors media query) | 0,25 h | Claude |
| T-182-02 | US-182 | TEST | Tests rouges, commités seuls et cités dans la PR : garde d'architecture sur `templates/demo/` (page conforme, sans `code`, règle globale, règle abs … | 0,75 h | Claude |
| T-182-03 | US-182 | FE-WEB | Pages que la garde trouve non conformes corrigées (règle hors media query sur les `code` des tableaux) | 0,5 h | Claude |
| T-182-04 | US-182 | TEST | Suite ciblée par `bin/run-tests` (`WORKER_ID=w182 …`), ECS, PHPStan max, Rector ; dernières lignes citées | 0,25 h | Claude |
| T-183-01 | US-183 | DOC | Cadrage : plugin de transmission v0.1.0 (port, journal, `additional_statuses`), événement de `facturx`, génération des factures dans la démo (`src/ … | 1 h | Claude |
| T-183-02 | US-183 | OPS | Prérequis et installation : paquet résolu sur Packagist (T-180-13 du sprint 25) sinon arrêt de la story ; `composer require ^0.1`, bundle, migratio … | 1 h | Claude |
| T-183-03 | US-183 | TEST | Tests rouges, commités seuls et cités dans la PR : dépôt et statut simulé, rejet avec motif, refus et encaissement, bandeau, événement rejoué, stat … | 1,5 h | Claude |
| T-183-04 | US-183 | BE | Adaptateur `FictitiousPlatformTransmitter` (`src/Demo/Einvoice/`) et service de PA fictive : réponse selon le scénario, motif simulé | 1,5 h | Claude |
| T-183-05 | US-183 | BE | Configuration de la démo : adaptateur choisi par service, statuts additionnels `refused` et `collected`, libellés FR et EN des statuts | 0,75 h | Claude |
| T-183-06 | US-183 | DB | Données de démonstration : commandes payées pour chaque scénario dans les fixtures, factures générées, rejouables après la remise à zéro nocturne ( … | 1,25 h | Claude |
| T-183-07 | US-183 | FE-WEB | Page « PA fictive de démonstration » (FR et EN) avec le bandeau « statuts simulés, sans valeur réglementaire, à vérifier » et la liste des scénario … | 1,5 h | Claude |
| T-183-08 | US-183 | DOC | Catalogue `config/demo/plugins.yaml`, matrice `compatibility.json`, compteur de plugins et tests qui le citent, documentation de la démo | 0,75 h | Claude |
| T-183-09 | US-183 | TEST | Suite ciblée par `bin/run-tests` (`WORKER_ID=w183 …`), ECS, PHPStan max, Rector, lint Twig et YAML ; dernières lignes citées | 0,75 h | Claude |
| T-183-10 | US-183 | REV | Revue (aucune plateforme réelle nommée, ton neutre, parité FR et EN, texte relu contre le code et l'ADR 0016) et table « scénario → test ou preuve … | 0,25 h | Claude |
| T-184-01 | US-184 | DOC | Cadrage : journal du plugin (`open`, `record`), règle de relance (statut `error` seul), écran du modèle « Webhooks reçus » (`src/Demo/Admin`, hooks … | 0,5 h | Claude |
| T-184-02 | US-184 | TEST | Tests rouges du plugin, commités seuls et cités dans la PR : réouverture d'une ligne en erreur, rappel de l'adaptateur une fois, refus d'un statut … | 0,75 h | Claude |
| T-184-03 | US-184 | BE | Plugin : cas d'usage de relance et réouverture du journal ; README FR et EN, CHANGELOG ; ECS, PHPStan max, Rector dans Docker (budget disque : `ven … | 1 h | Claude |
| T-184-04 | US-184 | TEST | Tests rouges de la démo, commités seuls : liste des statuts, relance, bouton absent hors erreur, refus d'une relance non autorisée ou sans jeton, a … | 0,75 h | Claude |
| T-184-05 | US-184 | FE-WEB | Démo : contrôleur et écran d'administration (liste, motif, dates, bandeau « simulés », bouton « Relancer » avec jeton), entrée de menu, scénario « … | 1,25 h | Claude |
| T-184-07 | US-184 | OPS | Après GO : push, CI verte sur toute la matrice (run terminé), concordance (CHANGELOG, tag, contrainte), tag et release ; démo en `^0.2` ; Packagist … | 0,75 h | Claude |
| T-184-08 | US-184 | TEST | Suite ciblée de la démo (`WORKER_ID=w184 …`), ECS, PHPStan max, Rector ; revue et table « scénario → test ou preuve » remplie | 0,75 h | Claude |
| T-185-01 | US-185 | DOC | Cadrage : outbox (`OutboxEvent`, dépôt des événements, enveloppe `order` v1) lu sans modification, squelette du modèle, contrat de l'ADR 0017, nom … | 1 h | Claude |
| T-185-02 | US-185 | OPS | Squelette local du plugin **sans `.env*`** (variables dans `phpunit.xml.dist`), MySQL de la démo avec une base à son nom (règle 12), `git init`, CI … | 1 h | Claude |
| T-185-03 | US-185 | TEST | Tests rouges, commités seuls et cités dans la PR : les 5 scénarios (contrat, lot de plusieurs commandes, dépôt atomique observé par un lecteur, évé … | 1,5 h | Claude |
| T-185-04 | US-185 | BE | Contrat de fichier plat : colonnes, en-tête, UTF-8, séparateur `;`, entiers de l'unité mineure, nom de fichier unique par lot | 1 h | Claude |
| T-185-05 | US-185 | BE | Export : consommation des événements de commande, une ligne par ligne de commande, idempotence par `webhook_id` | 1,25 h | Claude |
| T-185-06 | US-185 | BE | Dépôt atomique : nom provisoire puis renommage, aucun fichier partiel si le répertoire est inaccessible, événements laissés à exporter | 1 h | Claude |
| T-185-07 | US-185 | BE | Bundle, configuration (répertoire d'échange, lot) et commande console | 0,75 h | Claude |
| T-185-08 | US-185 | TEST | Suite complète en local (Docker, MySQL de la démo), ECS, PHPStan max, Rector ; dernières lignes citées ; `vendor/` et base d'essai supprimés, espac … | 0,75 h | Claude |
| T-185-09 | US-185 | DOC | README FR et EN (contrat, configuration, limites : aucun ERP réel), CHANGELOG ; texte relu contre le code et l'ADR 0017 | 1 h | Claude |
| T-185-10 | US-185 | REV | Revue (ton neutre, aucun ERP ni éditeur nommé, `webhook-outbox` inchangé, parité FR et EN) et table « scénario → test » remplie | 0,25 h | Claude |
| T-185-12 | US-185 | OPS | Après GO : création du dépôt avec ses topics (ecommerce, sylius, sylius-plugin, symfony, erp, csv), push, CI verte sur toute la matrice (run termin … | 0,75 h | Claude |
| T-TECH-26-05 | Transverse | OPS | Ressources avant chaque lot : `df -h .`, `bin/check-disk`, mémoire libre ; **après chaque lot** : aucun run actif, `bin/clean-test-caches --all`, ` … | 0,75 h | Orchestrateur |
| T-TECH-26-07 | Transverse | OPS | CI verte avant chaque merge (démo : « Quality and smoke tests », « Docker image » ; plugins : tous les jobs), run terminé et attendu avant le merge … | 0,5 h | Orchestrateur |
| T-TECH-26-10 | Transverse | OPS | Preuve publiée : chaque PR de la démo qui ajoute ou supprime un fichier de tests (US-182, US-183, US-184, US-186) embarque `php bin/proof-refresh` … | 0,5 h | Orchestrateur |
| T-TECH-26-12 | Transverse | OPS | Registre des GO de Thibaut : maquette (T-186-05), tag du plugin b2b-mcp (T-181-04), tag `v0.2.0` (T-184-06), dépôt et tag du nouveau plugin (T-185- … | 0,25 h | Orchestrateur |
| T-TECH-26-13 | Transverse | DOC | Revue et rétrospective du sprint 26 : indicateurs de la rétro du sprint 25 (espace libre minimal, défauts d'affichage trouvés en production, artefa … | 1 h | Orchestrateur |
| T-186-05 | US-186 | OPS | 📅 Thibaut valide la maquette (ou demande des retouches) : **aucun gabarit Twig avant son accord** | 0,5 h | Thibaut |
| T-181-04 | US-181 | OPS | 📅 Thibaut donne son GO pour pousser la branche, ouvrir la PR du plugin, la fusionner puis poser le tag et la release corrective | 0,25 h | Thibaut |
| T-184-06 | US-184 | OPS | 📅 Thibaut donne son GO pour pousser, fusionner le plugin puis poser le tag et la release `v0.2.0` | 0,25 h | Thibaut |
| T-185-11 | US-185 | OPS | 📅 Thibaut donne son GO pour créer le dépôt GitHub `thibmonier/sylius-erp-flatfile-plugin`, pousser et poser le tag `v0.1.0` | 0,25 h | Thibaut |
| T-185-13 | US-185 | OPS | 📅 Thibaut soumet le paquet à Packagist | 0,25 h | Thibaut |
| T-TECH-26-01 | Transverse | DOC | Ligne « budget disque d'un dépôt hors de la démo et restes listés à Thibaut, sous 6 Go aucun nouveau lot » ajoutée à `project-management/templates/ … | 0,25 h | Orchestrateur |
| T-TECH-26-02 | Transverse | DOC | Ligne « concordance des versions (dossier de recette, tag, contrainte) avec Packagist avant un tag, une release ou une PR publique » ajoutée à `che … | 0,25 h | Orchestrateur |
| T-TECH-26-03 | Transverse | DOC | Règle 12 de `project-management/templates/consigne-worker.md` : nouveau plugin sans `.env*`, MySQL de la démo, jamais de second serveur (action 4 d … | 0,25 h | Orchestrateur |
| T-TECH-26-04 | Transverse | DOC | ADR 0016 (décision du 2026-10-24, stories B et C), ADR 0017 (section « Décision (2026-10-24) », statut accepté), index des ADR, fiches US-183 à US- … | 0,5 h | Orchestrateur |

## Stories ajoutées en cours de sprint (sans décomposition en tâches)

| US | Titre | Points | PR | Ajoutée |
|----|-------|--------|----|---------|
| [US-190](../../backlog/user-stories/US-190.md) | Intégrer le plugin d'export en fichier plat à la démo | 2 | #88 | 2026-10-24, sur GO de Thibaut, après US-185 |
| [US-191](../../backlog/user-stories/US-191.md) | Page de connexion : formulaire à gauche, comptes de démo à droite | 1 | #90 | 2026-10-24, sur retour de Thibaut |
| [US-192](../../backlog/user-stories/US-192.md) | Menu de compte déroulant, compteur d'approbations, menu catalogue ouvert | 3 | #91 | 2026-10-24, sur retour de Thibaut |
