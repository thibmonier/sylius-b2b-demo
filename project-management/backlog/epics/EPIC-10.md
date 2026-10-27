# EPIC-10 — Ouverture publique

> Exigences : NFR-4 (sécurité), accessibilité WCAG, i18n · 21 stories · 63 points (dont 1 reprises de stories existantes)

## Description

Lever les bloquants avant l'ouverture publique de la démo : accessibilité mobile et structure (audit AAA 58/100, UX 68, UI 66), pied de page et barre de démo, sécurité et chaîne d'approvisionnement, performances et SEO de base, dossier de GO.

## MMF (Minimum Marketable Feature)

**Plus petite version livrable** : Une démo ouverte au public qui passe la revue de sécurité, se lit sur mobile, au clavier et au lecteur d'écran, sans traces d'un produit Sylius générique.
**US incluses** : US-052, US-071, US-072, US-073, US-074, US-079, US-080, US-085 (Must) ; le reste en Should et Could.

## Stories

| Story | Titre | MoSCoW | Points | Sprint cible |
|---|---|---|---|---|
| [US-052](../user-stories/US-052.md) | Ouverture publique de la démo | Must | 3 | 15 |
| [US-071](../user-stories/US-071.md) | Mise en page mobile sans débordement ni cible trop petite | Must | 3 | 14 |
| [US-072](../user-stories/US-072.md) | Structure sémantique du thème : repères, lien d'évitement et annonces | Must | 5 | 14 |
| [US-073](../user-stories/US-073.md) | Contrastes AAA et jetons de couleur uniques | Must | 3 | 14 |
| [US-074](../user-stories/US-074.md) | Pied de page dédié à la démo B2B | Must | 3 | 14 |
| [US-075](../user-stories/US-075.md) | Connexion : comptes de démo en premier, groupés par société | Should | 2 | 14 |
| [US-076](../user-stories/US-076.md) | Barre de démo sur une ligne, dans la langue du visiteur | Should | 2 | 14 |
| [US-077](../user-stories/US-077.md) | Libellés accessibles du cœur en français | Should | 2 | 14 |
| [US-078](../user-stories/US-078.md) | SEO de base et vitesse d'affichage mobile | Should | 5 | 15 |
| [US-079](../user-stories/US-079.md) | Sécurité avant ouverture : exposition, configuration versionnée, branche protégée | Must | 3 | 14 |
| [US-080](../user-stories/US-080.md) | Boîte de courriels de démo limitée aux mails des fixtures | Must | 2 | 14 |
| [US-081](../user-stories/US-081.md) | Chaîne d'approvisionnement : Dependabot, scan d'image, SBOM et empreintes | Should | 5 | 15 |
| [US-082](../user-stories/US-082.md) | Durcissement applicatif : en-têtes, fichiers servis, formulaire du simulateur, correctifs de dépendances | Should | 5 | 15 |
| [US-083](../user-stories/US-083.md) | Relire la licence du plugin MCP tiers | Should | 1 | 15 |
| [US-084](../user-stories/US-084.md) | Qualité : Rector en CI, couverture mesurée, méthodes longues, enregistrement de la liste société | Could | 5 | 22 |
| [US-085](../user-stories/US-085.md) | Revue de sécurité de réouverture et dossier de GO | Must | 2 | 15 |
| [US-139](../user-stories/US-139.md) | Plugins à jour dans la démo | Must | 2 | 15 |
| [US-140](../user-stories/US-140.md) | Hôtes de confiance indépendants du domaine public Railway | Must | 1 | 15 |
| [US-141](../user-stories/US-141.md) | Nettoyage des caches de test | Should | 1 | 15 |
| [US-145](../user-stories/US-145.md) | Images produit persistantes sur Railway | Must | 3 | 16 |
| [US-207](../user-stories/US-207.md) | Indexer les pages vitrine : sitemap, hreflang, métadonnées et données structurées | Should | 5 | à planifier |

**Notes** : Les stories de sécurité ne contiennent aucun détail exploitable (le dépôt est public) ; les actions de plateforme sont faites par Thibaut. La bascule publique reste US-052.
