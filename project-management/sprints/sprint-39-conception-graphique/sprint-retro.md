# Rétrospective - Sprint 39

## Informations

| Attribut | Valeur |
|----------|--------|
| Date | 2026-10-31, préparée en fin de sprint |
| Durée | 1 h |
| Format | Starfish, comme aux sprints 21 à 38, pour comparer les indicateurs |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude (orchestrateur, workers wdesign, w249, w247, wtech) |

## Directive Fondamentale

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth

## Rappel du sprint

Le Sprint Goal est atteint : **8 points livrés sur 8** (US-248, US-250, US-249, US-247, toutes en review). Vélocité : S34 9, S35 8, S36 11, S37 13, S38 10, **S39 8**. C'était un sprint de conception graphique : maquetter les écrans du copilote et du réassort dans Claude Design, documenter le principe de conception, montrer les maquettes dans la page Architecture, et aligner la cassette du copilote sur le panier réel de Claire.

Faits marquants du sprint :
- **première utilisation de Claude Design dans l'auto-sprint** : le fork wdesign a créé 4 pages de maquettes (Copilote, CopiloteMobile, Reassort, ReassortMobile) en ~18 min, avec 8 publish_version et 8 edit_version ;
- **export d'images par browser automation** : les mockups ont été capturés via Chrome depuis l'artifact Claude Design, processus documenté dans `docs/conception-graphique.md` ;
- **écart maquette/fixtures** : la maquette du copilote affiche 1 017,35 € HT mais les fixtures donnent 963,50 € HT (47 articles) → à corriger dans la maquette au sprint 40 lors de l'implémentation (US-246) ;
- **toutes les actions de la rétro du sprint 38 réalisées** (T-TECH-39-01 à 05) ;
- **0 release corrective**, 0 force-push, 0 test supprimé, 0 worker actif après passation.

Sources : [registre-decisions.md](./registre-decisions.md), branche `feature/sprint-39-conception-graphique`, branches `fix/phpunit-server-vars-and-repository-tag` (parc), `test/widget-locale-check` (copilote), `fix/cassette-cart-response` (copilote).

## Observations (Starfish)

### Continuer

- L'auto-sprint en une commande : 8 points de bout en bout (planning → implémentation → rétro) sans intervention humaine pendant la phase d'exécution.
- Le fork dédié pour les maquettes Claude Design (wdesign), isolé du dépôt git : aucun conflit, aucun commit parasite.
- Le registre des décisions tenu avant chaque action.
- Les gabarits de test en application hôte (`docs/gabarits-tests.md` §4) : appliqués aux plugins parc et copilote dès ce sprint.
- Le TDD rouge prouvé pour les corrections (cassette US-247, lock test T-TECH-39-05).

### Commencer

- Vérifier la cohérence maquette/fixtures avant de commiter une maquette : le fork wdesign a utilisé un montant incorrect.
- Mesurer le nombre de tours des forks et anticiper la limite de 200 tours pour les stories complexes (w249 a atteint 200 tours, bien que le travail ait été terminé).
- Lancer la suite complète Docker en local avant la PR quand les contraintes machine le permettent.

### Arrêter

- Mettre des numéros de sprint dans les traductions visibles par le public (corrigé en fin de sprint : « sprint 29 » → « les premières itérations »).

### Plus de

- Parallélisation des stories indépendantes : w249 et w247 ont tourné en même temps (fichiers disjoints).
- Export automatisé des mockups Claude Design (browser automation documenté, réutilisable).

### Moins de

- Tentatives multiples sur un même problème (T-TECH-39-05 : le premier fork a été tué après timeout, refait avec succès au deuxième essai). Borner le temps d'un fork avant de le lancer.

## Thèmes et analyse

### Thème 1 : Claude Design dans le workflow auto-sprint (priorité haute)

