# Tâches : US-062 : Mineurs du script d'images

## Informations US

- **Epic** : EPIC-11
- **Persona** : Thibaut (mainteneur)
- **Story Points** : 1 (Could, **sacrifice : n°1**, proposition)
- **Sprint** : sprint-32-preuve-et-architecture (reportée du sprint 22, sacrifiée alors par l'ordre du plan)
- **Worker proposé** : w062
- **Story** : [US-062](../../../backlog/user-stories/US-062.md)

## Résumé de la US

**En tant que** Thibaut  
**Je veux** corriger les mineurs restants du script d'images  
**Afin de** fiabiliser le téléchargement et la conservation des images du catalogue

## Règles et contraintes

- **Sans maquette ni porte de Thibaut** : story d'outillage, démarre dès le planning.
- **Aucun appel réseau, aucun rendu** : les tests utilisent des réponses simulées (`ProductImageFetcherTest` existant comme modèle) ; `bin/fetch-product-images` n'est pas lancé (disque et réseau).
- **Détail à confirmer au cadrage** : la fiche, écrite au sprint 22, liste quatre mineurs de la revue du sprint 10 (limite de pixels et `Content-Length`, code 429, image principale conservée si le manifeste change, corps en `chunked`). T-062-01 relit `src/Demo/Images/Fetch/ProductImageFetcher.php`, `ImageManifest` et le script `bin/fetch-product-images` pour confirmer ce qui reste à faire et corrige la fiche si un mineur est déjà traité.
- Les tests existants du fetcher restent verts et ne sont pas modifiés.
- Fichiers communs (règle 21) : `src/Demo/Images/Fetch/`, `bin/fetch-product-images`, `docs/images-produits.md` (si le comportement documenté change), `tests/Unit/Demo/Images/` : US-062 seule.
- Aucun nom de marque réelle, aucune clé ni URL privée dans les tests ; aucun détail de sécurité dans le dépôt public.
- Tests uniquement par `WORKER_ID=w062 TEST_DB_NAME=b2b_w062 TEST_CACHE_DIR=var/cache-w062 bin/run-tests`, dans l'arbre partagé (règle 22) ; preuve : `bin/proof-rebase` si un fichier de tests est ajouté.

## Lieu de vérification

**Local** : tests unitaires ; aucune vérification de production (outillage de fixtures).

## Fichiers principaux touchés

- `src/Demo/Images/Fetch/ProductImageFetcher.php` (et ses classes voisines si le cadrage l'exige), `bin/fetch-product-images`
- `tests/Unit/Demo/Images/` (nouveaux fichiers ou cas ; `ProductImageFetcherTest` existant non modifié)
- `docs/images-produits.md` (si besoin), `config/demo/proof.json` (par `bin/proof-rebase`)

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-062-01 | [DOC] | Cadrage : fetcher, manifeste et script relus ; chacun des quatre mineurs confirmé ou écarté contre le code ; fiche corrigée si besoin ; plan des tests | 0,25 h | - | Claude | ✅ |
| T-062-02 | [TEST] | Tests rouges, commités seuls et cités dans la PR : image conforme (enregistrée comme avant), manifeste modifié (image principale conservée), 429 (attente puis nouvel essai, bornés), image hors limites (pixels ou `Content-Length` excessif refusée et consignée), `chunked` sans longueur (taille lue plafonnée) | 0,5 h | T-062-01 | Claude | ✅ |
| T-062-03 | [BE] | Correctifs du fetcher : limite de pixels et contrôle du `Content-Length`, gestion du 429 avec attente bornée, image principale conservée si le manifeste change, lecture plafonnée du corps `chunked` ; comportement nominal inchangé | 0,75 h | T-062-02 | Claude | ✅ |
| T-062-04 | [TEST] | Suite ciblée par `bin/run-tests` (`tests/Unit/Demo/Images/`), PHPStan max sur tout le dépôt, ECS, Rector ; commande exacte et dernière ligne citées ; `bin/proof-rebase` si un fichier de tests est ajouté | 0,25 h | T-062-03 | Claude | 🔄 reste : suite complète, bin/proof-rebase |
| T-062-05 | [REV] | Revue (aucun appel réseau dans les tests, comportement nominal inchangé, documentation à jour) et table « scénario → test ou preuve » remplie | 0,25 h | T-062-04 | Claude | 📅 disque |

**Total estimé : 2 h** (5 tâches, toutes pour Claude).

## Table « scénario → test ou preuve »

| Scénario | Tâche | Test ou preuve |
|----------|-------|----------------|
| Image conforme (nominal) | T-062-02, T-062-04 | `ProductImageFetcherTest` existant non modifié et vert ; cas « conforme » ajouté (à nommer) |
| Manifeste modifié (alternatif) | T-062-02, T-062-03 | test « l'image principale est conservée » (à nommer) |
| Réponse 429 (alternatif) | T-062-02, T-062-03 | test avec réponse simulée 429 puis 200, attente bornée (à nommer) |
| Image hors limites (erreur) | T-062-02, T-062-03 | tests « pixels » et « `Content-Length` excessif » : refus et consignation (à nommer) |
| Corps en `chunked` sans longueur (erreur) | T-062-02, T-062-03 | test avec flux simulé plus gros que le plafond : taille lue plafonnée (à nommer) |
