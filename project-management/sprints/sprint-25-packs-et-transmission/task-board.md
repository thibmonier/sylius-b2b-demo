# Task Board : Sprint 025 (packs et transmission)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Dépend d'une action de Thibaut | ⚠️ Sacrifiable

Total décomposé : **62 tâches, 42,5 h** pour 17 points engagés ; **41,25 h pour Claude**, 1,25 h d'actions de Thibaut. État au 2026-10-24 (lancement) : 4 ✅ (actions de la rétro du sprint 24 appliquées au planning), 12 📅, 46 🔲.

Règle (action 5 de la rétro du sprint 22) : une tâche passe en ✅ dans le commit qui la livre. Sacrifices : US-177 (n°1), puis US-180 (n°2) ; leurs tâches portent ⚠️ dans leur fichier si le sacrifice est décidé.

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-176-01 | US-176 | OPS | Clones des deux dépôts de packs dans `/Users/tmonier/Projects/sylius_plugins/` (lecture seule d'abord), `git fetch` et branche courante contrôlées, br … | 0,25 h | Claude |
| T-176-02 | US-176 | DOC | Cadrage : README, `composer.json`, `etc/composition.json` et CI actuelle des deux packs lus ; gestes de l'ADR 0015 (bundles, configuration, routes, mi … | 0,75 h | Claude |
| T-176-03 | US-176 | TEST | Tests rouges, commités seuls et cités dans la PR de chaque dépôt : fichier de gestes appliqué sur un dossier fictif, README cohérent avec le fichier d … | 1,25 h | Claude |
| T-176-04 | US-176 | BE | Pack « IA et headless » : fichier de gestes, script d'application, enveloppe d'étape | 1,25 h | Claude |
| T-176-05 | US-176 | BE | Pack « Conformité et outillage » : mêmes fichiers (duplication assumée, deux dépôts : règle des trois) | 0,5 h | Claude |
| T-176-06 | US-176 | OPS | Workflow du pack IA : job d'installation en matrice Sylius 2.2 et 2.3 (projet vierge, dépôt `path`, gestes, migrations, `lint:container`, `doctrine:sc … | 1,25 h | Claude |
| T-176-07 | US-176 | OPS | Workflow du pack Conformité : même job | 0,5 h | Claude |
| T-176-08 | US-176 | OPS | Job « deux packs ensemble » : les deux packs dans un même projet, conteneur compilé sans conflit de service ni de route (`debug:router`) | 0,75 h | Claude |
| T-176-14 | US-176 | REV | Revue (aucun plugin exclu dans un pack, ton neutre, parité FR et EN, aucun détail de sécurité) et table « scénario → preuve » remplie | 0,25 h | Claude |
| T-178-01 | US-178 | DOC | Cadrage : forme du fichier de données (plugins, commande, gestes restants, source et date), mécanisme de retard repris de la matrice (US-171), route, … | 0,5 h | Claude |
| T-178-02 | US-178 | TEST | Tests rouges, commités seuls et cités dans la PR : page FR et EN (packs, plugins, commande, gestes restants), liste de la page égale à celle du fichie … | 0,75 h | Claude |
| T-178-03 | US-178 | BE | Collecteur et lecteur des données, commande `bin/packs-refresh` (lecture seule via `gh`), contrôle de retard branché sur `bin/proof-check`, route loca … | 0,75 h | Claude |
| T-178-04 | US-178 | FE-WEB | Gabarit et textes FR et EN (packs, plugins, commande d'installation, gestes restants), liens depuis les pages Intégrer et Architecture | 0,75 h | Claude |
| T-178-05 | US-178 | OPS | Première génération réelle après le merge d'US-176 (`php bin/packs-refresh`, jeu de données commité) ; `php bin/proof-refresh` puis `php bin/proof-che … | 0,5 h | Claude |
| T-178-06 | US-178 | TEST | Suite ciblée via `bin/run-tests` (`WORKER_ID=w178 …`), ECS (`src tests`), PHPStan max, Rector, lint Twig et YAML | 0,5 h | Claude |
| T-178-07 | US-178 | TEST | Rendu en production après le déploiement automatique de `main` : pa11y WCAG2AAA en FR et en EN, 360 px sans défilement horizontal, 1280 px, liens d'ac … | 0,75 h | Claude |
| T-178-08 | US-178 | REV | Revue (ton neutre, liste lue et non recopiée, parité FR et EN) et table « scénario → test » remplie | 0,25 h | Claude |
| T-177-01 | US-177 | DOC | Cadrage : fonctionnement des recettes Flex (manifeste, `copy-from-recipe`, `bundles`), endpoint de recettes local pour l'essai, contenu des recettes t … | 0,75 h | Claude |
| T-177-02 | US-177 | OPS | Essai de la porte de décision : projet jetable minimal avec Flex, métapaquet de pack avec recette servie par l'endpoint local ; la recette est-elle ap … | 1,5 h | Claude |
| T-177-03 | US-177 | DOC | Résultat de l'essai consigné dans l'ADR 0015 (question ouverte 3) et décision d'arrêt ou de poursuite ; si arrêt : T-177-04 à T-177-13 écartées avec l … | 0,25 h | Orchestrateur |
| T-177-04 | US-177 | TEST | Tests rouges, commités seuls et cités dans la PR de chaque dépôt : manifeste valide, fichiers de la recette présents, bundles de la recette égaux aux … | 1 h | Claude |
| T-177-05 | US-177 | BE | Recette du pack « IA et headless » : manifeste, `config/packages/`, `config/routes/` | 1,25 h | Claude |
| T-177-06 | US-177 | BE | Recette du pack « Conformité et outillage » | 0,75 h | Claude |
| T-177-07 | US-177 | TEST | Essai d'installation puis de retrait (`composer remove`) dans un projet jetable : la recette retire ce qu'elle a ajouté, un fichier préexistant n'est … | 1 h | Claude |
| T-177-08 | US-177 | OPS | Variante de la CI d'US-176 sans les gestes du README (recette appliquée par l'endpoint local), en matrice Sylius 2.2 et 2.3 | 1 h | Claude |
| T-177-09 | US-177 | DOC | README FR et EN : gestes remplacés par « appliqué par la recette » là où c'est vrai, gestes restants (classes de l'application) conservés ; texte relu … | 0,5 h | Claude |
| T-177-14 | US-177 | REV | Revue (essai rejouable, aucun écrasement, ton neutre, parité FR et EN, aucune PR sans GO) et table « scénario → preuve » remplie | 0,5 h | Claude |
| T-180-01 | US-180 | DOC | Cadrage : squelette du plugin modèle (composer.json, matrice de CI, outils de qualité, application de test) lu ; événement et classe `FacturXInvoiceGe … | 1 h | Claude |
| T-180-02 | US-180 | OPS | Squelette du plugin en local (copie nettoyée du modèle, `git init`, `composer.json`, CI sur la matrice commune, outils de qualité, LICENSE) ; ressourc … | 1 h | Claude |
| T-180-03 | US-180 | TEST | Tests rouges, commités seuls et cités dans la PR : les 5 scénarios (facture transmise et journalisée, événement rejoué, adaptateur nul par défaut, ada … | 1,5 h | Claude |
| T-180-04 | US-180 | BE | Port `InvoiceTransmitterInterface`, résultat de transmission, statuts (ensemble minimal, extensible), `NullInvoiceTransmitter` | 1 h | Claude |
| T-180-05 | US-180 | DB | Entité du journal des envois (facture, statut, message, dates), mapping, migration avec unicité par identifiant de facture, dépôt | 1 h | Claude |
| T-180-06 | US-180 | BE | Écouteur de `tmonier_facturx.invoice_generated` : appel de l'adaptateur, journalisation, idempotence, exception attrapée, statut inconnu refusé | 1 h | Claude |
| T-180-07 | US-180 | BE | Bundle et configuration (adaptateur choisi par service, défaut nul), enregistrement de l'écouteur, double de test `RecordingInvoiceTransmitter` | 0,75 h | Claude |
| T-180-08 | US-180 | TEST | Suite complète en local (commande du Makefile du plugin, dans Docker), ECS, PHPStan max, Rector ; dernières lignes citées ; base et vendor de l'essai … | 0,75 h | Claude |
| T-180-09 | US-180 | DOC | README FR et EN (installation, port, statuts minimaux « à vérifier », limites : aucune plateforme réelle), CHANGELOG ; texte relu contre le code et l' … | 1 h | Claude |
| T-180-10 | US-180 | REV | Revue (ton neutre, aucune plateforme réelle, `facturx` inchangé, parité FR et EN) et table « scénario → test » remplie | 0,25 h | Claude |
| T-TECH-25-05 | Transverse | OPS | Ressources avant chaque lot : `df -h .`, `bin/check-disk` et mémoire libre relevés ; **après chaque lot** : aucun run actif, `bin/clean-test-caches -- … | 0,75 h | Orchestrateur |
| T-TECH-25-06 | Transverse | OPS | ECS (`src tests`), PHPStan max et Rector rejoués sur l'arbre exact avant chaque push de la démo ; dans les dépôts des packs et du nouveau plugin, les … | 0,75 h | Orchestrateur |
| T-TECH-25-07 | Transverse | OPS | CI verte avant chaque merge (démo : « Quality and smoke tests », « Docker image » ; dépôts de packs et du plugin : tous les jobs), run terminé ; si la … | 0,5 h | Orchestrateur |
| T-TECH-25-08 | Transverse | REV | Avant chaque passage en done : table « scénario → test ou preuve » complète, commit de tests rouges cité dans la PR pour les stories de code (action 4 … | 0,75 h | Orchestrateur |
| T-TECH-25-09 | Transverse | DOC | Statuts : chaque tâche cochée dans le commit qui la livre ; tâches transverses revues à chaque merge | 0,25 h | Orchestrateur |
| T-TECH-25-10 | Transverse | OPS | Preuve publiée : la PR qui ajoute ou supprime un fichier de tests de la démo (US-178) embarque `php bin/proof-refresh` puis `php bin/proof-check` ; ap … | 0,5 h | Orchestrateur |
| T-TECH-25-11 | Transverse | REV | Texte public relu contre sa source avant le commit : README des packs et du nouveau plugin, page « Installer un pack », texte de la PR sur `recipes-co … | 0,5 h | Orchestrateur |
| T-TECH-25-12 | Transverse | OPS | Registre des GO de Thibaut : aucun push, PR, tag, release, dépôt ni PR publique hors démo sans GO ; chaque GO noté (date, objet) dans la réponse de l' … | 0,25 h | Orchestrateur |
| T-TECH-25-13 | Transverse | DOC | Revue et rétrospective du sprint 25 : indicateurs de la rétro du sprint 24 (espace libre minimal, workers bloqués par un refus prévisible, textes publ … | 1 h | Orchestrateur |

## 🔄 En Cours

_Aucune tâche._

## 📅 En attente d'une action de Thibaut

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-176-09 | US-176 | OPS | 📅 Thibaut donne son GO pour pousser les branches, ouvrir les PR et les fusionner dans les deux dépôts de packs (sans tag ni release) | 0,25 h | Thibaut |
| T-176-10 | US-176 | OPS | Push des branches et PR des deux dépôts après GO ; CI suivie jusqu'au vert (corrections de workflow, de gestes ou de README selon les échecs réels) ; … | 1,5 h | Claude |
| T-176-11 | US-176 | TEST | Preuves négatives en CI sur branches d'essai jetables : geste du README faux, membre qui casse l'installation (contrainte de version impossible) ; run … | 0,75 h | Claude |
| T-176-12 | US-176 | DOC | README FR et EN des deux packs : gestes corrigés si la CI en révèle de faux, écart de matrice écrit s'il existe, section sur la vérification en CI ; t … | 0,75 h | Claude |
| T-176-13 | US-176 | OPS | CI verte (tous les jobs, run terminé), puis merge des PR des deux dépôts ; aucun tag | 0,5 h | Orchestrateur |
| T-177-10 | US-177 | OPS | 📅 Thibaut donne son GO pour pousser les branches, ouvrir les PR et les fusionner dans les deux dépôts de packs (sans tag ni release) | 0,25 h | Thibaut |
| T-177-11 | US-177 | OPS | Push, PR et CI des deux dépôts après GO, corrections jusqu'au vert, puis merge (CI terminée) ; aucun tag | 0,75 h | Orchestrateur |
| T-177-12 | US-177 | OPS | 📅 Thibaut donne (ou refuse) son GO explicite pour la PR publique sur `symfony/recipes-contrib` ; sans GO, aucune PR | 0,25 h | Thibaut |
| T-177-13 | US-177 | OPS | Après GO seulement : fork, branche, PR sur `symfony/recipes-contrib` selon les conventions du dépôt, lien consigné ; texte de la PR relu contre la rec … | 0,75 h | Claude |
| T-180-11 | US-180 | OPS | 📅 Thibaut donne son GO pour créer le dépôt GitHub `thibmonier/sylius-einvoice-transmission-plugin`, pousser et poser le tag `v0.1.0` | 0,25 h | Thibaut |
| T-180-12 | US-180 | OPS | Après GO : création du dépôt avec ses topics (ecommerce, sylius, sylius-plugin, symfony, einvoice, factur-x), push, CI verte sur toute la matrice (run … | 0,75 h | Claude |
| T-180-13 | US-180 | OPS | 📅 Thibaut soumet le paquet à Packagist | 0,25 h | Thibaut |

## 🚫 Bloqué ou écarté

_Aucune tâche._

## ✅ Terminé

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-TECH-25-01 | Transverse | DOC | Ligne « bases de test nettoyées après chaque lot » ajoutée à `project-management/templates/checklist-integration.md` (action 1 de la rétro du sprint 2 … | 0,25 h | Orchestrateur |
| T-TECH-25-02 | Transverse | DOC | Règle 10 de `project-management/templates/consigne-worker.md` : pas de `git` dans un worktree isolé, story à `git` séquencée dans l'arbre principal (a … | 0,25 h | Orchestrateur |
| T-TECH-25-03 | Transverse | DOC | Ligne « texte public relu contre sa source » ajoutée à `checklist-integration.md` (action 3 de la rétro du sprint 24) | 0,25 h | Orchestrateur |
| T-TECH-25-04 | Transverse | DOC | Règle 11 de `consigne-worker.md` : mêmes règles de test pour l'orchestrateur, tests par `bin/run-tests`, test rouge commité seul (action 4 de la rétro … | 0,25 h | Orchestrateur |