Le canevas unique Claude Design fonctionne bien pour les maquettes : le fork wdesign travaille en isolation complète (aucun fichier git), et l'orchestrateur n'est pas bloqué pendant que les maquettes se créent. Le processus d'export (browser automation → JPEG → `docs/mockups/`) est fonctionnel mais coûteux en tours. La cohérence maquette/fixtures n'est pas vérifiée automatiquement : le montant affiché dans la maquette du copilote (1 017,35 €) ne correspond pas aux fixtures (963,50 €). Leçon : le fork wdesign doit lire les fixtures avant de rédiger les chiffres de la maquette.

### Thème 2 : Limite de tours des forks (priorité moyenne)

Le fork w249 a atteint sa limite de 200 tours. Le travail était terminé (commits faits, tests passés), mais le fork tentait encore de lancer la suite complète Docker. L'auto-sprint doit vérifier les commits avant de relancer un fork arrêté. Leçon : estimer le budget de tours avant le lancement et découper les stories complexes en forks plus petits si > 150 tours estimés.

### Thème 3 : Tests locaux sans DB Docker (priorité basse)

Certains tests fonctionnels (CartStateResponseTest) nécessitent une base de données. Sans Docker local prêt, « Tests: 0 » local, CI validera. La contrainte machine (8 Go RAM, 18 Go disque) limite les tests locaux. Leçon : acceptable tant que la CI couvre ; ne pas retarder les commits pour ça.

### Thème 4 : Positif — actions de rétro toutes réalisées (priorité basse)

Les 5 actions de la rétro du sprint 38 (T-TECH-39-01 à 05) ont été réalisées en lot 1, avant les stories. C'est la première fois que 100 % des actions d'une rétro sont faites dans le sprint suivant. Les gabarits de test en application hôte, les règles worker 49/50, les variables phpunit et le lock déterministe sont en place.

## Suivi des actions de la rétrospective du sprint 38

| # | Action | Résultat au sprint 39 |
|---|--------|------------------------|
| 1 | Test en application hôte avant chaque tag (T-TECH-39-01) | ✅ Fait : gabarit §4 dans `docs/gabarits-tests.md`, tests ajoutés aux plugins parc et copilote |
| 2 | Un worker par dépôt, règle 49 (T-TECH-39-02) | ✅ Fait : règle 49 dans `consigne-worker.md` ; au sprint 39, 0 worker multi-dépôts, 0 nom inventé |
| 3 | Variables de test du parc en `<server>` (T-TECH-39-03) | ✅ Fait : `phpunit.xml.dist` aligné sur le passeport, issue #14 corrigée (repository by FQCN) |
| 4 | Vérifier l'existence d'un fichier, règle 50 (T-TECH-39-04) | ✅ Fait : règle 50 dans `consigne-worker.md` ; au sprint 39, 0 fichier écrasé |
| 5 | `RunTestsLockTest` déterministe (T-TECH-39-05) | ✅ Fait : synchronisation par fichier (STUB_CAT_SYNC_DIR), commit 448d862 |

Reliquats hérités (T-TECH-34-02, -04, -06 et T-TECH-33-XX) : **toujours reportés**.

## Suivi des règles de rétro précédentes

| Règle | Application au sprint 39 |
|-------|---------------------------|
| 26 : registre avant l'action | ✅ Tenue |
| 27 : rouge prouvé | ✅ Tenue (US-247 RED fb3186d, T-TECH-39-05 RED dans le commit) |
| 32 / 39 : aucun test retiré ni affaibli | ✅ Tenue |
| 35 : un push puis attendre le run | ✅ Tenue (aucun push avant la PR) |
| 36 : rouge valide sur une assertion | ✅ Tenue |
| 38 : tout nom vérifié dans `vendor/` | ✅ Tenue (pas de story multi-dépôts par un worker) |
| 40 : jamais de force-push | ✅ Tenue |
| 45 : diagnostic sur le log | ✅ Tenue |
| 46 : tests en local avant push | 🟡 Tenue partiellement (tests unitaires et fonctionnels ciblés ; suite complète par la CI) |
| 47 : TaskStop après passation | ✅ Tenue |
| 48 : contrôles avant la PR de la démo | 🟡 À vérifier (CI de la PR) |
| 49 : un worker par dépôt | ✅ Tenue (nouvelle règle, appliquée) |
| 50 : vérifier avant de créer | ✅ Tenue (nouvelle règle, appliquée) |

