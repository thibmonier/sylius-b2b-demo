# Sprint 42 — Revue (sprint review)

> **Date** : 2026-10-31
> **Sprint** : sprint-42-copilote-et-parcours (11 pts)
> **PR** : #149 (`feature/sprint-42-copilote-et-parcours`)

## Sprint Goal

> « Permettre au copilote d'ajouter un produit au panier par sa référence et d'afficher l'état du panier, enrichir les scénarios enregistrés pour la démo publique, et ajouter un parcours guidé copilote dans la démo. »

**Verdict** : ATTEINT (sous réserve CI verte et push/tag du plugin v1.2.0)

## Stories livrées

| ID | Titre | Pts | Statut | Notes |
|----|-------|-----|--------|-------|
| US-213 | Copilote : ajout par référence et état du panier | 5 | review | 16 cassettes, 24 tests plugin, chip suggestion ajouté |
| US-215 | Copilote : mode rejoué et scénarios enregistrés | 3 | review | Alias + keyword matching, fallback avec suggestions, CopilotReplayConfigTest |
| US-237 | Parcours guidé « copilote d'achat » | 3 | review | 6e parcours dans journeys.yaml, traductions FR/EN, JourneyTest mis à jour |

**Total livré** : 11 / 11 points (100 %)

## Démonstration

### US-213 — Ajout par référence et état du panier

- **10 nouvelles cassettes JSONL** dans le plugin copilote (+ 6 existantes = 16 total) :
  - FR/EN : ajout par référence simple (`quick_order_add`, PB-GANT-NITR-09)
  - FR/EN : ajout par lot (`quick_order_add_batch`, 5 × PB-GANT-NITR-09 + 3 × EC-BOUCH-MOUS-50)
  - FR/EN : état du panier enrichi (47 articles, 963,50 € HT, Top 3 détaillé)
  - FR/EN : erreur référence inconnue (INEXISTANT-999)
  - FR/EN : erreur produit hors catalogue (PRODUIT-FERME-01 / CLOSED-PROD-01)
  - FR/EN : erreur quantité invalide (−3)
- **ToolCall model** : `result` élargi à `string|array` pour supporter les résultats enrichis
- **Chip suggestion** « ajoute la référence PB-GANT-NITR-09 au panier » ajouté dans le template du widget (4e bouton)
- **24 tests unitaires** GREEN (71 assertions), PHPStan level max 0 erreurs, ECS 0 erreurs

### US-215 — Mode rejoué et scénarios enregistrés

- **Matching par alias** : chaque cassette accepte un champ `aliases` (liste de formulations alternatives), matché par hash normalisé
- **Matching par mots-clés** : fallback fuzzy (extraction de mots significatifs > 2 caractères, hors stop words FR/EN, seuil minimal de 2 mots communs)
- **Fallback amélioré** : détection de langue (heuristique par indicateurs FR), liste des scénarios disponibles dans la langue détectée
- **Mention démo** : `demo_notice` déjà dans le header et le footer du widget (« Réponses enregistrées en démo — ce widget simule un copilote IA sans appeler d'API externe »)
- **CopilotReplayConfigTest** dans la démo : vérifie l'absence de clé API LLM, l'enregistrement du bundle, et la présence des cassettes (≥ 6)

### US-237 — Parcours guidé copilote

- **6e parcours** ajouté dans `config/demo/journeys.yaml` :
  - Persona : buyer (Claire), compte : `claire@acme.example`, 4 min
  - Étape 1 : tableau de bord (widget visible)
  - Étape 2 : commande rapide (ajout par référence)
  - Étape 3 : panier (scénario cart avec liste Atelier)
  - Étape 4 : tableau de bord (formulation libre)
- **Traductions FR/EN** complètes (titre, objectif, 4 étapes), mention « réponses enregistrées en démo » et « l'IA prépare, l'acheteuse valide »
- **Introduction mise à jour** : « Cinq parcours » → « Six parcours » (FR/EN)
- **JourneyTest** : data provider `everyStep` étendu avec `copilot => 4`, compteurs de liste 5 → 6

### Tâches techniques

| ID | Titre | Statut |
|----|-------|--------|
| T-TECH-42-01 | Garde-fou sélecteurs CSS plugin ↔ démo | done (PluginCssSelectorGuardTest.php) |
| T-TECH-42-02 | Script bin/plugin-push-all | done (fe5d847) |
| T-TECH-42-03 | Corriger le montant maquette Copilote | skipped (canevas Claude Design, hors code) |
| T-TECH-42-04 | Bump copilote ^1.1 et retirer le template override | done (aba5b82) |

## Métriques

| Métrique | Valeur |
|----------|--------|
| Points engagés | 11 |
| Points livrés | 11 |
| Commits sur la branche demo | 8 |
| Fichiers modifiés (demo) | 21 (+997 / −122 lignes) |
| Cassettes ajoutées (plugin) | 10 (total : 16) |
| Tests ajoutés (plugin) | 16 (total : 24 unitaires) |
| Tests ajoutés (demo) | 1 (CopilotReplayConfigTest) |
| Gate validation | 99/100 après remédiation (5 anomalies corrigées) |

## Risques et points d'attention

1. **Push et tag du plugin** : le plugin copilote a 6 commits locaux non poussés. Le tag v1.2.0 doit être créé et poussé avant que la CI de la démo puisse résoudre les cassettes via `composer update`. Nécessite le GO de Thibaut.
2. **Contrainte composer** : `^1.1` dans la démo accepte `1.2.0` par semver. Aucun bump nécessaire dans `composer.json`.
3. **Tests fonctionnels** : les tests fonctionnels du plugin (18 erreurs) nécessitent Docker et une base de données — seuls les tests unitaires (24/24) ont été validés localement.
4. **T-TECH-42-03** : la correction du montant dans le canevas Claude Design reste à faire (opération manuelle).

## Actions pour la clôture

- [ ] CI verte sur PR #149
- [ ] Pousser le plugin copilote + tag v1.2.0 (GO de Thibaut)
- [ ] `composer update tmonier/sylius-b2b-sales-copilot-plugin` dans la démo après le tag
- [ ] Vérifier la page des parcours en production (6 parcours visibles)
- [ ] Vérifier le widget copilote (4 chips de suggestion)
- [ ] Corriger le montant maquette (T-TECH-42-03, hors code)
