# Tâches : US-197 : Photos du catalogue : cadrage des cartes et variantes de couleur

## Informations US

- **Epic** : EPIC-12
- **Persona** : Camille (recruteuse IT) ; lead développeur d'une agence
- **Story Points** : 3 (Should, **sacrifice : n°1**)
- **Sprint** : sprint-29-liste-panier-et-photos
- **Worker proposé** : w197
- **Story** : [US-197](../../../backlog/user-stories/US-197.md)

## Résumé de la US

**En tant que** prospect ou évaluateur de la démo  
**Je veux** voir des photos bien cadrées et dont la couleur correspond au nom du produit  
**Afin que** le catalogue reste crédible jusque dans le détail des cartes

## Règles et contraintes (comme US-196)

- **Deux tranches** : (1) **point focal** (cadrage des cartes portrait et de la fiche 4:3), sans téléchargement, avancée pendant l'attente de la maquette ; (2) **variantes de couleur et visuels neutres** (veste de pluie, botte), qui passe par la porte de Thibaut.
- **Étapes obligatoires de la tranche 2** : recherche sur Pexels et Unsplash, **licence relue sur le site** pour chaque candidat ; **planche** présentée à Thibaut (T-197-05) ; **GO explicite avant tout téléchargement** ; puis WebP, manifeste, page Crédits. Avant le GO, **aucune image n'est enregistrée dans le dépôt**.
- **Aucune retouche** des photos (décision de Thibaut du 2026-10-25) : une photo mal cadrée est recadrée par le point focal à l'affichage, jamais modifiée ; une photo pixellisée ou avec marque est écartée.
- Familles dont le nom annonce une couleur : casques, gilets, bottes ; si aucune photo libre de la bonne couleur n'existe, la famille n'annonce plus de couleur ou garde la photo actuelle, raison consignée. Les visuels neutres d'EPI-CHAU-006, 010 et 017 sont conservés si aucune photo libre sans marque ne convient.
- WebP dans le budget de `ImageBudgetTest` ; aucun appel réseau au chargement des fixtures ; `bin/fetch-product-images` (sélection explicite, source vide refusée) réutilisé ; **aucune clé d'API ni fichier `.env*`**.
- **Ordre avec US-198** : T-197-03 (point focal) est mergée avant T-198-06 si possible ; sinon la carte d'US-198 garde le cadrage actuel.
- Commandes de copie, de déplacement ou de suppression à partir d'une variable ou d'un glob : règle 18 de la consigne (`set -u`, `[ -n "$D" ] && [ -d "$D" ]`), jamais `cp -R` sur une variable.
- Tests uniquement par `WORKER_ID=w197 TEST_DB_NAME=b2b_w197 TEST_CACHE_DIR=var/cache-w197 bin/run-tests` ; preuve : `bin/proof-rebase` ou `php bin/proof-refresh` puis `php bin/proof-check`.
- Règles du sprint et consigne de worker : voir [README.md](./README.md) et [../consigne-worker.md](../consigne-worker.md).

## Lieu de vérification

**Pages publiques en production** (lecture seule, sans connexion) **après la remise à zéro de 03:00 qui suit le déploiement** (la base survit aux déploiements, règle 16) : catalogue, une fiche par famille touchée, page Crédits ; pa11y, largeurs, LCP du catalogue. Mesures consignées le jour du contrôle.

## Fichiers principaux touchés

