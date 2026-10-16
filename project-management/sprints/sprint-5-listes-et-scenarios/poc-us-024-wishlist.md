# POC US-024 — sylius/wishlist-plugin sur Sylius 2.3 (T-024-01 à T-024-04)

Date : 2026-10-16 — Décision : **GO** (ADR-0012 option b : plugin officiel étendu).

## 1. Installation (T-024-01)

- `composer require sylius/wishlist-plugin` : **v1.3.0** installée (contrainte posée `^1.3`), sur `sylius/sylius` **v2.3.1**, PHP 8.5.11.
- Contraintes du plugin : `php ^8.2`, `sylius/sylius ^2.0.7`, `sylius/telemetry ^1.0`, `sylius/pdf-generation-bundle ^1.0`, `dompdf/dompdf ^2.0 || ^3.1`.
- Résolution : aucun conflit, un seul paquet ajouté au lock (`sylius/wishlist-plugin`) ; `sylius/telemetry` v1.0.1, pdf-generation et dompdf étaient déjà présents.
- `composer audit` : aucune advisory (2 paquets abandonnés préexistants : behat/transliterator, php-http/message-factory).
- Flex a appliqué la recette : bundle dans `config/bundles.php`, `config/packages/sylius_wishlist.yaml` (import `@SyliusWishlistPlugin/config/config.yaml`), `config/routes/sylius_wishlist.yaml`, imports JS `assets/admin|shop/entrypoint.js`. La recette a supprimé 2 commentaires de `bundles.php` (restaurés à la main).
- Piège : le script post-update `@auto-scripts` du require a planté en mémoire (128 Mo, CLI par défaut) ; sans conséquence, tout fonctionne avec `-d memory_limit=2G`.
- Migrations : **auto-préfixées** par le plugin (`PrependDoctrineMigrationsTrait`, 10 migrations `Sylius\WishlistPlugin\Migrations`), rien à générer ni à copier. Tables finales : `sylius_wishlist` et `sylius_wishlist_product` (les anciennes `bitbag_*` sont migrées/renommées). Non appliquées pendant le POC (pas de base). À appliquer en T-024-05.

Vérifications :
- `lint:container --env=test` : OK.
- `cache:warmup` APP_ENV=prod avec DATABASE_URL factice (étape du Dockerfile) : OK.
- `debug:router` : routes shop `/wishlist`, `/wishlists`, `/wishlists/{id}`, ajout/retrait, CSV, PDF, plus API `/api/v2/shop/wishlists…` et `/api/v2/admin/wishlists`.
- `phpstan` : 0 erreur ; `ecs check` : 0 erreur.
- Fumée navigateur admin/shop non faite (pas de base dans ce POC) : à faire en T-024-05.

## 2. Télémétrie (T-024-02)

- `sylius/telemetry` est un paquet minuscule (`TelemetryCompilerPass`). Le plugin l'enregistre dans `SyliusWishlistPlugin::build()`, comme **refund-plugin et invoicing-plugin déjà installés** : la démo envoie donc déjà de la télémétrie en prod, le wishlist n'ajoute aucun risque nouveau.
- Le pass charge les services de télémétrie du core (`services/telemetry/telemetry.php`) en env non dev/test si `sylius.telemetry.send_manager` est absent. L'envoi est déclenché par les listeners `sylius.telemetry.listener` (kernel.terminate sur les pages admin) et `sylius.telemetry.listener.notice`.
- Constat : `sylius_core: telemetry: { enabled: false }` ne suffit PAS. Le paramètre est posé mais le pass du plugin recharge quand même les services (vérifié : `sylius.telemetry.send_manager` présent avec `enabled: false`). Les variables `SYLIUS_TELEMETRY_ENABLED|BUSINESS|TECHNICAL|PLUGINS` agissent seulement sur le chargement du core, donc même limite.
- Neutralisation propre validée : un compiler pass dans `App\Kernel` (`implements CompilerPassInterface`) qui fait `removeDefinition()` de `sylius.telemetry.listener` et `sylius.telemetry.listener.notice`. Testé en prod : le conteneur compile, plus aucun listener de télémétrie sur `kernel.terminate`, donc aucun appel sortant (aucun déclencheur). Code testé puis retiré du POC (`src/Kernel.php` inchangé) : à intégrer en T-024-05, avec un test de conteneur qui vérifie l'absence des listeners. Cela couvre aussi refund et invoicing.
- Réserve : cela contourne la clause « l'usage des plugins officiels vaut accord de télémétrie » du README `sylius/telemetry` ; assumer ce choix (démo publique) et le documenter dans l'ADR.

