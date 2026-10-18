# Audits avant l'ouverture publique (après le sprint 13)

- Date : 2026-10-18 · Base : `main` @ `3927587` (sprint 13 mergé, déployé et remis à zéro)
- Cible : démo locale en prod (porte privée désactivée), en lecture seule ; aucun fichier du projet modifié pendant les audits.
- Versions partagées (Claude Docs, privées tant qu'elles ne sont pas partagées) : « Audits démo Sylius B2B » et « Audit fonctionnel B2B Fournitures Pro ».

| Audit | Commande | Résultat | Détail |
|---|---|---|---|
| UI/UX et accessibilité WCAG 2.2 AAA | `/uiux:audit` | accessibilité 58/100, UX 68/100, UI 66/100 ; Lighthouse mobile perf 75-79, a11y 91-95, SEO 58 | `uiux/rapport.md`, `uiux/lighthouse.md`, `uiux/pa11y-wcag2aaa.txt` |
| Sécurité (code, dépendances, infrastructure) | `/team:security` | risque moyen : 0 critique, 2 hauts conditionnels, 8 moyens, 18 bas | `securite/synthese.md` (sans détail exploitable, dépôt public) |
| Qualité du code (Symfony, mode séquentiel) | `/team:audit` | 45/100 au barème, environ 75/100 estimé : PHPStan niveau 9 et ECS à 0, 23 méthodes de plus de 30 lignes | `qualite/result.json` |
| Fonctionnel e-commerce B2B | revue experte | démonstration 6,5/10, visuel 5/10, attractivité fonctionnelle 6/10 ; conversion vers le consultant quasi nulle | `fonctionnel/audit-fonctionnel.md` |

## Lecture

- Bloquant pour l'ouverture : débordement mobile (connexion, pagination), structure de page (`<main>`, lien d'évitement), contrastes AAA, et les cinq vérifications de sécurité de `securite/synthese.md`.
- Bloquant pour l'objectif commercial : catalogue peu crédible, écrans vides, paliers invisibles sur la fiche, aucun contenu de conversion.
- Le score qualité du barème est tiré vers le bas par les méthodes longues des fixtures ; l'outillage est entièrement vert, mais la couverture n'est pas mesurée (ni pcov ni xdebug).

## Suite donnée

Backlog alimenté le 2026-10-18 : EPIC-10 à EPIC-14 et `project-management/backlog/roadmap-ouverture-et-communication.md` (sprints 14 et suivants : ouverture publique, puis communication LinkedIn et site).
