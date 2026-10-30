# Sprint 37 : Task Board

> Ce tableau est la vue visuelle du sprint backlog. État à jour dans `.bmad/sprint-status.yaml` (source de vérité).

## 🎯 Sprint Goal

> « Lancer le copilote d'achat B2B avec son widget du compte et son adaptateur rejoué, compléter le passeport numérique produit avec QR, lien GS1 Digital Link et pièces compatibles, et installer le plugin passeport dans la démo. »

## 📊 Métriques

| Métrique | Valeur |
|----------|--------|
| Points engagés | 13 |
| Tâches totales | 48 (décomposées le 2026-10-30) |
| Heures estimées | 32 h (31 h Claude, 1 h Thibaut) |
| Capacité | 35,3 h (13 × 2,1 + 8 h transverses) |
| Marge | 4,3 h (12,2 %) |
| Tâches terminées | 0 |
| Tâches en cours | 0 |
| Tâches bloquées | 0 |

## 🔲 À faire (43 tâches)

Voir détails dans [tasks/README.md](./tasks/README.md).

| US/Transverse | Tâches | Heures | Status |
|---------------|--------|--------|--------|
| [Transverse](./tasks/technical-tasks.md) | 5 | 4 h | 🔲 |
| [US-241](./tasks/US-241-tasks.md) | 7 | 4 h | 🔲 |
| [US-211](./tasks/US-211-tasks.md) | 15 | 11 h | 🔲 |
| [US-232](./tasks/US-232-tasks.md) | 8 | 6,5 h | 🔲 |
| [US-233](./tasks/US-233-tasks.md) | 8 | 6,5 h | 🔲 |

## 🔄 En cours

Aucune tâche en cours.

## 👀 En revue

Aucune tâche en revue.

## ✅ Terminé

Aucune tâche terminée.

## 🚫 Bloqué

Aucune tâche bloquée.

## Ordre d'exécution recommandé

1. **Tâches transverses** (T-TECH-37-01 à 05) : parallèles, lancées en premier
2. **US-241** : installation du plugin passeport dans la démo (après US-231 v0.1.0, pas de dépendance avec US-232/233)
3. **US-211** : squelette du plugin copilote (GO dépôt donné et dépôt créé le 2026-10-30)
4. **US-232** et **US-233** : extensions du plugin passeport (même dépôt, séquentielles côté worker)
