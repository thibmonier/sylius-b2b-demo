# Task Board : Sprint 018 (repositionnement)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Calendaire (action de Thibaut) | ⚠️ Sacrifiable

Total : 101 tâches, 92,5 h (6,5 h d'actions de Thibaut). État à la clôture du 2026-10-21 (merge de la PR #43, 9a4c461) : 64 ✅, 6 🔄, 29 🔲, 2 📅 ; US-147 sortie du sprint (correctif de sécurité PHP 8.5 non publié).

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-068-08 | US-068 | REV | Revue : `--check` sur les 24 branches principales, liens qui répondent, aucun secret — *Clôture 2026-10-21 : `--check` sur les 24 branches principales pas encore rejoué* | 0,5 h | Claude |
| T-103-03 | US-103 | OPS | Thibaut réalise la conversation réelle avec son assistant sur le MCP de production, enregistre le transcript brut et un GIF ou une vidéo courte | 1 h | Thibaut |
| T-103-07 | US-103 | DOC | Relecture du transcript : retrait de toute donnée privée avant intégration | 0,25 h | Claude |
| T-103-09 | US-103 | REV | Revue (aucune donnée personnelle ni secret dans le transcript, URL de production) | 0,5 h | Claude |
| T-111-08 | US-111 | REV | Revue (aucun chiffre sans source, aucun secret) | 0,5 h | Claude |
| T-124-04 | US-124 | REV | Thibaut relit la tranche 1 | 0,5 h | Thibaut |
| T-124-05 | US-124 | OPS | Thibaut relève les chiffres des semaines 1 et 2 (vues du profil, messages reçus) | 0,5 h | Thibaut |
| T-124-10 | US-124 | REV | Thibaut relit la tranche 2 ⚠️ | 0,5 h | Thibaut |
| T-147-02 | US-147 | TEST | Tests rouges : un test lit Dockerfile, `ci.yaml` et `composer.json` et exige PHP 8.5 partout ; `bin/check-pinned-refs` échoue sur une image référencée par tag seul | 1 h | Claude |
| T-147-03 | US-147 | OPS | Dockerfile (nouveau digest), deux jobs de la CI en 8.5, `composer.json` (`^8.5`, `platform.php` 8.5.x), `composer.lock` régénéré sans mise à jour de paquets (`composer validate`, `composer install`) | 1,5 h | Claude |
| T-147-04 | US-147 | BE | Dépréciations PHP 8.5 levées par `src/` corrigées (suite avec `E_ALL`) ; celles des dépendances listées sans être corrigées | 1 h | Claude |
| T-147-05 | US-147 | OPS | Reconstruction de l'image, `php -v` (version corrigée), extensions (intl, gd ou imagick, pdo_mysql, opcache, apcu), `docker/php.ini`, `/healthz`, mode worker (ADR-0013) revérifié, smoke test et scan d'image | 1,5 h | Orchestrateur |
| T-147-06 | US-147 | TEST | Suite complète, ECS, PHPStan en 8.5 ; rendu réel local (vitrine, compte acheteur, admin) ; preuves rejouables | 1 h | Claude |
| T-147-07 | US-147 | DOC | ADR-0014 complété (versions de la démo, dépréciations des dépendances) ; l'orchestrateur met à jour la mémoire « dernières versions » | 0,5 h | Claude |
| T-147-08 | US-147 | OPS | Thibaut redéploie sur Railway après le merge et vérifie `/healthz` et la version de PHP | 0,5 h | Thibaut |
| T-147-09 | US-147 | REV | Revue (digest épinglé, aucun détail de la faille, CI verte, run terminé avant merge) | 0,5 h | Claude |
| T-148-12 | US-148 | REV | Revue (aucune occurrence du type concret hors instanciation, 5 CHANGELOG, matrices vertes) | 0,5 h | Claude |
| T-158-06 | US-158 | TEST | Rendu réel FR et EN : accueil, fiche produit, écran de sortie d'un parcours, `/integrer`, avec URL renseignée puis vide ; `bin/measure-mobile` (23 pages sans débordement) — *Clôture 2026-10-21 : rendu réel et `bin/measure-mobile` non rejoués à la clôture* | 1 h | Claude |
| T-158-07 | US-158 | TEST | pa11y WCAG2AAA sur les pages modifiées, FR et EN (0 erreur) — *Clôture 2026-10-21 : pa11y non rejoué à la clôture* | 0,5 h | Claude |
| T-158-08 | US-158 | REV | Revue : aucun libellé commercial, aucun secret, `DEMO_BOOKING_URL` absente, fichiers partagés libérés pour US-160 et US-111 | 1 h | Claude |
| T-158-10 | US-158 | OPS | Thibaut valide les libellés exacts et pose `DEMO_PROFILE_URL` sur Railway après le merge (commande Railway par Thibaut) — *Clôture 2026-10-21 : à faire après le merge : `railway variables --set DEMO_PROFILE_URL=…`, puis retrait de `DEMO_BOOKING_URL`* | 0,25 h | Thibaut |
| T-159-04 | US-159 | REV | Thibaut relit et ajuste le post 1 | 0,5 h | Thibaut |
| T-159-05 | US-159 | OPS | Thibaut publie le post 1 avant le 2026-10-26 et épingle le commentaire | 0,5 h | Thibaut 📅 |
| T-159-10 | US-159 | REV | Thibaut relit le calendrier et les posts 2 et 3 | 0,5 h | Thibaut |
| T-159-11 | US-159 | OPS | Thibaut publie les posts 2 et 3 aux dates du calendrier | 0,5 h | Thibaut 📅 |
| T-160-08 | US-160 | REV | Revue (GET réellement sans effet de bord, aucun détail exploitable, verrou testé 20 fois) | 1 h | Claude |
| T-161-06 | US-161 | DOC | Checklist d'intégration et gabarit de rapport : commande et dernière ligne de sortie de PHPStan, ECS, lint Twig/YAML et suite ; rejeu de PHPStan et ECS par l'orchestrateur ; rapport refusé sans preuve ; suite en échec rejouée seule avant enquête | 1 h | Orchestrateur |
| T-161-09 | US-161 | DOC | Reste des actions du sprint 16 (T-TECH-06 et T-TECH-07) : arrêt explicite (TaskStop) et vérification avant relance dans le gabarit de consigne ; checklist de revue « image reconstruite après correctif » | 0,5 h | Orchestrateur |
| T-161-12 | US-161 | REV | Thibaut valide le format du registre | 0,5 h | Thibaut |
| T-161-13 | US-161 | REV | Revue : tests des scripts verts via `bin/run-tests`, gabarits à jour, bilan des cinq indicateurs préparé pour la rétro | 0,75 h | Claude |
| T-TECH-01 | Transverse | OPS | Reconstruction de l'image et démarrage du conteneur (`/healthz`) par l'orchestrateur avant la revue, puis après tout correctif touchant l'image | 1 h | Orchestrateur |

## 🔄 En Cours

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-111-06 | US-111 | TEST | Rendu réel FR et EN, pa11y WCAG2AAA, `bin/measure-mobile`, suite complète, ECS, PHPStan — *Clôture 2026-10-21 : suite, ECS, PHPStan et `ProofPageTest` verts ; `proof.json` rafraîchi (1055d17) ; pa11y, `bin/measure-mobile` et rendu réel non rejoués* | 1 h | Claude |
| T-124-06 | US-124 | DOC | Révision de la tranche 2 d'après les retours (angles conservés ou abandonnés, justification notée) ⚠️ | 1 h | Claude |
| T-103-08 | US-103 | TEST | Rendu réel FR et EN, pa11y WCAG2AAA, `bin/measure-mobile`, suite complète, ECS, PHPStan — *Clôture 2026-10-21 : suite, ECS et PHPStan verts ; rendu réel, pa11y et `bin/measure-mobile` attendent le transcript réel (T-103-03)* | 0,75 h | Claude |
| T-147-01 | US-147 | OPS | Veille : l'image FrankenPHP PHP 8.5 corrigée est-elle publiée (version de PHP au moins égale au correctif, digest relevé) ? Veille programmée de l'orchestrateur — *Clôture 2026-10-21 : veille horaire active ; à 13:29 UTC, PHP 8.5.11 et digest `1-php8.5-bookworm` non corrigé : story sortie du sprint, PR séparée dès l'image corrigée* | 0,5 h | Orchestrateur |
| T-148-06 | US-148 | TEST | Dans chaque plugin : ECS, PHPStan max, Rector, tests ; compilation du conteneur en autowiring (service surchargé avec `autowire: true`) sur Symfony 8.1 et 7.4 (Docker) — *Clôture 2026-10-21 : ECS, PHPStan et tests passés par la CI des plugins (matrices Symfony 7.4 et 8.0 vertes) ; cellule Symfony 6.4 retirée ; test d'autowiring d'un service surchargé non écrit* | 1 h | Claude |
| T-160-07 | US-160 | TEST | Suite complète sur trois graines aléatoires, ECS, PHPStan ; pa11y WCAG2AAA accueil FR et EN (connecté et anonyme) ; `bin/measure-mobile` — *Clôture 2026-10-21 : graines 1818, 2121 et 3131 vertes après 7de1789 (deux tests rendus indépendants de l'état laissé par d'autres tests) ; ECS et PHPStan OK ; pa11y et `bin/measure-mobile` non faits* | 1,5 h | Claude |

## 👀 En Review

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|

## ✅ Terminé

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-068-01 | US-068 | TEST | Tests rouges de l'outil (`VitrineReadmeTest`) : deux liens EN et FR identiques partout, relance sans diff, plugin sans démo (profil seul), URL absente refusée, terme commercial ou lien mort signalé | 1 h | Claude |
| T-068-02 | US-068 | BE | `bin/vitrine-readme apply` : liens démo et profil (argument d'URL), refus sans URL, détection du plugin sans démo | 1 h | Claude |
| T-068-03 | US-068 | BE | `--check` : termes « mission », « devis », « prestation », liens morts, cohérence des liens entre les 24 dépôts | 0,75 h | Claude |
| T-068-04 | US-068 | OPS | Application aux 24 dépôts (`git fetch`, branche courante vérifiée, une branche par dépôt), `--check` avant et après, aucun push — *Clôture 2026-10-21 : 24 branches `docs/readme-demo-profile`, une par dépôt* | 1 h | Claude |
| T-068-05 | US-068 | OPS | Thibaut donne le GO unique : push des 24 branches, 24 PR, merge quand les CI sont vertes — *Clôture 2026-10-21 : GO donné le 2026-10-21* | 0,25 h | Thibaut |
| T-068-06 | US-068 | OPS | 24 PR (une par dépôt) et suivi de chaque CI — *Clôture 2026-10-21 : 24 PR ouvertes, CI vertes* | 1,25 h | Claude |
| T-068-07 | US-068 | OPS | Merge des 24 PR après run terminé de chacune (branche principale protégée) — *Clôture 2026-10-21 : 24 PR fusionnées (squash) le 2026-10-21, lancées par Thibaut via un script ; purchase-approval #5 (correctif du test des onglets) fusionnée avant #4* | 0,75 h | Claude |
| T-103-01 | US-103 | DOC | Fiche complétée à cinq scénarios (1 nominal, 2 alternatifs, 2 erreurs) | 0,5 h | Claude |
| T-103-02 | US-103 | DOC | Script de la conversation (4 à 6 échanges, outils MCP appelés, compte de démo, rappel que l'assistant ne paie jamais) à partir de `docs/mcp.md` et de la page existante | 1 h | Claude |
| T-103-04 | US-103 | TEST | Tests rouges : transcript daté visible, transcript sans vidéo, bloc masqué sans enregistrement, URL MCP de l'hôte public, jeton, e-mail ou `localhost` refusés | 1 h | Claude |
| T-103-05 | US-103 | BE | Fichier de données du transcript, validation au chargement, URL MCP depuis l'hôte public | 1 h | Claude |
| T-103-06 | US-103 | FE-WEB | Bloc transcript daté et vidéo ou GIF (alternative textuelle, sans lecture automatique), masqué si absent ; libellés FR et EN ; `docs/mcp.md` | 1,25 h | Claude |
| T-111-01 | US-111 | DOC | Inventaire des sources vérifiables par plugin (Packagist, CI, PHPStan, tests, couverture) et décision de forme consignée dans la story | 0,5 h | Claude |
| T-111-02 | US-111 | TEST | Tests rouges : tableau complet, plugin non publié, couverture non mesurée, donnée sans source non affichée, source en échec (dernière valeur datée, pas de 500) | 1,25 h | Claude |
| T-111-03 | US-111 | BE | Lecture du fichier de données, service et contrôleur de la page de preuve | 1,25 h | Claude |
| T-111-04 | US-111 | BE | Commande manuelle de rafraîchissement (Packagist, GitHub Actions) avec liens de source ; première série de données | 1,25 h | Claude |
| T-111-05 | US-111 | FE-WEB | Gabarit du tableau et ligne « Comment cette démo est livrée » ; libellés FR et EN ; 320 px | 1,25 h | Claude |
| T-111-07 | US-111 | DOC | Documentation (`docs/theme-b2b.md`, procédure de rafraîchissement) | 0,5 h | Claude |
| T-124-01 | US-124 | DOC | Angles des six posts rattachés aux piliers, sources publiques vérifiées (sans détail de sécurité) | 1 h | Claude |
| T-124-02 | US-124 | DOC | Tranche 1 : posts des semaines 3 et 4 (accroche, corps, CTA unique, visuel, lien UTM, variante < 600 caractères) | 2 h | Claude |
| T-124-03 | US-124 | TEST | `charter_check.py --post` sur la tranche 1 | 0,5 h | Claude |
| T-124-07 | US-124 | DOC | Posts des semaines 5 et 6 ⚠️ | 2 h | Claude |
| T-124-08 | US-124 | DOC | Posts des semaines 7 et 8 ⚠️ | 2 h | Claude |
| T-124-09 | US-124 | TEST | `charter_check.py --post` sur la tranche 2 ; au plus 2 mentions de disponibilité sur les six posts ; aucune date de sortie ⚠️ | 0,5 h | Claude |
| T-124-11 | US-124 | DOC | Registre `communication/INDEX.md` à jour | 0,25 h | Claude |
| T-148-01 | US-148 | BE | punchout-cxml : test rouge (aucun `RateLimiterFactory` hors instanciation), puis 10 occurrences typées `RateLimiterFactoryInterface` — *Clôture 2026-10-21 : test `RateLimiterTypeTest` et typage par l'interface* | 1,5 h | Claude |
| T-148-02 | US-148 | BE | quote-request : idem (8 à 10 occurrences) ; branche vérifiée après `git fetch` | 0,75 h | Claude |
| T-148-03 | US-148 | BE | quick-order : idem | 0,75 h | Claude |
| T-148-04 | US-148 | BE | b2b-mcp : idem (`RateLimit/RateLimiter`, `RequestQuoteTool`) | 0,75 h | Claude |
| T-148-05 | US-148 | BE | command-palette : idem | 0,5 h | Claude |
| T-148-07 | US-148 | DOC | `CHANGELOG.md` de chaque plugin (créé pour quote-request et command-palette), numéro de version patch — *Clôture 2026-10-21 : versions mineures pour quatre plugins (Symfony 6.4 abandonné), patch pour b2b-mcp* | 0,5 h | Claude |
| T-148-08 | US-148 | OPS | Thibaut donne le GO unique : push des 5 branches, 5 PR, merge quand les CI sont vertes, tag des 5 versions patch — *Clôture 2026-10-21 : GO le 2026-10-21, avec la décision d'abandonner Symfony 6.4 (`RateLimiterFactoryInterface` existe depuis Symfony 7.3)* | 0,25 h | Thibaut |
| T-148-09 | US-148 | OPS | 5 commits, 5 PR, suivi des CI (matrices 7.4 et 8.1), merges après run terminé — *Clôture 2026-10-21 : premier run rouge sur la cellule Symfony 6.4 des quatre plugins, vert après retrait ; 5 PR fusionnées* | 1 h | Claude |
| T-148-10 | US-148 | OPS | Tags et releases patch des cinq plugins après merge ; vérification Packagist — *Clôture 2026-10-21 : punchout-cxml v1.3.0, quote-request v1.1.0, quick-order v1.3.0, command-palette v1.1.0, b2b-mcp v1.2.2 ; Packagist vérifié* | 0,5 h | Claude |
| T-148-11 | US-148 | BE | Démo : montée des cinq versions (`composer update` ciblé), tests de limitation de débit existants verts, aucun limiteur contourné — *Clôture 2026-10-21 : 586e2bb ; suite complète verte (1 633 tests, graine 3131)* | 1 h | Claude |
| T-158-01 | US-158 | BE | CTA vers `DEMO_PROFILE_URL` (https seulement, valeur http ou inconnue ignorée), `DEMO_BOOKING_URL` retirée de `config/` et de `.railway/railway.ts`, événement Umami `author_cta` | 2 h | Claude |
| T-158-02 | US-158 | FE-WEB | Libellés neutres FR et EN (accueil, fiche produit, pied de page, barre de démo, SEO), page `/integrer` en guide technique, écran de sortie des parcours | 3 h | Claude |
| T-158-03 | US-158 | TEST | `PositioningWordingTest` (termes interdits dans `translations/` et `templates/`) et tests du CTA, de la page Intégrer et des parcours | 2 h | Claude |
| T-158-04 | US-158 | DOC | Documentation (`docs/deploiement-railway.md`, `theme-b2b.md`, `mesure-communication.md`, `dossier-de-go.md`) | 1 h | Claude |
| T-158-05 | US-158 | TEST | Suite complète via `TEST_DB_NAME=<db> bin/run-tests`, ECS (`src tests`) et PHPStan ; preuves rejouables dans le rapport — *Clôture 2026-10-21 : suite complète verte (1 633 tests), ECS et PHPStan en local et dans la CI de la PR #43* | 1 h | Claude |
| T-158-09 | US-158 | OPS | Thibaut fournit l'URL de son profil LinkedIn (`DEMO_PROFILE_URL`, https) par message ; elle ne va pas dans le dépôt — *Clôture 2026-10-21 : URL fournie hors dépôt* | 0,25 h | Thibaut |
| T-159-01 | US-159 | DOC | Cadrage : relecture charte v2, profil v2 et posts du lot 1 ; liste des formules de consultant à retirer ; angle des trois posts | 0,75 h | Claude |
| T-159-02 | US-159 | DOC | Post de suite n°1 : pilier, accroche, corps, variante de moins de 600 caractères, visuel, CTA unique | 1,5 h | Claude |
| T-159-03 | US-159 | TEST | `charter_check.py --post` sur le post 1 ; chaque chiffre rattaché à une source publique | 0,5 h | Claude |
| T-159-06 | US-159 | DOC | Posts 2 et 3 réécrits avec variantes et légendes du kit média (US-120) | 2 h | Claude |
| T-159-07 | US-159 | DOC | Calendrier de huit semaines : 3 posts delivery et IA, 2 qualité, 2 coulisses, 1 leadership ; au plus 2 mentions de disponibilité | 1,5 h | Claude |
| T-159-08 | US-159 | TEST | `charter_check.py` sur posts 2 et 3 et calendrier ; chiffres invérifiables retirés | 0,5 h | Claude |
| T-159-09 | US-159 | DOC | Registre `communication/INDEX.md` à jour (livrables du sprint 18) | 0,25 h | Claude |
| T-160-01 | US-160 | TEST | Test de concurrence rouge : deux runs reprennent un verrou périmé en même temps, une seule obtient le verrou, 20 itérations ; verrou orphelin repris et journalisé | 1 h | Claude |
| T-160-02 | US-160 | OPS | `bin/run-tests` : reprise atomique du verrou périmé (renommage vers un nom unique puis `mkdir`), message journalisé | 1 h | Claude |
| T-160-03 | US-160 | TEST | Tests fonctionnels rouges : GET d'étape répété ou préchargé sans modification de session ; avancement par POST avec jeton CSRF | 1 h | Claude |
| T-160-04 | US-160 | BE | `JourneyController` et `JourneyProgress` : GET sans effet de bord, avancement par POST, formulaire « Suivant » | 1,5 h | Claude |
| T-160-05 | US-160 | TEST | Tests rouges : menu compte au nom accessible FR et EN ; nom et rôle du compte non rendus aux anonymes | 1 h | Claude |
| T-160-06 | US-160 | FE-WEB | Surcharge du gabarit du menu compte (nom accessible), information réduite pour les anonymes, libellés FR et EN | 2 h | Claude |
| T-161-01 | US-161 | TEST | Tests rouges de `bin/check-disk` (script shell) : alerte sous 15 Go, distinction projet et machine, seuil paramétrable | 0,75 h | Claude |
| T-161-02 | US-161 | OPS | `bin/check-disk` et appel par `bin/run-tests` (affichage, gel à 5 Go conservé) | 1,25 h | Claude |
| T-161-03 | US-161 | TEST | Tests rouges : un clean du worker A laisse la base du worker B ; le nettoyage global est refusé hors orchestrateur | 0,75 h | Claude |
| T-161-04 | US-161 | OPS | `bin/clean-test-caches` limité aux caches et bases du worker appelant (`WORKER_ID`), `--all` pour l'orchestrateur hors lots | 1,25 h | Claude |
| T-161-05 | US-161 | DOC | `docs/tests-locaux.md` et gabarit de consigne de worker : un sous-dossier de scratchpad, une base et un cache par worker, clean ciblé | 0,5 h | Claude |
| T-161-07 | US-161 | TEST | Tests rouges de `--wait` : verrou absent (sortie immédiate), verrou présent (attente), délai dépassé (sortie avec rapport) | 0,75 h | Claude |
| T-161-08 | US-161 | OPS | Option `--wait` de `bin/run-tests` (délai 30 min, variable de délai), lecture du verrou | 1,25 h | Claude |
| T-161-10 | US-161 | DOC | Rubrique « prérequis d'environnement » dans le gabarit de story ; contrôle au planning (story de captation ou d'intégration externe non engagée sans elle) | 0,75 h | Claude |
| T-161-11 | US-161 | DOC | Registre `communication/INDEX.md` (nom, date, statut, sans contenu) avec les livrables du sprint 17 | 1 h | Claude |
| T-TECH-02 | Transverse | DOC | Bilan des cinq indicateurs de la rétro S17 (blocage disque, fichiers écrasés, écarts « vert annoncé », workers bloqués, prérequis découverts) préparé pour la rétro du 2027-03-05 — *Clôture 2026-10-21 : bilan partiel dans [sprint-retro.md](../sprint-retro.md)* | 0,5 h | Orchestrateur |

## 🚫 Bloqué

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
