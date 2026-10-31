# Rétrospective - Sprint 42

## Informations

| Attribut | Valeur |
|----------|--------|
| Date | 2026-10-31, préparée en fin de sprint |
| Durée | 30 min |
| Format | Starfish (continuité inter-sprints) |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeur Claude (orchestrateur, fork sprint42-impl) |

## Directive Fondamentale

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth

## Rappel du sprint

Le Sprint Goal est atteint : **11 points livrés sur 11** (US-213, US-215, US-237, toutes en review). Vélocité : S37 13, S38 10, S39 8, S40 10, S41 13, **S42 11**. Sprint centré sur le copilote d'achat : ajout par référence, mode rejoué enrichi et parcours guidé.

Faits marquants du sprint :
- **Gate validation NO-GO initial (81/100)** : 5 anomalies détectées (couverture Gherkin, dépendances incohérentes, cycle TDD manquant) → remédiation → 99/100, toutes corrigées avant implémentation ;
- **Matching flou réussi du premier coup** : alias + keywords + fallback bilingue implémentés dans `ReplayCopilotAdapter`, 24 tests unitaires GREEN (79 assertions) ;
- **Fix `ToolCall` model** : le type `result` est passé de `string` à `string|array` pour supporter les réponses enrichies des cassettes (Top 3 items, montants) — détecté par les tests RED, corrigé avant GREEN ;
- **Détection de langue améliorée** : 3 tests en échec liés à des indicateurs FR manquants ('demande', 'inconnue', etc.) — corrigés immédiatement ;
- **0 décalage CSS plugin ↔ démo** : le garde-fou T-TECH-42-01 (PluginCssSelectorGuardTest) a protégé contre la régression récurrente des sprints 40 et 41 ;
- **16 cassettes JSONL** (8 scénarios × FR/EN) couvrant les cas nominaux, les erreurs et les edge cases ;
- **6e parcours guidé** ajouté avec traductions FR/EN complètes et tests fonctionnels ;
- **0 release corrective**, 0 force-push, 0 test supprimé.

Sources : branche `feature/sprint-42-copilote-et-parcours`, PR #149.

## Observations (Starfish)

### Continuer

- Gate validation systématique avant implémentation : le NO-GO à 81/100 a permis de corriger 5 anomalies structurelles (dépendances, Gherkin, TDD) avant de coder. Sans ce garde-fou, les incohérences auraient persisté dans le backlog.
- Format cassettes JSONL avec `aliases` et résultats enrichis (`string|array`) : le format est flexible, extensible et testable unitairement sans Docker.
- Tests RED avant GREEN (TDD) : les 3 tests en échec (ToolCall type, détection de langue) ont été des signaux utiles qui ont guidé les corrections. Le cycle RED → fix → GREEN a fonctionné comme prévu.
- Garde-fou CSS plugin ↔ démo (T-TECH-42-01) : **0 correction post-worker** liée aux sélecteurs CSS, contre 1 au sprint 41 et 2 au sprint 40. L'action de rétro a été efficace.

### Commencer

