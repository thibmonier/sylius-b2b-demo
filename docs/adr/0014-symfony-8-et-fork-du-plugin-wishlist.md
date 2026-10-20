# ADR-0014 — Symfony 8.1 et fork temporaire du plugin wishlist

- **Statut** : Accepté (2026-10-19), fork abandonné le 2026-10-20 (voir la mise à jour en fin de document)
- **Contexte projet** : sylius-b2b-demo, hors sprint (montée de version demandée après l'ouverture publique)

## Contexte

La démo tournait en Symfony 7.4. Ce n'était pas un choix : le squelette Sylius-Standard repris au sprint 1 livre `extra.symfony.require: "7.4.*"`, ce qui bride tous les paquets `symfony/*` via Flex. S'y ajoutaient `symfony/process` figé à `7.4.*` et `config.platform.php` à `8.4.0`, alors que Symfony 8.1 exige PHP 8.4.1 ou plus.

Une démo sert de vitrine : elle doit tourner sur les versions les plus récentes proposées. Sylius 2.3.1, les plugins officiels utilisés et les plugins `tmonier/*` acceptent tous Symfony 8. Symfony 8.0, déjà en fin de vie, est écarté : Composer refuse ses versions à cause d'avis de sécurité non corrigés. La cible est la **8.1**.

Un seul paquet bloque : `sylius/wishlist-plugin` v1.3.0, la dernière version publiée.
- Il charge ses 78 services en XML, format supprimé en Symfony 8 (`XmlFileLoader` n'existe plus).
- Son code utilise encore `Request::get()` et l'ancienne signature de `Voter::voteOnAttribute()`, tous deux retirés en Symfony 8.

Chez Sylius, la PR [#91](https://github.com/Sylius/WishlistPlugin/pull/91) (conversion XML → PHP) est ouverte mais en conflit. La PR [#99](https://github.com/Sylius/WishlistPlugin/pull/99) (« Sylius 2.3 symfony 8 ») a été fermée sans fusion le 2026-10-19. Aucune version compatible n'est annoncée.

## Décision

1. La démo passe en **Symfony 8.1** :
   - `extra.symfony.require: "8.1.*"` ;
   - contraintes `symfony/*` en `^8.1` ;
   - `config.platform.php` à `8.4.15`.
2. Le plugin wishlist vient d'un **fork** : [thibmonier/WishlistPlugin](https://github.com/thibmonier/WishlistPlugin), branche `symfony-8`.
   - Le fork part du tag v1.3.0.
   - Il reprend la conversion de la PR #91, adaptée à v1.3.0, et les correctifs de code de la PR #99. Les auteurs d'origine sont crédités dans les commits.
   - Le dépôt VCS est restreint à ce seul paquet (`only`). La contrainte `dev-symfony-8 as 1.3.0` garde la compatibilité avec les paquets qui exigent `sylius/wishlist-plugin ^1.3`.
3. La conversion a été vérifiée mécaniquement. Les deux configurations (XML v1.3.0 et PHP du fork) ont été chargées avec le DI de Symfony 7.4, puis les 81 définitions et alias comparés (classe, arguments, appels, tags, parent, décoration, visibilité) : aucune différence. Un contrôle négatif sur l'ancienne base XML de la PR #91 détecte bien l'écart attendu.

## Conditions de sortie

Revenir au paquet officiel (supprimer l'entrée `repositories` et remettre `^1.3`, ou la version qui conviendra) dès que Sylius publie une version de `sylius/wishlist-plugin` compatible Symfony 8. À vérifier à chaque revue de dépendances.

## Mise à jour du 2026-10-20 : fork abandonné

Sylius a fusionné le 2026-10-19 « Update to Sylius 2.3 » ([#109](https://github.com/Sylius/WishlistPlugin/pull/109)) dans la branche `1.4` du dépôt officiel. Elle reprend la conversion des services en PHP et les correctifs Symfony 8 du fork (voter, `Request::get()`), avec quelques nettoyages : deux définitions de service en double retirées, `$request->request->all('…')`.

La démo dépend désormais de **`sylius/wishlist-plugin: 1.4.x-dev`**, depuis Packagist et sans dépôt VCS. Le lock épingle le commit `bff330c`. Ont été vérifiés : la compilation du conteneur en `dev` et en `prod`, PHPStan et la suite PHPUnit.

Nouvelle condition de sortie : passer à `^1.4` dès que la version 1.4.0 est publiée. On quitte ainsi une branche de développement pour une version stable. D'ici là, un `composer update` peut faire avancer le commit : relire le diff de `sylius/wishlist-plugin` à chaque mise à jour.

Le fork `thibmonier/WishlistPlugin` ne sert plus à la démo. Sa branche `symfony-8` peut être supprimée dès la publication de la 1.4.0.

## Conséquences

- ✅ La démo tourne sur la dernière version mineure de Symfony.
- ✅ Les limiteurs de débit de la démo s'injectent par `RateLimiterFactoryInterface`, seul type autowirable en Symfony 8.
- ✅ Symfony 8 ignore toute surcharge de méthode vers `GET` (`_method=GET` sur un `POST`) : la requête reste un `POST`, que le routeur refuse en 405 avant la garde d'écriture de l'admin. Le test de contournement vérifie désormais ce 405 et l'absence d'écriture.
- ❌ Un fork à suivre : les correctifs de sécurité du plugin officiel ne remontent pas tout seuls. La branche `symfony-8` doit être rebasée sur toute nouvelle version 1.x.
- ❌ `sylius/fixtures-bundle` v1.10.1 déclare encore ses commandes sans `#[AsCommand]`. En Symfony 8, la console les instancie au démarrage, ce qui fait échouer `cache:warmup` dans l'environnement `test` (le warmer des métadonnées Doctrine). Sans effet sur les tests (`bin/prepare-test-db` vide le cache avec `--no-warmup`) ni sur l'image Docker (warmup `prod` vérifié).
