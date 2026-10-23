# Vérification des stories en review (T-TECH-22-01)

- Date : 2026-10-23 · Base : `main` @ `c45d9d1` (sprint 22 et #61 mergés, déployés).
- Méthode : démo locale en mode prod (remise à zéro faite, mêmes données que la production), parcours au navigateur sans interface sur les comptes de démo ; production interrogée en lecture seule, sans connexion (`bin/verify-public-mode`, en-têtes, mesure mobile).
- Limites : remise à zéro nocturne, Umami et variables Railway non vérifiables en local ; aucun détail des limiteurs de débit ici (dépôt public).

## Production (T-TECH-22-01c)

`bin/verify-public-mode` : les 12 vérifications automatiques sont OK. HSTS envoyé une seule fois, aucun lien « Créer un compte », `/build/*` en cache d'un an `immutable`, premières vignettes du catalogue en chargement immédiat. LCP mobile du catalogue : 1 976 ms le 2026-10-22, 1 448 ms le 2026-10-23 ; les quatre pages clés restent dans la zone « bon ».

## Verdicts

| Story | Verdict | Remarque |
|---|---|---|
| US-105 | done | Reprise d'un parcours après remise à zéro non rejouée. |
| US-106 | review | Écran de sortie sans lien profil : corrigé par la PR #62. |
| US-107 | done | |
| US-117 | done | Partie locale conforme ; comptages Umami confirmés par Thibaut le 2026-10-23. |
| US-158 | review | Même écart que US-106 (PR #62) ; le reste est conforme. |
| US-160 | done | Verrou de `bin/run-tests` lu dans le code. |
| US-111 | done | |
| US-068 | done | `bin/vitrine-readme --check --ref=origin/main` : les écarts relevés sur les dépôts d'origine sont des faux positifs (commentaires de code traduits, captures propres à chaque langue). Le README de company-insights, créé après la story, n'a ni bloc démo ni lien de langue : suivi à part. |
| US-155 | done | |
| US-056 | done | Écritures admin refusées en démo publique par conception ; création, modification et suppression d'un modèle acceptées par Thibaut le 2026-10-23 (tests du plugin). |
| US-057 | done | Ajout, modification, validation, 404 sur un parc étranger et partage par entreprise conformes. La suppression ne demande pas de confirmation (`data-turbo-confirm` sans Turbo), comme la suppression d'adresse de Sylius. Décision de Thibaut le 2026-10-23 : ajouter une confirmation ; livrée le jour même (page de confirmation sans JavaScript, plugin v0.1.1, montée dans la démo). « Paul modifie » non vérifié (comptes partagés en lecture seule). |
| US-058 | done | |
| US-157 | done | Panneau vérifié en local avec le script du plugin ; script servi en 200 en production. |
| US-164 | review | Parc en lecture seule pour les comptes partagés et limiteurs actifs ; un refus de suppression menait à une page 405 : corrigé par la PR #63. |
| US-162 | done | Vérifiée en production. |
| US-167 | done | Vérifiée en production. |
| US-088 | done | |
| US-098 | done | |
| US-060 | done | |
| US-084 | done | Rector et seuil de couverture actifs dans la CI de `main`. |
| US-165 | done | CI de `main` verte. |

Restent en review hors de cette vérification : les stories de communication (US-116, US-120, US-122, US-123, US-124, US-127, US-129, US-159), qui attendent la relecture de Thibaut (T-TECH-22-01g). US-161 passe en done : format du registre validé par Thibaut le 2026-10-23 (T-161-12).

US-148 passe en done : le 2026-10-23, les cinq services à limiteurs (quote-request, quick-order, b2b-mcp, punchout-cxml, command-palette) ont été redéclarés en `autowire: true` dans la démo, limiteurs laissés à l'autowiring par liaisons typées `RateLimiterFactoryInterface $argument` ; `lint:container` passe et chaque service reçoit ses limiteurs nommés. Aucun type concret `RateLimiterFactory` ne reste dans le code des cinq plugins (T-148-06).
