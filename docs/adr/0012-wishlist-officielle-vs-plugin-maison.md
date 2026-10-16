# ADR-0012 — Wishlist officielle vs plugin maison pour les listes d'achat partagées

- **Statut** : Accepté (2026-10-16) — option (b), après POC GO (voir « Résultat du POC »)
- **Contexte projet** : sylius-b2b-demo, sprint 3 (US-023, spike) — prépare la décision de US-024 (« listes d'achat partagées par l'entreprise »)

## Contexte

La démo B2B doit offrir des **listes d'achat partagées au niveau de l'entreprise** (US-024) : plusieurs membres d'une même entreprise consultent et réutilisent des listes communes au réachat, avec des rôles. La question du spike : le plugin **wishlist officiel Sylius** convient-il sur **Sylius 2.3** avec la notion d'entreprise, ou faut-il un plugin maison ?

Constats de l'évaluation :
- **Compatibilité Sylius 2.3 : sous conditions.** `sylius/wishlist-plugin` v1.3.0 (2026-08-19, MIT) déclare `sylius/sylius ^2.0.7` et `php ^8.2` ; la page addons ne cite explicitement que Sylius 2.0 à 2.2. Aucun test d'installation réel sur 2.3 n'a été réalisé dans ce spike.
- **Adéquation entreprise : absente.** Le plugin gère des wishlists **par utilisateur**, sans notion de `Company` ni de rôles. Le partage au niveau entreprise (et sa visibilité par membre) n'existe pas.
- **Télémétrie imposée** (`sylius/telemetry`), à neutraliser pour une démo publique.
- Un plugin `tmonier/sylius-company-accounts-plugin` fournit déjà `Company` et les membres : toute solution de liste partagée doit s'y rattacher.

## Options considérées

### (a) Plugin officiel tel quel
- ✅ Rapide, maintenu, MIT.
- ❌ Pas de partage par entreprise (le besoin central de US-024) ; par utilisateur seulement.
- ❌ Compat 2.3 non prouvée ; télémétrie imposée.

### (b) Plugin officiel étendu (recommandé, sous réserve)
- ✅ Réutilise le CRUD wishlist, les vues et l'intégration réachat.
- ✅ Ajoute une couche « propriétaire = Company » + visibilité par membre/rôle, branchée sur company-accounts.
- ❌ Dépend d'un test d'installation réel sur Sylius 2.3 (bloquant à lever en tout début de US-024).
- ❌ Risque de devoir surcharger une large part du modèle si le couplage « owner = user » est profond.

### (c) Plugin maison dédié
- ✅ Modèle taillé pour l'entreprise dès le départ (Company, rôles, partage), sans télémétrie.
- ✅ Cohérent avec la mission OSS du projet (plugins maison).
- ❌ Coût de développement et de maintenance le plus élevé ; réinvente le CRUD et les vues.

## Décision

**Recommandation : option (b)** — étendre `sylius/wishlist-plugin` pour porter des listes partagées par `Company`, **sous réserve d'un test d'installation réel sur Sylius 2.3** à réaliser au tout début de US-024.

**Critère de bascule vers (c)** : si la couche entreprise impose de réécrire plus de la moitié du modèle/CRUD du plugin, ou si la dépendance `sylius/telemetry` ne peut pas être neutralisée proprement pour une démo publique, alors partir sur un plugin maison dédié.

## Résultat du POC (US-024, 2026-10-16) : GO

Détail : `project-management/sprints/sprint-5-listes-et-scenarios/poc-us-024-wishlist.md`.

| Critère de bascule | Résultat |
|---|---|
| Installation sur Sylius 2.3 | OK : `sylius/wishlist-plugin` v1.3.0 sur Sylius 2.3.1, PHP 8.5, 0 conflit, `composer audit` propre, migrations du plugin appliquées |
| Télémétrie neutralisable | OK : `sylius_core.telemetry.enabled: false` ne suffit pas (le pass `sylius/telemetry` des plugins recharge les services) ; `App\DependencyInjection\Compiler\RemoveTelemetryServicesPass`, enregistré par le Kernel, retire `sylius.telemetry.*` (et, hors dev/test, `sylius.listener.telemetry_index_schema`). Test : `TelemetryAbsentInProdTest` |
| Réécriture < 50 % | OK : ~10-15 % estimé ; en pratique **aucune ligne du plugin n'est modifiée ni surchargée** |

Mise en œuvre retenue : le modèle du plugin reste inchangé (propriétaire = ShopUser, listes personnelles intactes). Une entité de liaison `App\Entity\Wishlist\CompanyWishlist` (table `app_company_wishlist`, migration `Version20261016200000`, id UUID) rattache une `Wishlist` du plugin à une `Company` de company-accounts et à son créateur. Lecture : tout membre actif de l'entreprise ; édition : créateur ou administrateur de l'entreprise (`CompanyWishlistVoter`) ; autre entreprise, visiteur ou membre désactivé : refusé. Pages et ajout (tout ou sélection) au panier : contrôleur dédié sous `/account/company-wishlists`, car les actions du plugin vérifient `shopUser` (pas de partage possible) et ne contrôlent pas l'accès à une liste par identifiant : elles ne sont donc pas exposées pour les listes d'entreprise.

Réserve assumée : neutraliser la télémétrie contourne la clause « l'usage des plugins officiels vaut accord de télémétrie » du README de `sylius/telemetry` ; choix de la démo publique, qui couvre aussi refund et invoicing.

## Conséquences

- **US-024 commence par un POC d'installation** du plugin officiel sur Sylius 2.3 (go/no-go) avant tout développement.
- La couche « liste appartenant à une entreprise + visibilité par membre/rôle » se branche sur `tmonier/sylius-company-accounts-plugin`.
- La télémétrie (`sylius/telemetry`) devra être désactivée/neutralisée pour la démo publique.
- Décision acceptée après le POC 2.3 (GO).
- Le spike (US-023) n'avait ajouté ni dépendance ni code ; le POC et l'intégration sont faits en US-024.
