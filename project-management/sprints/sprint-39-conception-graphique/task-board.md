# Sprint 39 : Task Board

> Ce tableau est la vue visuelle du sprint backlog. État à jour dans `.bmad/sprint-status.yaml` (source de vérité).

## 🎯 Sprint Goal

> « Répondre aux retours de la revue du sprint 38 sur la conception graphique : maquetter dans Claude Design le widget du copilote et le bloc de réassort, écrire le principe de conception et le montrer dans la démo, et aligner la réponse enregistrée du copilote sur le panier de Claire. »

## 📊 Métriques

| Métrique | Valeur |
|----------|--------|
| Points engagés | 8 |
| Tâches totales | 36 (décomposées le 2026-10-31) |
| Heures estimées | 24,5 h (toutes pour Claude) |
| Capacité | 22,8 h (8 × 2,1 + 6 h transverses) |
| Marge | -1,7 h (-7 %, acceptable pour tâches de maquettes) |
| Stories livrées | 0 / 4 |
| Transverses terminées | 0 / 5 |
| Tâches bloquées | 0 |

## 🔲 À faire

**Toutes les tâches sont à faire** (36 tâches au total).

Voir détails dans [tasks/README.md](./tasks/README.md).

## 🔄 En cours

Aucune tâche en cours.

## 👀 En revue

Aucune tâche en revue.

## ✅ Terminé

Aucune tâche terminée.

## 🚫 Bloqué

Aucune tâche bloquée.

## Ordre d'exécution recommandé

1. **Lot 1 (parallèle partiel)** : wtech (T-TECH-39-01 à 05)
   - T-TECH-39-02, T-TECH-39-04, T-TECH-39-05 (parallèles, 3 h)
   - T-TECH-39-01 (test en application hôte, 1,5 h)
   - T-TECH-39-03 (variables test parc, 1,5 h)

2. **Lot 2 (séquentiel)** : wdesign (US-248 puis US-250, **impératif**)
   - US-248 : maquette du widget du copilote (8 tâches, 5 h)
   - US-250 : maquette du bloc de réassort (8 tâches, 4,5 h)

3. **Lot 3 (séquentiel après lot 2)** : w249 (US-249)
   - US-249 : principe écrit et section Architecture (9 tâches, 6,25 h)

4. **Lot 4 (séquentiel après lot 3)** : w247 (US-247)
   - US-247 : réponse enregistrée cohérente (6 tâches, 2,75 h)

**Contrainte critique** : US-248 et US-250 par le **même worker**, dans l'ordre US-248 puis US-250, pour partager les jetons de design. US-246 et US-251 **exclues** (portes humaines de validation).

## Vue par US

| US | Statut | Tâches | Heures |
|----|--------|--------|--------|
| [US-248](./tasks/US-248-tasks.md) | 🔲 prête | 0 / 8 | 5 h |
| [US-250](./tasks/US-250-tasks.md) | 🔲 prête | 0 / 8 | 4,5 h |
| [US-249](./tasks/US-249-tasks.md) | 🔲 prête | 0 / 9 | 6,25 h |
| [US-247](./tasks/US-247-tasks.md) | 🔲 prête | 0 / 6 | 2,75 h |
| [Transverse](./tasks/technical-tasks.md) | 🔲 | 0 / 5 | 6 h |
