# Sprint Review - Sprint 38 (Consolidation de la vitrine)

> Préparée le 2026-10-31. Toutes les PR des plugins sont fusionnées. La PR #144 de la démo est ouverte en brouillon ; son premier run de CI a échoué sur un test instable sans rapport avec le sprint, et le job est relancé.

## Informations

| Attribut | Valeur |
|----------|--------|
| Sprint | 38, `sprint-38-consolidation-vitrine` |
| Période | 2026-10-30 au 2026-11-13 (livré le 2026-10-31) |
| Points engagés | **10** (US-242, US-243, US-244, US-245) |
| Points livrés | **10** (4 stories en review ; comptées done après la validation de Thibaut) |
| PR de la démo | #144 (brouillon, CI relancée) |
| PR plugin parc (customer-asset-management) | #8 (page machine), #10 (v0.2.1), #11 (libellés), #12 (CHANGELOG 0.2.2), #13 (captures), toutes fusionnées |
| PR plugin passeport | #13 (bloc passeport sur la page machine), fusionnée |
| PR plugin copilote | #2 (adresse du widget), #3 (CHANGELOG 0.1.1), #4 (captures), toutes fusionnées |
| Releases | Parc v0.2.0, v0.2.1, v0.2.2 (6581c2ae) ; passeport v0.3.0 ; copilote v0.1.1 (57e47500), toutes sur GO de Thibaut |
| Issue publique | thibmonier/sylius-customer-asset-management-plugin#14 (repository non résolu par `getRepository()`) |
| Mode | Orchestrateur (Claude) et workers wtech, w245, w244, w243, w242, w242b |

## Sprint Goal

> « Installer le copilote d'achat dans la démo avec ses captures, vérifier le passeport produit en production, rendre la démo Registration Approval utilisable en mode public et créer la fiche équipement avec lien vers le passeport et les pièces compatibles. »

**Atteint.** Les quatre stories sont livrées. Intégrer les plugins dans la démo a révélé deux défauts de plugins déjà publiés, tous deux corrigés en TDD et publiés : les libellés de la page machine (parc v0.2.2) et l'adresse du widget sans préfixe de langue (copilote v0.1.1).

## User stories livrées

| Story | Points | Livré | Preuve principale |
|-------|--------|-------|-------------------|
| US-244 Copilote d'achat dans la démo et captures du widget | 3 | Copilote v0.1.1 installé sur le canal B2B ; widget inclus dans le tableau de bord B2B de la démo, car ce tableau masque la section où le plugin s'accroche ; réponse enregistrée, message de repli, aucun widget pour Léa sur le canal B2C. Captures FR/EN dans le README du copilote. | `tests/Smoke/PurchaseCopilotSmokeTest.php` (4 tests) ; copilote PR #2 (rouge 41174631885) et #4 |
| US-243 Démo Registration Approval en mode public et boutons « Voir la démo » | 3 | Page `app_demo_registration_approval` : redirige vers le formulaire si les inscriptions sont ouvertes, sinon explique le parcours de Sophie. Deux inscriptions fictives en attente (fixture `demo_pending_registrations`). Le bouton PunchOut ouvre désormais le simulateur, dans la langue de la page. | `PluginDemoLinksTest` (tous les boutons FR et EN, visiteur anonyme, mode public ; rouges locaux sur Registration Approval puis PunchOut), `RegistrationApprovalPublicDemoTest` |
| US-242 Fiche équipement avec lien vers le passeport et les pièces compatibles | 3 | Page machine du parc (v0.2.0 à v0.2.2) et bloc passeport (v0.3.0). Dans la démo, Claire a une 4e machine : la clé dynamométrique DEMO-0004, vendue sous OUTI-028, dont le passeport mène aux pièces compatibles (OUTI-026). Captures FR/EN dans le README du parc. | `EquipmentFleetSmokeTest` (fiche, lien passeport, pièces compatibles, page du passeport) ; parc PR #10 (rouge 41125377355, vert 41125961866) et #11 (rouge 41174423542) |
| US-245 Passeport vérifié en production et test de fumée | 1 | Vérification en production du 2026-10-31, après la remise à zéro : EPI-CASK-001 à 003, FR et EN, lien présent et page du passeport 200. | `tasks/US-245-tasks.md` ; test de fumée a5d344b et 742517f |

**Contrôles locaux avant la PR (règle 48)** : suite complète de la démo lancée en local (3949 tests). Elle a trouvé 3 échecs, tous corrigés puis relancés : le nombre de clients après la remise à zéro, une priorité de fixture partagée et un cache de rendu périmé. `bin/verify-test-coverage` (334 fichiers couverts), `bin/proof-check`, ECS et PHPStan passent.

**CI de la PR #144** : le premier run (41182351481) a échoué sur un seul test, `RunTestsLockTest::testTwoRunsTakingOverTheSameStaleLockLeaveTheLockToExactlyOne`. Ce test de concurrence est minuté, il était déjà signalé instable au sprint 22, et le sprint n'a touché ni `bin/run-tests` ni ce test. Il passe en local (17 tests OK). Seul le job échoué est relancé, sans nouveau push.

