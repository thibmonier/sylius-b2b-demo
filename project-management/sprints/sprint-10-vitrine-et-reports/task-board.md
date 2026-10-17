# Task Board — Sprint 010 (Vitrine et reports)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Calendaire | ⚠️ Sacrifiable

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Assigné |
|----|-----|------|-------|------------|---------|
| T-048-01 | US-048 | [DOC] | Contrat de fin de worker appliqué à chaque consigne | 0.5h | |
| T-048-02 | US-048 | [OPS] | Commande unique de préparation de la base de test locale | 2h | |
| T-048-03 | US-048 | [DOC] | Procédure `docs/` et cas Factur-X | 0.5h | |
| T-048-04 | US-048 | [TEST] | Profilage du reset par fixture et objectifs chiffrés | 2h | |
| T-047-01 | US-047 | [BE] | Spike Openverse CC0 et budget de taille | 1.5h | |
| T-047-02 | US-047 | [BE] | Script manuel de récupération | 4.5h | |
| T-047-03 | US-047 | [TEST] | Tests unitaires du script | 2h | |
| T-047-04 | US-047 | [OPS] | Lancement unique et contrôle visuel (GO utilisateur) | 3h | |
| T-047-05 | US-047 | [TEST] | Garde de budget et de licences | 1h | |
| T-047-06 | US-047 | [BE] | Association image-produit dans l'importeur CSV | 3h | |
| T-047-07 | US-047 | [BE] | Purge M4 et cache LiipImagine | 2h | |
| T-047-08 | US-047 | [TEST] | Catalogue illustré, aucun appel réseau, purge réelle | 2.5h | |
| T-047-10 | US-047 | [DOC] | Documentation des images | 1h | |
| T-047-11 | US-047 | [REV] | Revue | 1h | |
| T-029-01 | US-029 | [DB] | Entité `ReceivedWebhook` et migration | 2h | |
| T-029-02 | US-029 | [BE] | Récepteur compatible avec le garde sortant | 4h | |
| T-029-03 | US-029 | [BE] | Abonnement de démo et validateur d'URL | 2.5h | |
| T-029-04 | US-029 | [FE-WEB] | Page « Webhooks reçus » FR/EN | 2.5h | |
| T-029-05 | US-029 | [TEST] | Test fonctionnel avec livraison réelle via le transport gardé | 3h | |
| T-029-06 | US-029 | [DOC] | Documentation de la page et de l'outbox | 0.5h | |
| T-029-07 | US-029 | [REV] | Revue SSRF, signature, rétention | 1h | |
| T-046-01 | US-046 | [DOC] | Doc officielle Sylius Plus et texte FR/EN (proposition) | 1.5h | |
| T-046-02 | US-046 | [FE-WEB] | Encart sur la page Plugins, FR/EN, lien sûr | 1.5h | |
| T-046-03 | US-046 | [TEST] | Test fonctionnel de l'encart | 1.5h | |
| T-046-04 | US-046 | [REV] | **Validation du texte par l'utilisateur (bloquante avant merge)** | 0.5h | |
| T-046-05 | US-046 | [REV] | Revue technique et politique de communication | 0.5h | |
| T-048-14 | US-048 | [REV] | Revue | 1h | |
| T-048-05 | US-048 | [BE] | ⚠️ Requêtes de `demo_orders` (sacrifice n° 2) | 3h | |
| T-048-06 | US-048 | [BE] | ⚠️ Requêtes de `demo_container_fill` (sacrifice n° 1) | 2.5h | |
| T-048-07 | US-048 | [BE] | ⚠️ N+1 Wishlist sur les listes (sacrifice n° 6) | 2.5h | |
| T-048-08 | US-048 | [BE] | ⚠️ Capacité périmée `VariantLogisticsSeeder` (sacrifice n° 5) | 1h | |
| T-048-09 | US-048 | [BE] | ⚠️ NFC dans `PackagingUnitParser` (sacrifice n° 5) | 0.5h | |
| T-048-10 | US-048 | [BE] | ⚠️ Entrypoint : rechargement après interruption (sacrifice n° 4) | 1.5h | |
| T-047-09 | US-047 | [FE-WEB] | ⚠️ Crédits des images (sacrifice n° 3) | 1h | |

## 📅 Calendaire (GO utilisateur, hors chemin critique)

| ID | US | Tâche | Estimation | Condition |
|----|-----|-------|------------|-----------|
| T-048-11 | US-048 | [OPS] Relevé complet du coût Railway (ex T-041-15) | 0.5h | GO et accès Railway |
| T-048-12 | US-048 | [OPS] `/api/v2/shop/*` sur Railway | 0.5h | GO et accès Railway |
| T-048-13 | US-048 | [OPS] Pic mémoire du reset contre la limite Railway | 1h | GO, accès Railway, après T-048-04 |

## 🔄 En Cours
| ID | US | Tâche | Démarré | Assigné |
|----|-----|-------|---------|---------|

## 👀 En Review
| ID | US | Tâche | Reviewer |
|----|-----|-------|----------|

## ✅ Terminé
| ID | US | Tâche | Réel | Terminé |
|----|-----|-------|------|---------|

## 🚫 Bloqué
| ID | US | Raison | Action |
|----|-----|--------|--------|

## Métriques
- **Tâches** : 37 total | 0 terminées (0 %)
- **Heures** : 62.5h estimées (dont 2h calendaires) | 0h consommées | 62.5h restantes (capacité ≈ 50h, ordre de sacrifice dans [tasks/README.md](./tasks/README.md))
