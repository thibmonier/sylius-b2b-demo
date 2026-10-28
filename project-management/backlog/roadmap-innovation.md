# Feuille de route innovation : copilote, prédictif, thème et passeport produit (sprints 34 à 41)

> Proposition du Product Owner du 2026-10-28, sur décisions de Thibaut du même jour (séquencement validé « ok », puis ajusté par « DPP maintenant »). Backlog : EPIC-16 à EPIC-19 plus 2 stories transverses, soit 28 stories et 117 points (US-210 à US-237). Aucune section de sprint n'est créée dans `.bmad/sprint-status.yaml` : les stories sont dans la section `backlog`. Le sprint 33 en cours (US-207, US-209) n'est pas modifié.

## Vision

Prolonger la preuve de la démo par quatre capacités qu'un intégrateur ou un décideur peut essayer : un assistant d'achat qui prépare sans décider, des alertes fondées sur des règles lisibles, un thème complet installable et une base de passeport produit. Chaque capacité est un plugin ou un paquet open source, publié, testé et relié à la démo.

## Positionnement

**Un écosystème B2B complet et maintenu sur Sylius 2, en open source.** Ton neutre : aucune comparaison avec une offre éditeur, aucune communication sur les canaux Sylius. Avant toute promotion d'un plugin, son spike d'entrée vérifie l'absence de recoupement avec Sylius Plus dans la documentation publique ; en cas de recoupement, le dépôt reste public mais n'est pas promu (politique du 2026-10-14). Communication uniquement par le profil LinkedIn de Thibaut, qui publie lui-même.

## Epics

| Epic | Plugin ou paquet | Stories | Points | Décision structurante |
|---|---|---|---|---|
| [EPIC-16](epics/EPIC-16.md) Copilote d'achat B2B | `sylius-b2b-sales-copilot-plugin` | 7 | 34 | L'IA prépare, l'acheteur valide ; démo en mode rejoué (aucun appel LLM payant) |
| [EPIC-17](epics/EPIC-17.md) Commerce prédictif | `sylius-b2b-predictive-analytics-plugin` | 4 | 18 | Règles statistiques explicables, pas de ML opaque |
| [EPIC-18](epics/EPIC-18.md) Thème Industrie & Négoce | `sylius-industry-trade-theme` | 8 | 34 | Thème complet installable, mode sombre AAA, saisie rapide |
| [EPIC-19](epics/EPIC-19.md) Passeport numérique produit | `sylius-digital-product-passport-plugin` | 7 | 23 | Maintenant ; aucune promesse de conformité (actes délégués de l'ESPR non fixés) |
| Transverse (EPIC-13 et EPIC-12) | contenus et parcours | 2 | 8 | [US-236](user-stories/US-236.md) posts et GIF par plugin ; [US-237](user-stories/US-237.md) parcours guidé copilote |

Chaque nouveau plugin applique la même Definition of Done : dépôt durci comme [US-209](user-stories/US-209.md), CI Sylius 2.2/2.3, README en français (lien démo, profil de l'auteur), intégration dans la démo, ajout aux pages « Plugins » et « Preuve technique », Packagist soumis par Thibaut, aucun tag sans son GO.

## Séquencement (vélocité de référence : 13 points par sprint)

Après le sprint 33 en cours (US-207, US-209).

| Sprint | Objectif | Stories | Points |
|---|---|---|---|
| 34 | Spikes copilote, DPP et packaging du thème ; EPIC-18 tranche 1 (paquet, jetons, composants) | US-210, US-221, US-222, US-229 | 14 |
| 35 | EPIC-17 MVP : réassort prédictif et alerte | US-217, US-218, US-219 | 13 |
| 36 | EPIC-19 MVP : modèle, page publique, QR, pièces compatibles | US-230, US-231, US-232, US-233 | 14 |
| 37 | EPIC-16 MVP, première moitié : widget, adaptateur rejoué, réassort | US-211, US-212 | 13 |
| 38 | EPIC-16 MVP, seconde moitié : ajout par référence, garde-fous, mode rejoué | US-213, US-214, US-215 | 13 |
| 39 | EPIC-17 attrition ; EPIC-18 tranche 2 (mode sombre AAA, saisie rapide) | US-220, US-226, US-227 | 13 |
| 40 | EPIC-18 pages (home, liste, fiche, panier, compte) et test de dérive (proposé, au-delà du séquencement validé) | US-223, US-224, US-225 | 13 |
| 41 | Démo sur le thème publié, intégration du DPP, contenus transverses (proposé) | US-228, US-235, US-236, US-237 | 16 |

Hors sprint : US-216 (5 pts), US-234 (3 pts).

**Écarts à la vélocité à arbitrer par Thibaut** : le sprint 34 monte à 14 points (trois spikes de 3 points plus la tranche 1 du thème), les sprints 36 et 39 sont à leur limite, le sprint 41 monte à 16 points. Les sprints 40 et 41 prolongent le séquencement validé (S34 à S39) parce que le thème complet (pages, test de dérive, démo sur le paquet publié) ne tient pas dans les sprints 34 et 39 à cette vélocité.

## Risques

| Risque | Effet | Mitigation |
|---|---|---|
| Coût et abus du copilote | Facture d'API, détournement | Démo en mode rejoué sans clé ; liste blanche d'outils, limites de débit et journal d'audit (US-214) ; aucun détail dans le dépôt public |
| Crédibilité des données prédictives | Alertes erronées, perte de confiance | Règles explicables, seuil d'historique minimal, mention « indicatif », fixtures vraisemblables (US-219) |
| Réglementation DPP mouvante | Promesse devenue fausse | Spike US-229, formulation prudente, test de contenu bannissant « conforme » et « certifié » (US-235), modèle versionné |
| Maintenance des surcharges du thème | Dérive à chaque mise à jour de Sylius | Test de dérive des gabarits (US-225), CI planifiée sur Sylius 2.2/2.3, empreintes revues |
| Recoupement avec Sylius Plus | Contradiction avec la politique de communication | Vérification dans chaque spike ; pas de promotion en cas de recoupement ; ton neutre |
| Disque de la machine | Tests et rendus locaux impossibles (4,7 Go libres relevés le 2026-10-28) | Seuils de la consigne des workers (5 Go avant rendu, 6 Go avant suite complète), nettoyage des worktrees terminés, CI plutôt que local |
| Surcharge de capacité | Sprints au-dessus de 13 points | Arbitrage de Thibaut ; Could non planifiées (US-216, US-234) |
