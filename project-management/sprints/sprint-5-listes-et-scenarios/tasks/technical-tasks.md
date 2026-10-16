# Tâches techniques transverses — Sprint 5

## Vue d'ensemble

Issues de l'audit (action n°1) : mesurer la couverture en CI et valider le schéma Doctrine. Transverses au sprint.

---

## CI / Qualité

### T-TECH-01 — Couverture en CI : driver pcov (coverage: pcov dans setup-php)

- **Type** : [OPS]
- **Estimation** : 1h
- **Dépend de** : —

**Description** :
Dans le workflow CI, activer `coverage: pcov` (setup-php) et lancer PHPUnit avec `--coverage-text`.

**Critères** :
- [x] Rapport de couverture texte dans le journal CI

---

### T-TECH-02 — Seuil de couverture affiché (rapport texte + seuil, non bloquant)

- **Type** : [OPS]
- **Estimation** : 1h
- **Dépend de** : T-TECH-01

**Description** :
Afficher le pourcentage mesuré face au seuil cible (≥ 80 %) dans le résumé du job ; bloquant seulement après décision explicite.

**Critères** :
- [x] Pourcentage et seuil visibles dans le résumé du job

---

### T-TECH-03 — Validation du schéma Doctrine en CI : `doctrine:schema:validate --skip-sync`

- **Type** : [OPS]
- **Estimation** : 0.5h
- **Dépend de** : —

**Description** :
Ajouter l'étape CI (mapping seul, sans comparer la base).

**Critères** :
- [x] Étape verte en CI ; échec si mapping invalide (validé en local, CI au push)

---

### T-TECH-04 — Documentation CI (couverture, schema:validate)

- **Type** : [DOC]
- **Estimation** : 0.5h
- **Dépend de** : T-TECH-02, T-TECH-03

**Description** :
Compléter la doc qualité.

**Critères** :
- [ ] Doc relue

---

## Résumé des tâches techniques

| ID | Type | Tâche | Estimation |
|----|------|-------|------------|
| T-TECH-01 | [OPS] | Couverture en CI : driver pcov (coverage: pcov dans setup-php) | 1h |
| T-TECH-02 | [OPS] | Seuil de couverture affiché (rapport texte + seuil, non bloquant) | 1h |
| T-TECH-03 | [OPS] | Validation du schéma Doctrine en CI : `doctrine:schema:validate --skip-sync` | 0.5h |
| T-TECH-04 | [DOC] | Documentation CI (couverture, schema:validate) | 0.5h |
| **TOTAL** | | | **3h** |
