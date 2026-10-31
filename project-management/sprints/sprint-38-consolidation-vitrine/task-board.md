# Sprint 38 : Task Board

> Ce tableau est la vue visuelle du sprint backlog. État à jour dans `.bmad/sprint-status.yaml` (source de vérité).

## 🎯 Sprint Goal

> « Installer le copilote d'achat dans la démo avec ses captures, vérifier le passeport produit en production, rendre la démo Registration Approval utilisable en mode public et créer la fiche équipement avec lien vers le passeport et les pièces compatibles. »

## 📊 Métriques

| Métrique | Valeur |
|----------|--------|
| Points engagés | 10 |
| Tâches totales | 33 (décomposées le 2026-10-30) |
| Heures estimées | 23,75 h (toutes pour Claude) |
| Capacité | 27 h (10 × 2,1 + 6 h transverses) |
| Marge | 3,25 h (12 %) |
| Stories livrées | 4 / 4 (en review) |
| Transverses terminées | 5 / 5 |
| Tâches bloquées | 0 |

## 🔲 À faire

Aucune tâche à faire.

Voir détails dans [tasks/README.md](./tasks/README.md).

## 🔄 En cours

Aucune tâche en cours.

## 👀 En revue

US-242, US-243, US-244, US-245 : livrées le 2026-10-31, validation de Thibaut attendue (PR #144 de la démo).

## ✅ Terminé

T-TECH-38-01 à 05, et toutes les tâches des quatre stories (détail dans `tasks/`).

## 🚫 Bloqué

Aucune tâche bloquée.

## Ordre d'exécution recommandé

1. **Lot 1 (parallèle)** : wtech (T-TECH-38-01 à 05), w244 (US-244), w245 (US-245)
   - wtech : actions de rétro, gabarits de tests (parallèles, 4,25 h)
   - w244 : copilote dans la démo, captures (séquentiel, 5,75 h)
   - w245 : vérification production, test fumée (séquentiel, 2 h)

2. **Lot 2 (parallèle après lot 1)** : w243 (US-243), w242 (US-242)
   - w243 : démo Registration Approval en mode public (séquentiel, 5,25 h)
   - w242 : fiche équipement avec passeport (deux plugins + démo, séquentiel, 6,5 h)

Contrainte : au plus 3 workers lancent des tests en même temps (8 Go RAM). Chevauchement config/demo/plugins.yaml : w244 avant w243.

## Vue par US

| US | Statut | Tâches | Heures |
|----|--------|--------|--------|
| [US-244](./tasks/US-244-tasks.md) | 👀 review | 8 / 8 | 5,75 h |
| [US-243](./tasks/US-243-tasks.md) | 👀 review | 7 / 7 | 5,25 h |
| [US-242](./tasks/US-242-tasks.md) | 👀 review | 9 / 9 | 6,5 h |
| [US-245](./tasks/US-245-tasks.md) | 👀 review | 4 / 4 | 2 h |
| [Transverse](./tasks/technical-tasks.md) | ✅ | 5 / 5 | 4,25 h |