## 3. Modèle et couplage owner = user (T-024-03)

- `Wishlist` (mapped-superclass, table `sylius_wishlist`) : `id`, `name`, `token`, `channel`, `shopUser` (ShopUserInterface, nullable), `wishlistProducts`, timestamps. Ressource Sylius remplaçable via `sylius_wishlist_plugin.resources.wishlist.classes.model` (extension standard par sous-classe + mapping Doctrine).
- Propriétaire = **ShopUser** (pas Customer). Les wishlists anonymes sont rattachées par cookie (token) puis à l'utilisateur à la connexion (`LoggedUserWishlistSubscriber`, `AddWishlistToUserHandler`).
- Voter `WishlistVoter` (UPDATE/DELETE) : autorisé si anonyme sans owner, ou `wishlist.shopUser === user`. 87 lignes.
- Taille : ~8 350 lignes PHP au total (hors migrations). Les parties couplées au user : voter, `ShopUserWishlistResolver`, `WishlistsResolver`, `TokenUserResolver`, `WishlistContext`, `WishlistFactory`, 3 CommandHandlers, `LoggedUserWishlistSubscriber`, repository (`findAllByShopUser`). Soit environ 10 à 15 fichiers.
- Estimation de réécriture pour « owner = Company » : **environ 10 à 15 % du plugin**, très loin du seuil de 50 % de l'ADR. Le CRUD, les vues, l'export CSV/PDF, l'ajout au panier et l'API restent réutilisés tels quels. Le point d'appui : une sous-classe de `Wishlist` ajoutant `company` (ManyToOne vers `Company` de company-accounts), laissant `shopUser` intact pour les listes personnelles.

## 4. Décision (T-024-04)

| Critère ADR-0012 | Résultat |
|---|---|
| Installation propre sur Sylius 2.3 | OK (v1.3.0 / sylius 2.3.1, 0 conflit, audit propre, lint/warmup/phpstan/ecs verts) |
| Télémétrie neutralisable | OK via compiler pass Kernel (la config `enabled: false` seule est insuffisante) |
| Extension entreprise sans réécrire > 50 % | OK (~10–15 %) |

**GO.** L'ADR-0012 peut passer de Proposé à Accepté (option b), à confirmer par l'utilisateur.

## 5. Esquisse de l'extension entreprise (T-024-06/07)

1. Entité `App\Entity\Wishlist\CompanyWishlist extends Sylius\WishlistPlugin\Entity\Wishlist` : champs `company` (ManyToOne `Company`, nullable, index) et `createdBy` (ShopUser). Mapping XML/attributs Doctrine, migration ajoutant `company_id`/`created_by_id` à `sylius_wishlist`. Déclarée comme classe modèle de la ressource `wishlist` (config `sylius_wishlist_plugin.resources`). Listes personnelles : `company` null, comportement inchangé.
2. Voter `CompanyWishlistVoter` (VIEW/UPDATE/DELETE) décorant ou complétant `WishlistVoter` : lecture pour tout membre de la `Company` (via company-accounts), édition pour le créateur ou rôle admin entreprise, refus pour autre entreprise et anonyme.
3. Resolver de listes visibles (décorateur de `WishlistsResolver`) : listes personnelles + listes de l'entreprise du user ; action de création « liste d'entreprise » ; cookie/token anonyme inchangé.
4. Écrans : réutiliser les templates/hooks Twig du plugin (`config/twig_hooks`), ajouter l'onglet « Listes de l'entreprise » ; ajout partiel/total au panier via les actions existantes (`add_selected_products`).
5. Fixtures : liste « Atelier » d'Acme. Tests : liste partagée, ajout partiel, rôles, autre entreprise, anonyme.
6. Télémétrie : intégrer le compiler pass Kernel et un test de conteneur dans T-024-05.

## 6. Fichiers modifiés / ajoutés (non commités)

Modifiés :
- `composer.json` (+ `"sylius/wishlist-plugin": "^1.3"`)
- `composer.lock`
- `symfony.lock`
- `config/bundles.php` (+ `Sylius\WishlistPlugin\SyliusWishlistPlugin`)
- `assets/admin/entrypoint.js` (import du plugin, recette Flex)
- `assets/shop/entrypoint.js` (import du plugin, recette Flex)

Ajoutés :
- `config/packages/sylius_wishlist.yaml`
- `config/routes/sylius_wishlist.yaml`
- `project-management/sprints/sprint-5-listes-et-scenarios/poc-us-024-wishlist.md` (ce rapport)

Non modifiés : `src/Kernel.php` (essai de neutralisation retiré), `migrations/`.
