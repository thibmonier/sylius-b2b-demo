# Audits après le sprint 21

- Date : 2026-10-22 · Base : `main` @ `4a4dcfc` (sprint 21 mergé et déployé, Sylius 2.3.2, node 24)
- Mode : `/team:audit` en séquentiel (une seule pile, Symfony) et revue de sécurité en deux passes, en lecture seule ; production interrogée en GET passifs uniquement.

| Audit | Résultat | Évolution depuis le 2026-10-18 | Détail |
|---|---|---|---|
| Qualité du code | 67/100 (architecture 15/25, code 15/25, tests 20/25) ; PHPStan niveau 9 et ECS à 0 ; suite locale 1787 tests verts | 45 → 67/100 | `qualite/rapport.md`, `qualite/result.json` |
| Sécurité | risque moyen : 0 critique, 2 hauts conditionnels (repris du 2026-10-18, à confirmer par Thibaut), 1 moyen, 3 bas, 1 information | 8 moyens et 18 bas → 1 moyen et 3 bas | `securite/synthese.md` (sans détail exploitable, dépôt public) |
| Performance web (2026-10-23) | bon sur les 4 pages clés, bureau et mobile bridé : LCP mobile 1,6 à 2,0 s, CLS 0, TBT < 40 ms, TTFB < 500 ms | première mesure | `performance/rapport.md` (mesure de repli sans MCP chrome-devtools) |

## Actions proposées pour le sprint 22

1. Sécurité : clore les deux points hauts du 2026-10-18 (exposition des services Railway, configuration versionnée) et cocher la checklist US-079.
2. Sécurité : borner le parc d'équipements des comptes partagés (débit et plafond) et ajouter les routes récentes aux seaux de limitation.
3. Qualité : sortir l'écriture en base du contrôleur des listes de l'entreprise, extraire les codes acheteurs du simulateur PunchOut, supprimer la branche « absent de la démo » de la vitrine.
4. Qualité : Rector en CI, seuil de couverture, `bin/proof-refresh` (le nombre de tests de la preuve est en retard).
5. Qualité : découper les méthodes de plus de 45 lignes ; `usleep` à retirer des tests.
6. Performance (faible effort) : vignettes du haut du catalogue en chargement immédiat, cache `immutable` d'un an sur `/build/*`.