- `fixtures/images/*.webp`, `fixtures/images/manifest.csv` (et `selection.json` s'il porte le point focal), `bin/fetch-product-images`
- `src/Demo/DataFixtures/ProductImageAttacher.php`, `src/Demo/Images/ImageManifest.php`, `src/Demo/Images/ImageCredits.php`, gabarits d'image de la carte et de la fiche (`object-position`)
- `tests/Unit/Demo/Images/`, `tests/Functional/Demo/ImageCreditsPageTest.php`, `docs/images-produits.md`, `config/demo/proof.json` (rafraîchi)

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-197-01 | [DOC] | Cadrage : réserves de la PR #106 relues, `manifest.csv`, `selection.json`, `ProductImageAttacher`, `ImageCredits`, cadrage actuel des cartes portrait et de la fiche 4:3, familles à couleur annoncée (casques, gilets, bottes), EPI-CHAU-006, 010 et 017 ; captures « avant » locales ; choix du support du point focal (colonne du manifeste ou `selection.json`) | 0,5 h | - | Claude | ✅ |
| T-197-02 | [TEST] | Tests rouges, commités seuls et cités dans la PR : point focal lu et appliqué à la carte et à la fiche, défaut sans point focal inchangé, photo de la couleur annoncée par le nom (ou famille sans couleur annoncée), manifeste (licence libre sans attribution, échec nommant le SKU), visuel neutre conservé avec raison consignée, aucun appel réseau, budget WebP | 0,75 h | T-197-01 | Claude | ✅ |
| T-197-03 | [BE] | **Tranche 1** : point focal par photo (support de T-197-01) lu par `ImageManifest` et appliqué par un composant partagé (`object-position`) à la carte portrait et à la fiche 4:3 ; défaut inchangé sans point focal ; points focaux renseignés pour les photos coupées (casque, clés) | 1 h | T-197-02 | Claude | ✅ |
| T-197-04 | [DOC] | **Tranche 2** : recherche sur Pexels et Unsplash pour les couleurs annoncées et pour la veste de pluie et la botte, 2 à 3 candidats par cas, **licence relue sur le site pour chacun** (usage sans attribution obligatoire, pas de marque ni de logo lisible, pas d'article d'un autre type), noms d'US-195 ; **planche** (page locale de liens et d'aperçus, mini-planche puis pleine résolution) présentée à Thibaut, **aucune image enregistrée dans le dépôt** | 1,25 h | T-197-01 | Claude | ✅ |
| T-197-05 | [OPS] | 📅 Thibaut choisit les photos sur la planche (une photo avec marque ou pixellisée est écartée, aucune retouche) et donne le **GO explicite de téléchargement** ; GO noté au registre des décisions avant T-197-06 | 0,5 h | T-197-04 | Thibaut | ✅ |
| T-197-06 | [DOC] | 📅 **Après le GO (T-197-05) seulement** : téléchargement des seules photos choisies, optimisation WebP, `manifest.csv`, anciens fichiers orphelins retirés par `git rm`, `docs/images-produits.md` ; texte relu contre le manifeste | 0,75 h | T-197-05, T-197-02 | Claude | ✅ |
| T-197-07 | [BE] | 📅 Variantes de couleur : rattachement par couleur dans `ProductImageAttacher`, crédits (`ImageCredits`) avec auteur, source et licence ; visuels neutres de la veste de pluie et de la botte remplacés si une photo a été retenue | 0,75 h | T-197-06 | Claude | ✅ |
| T-197-08 | [TEST] | Suite ciblée par `bin/run-tests`, PHPStan max sur tout le dépôt, ECS, Rector, lint ; **captures avant/après** des cartes portrait et de la fiche pour les familles touchées (rendu du noyau en mode prod, un seul cache de rendu) ; commande exacte et dernière ligne citées | 0,5 h | T-197-03, T-197-07 | Claude | 🔄 |
| T-197-09 | [TEST] | Vérification en production après le déploiement **et la remise à zéro de 03:00** : pa11y et largeurs (catalogue, une fiche par famille touchée, page Crédits), LCP mobile du catalogue ; mesures consignées le jour du contrôle ; puis revue (licences relues contre le manifeste, ton neutre, aucune marque visible), table « scénario → test ou preuve » remplie, `bin/proof-rebase` ou `php bin/proof-refresh` puis `php bin/proof-check` | 0,75 h | merge de la PR | Claude | 📅 |

**Total estimé : 6,75 h** (9 tâches ; 6,25 h pour Claude, 0,5 h de Thibaut).

## Scénarios et preuves

| Scénario | Tâche de test | Test ou preuve |
|----------|---------------|----------------|
| Le sujet reste entier sur la carte et sur la fiche (nominal) | T-197-02 (rouge), T-197-08 (captures avant/après) | |
| La couleur annoncée par le nom correspond à la photo (alternatif) | T-197-02, T-197-08 | |
| Produit sans point focal (alternatif) | T-197-02 | |
| Photo avec marque ou licence non conforme (erreur) | T-197-02 (manifeste, échec nommant le SKU), T-197-04 (planche relue) | |
| Aucune photo libre trouvée pour la veste de pluie ou la botte (erreur) | T-197-02 (visuel neutre conservé), T-197-04 (raison consignée) | |

## Registre de la planche et du GO

Planche de recherche (T-197-04, 2026-10-25) : 8 cas (casques jaune, rouge, orange, bleu, vert ; gilet jaune ; botte verte ; veste de pluie jaune), 18 candidats Pexels et Unsplash, licences relues sur les pages des deux sites ; **aucun candidat pour le casque vert** ; aucune image téléchargée. Fichier local de l'orchestrateur, hors dépôt. GO de Thibaut : 2026-10-25 (registre, commit 1aec524) : 6 photos retenues, casque vert et veste de pluie inchangés.

Téléchargées le 2026-10-25 en pleine taille depuis les pages officielles (Pexels : fichier original, 6 000 px ; Unsplash : fichier original du CDN `images.unsplash.com`, le lien `/download` refusant les requêtes hors navigateur), dans un dossier hors dépôt (non versionné) : Richard Heinen (casque jaune), Ümit Yıldırım (casque rouge), Thirdman (casque orange), Carsten Ruthemann (casque bleu), Tanya Paquet (gilet jaune), Jonathan Cosens Photography (botte verte). Aucune retouche : découpe 4:3 par `PhotoCropper`, WebP par `ImageProcessor`. Relues à 800 px : aucune marque lisible (une pastille du casque de gauche de la photo de Tanya Paquet est illisible), aucune personne. Import partiel par `php bin/import-product-images --only=...`. Le visuel neutre `category-chaussures-securite.webp`, devenu orphelin, est retiré. Le GO de téléchargement est noté ici et dans [../registre-decisions.md](../registre-decisions.md) **avant** T-197-06.

## Mesures de production (jour du contrôle)

_Non consignées (sprint planifié le 2026-10-25)._
