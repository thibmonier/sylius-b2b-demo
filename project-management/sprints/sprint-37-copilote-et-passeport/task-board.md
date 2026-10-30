# Sprint 37 : Task Board

> Ce tableau est la vue visuelle du sprint backlog. État à jour dans `.bmad/sprint-status.yaml` (source de vérité).

## 🎯 Sprint Goal

> « Lancer le copilote d'achat B2B avec son widget du compte et son adaptateur rejoué, compléter le passeport numérique produit avec QR, lien GS1 Digital Link et pièces compatibles, et installer le plugin passeport dans la démo. »

## 📊 Métriques

| Métrique | Valeur |
|----------|--------|
| Points engagés | 13 |
| Tâches totales | 43 + T-TECH-37-06 (décomposées le 2026-10-30) |
| Heures estimées | 32 h (31 h Claude, 1 h Thibaut) |
| Capacité | 35,3 h (13 × 2,1 + 8 h transverses) |
| Marge | 4,3 h (12,2 %) |
| Stories livrées (en revue) | 4 / 4 (13 points) |
| Transverses terminées | 6 / 6 |
| Tâches bloquées | 0 |

## 🔲 À faire

Aucune tâche.

## 🔄 En cours

Aucune tâche en cours.

## 👀 En revue (2026-10-30)

Voir détails dans [tasks/README.md](./tasks/README.md) et le [registre](./registre-decisions.md).

| US | Livraison | Écarts au périmètre |
|----|-----------|---------------------|
| [US-241](./tasks/US-241-tasks.md) | Plugin passeport installé dans la démo (8 tests) ; captures FR/EN et pa11y AAA sans erreur (PR n° 5 du plugin) | Valeurs du passeport non traduisibles (dette) |
| [US-211](./tasks/US-211-tasks.md) | Nouveau dépôt copilote, PR n° 1 fusionnée (17 tests) | Captures du widget non livrées ; dépendance MCP retirée |
| [US-232](./tasks/US-232-tasks.md) | Lien GS1 et QR, onglet admin « Étiquettes », PR n° 6 fusionnée (25 tests) | Téléchargements SVG/PNG non livrés |
| [US-233](./tasks/US-233-tasks.md) | Pièces compatibles sur la page publique, PR n° 7 fusionnée (29 tests) | Scénario « Depuis le parc » reporté en US-242 (décision de Thibaut) |

## ✅ Terminé

| Transverse | Tâches | Status |
|------------|--------|--------|
| [T-TECH-37-01 à 06](./tasks/technical-tasks.md) | 6 (dont 06, bug de `bin/check-lock-diff`) | ✅ |

## 🚫 Bloqué

Aucune tâche bloquée.

## Ordre d'exécution recommandé

1. **Tâches transverses** (T-TECH-37-01 à 05) : parallèles, lancées en premier
2. **US-241** : installation du plugin passeport dans la démo (après US-231 v0.1.0, pas de dépendance avec US-232/233)
3. **US-211** : squelette du plugin copilote (GO dépôt donné et dépôt créé le 2026-10-30)
4. **US-232** et **US-233** : extensions du plugin passeport (même dépôt, séquentielles côté worker)
