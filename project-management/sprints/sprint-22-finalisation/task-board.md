# Task Board : Sprint 022 (finalisation)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Dépend d'une action de Thibaut | ⚠️ Sacrifiable

Total décomposé : **90 tâches, 77 h** pour 27 points (US-163, 2 points, déjà livrée) ; **74,5 h pour Claude**, 2,5 h d'actions de Thibaut. Capacité : 60,5 h (25 points restants × 2,1 h + 8 h transverses) ; dépassement de 14 h absorbé par les sacrifices ordonnés (US-062, US-154, US-098, US-088, US-165). État au lancement (2026-10-23) : 90 tâches 🔲 (5 d'entre elles dépendent de Thibaut), aucune commencée ; US-163 ✅ livrée (PR #57), US-161 🔄 (solde). Point d'étape le 2026-10-30. Voir [tasks/README.md](./tasks/README.md).

**Capacité** : 60,5 h ; charge de Claude 74,5 h avant sacrifices ; 59 h après les sacrifices 1 et 2 ; plancher 40,25 h après les cinq.

Les tâches de Thibaut sont marquées dans la colonne « Réalisé par ». Les stories ⚠️ ne s'engagent qu'après les Must, dans l'ordre inverse du sacrifice (US-165, US-088, US-098, US-154, US-062).

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-TECH-22-01a | Transverse | DOC | Liste des stories à vérifier tirée du YAML (tableau ci-dessous) et checklist de vérification par story (URL ou page, comportement attendu, FR et EN) ; version déployée … | 0,75 h | Claude |
| T-TECH-22-01b | Transverse | OPS | 📅 Thibaut donne le GO et réalise les opérations en attente : merge des PR de plugins ouvertes (parc d'équipements, purchase-approval), tags, indexation Packagist, puis … | 0,5 h | Thibaut |
| T-TECH-22-01c | Transverse | TEST | Checks du runbook en lecture seule sur la production (`docs/ouverture-publique.md`, `docs/deploiement-railway.md`) : santé, en-têtes, mode public, `noindex`, pages … | 1 h | Claude |
| T-TECH-22-01d | Transverse | TEST | Vérification des stories du sprint 17 (10 stories : US-105, US-106, US-107, US-116, US-117, US-120, US-122, US-123, US-127, US-129) : pages et documents vérifiables en … | 1,5 h | Claude |
| T-TECH-22-01e | Transverse | TEST | Vérification des stories du sprint 18 (7 stories : US-158, US-148, US-160, US-159, US-124, US-111, US-068) : profil et libellés neutres, limiteurs de débit, page de … | 1,5 h | Claude |
| T-TECH-22-01f | Transverse | TEST | Vérification des stories des sprints 19 et 21 (US-155, US-056, US-057, US-058, US-157, US-060) : tableau de bord company-insights, parc d'équipements (compte de démo, … | 1,5 h | Claude |
| T-TECH-22-01g | Transverse | REV | 📅 Thibaut relit et valide ce qui demande un humain : kit média (US-120), profil LinkedIn (US-129), calendrier et posts (US-122, US-123, US-124, US-159, US-127), captures … | 1 h | Thibaut |
| T-TECH-22-01h | Transverse | DOC | Passage à `done` dans `.bmad/sprint-status.yaml` des stories vérifiées (une par une, avec la date et le commit de déploiement) ; stories non vérifiables laissées en … | 0,75 h | Claude |
| T-TECH-22-02 | Transverse | OPS | Ordre d'écriture du `composer.lock` de la démo (écrivain unique) : montées en attente décidées par Thibaut (purchase-approval v1.3.0 : T-157-12 du sprint 21 ; parc … | 0,5 h | Orchestrateur |
| T-TECH-22-03 | Transverse | OPS | ECS (`src tests`) et PHPStan max rejoués sur l'arbre exact avant chaque push de la démo (et du plugin company-insights), dans Docker ; dernière ligne de sortie citée … | 1 h | Orchestrateur |
| T-TECH-22-04 | Transverse | OPS | Disque : `df -h .` et `bin/check-disk` au démarrage et avant chaque lot ; `bin/clean-test-caches` limité au worker ; suites lancées uniquement par `WORKER_ID=wN … | 0,5 h | Orchestrateur |
| T-TECH-22-05 | Transverse | OPS | PR du sprint : CI verte (« Quality and smoke tests » et « Docker image »), **run terminé avant le merge** (`main` protégée) ; l'image n'est reconstruite qu'en CI si le … | 0,5 h | Orchestrateur |
| T-TECH-22-06 | Transverse | DOC | Bilan des indicateurs de la rétro du sprint 21 (arrêts dus au disque, écart de gate, tâches livrées restées 🔲) et des cinq indicateurs d'US-161, préparés pour la rétro … | 0,5 h | Orchestrateur |
| T-164-01 | US-164 | DOC | Cadrage : rapport d'audit de sécurité lu hors dépôt, routes récentes et parc d'équipements repérés dans le code, choix du lieu du plafond (démo ou plugin) consigné sans … | 0,75 h | Claude |
| T-164-02 | US-164 | TEST | Tests rouges, formulés en termes généraux : ajout refusé au plafond avec parc inchangé, ajout accepté sous le plafond, réponse de limitation avec en-tête d'attente sur … | 1,5 h | Claude |
| T-164-03 | US-164 | BE | Garde de plafond des écritures du parc pour les comptes de démo partagés (service injectable, valeur par paramètre d'environnement) et message clair FR et EN | 2 h | Claude |
| T-164-04 | US-164 | BE | Rattachement des routes récentes aux seaux de limitation existants (table des règles et configuration), sans créer d'exception à la règle « clé = IP du visiteur » | 1,25 h | Claude |
| T-164-05 | US-164 | BE | Remise à zéro nocturne : le compteur du plafond est réinitialisé avec les données (un compte bloqué est libéré) | 0,5 h | Claude |
| T-164-06 | US-164 | TEST | Suite ciblée puis complète via `bin/run-tests`, ECS (`src tests`), PHPStan max rejoués sur l'arbre exact ; dernière ligne de sortie citée | 1 h | Claude |
| T-164-07 | US-164 | DOC | Rapport détaillé remis à Thibaut hors dépôt ; `docs/durcissement-demo.md` complété en termes généraux (existence d'un plafond et d'une limitation, sans valeur) | 0,5 h | Claude |
| T-164-08 | US-164 | REV | Revue : relecture du diff et des messages de commit à la recherche de tout détail exploitable (route, seuil, valeur) ; parité FR et EN | 0,5 h | Claude |
| T-162-01 | US-162 | TEST | Tests rouges : aucun lien d'inscription sur l'accueil et la connexion quand l'inscription est fermée, lien présent quand elle est ouverte, accès direct toujours refusé, … | 0,75 h | Claude |
| T-162-02 | US-162 | FE-WEB | Liens « Créer un compte » masqués quand l'inscription est fermée (accueil, page de connexion, menu de compte), conservés quand elle est ouverte | 0,75 h | Claude |
| T-162-03 | US-162 | OPS | HSTS : une seule source (Caddy pour le statique, application pour le dynamique, sans doublon) ; test sur le Caddyfile et sur l'écouteur de l'application ; vérification … | 0,75 h | Claude |
| T-162-04 | US-162 | TEST | Suite ciblée via `bin/run-tests`, ECS, PHPStan ; rendu réel de l'accueil et de la connexion FR et EN avec inscription fermée puis ouverte ; `docs/durcissement-demo.md` à … | 0,75 h | Claude |
| T-162-05 | US-162 | REV | Revue (aucun lien mort restant, parité FR et EN) | 0,25 h | Claude |
| T-161-06 | US-161 | DOC | Checklist d'intégration et gabarit de rapport : commande et dernière ligne de sortie de PHPStan, ECS, lint Twig/YAML et suite ; rejeu de PHPStan et ECS par … | 1 h | Orchestrateur |
| T-161-09 | US-161 | DOC | Reste des actions du sprint 16 : arrêt explicite des tâches de fond (TaskStop) et vérification avant relance dans le gabarit de consigne ; checklist de revue « image … | 0,5 h | Orchestrateur |
| T-161-12 | US-161 | REV | 📅 Thibaut valide le format du registre `communication/INDEX.md` (nom, date, statut, sans contenu) | 0,5 h | Thibaut |
| T-161-13 | US-161 | REV | Revue : gabarits à jour, tests des scripts verts via `bin/run-tests` (rejeu), bilan des cinq indicateurs préparé pour la rétro du sprint 22 | 0,75 h | Claude |
| T-084-01 | US-084 | DOC | Cadrage : `composer rector-dry-run` rejoué (nombre de fichiers, familles de règles), étape de couverture de la CI et dernier taux publié, `proof.json` et sa date, … | 1 h | Claude |
| T-084-02 | US-084 | TEST | Tests rouges : garde sur le workflow (étape Rector présente, seuil défini, résumé du job), `bin/proof-refresh` signale un écart de nombre de tests au lieu de passer en … | 1,25 h | Claude |
| T-084-03 | US-084 | BE | Rector appliqué par lots de familles de règles sur `src` et `tests`, diff relu à chaque lot, suite ciblée entre les lots ; 0 fichier modifiable à la fin (par lots, une … | 3 h | Claude |
| T-084-04 | US-084 | BE | `bin/proof-refresh` : contrôle de retard (nombre de tests, d'assertions et de plugins comparés à la suite réelle), message d'écart, option sans écriture | 1,5 h | Claude |
| T-084-05 | US-084 | OPS | CI : étape Rector en mode vérification (échec si un fichier est modifiable), après ECS et PHPStan | 0,75 h | Claude |
| T-084-06 | US-084 | OPS | CI : seuil de couverture bloquant (échec avec taux et seuil dans le message), taux publié dans le résumé du job ; seuil fixé sur le taux mesuré moins une marge | 1,5 h | Claude |
| T-084-07 | US-084 | OPS | `bin/proof-refresh` rejoué, `config/demo/proof.json` rafraîchi (nombre de tests, assertions, plugins), diff relu | 0,75 h | Claude |
| T-084-08 | US-084 | TEST | Suite via `bin/run-tests` (si le disque le permet, sinon CI seule), ECS (`src tests`), PHPStan max, lint YAML du workflow ; première CI verte attendue avant merge | 1 h | Claude |
| T-084-09 | US-084 | DOC | Documentation à jour (`docs/tests-locaux.md`, bloc de preuve de `docs/theme-b2b.md`) : Rector en CI, seuil, procédure de rafraîchissement de la preuve | 0,5 h | Claude |
| T-084-10 | US-084 | REV | Revue (aucun secret dans le workflow, noms de jobs inchangés pour la protection de `main`, `proof.json` sans chiffre de couverture) | 0,5 h | Claude |
| T-167-01 | US-167 | DOC | Cadrage : appelants de `main_image`, boucle de la liste produits, bloc `@static` du Caddyfile lus ; mesure initiale du LCP mobile du catalogue (`bin/measure-mobile`) … | 0,75 h | Claude |
| T-167-02 | US-167 | TEST | Tests rouges : vignettes du haut sans `loading="lazy"` et première avec `fetchpriority="high"`, vignettes du bas différées, catégorie vide ou à une vignette sans erreur, … | 1 h | Claude |
| T-167-03 | US-167 | FE-WEB | Composant d'image : paramètre de position (chargement immédiat des premières vignettes, priorité haute pour la première) ; liste produits : transmission de l'indice ; … | 1,25 h | Claude |
| T-167-04 | US-167 | OPS | Caddyfile : cache long immutable sur `/build/*` seulement ; `/media/*` et `/bundles/*` conservent 7 jours | 0,5 h | Claude |
| T-167-05 | US-167 | TEST | Rendu réel du catalogue FR et EN, pa11y WCAG2AAA, `bin/measure-mobile` après modification (LCP consigné) ; suite ciblée via `bin/run-tests`, ECS, PHPStan ; construction … | 1 h | Claude |
| T-167-06 | US-167 | DOC | `docs/seo-et-vitesse.md` : règle de cache, chargement des vignettes, mesures avant/après | 0,25 h | Claude |
| T-167-07 | US-167 | REV | Revue (aucun `immutable` hors `/build/*`, accessibilité des images inchangée) | 0,25 h | Claude |
| T-165-01 | US-165 | DOC | ⚠️ Cadrage : contrôleur des listes de l'entreprise, ses tests (`CompanyWishlistSmokeTest`, `CompanyWishlistVoterTest`), usages de `BUYER_CODE`/`OCI_BUYER_CODE`, usages … | 0,5 h | Claude |
| T-165-02 | US-165 | TEST | ⚠️ Tests rouges du service des listes : écriture nominale avec un seul flush, échec d'écriture sans état partiel, erreur propre côté contrôleur | 1,25 h | Claude |
| T-165-03 | US-165 | BE | ⚠️ Service applicatif d'enregistrement de la liste de l'entreprise ; contrôleur allégé (plus d'accès direct à l'`EntityManager` pour écrire) | 1,5 h | Claude |
| T-165-04 | US-165 | BE | ⚠️ Constante neutre des codes acheteurs partagée entre le simulateur et la fixture ; la fixture dépend de la constante, le simulateur ne dépend plus de la fixture | 1 h | Claude |
| T-165-05 | US-165 | TEST | ⚠️ Test du simulateur : code acheteur absent de la constante refusé avec le message d'erreur existant ; code connu accepté | 0,5 h | Claude |
| T-165-06 | US-165 | BE | ⚠️ Suppression d'`isInDemo()` et des branches « absent de la démo » (applicateur, vérificateur, rendu de la vitrine) ; tests adaptés | 1,25 h | Claude |
| T-165-07 | US-165 | TEST | ⚠️ Rendu de la vitrine et des README identique : `bin/vitrine-readme --check` et tests de la vitrine | 0,75 h | Claude |
| T-165-08 | US-165 | TEST | ⚠️ Suite ciblée puis complète via `bin/run-tests`, ECS (`src tests`), PHPStan max rejoués sur l'arbre exact | 0,75 h | Claude |
| T-165-09 | US-165 | DOC | ⚠️ Audit qualité : les trois constats passent à « corrigée » (état, sans détail superflu) | 0,25 h | Claude |
| T-165-10 | US-165 | REV | ⚠️ Revue (aucun comportement visible modifié, injection par constructeur, SOLID) | 0,5 h | Claude |
| T-088-01 | US-088 | DOC | ⚠️ Cadrage : état réel relevé (configuration d'exclusion, tests existants, 3 types d'événements déjà rejoués, types manquants), décompte des lignes « Création » après un … | 0,75 h | Claude |
| T-088-02 | US-088 | TEST | ⚠️ Tests rouges : journal sans ligne de reset après deux resets de suite, 5 à 6 événements datés et attribués couvrant prix, validation d'inscription, plafond et … | 1,5 h | Claude |
| T-088-03 | US-088 | BE | ⚠️ Exclusion confirmée ou complétée (configuration ou purge en fin de reset) sans toucher aux écritures des visiteurs | 1,25 h | Claude |
| T-088-04 | US-088 | BE | ⚠️ Événements métier manquants dans `AuditLogDemoFixture` (validation d'inscription, plafond modifié, approbation), attribués à des comptes de démo, dates cohérentes, … | 1,5 h | Claude |
| T-088-05 | US-088 | TEST | ⚠️ Reset réel exécuté deux fois ; rendu réel du journal FR et EN, pa11y WCAG2AAA ; suite via `bin/run-tests`, ECS (`src tests`), PHPStan | 1 h | Claude |
| T-088-06 | US-088 | DOC | ⚠️ `docs/remise-a-zero-demo.md` mis à jour (journal propre, événements rejoués) | 0,25 h | Claude |
| T-088-07 | US-088 | REV | ⚠️ Revue (aucun secret, chiffres vérifiables, parité FR et EN) | 0,25 h | Claude |
| T-098-01 | US-098 | DOC | ⚠️ Cadrage : gabarit de la jauge de la fiche produit repéré (plugin ou démo), `docs/remplissage-contenants.md` lu, définition de « pertinent » confirmée, emplacement du … | 0,5 h | Claude |
| T-098-02 | US-098 | TEST | ⚠️ Tests rouges : 2 contenants pertinents sur 9, détails repliables au clavier, 9 sur 9 sans contrôle « détails », aucune donnée de contenant (bloc masqué), `<details>` … | 1 h | Claude |
| T-098-03 | US-098 | FE-WEB | ⚠️ Gabarit : filtre des contenants pertinents, `<details>` des autres, libellés FR et EN, contraste AAA | 1,25 h | Claude |
| T-098-04 | US-098 | TEST | ⚠️ Rendu réel FR et EN, pa11y WCAG2AAA, `bin/measure-mobile` ; suite ciblée via `bin/run-tests`, ECS (`src tests`), PHPStan | 0,75 h | Claude |
| T-098-05 | US-098 | DOC | ⚠️ `docs/remplissage-contenants.md` mis à jour | 0,25 h | Claude |
| T-098-06 | US-098 | REV | ⚠️ Revue (ton neutre, parité FR et EN, accessibilité) | 0,25 h | Claude |
| T-154-01 | US-154 | DOC | ⚠️ Cadrage : `git fetch`, branche depuis `origin/main` (v0.2.1), branche vérifiée ; matrice CI et `composer.json` relus ; services publics de quote-request et tier-pricing, … | 1 h | Claude |
| T-154-02 | US-154 | TEST | ⚠️ Tests rouges : indicateurs des devis (4 demandes, 3 devis, 2 commandes, 67 %, délai médian, expirés), économie (120 casques à 9 € au lieu de 11 € = 240 € HT), grille … | 1,5 h | Claude |
| T-154-03 | US-154 | BE | ⚠️ Widget « Devis » (fournisseur de widget, permission, taux « non applicable » sans demande, repli « Données indisponibles ») | 2 h | Claude |
| T-154-04 | US-154 | BE | ⚠️ Widget « Économies » (différence prix de base et prix figé, sommée sur la période, rapportée à la dépense HT) | 2 h | Claude |
| T-154-05 | US-154 | FE-WEB | ⚠️ Rendu des deux widgets avec tableau équivalent ; libellés FR et EN, libellé factuel « Économies sur prix dégressifs » ; contraste AAA ; mobile | 1 h | Claude |
| T-154-06 | US-154 | TEST | ⚠️ Dans Docker : ECS, PHPStan max, Rector, suite avec et sans compagnons ; rendu réel FR et EN, pa11y WCAG2AAA ; lecture du code pour les cellules PHP 8.2 de la matrice | 1 h | Claude |
| T-154-07 | US-154 | OPS | ⚠️ Captures des deux widgets pour le README (application de test, données fictives) ; relevé du disque avant | 0,5 h | Claude |
| T-154-08 | US-154 | REV | ⚠️ Revue (économie sur prix figé, cloisonnement entreprise, journal sans donnée de commande) rapportée hors dépôt public, et revue de code | 0,5 h | Claude |
| T-154-09 | US-154 | DOC | ⚠️ README EN et FR (deux widgets, plugins optionnels, versions supportées), `CHANGELOG.md` de la 0.3.0, captures | 0,5 h | Claude |
| T-154-10 | US-154 | OPS | ⚠️ 📅 Thibaut donne le GO du tag et de la release `v0.3.0` de company-insights | 0,25 h | Thibaut |
| T-154-11 | US-154 | OPS | ⚠️ Push, PR, matrice complète verte (run terminé), merge, tag `v0.3.0` et release GitHub **après le GO** ; script de vérification Packagist remis à Thibaut | 0,75 h | Claude |
| T-154-12 | US-154 | OPS | ⚠️ 📅 Thibaut confirme que Packagist indexe la 0.3.0 (paquet déjà soumis) | 0,25 h | Thibaut |
| T-154-13 | US-154 | OPS | ⚠️ Vérification de la version publiée (script du scratchpad à OK ou FAIL) avant toute PR de la démo | 0,25 h | Claude |
| T-154-14 | US-154 | BE | ⚠️ Démo : `php -d memory_limit=1G composer require tmonier/sylius-company-insights-plugin:^0.3` ; `composer.lock` écrit seul (après les montées en attente, T-TECH-22-02) | 0,5 h | Claude |
| T-154-15 | US-154 | TEST | ⚠️ Tests de la démo : widgets d'Acme (devis, économies), TechParts absent des chiffres d'Acme ; scénario d'Isabelle dans `config/demo/plugins.yaml` et … | 1 h | Claude |
| T-154-16 | US-154 | OPS | ⚠️ Fin de worker : suppression de `vendor`, des caches et de l'image du plugin, puis des caches du worker de démo ; `df -h .` avant et après | 0,25 h | Claude |
| T-062-01 | US-062 | TEST | ⚠️ Tests rouges (client HTTP simulé) : image conforme enregistrée comme avant, `Content-Length` excessif ou pixels hors limite refusés et consignés, 429 suivi d'une attente … | 0,75 h | Claude |
| T-062-02 | US-062 | BE | ⚠️ Limites de pixels et de `Content-Length` avant lecture, lecture plafonnée en `chunked`, refus consigné dans le rapport du script | 0,75 h | Claude |
| T-062-03 | US-062 | BE | ⚠️ Gestion du 429 (attente bornée selon `Retry-After`, nombre d'essais limité) et conservation de l'image principale quand le manifeste change | 0,75 h | Claude |
| T-062-04 | US-062 | TEST | ⚠️ Suite ciblée via `bin/run-tests`, ECS (`src tests`), PHPStan max ; `docs/images-produits.md` mis à jour (limites, 429) | 0,5 h | Claude |

## 🔄 En cours

| ID | US | Note |
|----|-----|------|
| US-161 (solde) | US-161 | Story en cours depuis le sprint 18 ; tâches restantes ci-dessus (T-161-06, T-161-09, T-161-12, T-161-13) |

## 👀 En review

Aucune tâche. Les stories des sprints 17 à 21 en `review` sont traitées par T-TECH-22-01 (vérification en production puis `done`).

## ✅ Terminé

| ID | US | Note |
|----|-----|------|
| US-163 | US-163 | Livrée le 2026-10-23 (PR #57, 2 points) : les 4 vérifications US-079 confirmées par Thibaut |

## 🚫 Bloqué

Aucune tâche bloquée au lancement. Risque : la vérification en production (T-TECH-22-01c à f) attend le déploiement de Thibaut (T-TECH-22-01b).