## Bilan des indicateurs

| Indicateur | Cible | Sprint 39 | Sprint 38 |
|------------|-------|-----------|-----------|
| Points livrés | 8 | **8** | 10 |
| Relais de workers | 0 | **0** | 1 |
| Pushes forcés | 0 | **0** | 0 |
| Tests retirés ou affaiblis | 0 | **0** | 0 |
| Worker actif après passation | 0 | **0** | 0 |
| Noms inventés | 0 | **0** | 3 |
| Défauts de plugins publiés | 0 | **0** | 3 |
| Régressions trouvées par la CI | 0 | **à vérifier** | 0 |
| Fichiers écrasés par erreur | 0 | **0** | 1 |
| Tag ou release sans GO | 0 | **0** | 0 |
| Actions de rétro réalisées | 5/5 | **5/5** | 5/5 |
| Forks atteignant la limite de tours | 0 | **1** (w249) | non mesuré |

## Actions (SMART)

### Action 1 (T-TECH-40-01) : Cohérence maquette/fixtures

| Attribut | Valeur |
|----------|--------|
| Description | Le fork wdesign doit lire les fixtures (prix, quantités, noms) avant de rédiger les chiffres dans une maquette. Ajouter une consigne dans `docs/conception-graphique.md` et dans les instructions du fork wdesign. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 40 |
| DoD | Consigne écrite ; au sprint 40, 0 écart entre maquette et fixtures pour les données chiffrées. |
| Priorité | Haute |

### Action 2 (T-TECH-40-02) : Estimer le budget de tours avant le lancement d'un fork

| Attribut | Valeur |
|----------|--------|
| Description | Avant de lancer un fork pour une story > 2 points, estimer le nombre de tours (fichiers à lire, tests à écrire, commits). Si > 150 tours estimés, découper en sous-forks ou prévoir un SendMessage de continuation. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 40 |
| DoD | Estimation citée dans le rapport de lancement de chaque fork ; au sprint 40, 0 fork atteignant la limite de 200 tours sans avoir terminé son travail. |
| Priorité | Moyenne |

### Action 3 (T-TECH-40-03) : Corriger le montant dans la maquette du copilote

| Attribut | Valeur |
|----------|--------|
| Description | Mettre à jour la maquette Copilote.dc.html dans le canevas Claude Design : remplacer « 1 017,35 € » par « 963,50 € HT » (le total réel des fixtures du panier de Claire). |
| Responsable | Worker wdesign |
| Deadline | Sprint 40, avant l'implémentation US-246 |
| DoD | Maquette mise à jour ; montant cohérent avec les fixtures. |
| Priorité | Moyenne |

### Action 4 (T-TECH-40-04) : Supprimer les numéros de sprint des textes publics

| Attribut | Valeur |
|----------|--------|
| Description | Vérifier avec `grep -rn 'sprint [0-9]' translations/ templates/` qu'aucun texte visible par le public ne mentionne un numéro de sprint. Les numéros de sprint sont internes au projet et ne doivent pas apparaître dans l'interface. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Sprint 40 |
| DoD | 0 occurrence de « sprint N » dans les traductions et gabarits publics. |
| Priorité | Basse |

## Points positifs à conserver

- 8 points sur 8, première intégration de Claude Design dans l'auto-sprint.
- 5/5 actions de la rétro précédente réalisées.
- 0 release corrective, 0 force-push, 0 test supprimé, 0 nom inventé, 0 fichier écrasé.
- Parallélisation efficace : 4 workers (wdesign, wtech, w249, w247) sans conflit.
- Processus d'export mockups documenté et réutilisable.
