# Rétrospective - Sprint 38

## Informations

| Attribut | Valeur |
|----------|--------|
| Date | 2026-10-31, préparée en fin de sprint |
| Durée | 1 h |
| Format | Starfish, comme aux sprints 21 à 37, pour comparer les indicateurs |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude (orchestrateur, workers wtech, w245, w244, w243, w242, w242b) |

## Directive Fondamentale

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth

## Rappel du sprint

Le Sprint Goal est atteint : **10 points livrés sur 10** (US-242, US-243, US-244, US-245, toutes en review). Vélocité : S33 13, S34 9, S35 8, S36 11, S37 13, **S38 10**. C'était un sprint de consolidation : installer dans la démo les plugins publiés au sprint 37 et faire fonctionner chaque bouton « Voir la démo » en mode public.

Les écarts du sprint :
- **défauts de plugins déjà publiés**, trouvés seulement en les intégrant dans la démo : le hook de la page machine encore préfixé (parc v0.2.0), le fil d'Ariane et une clé de traduction brute (parc v0.2.1), l'adresse du widget sans préfixe de langue (copilote v0.1.0). Il a fallu trois releases correctives ;
- **w242 et w242b arrêtés** après des erreurs répétées (méthode inventée, fonctions Twig supprimées, route inexistante), et fin d'US-242 reprise par l'orchestrateur ;
- **un fichier de test écrasé** par l'orchestrateur (`RegistrationApprovalDemoTest.php`, 9 cas), vu dans `git status` et restauré ;
- **repository non étiqueté** dans le plugin parc (issue #14) ;
- **plugin parc non testable en local** (variables lues dans un `.env` interdit aux workers) ;
- **disque bas** (4,0 à 10 Go libres) ;
- **fusions refusées par le classifieur** à deux reprises, lancées ensuite à la demande explicite de Thibaut ;
- **un test instable** (`RunTestsLockTest`) a fait échouer le premier run de la PR #144 ;
- positif : 0 force-push, 0 test supprimé, la suite complète lancée en local avant la PR (3 régressions trouvées et corrigées avant la CI), les captures prises avant les tags, qui ont fait voir les défauts.

Sources : [sprint-review.md](./sprint-review.md), [registre-decisions.md](./registre-decisions.md), PR #8, #10 à #13 du parc, PR #13 du passeport, PR #2 à #4 du copilote, PR #144 de la démo.

## Observations (Starfish)

### Continuer

- La suite complète de la démo en local avant la PR de fin de sprint (règle 48) : elle a trouvé 3 régressions avant la CI, contre 4 trouvées par la CI au sprint 37.
- Les captures prises dans la vraie démo avant chaque tag : c'est sur les captures qu'on a vu les libellés faux.
- Les correctifs de plugins en TDD avec un rouge prouvé en CI, puis une release corrective sur GO.
- Le registre tenu avant chaque action.
- L'arrêt (TaskStop) de chaque worker après sa passation : aucun worker actif après passation au sprint 38.

### Commencer

- Un test d'intégration « dans une application hôte » dans chaque plugin avant le premier tag : rendu de la page par une route traduite (préfixe de langue), fil d'Ariane et libellés.
- Vérifier, avant de créer un fichier, qu'aucun fichier du même nom n'existe.
- Rendre `RunTestsLockTest` déterministe.

### Arrêter

- Taguer un plugin sans l'avoir vu fonctionner dans la démo.
- Créer un fichier avec Write sans avoir vérifié qu'il n'existe pas déjà.

### Plus de

- Vérification visuelle (captures) des pages des plugins avant release.
- Variables de test en `<server>` dans `phpunit.xml.dist`, pour que les tests tournent en local partout.

### Moins de

- Releases correctives (3 ce sprint).
- Travail d'un worker sur deux dépôts à la fois (w242 : parc, passeport et démo).

## Thèmes et analyse

### Thème 1 : Défauts de plugins publiés trouvés à l'intégration (priorité haute)

Les tests des plugins passaient sur leur TestApplication, mais trois défauts ne sont apparus que dans la démo :
- le nom de hook préfixé par Sylius ;
- le fil d'Ariane « Edit the machine » et la clé brute `sylius.ui.back_to_list` ;
- l'adresse `/api/copilot/chat` codée en dur, sans le préfixe de langue que toutes les routes du shop portent dans une vraie application.

Cause racine : les TestApplication des plugins n'ont pas de routes préfixées par la langue, et aucun test n'affiche le rendu final, libellés compris. Impact : trois releases correctives (parc v0.2.1, v0.2.2 ; copilote v0.1.1). Leçon : un test fonctionnel du plugin doit tourner avec des routes préfixées par la langue et vérifier les textes visibles. Une capture dans la démo doit précéder chaque tag.

### Thème 2 : Workers sur des stories multi-dépôts (priorité haute)

w242 et w242b travaillaient à la fois sur le plugin parc, le plugin passeport et la démo. Ils ont inventé une méthode, supprimé des fonctions Twig et appelé une route inexistante. Cause : une story sur trois dépôts dépasse le contexte d'un worker, et la règle 38 (tout nom vérifié dans `vendor/`) n'a pas été appliquée. Impact : deux workers arrêtés, fin de story reprise par l'orchestrateur. Leçon : découper une story multi-dépôts en un worker par dépôt, dans l'ordre des dépendances.

### Thème 3 : Fichier de test écrasé (priorité moyenne)

L'orchestrateur a créé `RegistrationApprovalDemoTest.php` alors qu'un test du même nom existait (US-020, 9 cas). Le fichier a été restauré depuis git avant le commit, sans perte. Cause : nom choisi sans chercher l'existant. Leçon : chercher le nom (`ls`, `git ls-files`) avant toute création de fichier.

### Thème 4 : Plugin parc non testable en local (priorité moyenne)

La TestApplication du parc lit ses variables `SYLIUS_TEST_APP_*` dans `tests/TestApplication/.env`, que les workers ne doivent pas toucher. Ses tests ne tournent donc pas en local, et une exception à la règle 46 a dû être notée. Le repository non étiqueté (issue #14) relève du même manque de tests en application hôte. Leçon : aligner le parc sur le passeport (`<server>` dans `phpunit.xml.dist`).

### Thème 5 : Test de verrou instable (priorité basse)

`RunTestsLockTest::testTwoRunsTakingOverTheSameStaleLockLeaveTheLockToExactlyOne` repose sur des délais de 0,2 et 0,6 s. Sous la charge de la CI avec couverture, les deux runs peuvent s'arrêter. Il a échoué au sprint 22 et de nouveau au sprint 38. Leçon : le rendre déterministe, avec une synchronisation par fichier plutôt que des délais.

### Thème 6 : Positif : contrôles avant la PR (priorité basse)

Les règles 46 à 48 issues de la rétro du sprint 37 ont fonctionné : 0 force-push, 0 test supprimé, 0 worker actif après passation, 0 régression laissée à la CI.

## Suivi des actions de la rétrospective du sprint 37

| # | Action | Résultat au sprint 38 |
|---|--------|------------------------|
| 1 | Gabarit de test fonctionnel partagé (T-TECH-38-01) | ✅ Fait : `docs/gabarits-tests.md` ; utilisé pour la mise en place d'US-242 |
| 2 | Règle 46 : tests en local avant tout push (T-TECH-38-02) | ✅ Fait, une exception consignée (plugin parc) |
| 3 | TaskStop après passation (T-TECH-38-03) | ✅ Appliqué (règle 47) : 0 worker actif après passation |
| 4 | Suite complète ou pré-contrôle avant la PR de fin de sprint (T-TECH-38-04) | ✅ Fait (règle 48) : 3 régressions trouvées en local, 0 par la CI |
| 5 | Test d'installation vérifiant `kernel.bundles` (T-TECH-38-05) | ✅ Gabarit écrit ; aucun nouveau plugin ce sprint pour l'appliquer |

Reliquats hérités (T-TECH-34-02, -04, -06 et T-TECH-33-XX) : **toujours reportés**.

## Suivi des règles de rétro précédentes

| Règle | Application au sprint 38 |
|-------|---------------------------|
| 26 : registre avant l'action | ✅ Tenue |
| 27 : rouge prouvé | ✅ Tenue (CI pour les plugins, local pour la démo) |
| 32 / 39 : aucun test retiré ni affaibli | ✅ Tenue (le test écrasé a été restauré avant le commit) |
| 35 : un push puis attendre le run | ✅ Tenue (un push de la démo, relance du job sans push) |
| 36 : rouge valide sur une assertion | ✅ Tenue |
| 38 : tout nom vérifié dans `vendor/` | 🔴 Non tenue par w242 et w242b (méthode et route inventées) |
| 40 : jamais de force-push | ✅ Tenue |
| 45 : diagnostic sur le log | ✅ Tenue (échec CI lu dans le log, historique du test vérifié) |
| 46 : tests en local avant push | 🟡 Tenue, sauf l'exception consignée du plugin parc |
| 47 : TaskStop après passation | ✅ Tenue |
| 48 : contrôles avant la PR de la démo | ✅ Tenue |

## Bilan des indicateurs

| Indicateur | Cible | Sprint 38 | Sprint 37 |
|------------|-------|-----------|-----------|
| Points livrés | 10 | **10** | 13 |
| Relais de workers | 0 | **1** (w242 → w242b) | 7 |
| Pushes forcés | 0 | **0** | 2 |
| Tests retirés ou affaiblis | 0 | **0** | 1 |
| Worker actif après passation | 0 | **0** | 1 |
| Noms inventés | 0 | **3** (w242, w242b) | 0 |
| Défauts de plugins publiés trouvés à l'intégration | 0 | **3** | non mesuré |
| Régressions trouvées par la CI de la PR de fin de sprint | 0 | **0** (1 test instable) | 4 |
| Fichiers écrasés par erreur | 0 | **1** (restauré) | non mesuré |
| Tag ou release sans GO | 0 | **0** | 0 |

## Actions (SMART)

### Action 1 (T-TECH-39-01) : Test en application hôte avant chaque tag de plugin

| Attribut | Valeur |
|----------|--------|
| Description | Ajouter à `docs/gabarits-tests.md` un gabarit de test fonctionnel qui rend chaque page shop du plugin par une route préfixée par la langue (`/en_US/...`, `/fr_FR/...`) et vérifie les textes visibles : titre, fil d'Ariane, boutons, et aucune clé de traduction brute (`/\b[a-z_]+\.[a-z_.]+\b/` absent du texte). Appliquer le gabarit aux plugins parc et copilote. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Fin du sprint 39 |
| DoD | Gabarit écrit ; un test par page shop dans le parc et le copilote, rouge prouvé en réintroduisant l'un des défauts du sprint 38 ; 0 release corrective pour libellé ou adresse au sprint 39. |
| Priorité | Haute |

### Action 2 (T-TECH-39-02) : Un worker par dépôt pour les stories multi-dépôts

| Attribut | Valeur |
|----------|--------|
| Description | Règle 49 dans `consigne-worker.md` : une story qui touche plusieurs dépôts est découpée en un worker par dépôt, lancés dans l'ordre des dépendances (plugin, puis démo). Chaque worker cite dans son rapport la commande `grep` dans `vendor/` qui prouve l'existence de chaque méthode, route ou fonction Twig utilisée (règle 38). |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 39 |
| DoD | Règle écrite ; au sprint 39, 0 worker sur deux dépôts et 0 nom inventé. |
| Priorité | Haute |

### Action 3 (T-TECH-39-03) : Variables de test du plugin parc en `<server>`

| Attribut | Valeur |
|----------|--------|
| Description | Dans le plugin customer-asset-management, déclarer les variables `SYLIUS_TEST_APP_*` en `<server>` dans `phpunit.xml.dist`, comme le passeport, et étiqueter le repository comme service Doctrine (issue #14), avec un test qui appelle `getRepository(CustomerAsset::class)`. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Fin du sprint 39 |
| DoD | `bin/run-tests` du plugin passe en local ; test de l'issue #14 rouge puis vert en CI ; issue #14 fermée par la PR ; l'exception à la règle 46 est levée. |
| Priorité | Moyenne |

### Action 4 (T-TECH-39-04) : Vérifier l'existence d'un fichier avant de le créer

| Attribut | Valeur |
|----------|--------|
| Description | Règle 50 dans `consigne-worker.md`, appliquée aussi par l'orchestrateur : avant de créer un fichier, lancer `git ls-files <chemin>` et `ls <chemin>`. Si le fichier existe, le lire et le modifier, ou choisir un autre nom. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 39 |
| DoD | Règle écrite ; au sprint 39, 0 fichier écrasé par erreur. |
| Priorité | Moyenne |

### Action 5 (T-TECH-39-05) : Rendre `RunTestsLockTest` déterministe

| Attribut | Valeur |
|----------|--------|
| Description | Remplacer les délais `STUB_CAT_DELAY` et `STUB_LOCK_OP_DELAY` du test de prise d'un verrou périmé par une synchronisation par fichier : chaque run attend un fichier de feu vert au point critique. Le test reste rouge si le verrou de `bin/run-tests` n'est plus atomique. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Fin du sprint 39 |
| DoD | Test passé 20 fois de suite en CI, sous la couverture ; rouge prouvé en retirant l'opération atomique du verrou ; 0 échec de ce test au sprint 39. |
| Priorité | Basse |

## Éléments pour le backlog (ajoutés le 2026-10-31 sur décision de Thibaut)

- US-246 : mise en forme du widget du copilote (question et réponse distinguées, état de chargement).
- US-247 : réponses enregistrées du copilote cohérentes avec les données de la démo (le panier réel de Claire compte 47 articles pour 1 017,35 €, la réponse annonce « 3 articles, 150 € »).

## Intégration au sprint 39

Les actions 1 à 5 sont à reprendre au planning du sprint 39 (T-TECH-39-01 à 05). Les reliquats T-TECH-34-XX et T-TECH-33-XX restent reportés.

## Points positifs à conserver

- 10 points sur 10, avec tous les boutons « Voir la démo » utilisables en mode public.
- 0 force-push, 0 test supprimé, 0 worker actif après passation : les règles 46 à 48 ont fonctionné.
- Les captures dans la vraie démo, qui ont fait voir les défauts avant que d'autres les voient.
- Aucun tag ni release sans GO de Thibaut.