## Décisions de Thibaut (registre du sprint)

- Périmètre de 10 points, plus les actions de rétro T-TECH-38-01 à 05.
- GO des tags parc v0.2.0, passeport v0.3.0, parc v0.2.2 et copilote v0.1.1.
- Fusion des PR #11 et #13 du parc et #2 et #4 du copilote : le classifieur a refusé ces fusions, que l'orchestrateur a lancées à la demande explicite de Thibaut.
- Accord pour l'issue publique #14 du plugin parc.

## Incidents

| Incident | Effet | Traitement |
|----------|-------|------------|
| w242 et w242b arrêtés après des erreurs répétées (méthode inventée, fonctions Twig supprimées, route inexistante) | Fin d'US-242 reprise par l'orchestrateur | Consigné au registre ; pour la rétro |
| Hook d'extension de la page machine encore préfixé par Sylius : aucun bloc ne s'y affichait (parc v0.2.0) | Release corrective v0.2.1 | `_prefixes: []`, rouge et vert prouvés en CI |
| Défauts de plugins publiés vus seulement à l'intégration : fil d'Ariane et clé brute (parc), adresse du widget sans langue (copilote) | Deux releases correctives | Corrigés en TDD (v0.2.2, v0.1.1) |
| L'orchestrateur a écrasé `RegistrationApprovalDemoTest.php` (9 cas) en créant un test du même nom | Vu dans `git status` avant le commit, rien de perdu | Restauré depuis git, nouveaux tests déplacés dans un autre fichier |
| Repository du parc non étiqueté : `getRepository(CustomerAsset::class)` plante | Requête DQL dans le test de la démo | Issue #14 ouverte |
| Disque entre 4,0 et 10 Go libres | w244 a attendu ; un `rm -rf` refusé | `bin/clean-test-caches` à la place |

## Métriques

| Métrique | Sprint 38 | Sprint 37 |
|----------|-----------|-----------|
| Points engagés / livrés | 10 / 10 (en review) | 13 / 13 (en review) |
| Stories de code avec rouge prouvé | 3 sur 3 (US-242, US-243, US-244 ; US-245 : rouge local sur l'assertion du lien) | 4 sur 4 |
| Relais de workers | 1 (w242 → w242b, puis reprise par l'orchestrateur) | 7 |
| Pushes forcés | 0 | 2 |
| Tests supprimés ou affaiblis | 0 | 1 |
| Défauts de plugins publiés trouvés à l'intégration | 3 (hook préfixé, libellés, adresse du widget) | non mesuré |
| Régressions trouvées par la CI de la PR de fin de sprint | 0 (1 test instable préexistant) | 4 |
| Tag ou release sans GO | 0 | 0 |

## Démonstration (ordre suggéré)

1. La page « Plugins » en mode public : chaque bouton « Voir la démo » ouvre une page utilisable.
2. La page de démo Registration Approval, puis l'admin de Sophie avec les deux inscriptions en attente (Nadia Roux, Paul Leroy).
3. Le bouton PunchOut, qui ouvre le simulateur en anglais depuis la page anglaise.
4. Le tableau de bord de Claire : le widget du copilote et sa réponse enregistrée.
5. Le parc de Claire, la fiche de la clé dynamométrique, le lien vers son passeport et les pièces compatibles.
6. Le passeport en production (EPI-CASK-001), en FR et en EN.

## Impact sur le backlog

| Élément | Statut |
|---------|--------|
| US-242, US-243, US-244, US-245 | Review (done après validation de Thibaut) |
| Issue #14 du plugin parc | Ouverte (repository à étiqueter comme service Doctrine) |
| Copilote : mise en forme du widget | À créer au backlog (question et réponse non distinguées visuellement) |
| Copilote : réponse enregistrée | À créer au backlog (« 3 articles, 150 € » alors que le vrai panier de Claire compte 47 articles pour 1 017,35 €) |
| US-212 | Backlog (reportée) |

## Prochaines étapes

1. Relance de la CI de la PR #144, puis GO de Thibaut pour la fusion.
2. Création au backlog des deux points du copilote et d'une action pour le test de verrou instable.
3. Sprint 39 : planning sur demande de Thibaut.

## Dettes techniques

- `RunTestsLockTest` instable sous la charge de la CI (deuxième échec connu, après le sprint 22).
- Plugin parc : variables `SYLIUS_TEST_APP_*` lues dans `tests/TestApplication/.env`, donc ses tests ne tournent pas en local (exception à la règle 46). À déclarer en `<server>` dans `phpunit.xml.dist`, comme le passeport.
- Plugin parc : repository non étiqueté (issue #14).
- Copilote : widget sans mise en forme, réponse enregistrée sans rapport avec le panier réel.
- Dérive du schéma de la démo antérieure au sprint (tables du prédictif et de Sylius).
