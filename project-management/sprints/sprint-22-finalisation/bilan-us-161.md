# Bilan des cinq indicateurs de la rétrospective du sprint 17 (US-161)

Préparé le 2026-10-23 pour la rétrospective du sprint 22 ; les cases « à confirmer » se remplissent à la rétro avec les incidents réels du sprint.

| # | Action | Livrable en place | Indicateur | Constat |
|---|--------|-------------------|------------|---------|
| 1 | Contrôle du disque | `bin/check-disk`, gel de `bin/run-tests` sous 5 Go | 0 blocage disque de plus de 30 min | Le gel de 5 Go a bien refusé un run le 2026-10-23 (4,6 Go libres) ; à confirmer : durée des blocages |
| 2 | Isolation des workers | `WORKER_ID`, `bin/clean-test-caches` ciblé, `--all` réservé | 0 fichier écrasé, 0 base supprimée par un autre worker | à confirmer à la rétro |
| 3 | Vérification croisée | `project-management/templates/checklist-integration.md` | 0 écart « vert annoncé / rouge à l'intégration » | checklist livrée (T-161-06) ; à confirmer |
| 4 | Attente bornée | `bin/run-tests --wait` (30 min), `TaskStop` vérifié | 0 worker bloqué sur un run terminé | à confirmer |
| 5 | Prérequis et registre | rubrique du gabarit de story, `communication/INDEX.md` | 0 prérequis découvert en cours de sprint | format du registre validé par Thibaut le 2026-10-23 (T-161-12) |
