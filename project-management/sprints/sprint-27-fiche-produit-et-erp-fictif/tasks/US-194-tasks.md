# Tâches : US-194 : Logo de l'auteur dans le pied de page

## Informations US

- **Epic** : EPIC-12
- **Story Points** : 1 (Should, ajoutée le 2026-10-24 sur demande de Thibaut)
- **Sprint** : sprint-27-fiche-produit-et-erp-fictif
- **Worker** : w185
- **Story** : [US-194](../../../backlog/user-stories/US-194.md)

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-194-01 | [FE-WEB] | Test rouge commité seul puis logo de l'auteur dans `footer_demo.html.twig` : `alt` vide, `width` et `height`, fichier de moins de 8 Ko, 360 px sans défilement ; suite ciblée, PHPStan sur tout le dépôt, ECS, Rector ; mesures de production (pa11y, 360 px) consignées le jour du déploiement | 1 h | - | Claude | ✅ |

**Total estimé : 1 h** (1 tâche).

## Scénarios et preuves

| Scénario | Test ou preuve |
|----------|----------------|
| Logo près de la mention de l'auteur (nominal) | `FooterAuthorLogoTest` (rouge seul `1e990ca`, code `0eac3a7`) ; PR #95 |
| Image décorative (alternatif) | `FooterAuthorLogoTest` (`alt=""`) ; pa11y WCAG2AAA sans problème, FR et EN (local) |
| Dimensions déclarées (alternatif) | `FooterAuthorLogoTest` (`width`, `height`, `loading=lazy`, `decoding=async`) |
| Téléphone (erreur) | mesuré à 360 et 1280 px en local : pas de défilement horizontal, logo centré sur le texte |
| Fichier trop lourd (erreur) | `DemoFooterTest` (n'admet que cette image dans le pied de page) ; WebP 112×112, 2,4 Ko (budget de 8 Ko) |

**Preuves (sprint 27).** PR #95 (`61a8829`), PHPStan sur tout le dépôt sans erreur. **Contrôle de production fait le 2026-10-24 après déploiement** : le logo est servi dans le pied de page (`img data-author-logo`, WebP haché, `width` et `height` 56, `alt` vide, `loading=lazy`) ; la home FR et EN, mesurée le même jour, n'a aucun défilement horizontal à 360 et 1280 px et pa11y WCAG2AAA « No issues found! ».
