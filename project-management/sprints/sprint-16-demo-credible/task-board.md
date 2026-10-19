# Task Board : Sprint 016 (Démo crédible)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Calendaire | ⚠️ Sacrifiable

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Assigné |
|----|-----|------|-------|------------|---------|
| T-145-01 | US-145 | [DOC] | Spike : chemin exact des médias (images Sylius, cache LiipImagine), droits `www-data`, d… | 1h | |
| T-145-02 | US-145 | [TEST] | Tests rouges : `railway.ts` déclare un volume sur le dossier des médias (sans identifian… | 1h | |
| T-145-03 | US-145 | [BE] | Déclarer le volume dans `.railway/railway.ts` (chemin de montage = dossier des médias) | 1h | |
| T-145-04 | US-145 | [BE] | Premier démarrage : droits `www-data` et dossiers rétablis à l'entrée du conteneur, `/he… | 1,5h | |
| T-145-05 | US-145 | [BE] | Purge des images orphelines après le reset (`--dry-run`), cache LiipImagine purgé comme… | 1h | |
| T-145-06 | US-145 | [DOC] | `docs/deploiement-railway.md` (volume, premier démarrage, purge, procédure de redéploiem… | 0,5h | |
| T-145-07 | US-145 | [REV] | Revue (relecture « aucun détail exploitable », rendu réel d'une fiche produit après reset) | 0,5h | |
| T-142-01 | US-142 | [DOC] | Inventaire des emplacements (barre de démo, accueil, fiche, Plugins, À propos, MCP, simu… | 1h | |
| T-142-02 | US-142 | [TEST] | Tests rouges : validation des URL (https, sans identifiants), masquage si vide ou invali… | 2h | |
| T-142-03 | US-142 | [BE] | Paramètre `DEMO_BOOKING_URL`, extension de `SiteLinks`, fonction Twig et construction de… | 1,5h | |
| T-142-04 | US-142 | [FE-WEB] | Composant partagé (bandeau, carte, lien), libellés FR et EN, cibles de 44 px, AAA | 1,5h | |
| T-142-05 | US-142 | [FE-WEB] | Intégration aux emplacements (barre de démo, accueil, fiche produit, Plugins, À propos,… | 2h | |
| T-142-06 | US-142 | [TEST] | Rendu réel FR et EN avec valeurs factices et vides : desktop, 375 et 320 px (`bin/measur… | 1h | |
| T-142-07 | US-142 | [DOC] | ⚠️ `docs/theme-b2b.md`, variables dans `docs/deploiement-railway.md`, US-115 marquée absorbée | 0,5h | |
| T-142-08 | US-142 | [REV] | Revue (URL absentes = composant masqué, aucune URL en dur) | 0,5h | |
| T-143-01 | US-143 | [DOC] | Volume d'événements autorisé par le plan gratuit, format de l'identifiant, périmètre et… | 0,5h | |
| T-143-02 | US-143 | [TEST] | Tests rouges : configuration vide ou invalide désactive la mesure, script en `defer`, CS… | 1h | |
| T-143-03 | US-143 | [BE] | Paramètres, validation (https, UUID), extension de la CSP et variable Twig | 1,5h | |
| T-143-04 | US-143 | [FE-WEB] | Script dans le gabarit de la boutique, `data-umami-event` sur CTA, connexion, devis, Pun… | 1,5h | |
| T-143-05 | US-143 | [TEST] | Rendu réel : script présent sur une page mesurée, absent de l'admin, aucun cookie, conso… | 1h | |
| T-143-06 | US-143 | [DOC] | ⚠️ `docs/deploiement-railway.md` (variables), `docs/durcissement-demo.md` (CSP), décision d… | 0,5h | |
| T-143-07 | US-143 | [REV] | Revue (aucune donnée personnelle dans les événements) | 0,5h | |
| T-092-01 | US-092 | [DOC] | Relever les images incohérentes du manifeste (`ImageManifest`), lister les produits à co… | 1h | |
| T-092-02 | US-092 | [TEST] | Tests rouges : chaque image validée ou remplacée, licence non libre refusée, visuel de r… | 1,5h | |
| T-092-03 | US-092 | [BE] | Contrôle de licence et statut « validé » dans le manifeste, commande de contrôle | 1,5h | |
| T-092-04 | US-092 | [BE] | ⚠️ Visuels neutres de catégorie (repli) | 1h | |
| T-092-05 | US-092 | [BE] | Remplacer les photos incohérentes (CC0, cache hors dépôt), manifeste à jour | 2,5h | |
| T-092-06 | US-092 | [FE-WEB] | Vérifier que la page Crédits d'US-061 couvre chaque nouvelle image, FR et EN | 0,5h | |
| T-092-07 | US-092 | [TEST] | Rendu réel FR et EN sur un échantillon de 20 produits (captures pour Thibaut), fiche et… | 1h | |
| T-092-08 | US-092 | [DOC] | ⚠️ `docs/images-produits.md` (manifeste, repli, crédits) | 0,5h | |
| T-092-09 | US-092 | [REV] | Revue (licences, aucune image jugée incohérente ne subsiste) | 0,5h | |
| T-096-01 | US-096 | [DOC] | État des fixtures (panier, commandes, approbations), dates, durée du reset actuelle (`do… | 0,5h | |
| T-096-02 | US-096 | [TEST] | Tests rouges : panier de Claire (liste Atelier), 6 à 8 commandes sur 6 mois, approbation… | 2h | |
| T-096-03 | US-096 | [BE] | Fixture du panier de départ de Claire | 1,5h | |
| T-096-04 | US-096 | [BE] | Fixtures de l'historique : 6 à 8 commandes réalistes, dates relatives au reset | 2h | |
| T-096-05 | US-096 | [BE] | Fixture de la commande en attente d'approbation chez Paul (validable) | 1,5h | |
| T-096-06 | US-096 | [TEST] | Mesure du reset (`bin/profile-reset`) < 10 minutes ; fumée : aucun de ces écrans vide | 1h | |
| T-096-07 | US-096 | [TEST] | Rendu réel FR et EN : panier de Claire, commandes, approbations de Paul | 0,5h | |
| T-096-08 | US-096 | [DOC] | ⚠️ `docs/remise-a-zero-demo.md` (fixtures et mesure) | 0,5h | |
| T-096-09 | US-096 | [REV] | Revue (dates relatives, aucun secret) | 0,5h | |
| T-097-01 | US-097 | [DOC] | État des devis, du crédit, des brouillons, des suggestions IA et du journal d'audit ; év… | 1h | |
| T-097-02 | US-097 | [TEST] | Tests rouges : devis en négociation (numéro de l'année courante), pièces ouvertes dont u… | 1,5h | |
| T-097-03 | US-097 | [BE] | Fixture du devis en négociation avec historique | 1,5h | |
| T-097-04 | US-097 | [BE] | Fixtures du crédit : pièces ouvertes dont une échue, dépassement de plafond, jauge | 1,5h | |
| T-097-05 | US-097 | [BE] | Brouillons, commandes en préparation et suggestions IA pré-générées | 1h | |
| T-097-06 | US-097 | [BE] | ⚠️ Quelques événements métier au journal d'audit après le reset (commande, approbation, dev… | 1h | |
| T-097-07 | US-097 | [TEST] | Contrôle de données, fumée « écran resté vide » nommant l'écran, reset < 10 minutes (`bi… | 1h | |
| T-097-08 | US-097 | [TEST] | Rendu réel FR et EN : Mes devis, encours, suggestions IA, journal d'audit | 0,5h | |
| T-097-09 | US-097 | [DOC] | ⚠️ `docs/remise-a-zero-demo.md` (fixtures, journal d'audit) | 0,5h | |
| T-097-10 | US-097 | [REV] | Revue (numérotation, mention « démonstration », aucun détail exploitable) | 0,5h | |
| T-061-01 | US-061 | [DOC] | ⚠️ Relever source et licence de chaque image du manifeste, écarts consignés dans la story | 0,5h | |
| T-061-02 | US-061 | [TEST] | ⚠️ Tests rouges : chaque image du manifeste figure sur la page Crédits (FR et EN), lien dep… | 0,5h | |
| T-061-03 | US-061 | [FE-WEB] | ⚠️ Page Crédits générée depuis le manifeste et lien dans le pied de page B2B, FR et EN | 0,5h | |
| T-061-04 | US-061 | [REV] | ⚠️ Revue (rendu réel FR et EN, pa11y) | 0,5h | |

## 📅 Calendaire (GO ou action de Thibaut, hors chemin critique)

| ID | US | Tâche | Estimation | Dépend de |
|----|-----|-------|------------|-----------|
| T-145-08 | US-145 | [OPS] Thibaut : `railway config plan` puis `apply`, lancer le reset, redéployer deux fois sans reset et vérifier les images d'une fiche (FR et EN) | 0,5h | T-145-07 |
| T-142-09 | US-142 | [OPS] Thibaut : fournir l'URL Calendly et celle du formulaire de contact, les poser dans Railway | 0,5h | T-142-08 |
| T-143-08 | US-143 | [OPS] Thibaut : fournir l'identifiant du site Umami de thibaut-monier.fr et l'URL du script, les poser dans Railway | 0,5h | T-143-07 |
| T-092-10 | US-092 | [OPS] Thibaut : relire l'échantillon de 20 produits | 0,5h | T-092-09 |

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
- **Tâches** : 58 total | 0 terminées (0 %)
- **Heures** : 57 estimées (dont 2 calendaires) | 0 h consommées | 57 restantes (ordre de sacrifice dans [tasks/README.md](./tasks/README.md))