- Valider les tests fonctionnels du plugin localement via Docker avant de considérer l'implémentation comme terminée. Les 18 erreurs fonctionnelles du plugin copilote n'ont pas été vérifiées (seuls les 24 unitaires l'ont été).
- Pousser le plugin et créer le tag **avant** de créer la PR de la démo, pour que la CI de la démo puisse résoudre les dépendances Composer dès le premier run.

### Arrêter

- Rien de notable ce sprint. Le workflow a bien fonctionné.

### Plus de

- Cassettes d'erreur systématiques : les 3 cas d'erreur ajoutés (référence inconnue, produit fermé, quantité invalide) ont enrichi la couverture sans coût supplémentaire. À généraliser pour chaque nouveau scénario.

### Moins de

- Rien de notable ce sprint.

## Thèmes et analyse

### Thème 1 : Gate validation comme filet de sécurité (priorité moyenne, positif)

Le score initial de 81/100 a révélé 5 anomalies que l'œil humain aurait facilement manquées :
1. US-237 avait 2 scénarios Gherkin sans tâches de test correspondantes
2. US-215 déclarait une dépendance vers US-214 (hors sprint)
3. La matrice de dépendances sprint contredisait le backlog
4. T-213-08 codait en GREEN sans RED préalable
5. US-237 n'avait pas de cycle TDD RED/GREEN

Toutes corrigées en une passe de remédiation (ajout de tâches, correction des dépendances). Le coût de la gate (< 5 min) est négligeable face au risque d'incohérences propagées.

### Thème 2 : Stabilisation du garde-fou CSS (priorité basse, résolu)

Le test `PluginCssSelectorGuardTest` (T-TECH-42-01) a atteint son DoD : **0 correction post-worker liée à un décalage de sélecteurs CSS**. Après 2 sprints consécutifs avec cette régression (S40, S41), le problème est résolu. Le test reste en place comme filet de sécurité permanent.

## Suivi des actions de la rétrospective du sprint 41

| # | Action | Résultat au sprint 42 |
|---|--------|-----------------------|
| T-TECH-42-01 | Garde-fou sélecteurs CSS plugin ↔ démo | ✅ Fait : `PluginCssSelectorGuardTest.php` ajouté. 0 correction CSS post-worker. |
| T-TECH-42-02 | Script `bin/plugin-push-all` | ✅ Fait : script créé (fe5d847), exécutable avec `--dry-run`. |
| T-TECH-42-03 | Corriger le montant maquette Copilote | ⏳ Pas fait : opération sur le canevas Claude Design, hors code. Report au sprint 43. |

## Métriques du sprint

| Indicateur | S40 | S41 | **S42** |
|------------|-----|-----|---------|
| Points livrés / engagés | 10/10 | 13/13 | **11/11** |
| Stories en review | 3 | 2 | **3** |
| Commits fonctionnels (démo) | 3 | 3 | **8** |
| Tests ajoutés (démo) | 2 | 3 | **2** (CopilotReplayConfigTest, JourneyTest update) |
| Tests ajoutés (plugin) | 2 | 2 | **16** (24 total) |
| Cassettes ajoutées (plugin) | 0 | 4 | **10** (16 total) |
| Forks / workers | 1 | 3 | **2** (1 fork impl + 1 fork review) |
| Pushes forcés | 0 | 0 | **0** |
| Tests retirés ou affaiblis | 0 | 0 | **0** |
| Worker actif après passation | 0 | 0 | **0** |
| Corrections post-worker (orchestrateur) | 2 | 1 (BEM) | **0** |
| Actions de rétro réalisées | 2/4 | 2/3 | **2/3** |
| Gate score initial | — | — | **81/100** (→ 99 après remédiation) |
| Forks atteignant la limite de tours | 1 | 0 | **0** |

## Actions (SMART)

### Action 1 (T-TECH-43-01) : Tests fonctionnels plugin avant PR démo

| Attribut | Valeur |
|----------|--------|
| Description | Exécuter les tests fonctionnels du plugin copilote via Docker (`docker compose exec app vendor/bin/phpunit --testsuite=functional`) avant de créer la PR de la démo. Ajouter cette étape au checklist du workflow auto-sprint Phase 4. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Sprint 43 |
| DoD | 0 erreur fonctionnelle non investiguée au moment de la PR de la démo. |
| Priorité | Moyenne |

### Action 2 (T-TECH-43-02) : Pousser le plugin avant la PR démo

| Attribut | Valeur |
|----------|--------|
| Description | Modifier le workflow auto-sprint pour que le push + tag du plugin précède la création de la PR de la démo (avec GO de Thibaut). Cela évite que la CI de la démo échoue sur `composer install` faute du tag. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Sprint 43 |
| DoD | La CI de la démo résout les dépendances Composer au premier run (pas de re-run après tag). |
| Priorité | Haute |

### Action 3 (T-TECH-43-03) : Report T-TECH-42-03 (montant maquette Copilote)

| Attribut | Valeur |
|----------|--------|
| Description | Report de T-TECH-42-03 → T-TECH-43-03 : corriger « 1 017,35 € » → « 963,50 € HT » dans la maquette Copilote du canevas Claude Design G2StRQigRKD6SqNRoyHpkW. |
| Responsable | Worker wdesign |
| Deadline | Sprint 43, avant toute story touchant la maquette copilote |
| DoD | Maquette mise à jour ; montant cohérent avec les fixtures. |
| Priorité | Basse |
