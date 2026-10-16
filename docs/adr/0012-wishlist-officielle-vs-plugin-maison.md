# ADR-0012 — Wishlist officielle vs plugin maison pour les listes d'achat partagées

- **Statut** : Proposé (2026-10-16)
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

## Conséquences

- **US-024 commence par un POC d'installation** du plugin officiel sur Sylius 2.3 (go/no-go) avant tout développement.
- La couche « liste appartenant à une entreprise + visibilité par membre/rôle » se branche sur `tmonier/sylius-company-accounts-plugin`.
- La télémétrie (`sylius/telemetry`) devra être désactivée/neutralisée pour la démo publique.
- Décision à confirmer (statut Proposé → Accepté) à l'ouverture de US-024, après le POC 2.3.
- Aucune dépendance ni code ajoutés par ce spike : seule cette ADR est produite.
