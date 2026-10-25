# EPIC-12 — La démo comme preuve : parcours, guide technique et preuve de qualité

> Exigences : décision de Thibaut du 2026-10-21 (la démo sert la recherche d'emploi, plus la vente de conseil) · 9 stories actives · 36 points · 4 stories retirées le 2026-10-21 (US-069, US-109, US-110, US-112, US-114 : 21 points) · titre d'origine : « Conversion et contenu commercial »

## Description

Faire de la démo une **preuve vérifiable** de ce qu'affirme le profil de Thibaut : 25 ans de delivery, IA générative intégrée au delivery avec qualité, sécurité et conformité intactes, expertise Symfony, Sylius et e-commerce B2B. Parcours guidés par persona, guide technique en libre-service (page « Intégrer »), bloc de preuve de qualité, accueil qui dit qui a conçu la démo et comment. Ton neutre, aucune sollicitation commerciale, jamais de comparaison avec une offre éditeur. Le point d'arrivée des appels à l'action est le profil LinkedIn (`DEMO_PROFILE_URL`).

## MMF (Minimum Marketable Feature)

**Plus petite version livrable** : Un visiteur (recruteur, dirigeant, pair) qui termine un parcours a vu la démo fonctionner, sait comment elle est construite et atteint le profil de l'auteur en un clic.
**US incluses** : US-104, US-105, US-106, US-107 (livrées), US-158 (repositionnement, sprint 18) ; le reste en Should.

## Stories

| Story | Titre | MoSCoW | Points | Sprint cible |
|---|---|---|---|---|
| [US-104](../user-stories/US-104.md) | Retirer l'encart « Sylius Plus » de la page Plugins | Must | 1 | 14 (fait) |
| [US-105](../user-stories/US-105.md) | Parcours guidés : moteur de visite par étapes | Must | 8 | 17 |
| [US-106](../user-stories/US-106.md) | Parcours guidés : contenu par persona et écran de sortie (CTA neutre) | Must | 5 | 17 |
| [US-107](../user-stories/US-107.md) | Page « Intégrer » : guide technique en libre-service | Must | 5 | 17 (recadrée par US-158) |
| [US-142](../user-stories/US-142.md) | Appel à l'action vers l'auteur dans toute la démo (cible : profil LinkedIn) | Must | 5 | 16 (recadrée par US-158) |
| [US-158](../user-stories/US-158.md) | Repositionnement de la démo : profil LinkedIn, libellés neutres, guide technique | Must | 3 | 18 (en cours) |
| [US-111](../user-stories/US-111.md) | Bloc de preuve technique : qualité, compatibilité, versions | Should | 3 | 18 |
| [US-113](../user-stories/US-113.md) | Accueil réorganisé : trois colonnes, pourquoi cette démo, qui l'a conçue | Should | 3 | 20 |
| [US-108](../user-stories/US-108.md) | Page « Intégrations » : ERP, PIM, transporteurs, e-facture, grands comptes, assistants | Should | 3 | 20 |
| [US-197](../user-stories/US-197.md) | Photos du catalogue : cadrage des cartes et variantes de couleur | Should | 3 | 29 |
| [US-198](../user-stories/US-198.md) | Refonte de la page de liste des produits dans la continuité de la home | Should | 5 | 29 |
| [US-199](../user-stories/US-199.md) | Refonte de la page « Mon compte » dans la continuité de la home | Should | 5 | sprint 30 |
| [US-200](../user-stories/US-200.md) | Refonte de la page panier dans la continuité de la home | Should | 5 | 29 |
| [US-201](../user-stories/US-201.md) | Tableau de bord des achats plus lisible, dans la continuité de la home | Should | 5 | sprint 30 |
| [US-202](../user-stories/US-202.md) | Catégories de taxe sur le catalogue de démonstration : TVA affichée au panier et sur les commandes | Should | 3 | sprint 30 |
| ~~US-069~~ | La page Plugins renvoie vers le site | retirée (absorbée par US-158) | 2 | - |
| ~~US-109~~ | Cas d'usage sectoriels avec appel à l'action | retirée | 5 | - |
| ~~US-110~~ | Mini-diagnostic en six questions | retirée | 5 | - |
| ~~US-112~~ | Mode « présentation commerciale » | retirée | 5 | - |
| ~~US-114~~ | Études de cas fictives signalées | retirée | 3 | - |
| ~~US-115~~ | Appels à l'action cohérents | annulée (absorbée par US-142) | 2 | - |

**Notes** : `DEMO_SITE_URL` et `DEMO_CONTACT_URL` restent facultatives (liens masqués quand elles sont vides). `DEMO_BOOKING_URL` est supprimée au profit de `DEMO_PROFILE_URL`.
