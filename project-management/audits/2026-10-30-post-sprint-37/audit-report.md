# Audits après le sprint 37

- Date : 2026-10-30 · Base : `main` @ `b1ad1c6` (sprint 37 fusionné ; passeport v0.2.0 et copilote v0.1.0 publiés)
- Demande de Thibaut (registre du sprint 37) : relancer tous les audits après la revue et la rétro, puis transformer chaque axe d'amélioration en story ou en issue, proposées avant création.
- Mode : quatre audits en parallèle par des sous-agents, en lecture seule ; production interrogée en GET passifs uniquement. Chaque rapport a été vérifié par l'orchestrateur avant d'être retenu : l'audit fonctionnel a été refait (URL devinées par le sous-agent), un axe qualité a été écarté (preuve déjà à jour), la conclusion d'accessibilité a été nuancée et une référence de story corrigée en sécurité.

| Audit | Résultat | Évolution | Détail |
|---|---|---|---|
| Qualité, architecture, tests | 84/100 (architecture 17/25, code 21/25, tests 25/25) ; PHPStan et ECS à 0, Rector à 0 et en CI, seuil de couverture de 90 % bloquant ; 330 fichiers de tests | 67 → 84/100 depuis le 2026-10-22 | `qualite/rapport.md`, `qualite/result.json` |
| Sécurité | risque faible : 0 critique, 0 haut, 4 bas (2 ouverts repris, 1 nouveau, 1 clôturé), 2 informations ; les deux hauts conditionnels du 2026-10-18 sont fermés (US-079) | moyen → faible | `securite/synthese.md` (sans détail exploitable, dépôt public ; détail conservé hors dépôt) |
| Performance web | TTFB moyen 110 ms sur 6 pages (91 à 143 ms) ; mesure de repli par cURL, sans LCP, CLS ni TBT cette fois | TTFB 325 → 110 ms | `performance/rapport.md` |
| UI/UX et accessibilité | pa11y WCAG2AAA : 0 erreur, 0 avertissement sur 6 pages publiques (accueil, plugins, catégorie, fiche produit, À propos, Preuve) ; pages connectées non testées | 55 erreurs (sprint 13) → 0 | `uiux/rapport.md`, `uiux/pa11y-wcag2aaa.txt` |
| Fonctionnel | routes de démo du catalogue toutes présentes ; 1 écart moyen (démo Registration Approval en 403 en mode public), 1 bas (passeport sans données en production avant la remise à zéro nocturne), 1 information | première passe vérifiée route par route | `fonctionnel/audit-fonctionnel.md` |

## Axes d'amélioration retenus

Chaque axe est proposé à Thibaut avant création, soit en story du backlog de la démo, soit en issue GitHub du dépôt de plugin concerné. Les axes de sécurité ne deviennent pas des issues publiques détaillées : ils sont suivis avec une formulation neutre, le détail restant hors dépôt.

| # | Axe | Origine | Priorité | Estimation | Cible proposée |
|---|---|---|---|---|---|
| A1 | Démo Registration Approval utilisable en mode public, et contrôle automatique qu'aucun bouton « Voir la démo » ne mène à un 403 ou un 404 | Fonctionnel | Moyenne | 3 pts | Story de la démo |
| A2 | Installer le copilote d'achat dans la démo, captures du widget (suite d'US-211) | Fonctionnel, revue S37 | Moyenne | 3 pts | Story de la démo |
| A3 | Passeport en production : revérifier après la remise à zéro, test de fumée du lien passeport sur EPI-CASK-001 | Fonctionnel | Basse | 1 pt | Tâche de la démo |
| A4 | Découper les classes de plus de 200 lignes (VersionsReport, ProductImageFetcher, AuditLogDemoFixture) et `cartFromState` du simulateur PunchOut | Qualité | Moyenne | 5 pts | Story de la démo |
| A5 | Supprimer les dépendances inversées : codes acheteurs du simulateur PunchOut hors des fixtures, réception de webhook hors du contrôleur | Qualité | Moyenne | 3 pts | Story de la démo |
| A6 | Tests : test fonctionnel de la liste d'achats de société, 4 `usleep` restants, `DEMO_LEGAL_REQUIRED` en CI | Qualité | Basse | 2 pts | Story de la démo |
| A7 | Mesure complète des signaux web (LCP, CLS, TBT) et cache `immutable` sur `/build/*` | Performance | Basse | 2 pts | Story de la démo |
| A8 | En-tête `Vary` en double ; décision sur `preload` HSTS | Sécurité (information) | Basse | 1 pt | Story de la démo (décision de Thibaut pour `preload`) |
| A9 | Passeport : valeurs traduisibles (origine, recyclabilité, réparabilité par langue) | Revue S37, US-241 | Moyenne | 3 pts | Issue du plugin passeport |
| A10 | Passeport : téléchargement du QR en SVG et PNG | Revue S37, US-232 | Moyenne | 2 pts | Issue du plugin passeport |
| A11 | Passeport : écran d'administration pour créer et modifier un passeport (dont code de modèle d'équipement) | Revue S37 | Haute | 5 pts | Issue du plugin passeport |
| A12 | Passeport : pièce « indisponible » aussi pour une variante désactivée ou un produit absent du canal | Revue S37, US-233 | Basse | 1 pt | Issue du plugin passeport |
| A13 | Passeport : identifiant public non séquentiel pour la page publique, et revue de la sortie SVG du QR | Sécurité (bas, information) | Basse | 3 pts | Suivi neutre, détail hors dépôt |
| A14 | Passeport : passer à endroid/qr-code ^6 quand la version minimale de PHP le permettra | Revue S36 | Basse | 1 pt | Issue du plugin passeport |
| A15 | Parc d'équipements : droits de modification et de suppression selon le rôle dans la société, plafond de machines atomique par flotte | Sécurité (bas, repris) | Basse | 3 pts | Issue du plugin customer-asset-management (règle métier) |

US-242 (fiche équipement avec lien vers le passeport) et US-212 (réassort du copilote) sont déjà au backlog et ne sont pas reprises ici.

## Décision de Thibaut (2026-10-30)

- Créés au backlog de la démo : A1 → US-243, A2 → US-244, A3 → US-245.
- Ouverts dans le dépôt du plugin passeport : A9 → issue n° 9, A10 → n° 10, A11 → n° 11, A12 → n° 12.
- Non créés pour l'instant : A4 à A8, A13 à A15 (restent dans ce rapport).
