# Spike US-286 — Santé de la plateforme : ce que Symfony, Sylius et l'écosystème fournissent déjà

> Date : 2026-11-22 · Story : [US-286](../backlog/user-stories/US-286.md) · Epic : [EPIC-20](../backlog/epics/EPIC-20.md) · Sprint : [56](../sprints/sprint-56-sante-plateforme/sprint-goal.md)
> Toutes les sources web ont été consultées le **2026-11-22**. Les lectures de code portent sur la démo installée (Sylius **v2.3.3**, Symfony 8.1, `doctrine/orm` 3.6.9, `doctrine/migrations` 3.9.8, `symfony/ux-live-component` v3.7.2), en lecture seule.
> Toute affirmation qu'aucune source officielle ne confirme est marquée **« non confirmée »** et exclue de tout texte public.

## Conclusion

**On écrit le contrat ; on enveloppe les sources de données.**

- **On écrit** `HealthCheckInterface`, `HealthResult`, `HealthStatus`, `HealthCategory` et le `HealthCheckRunner` dans le nouveau plugin. Motif : le seul contrat existant et maintenu, celui de `liip/monitor-bundle` (qui repose sur `laminas/laminas-diagnostics`), ne porte nativement que **2 des 8 exigences** d'EPIC-20 (voir tableau en question 1). Les six autres (catégorie typée, lien d'action, statut `unknown`, budget de temps, cache mutualisé, sécurité passive de l'exécution) devraient être ajoutées par-dessus, dans un exécuteur que Liip ne laisse pas remplacer sonde par sonde. L'adaptateur serait plus gros que le contrat qu'il adapte.
- **On enveloppe** ce que le cœur fournit déjà, **par ses services et non par ses commandes** : `sylius.requirements.installer.sylius`, `Doctrine\ORM\Tools\SchemaValidator`, `MigrationStatusCalculator`, `MessageCountAwareInterface`. Ces briques sont réutilisables sans réécriture dans des sondes de 15 à 30 lignes (EPIC-21).
- **Passerelle Laminas/Liip : non retenue pour ce sprint** (YAGNI). Elle reste possible plus tard sous la forme d'un adaptateur facultatif `LaminasCheckAdapter` (une classe, `suggest`), sans changer le contrat. À décider par Thibaut, voir « Points ouverts ».

**Question 6 (montée FrankenPHP 1.14 / PHP 8.6) : l'ADR-0021 peut être rouverte.** Les deux conditions de réouverture sont remplies le 2026-11-22 : des tags `dunglas/frankenphp` `*php8.6*` existent (dont `1-php8.6-bookworm` et `1.14-php8.6-bookworm`, épinglables par digest) et le seul paquet verrouillé qui excluait PHP 8.6, `league/oauth2-server`, l'autorise depuis sa 9.5.0. Le verrou ZTS de l'ADR est levé par mesure : l'image 8.6 embarque un PHP ZTS, l'architecture des ADR-0002 et ADR-0013 est conservée. Il reste **deux frictions de build**, aucune bloquante : `mbstring` n'est plus fourni et `apcu` ne compile plus en 8.6 (non utilisé par le code de la démo). Une story de montée est proposée **hors sprint 56** (voir section 6).

Conséquence pour le sprint : US-286 conclut « écrire » ; US-287 et US-288 démarrent comme prévu (aucune reformulation en adaptateur). Aucun dépôt n'est créé avant la réponse de Thibaut (porte du spike).

## Décisions de conception tranchées

| Point | Décision | Motif |
|---|---|---|
| Nom du paquet | `tmonier/sylius-platform-health-plugin`, dépôt `thibmonier/sylius-platform-health-plugin` | Cohérent avec le préfixe `tmonier/sylius-…-plugin` du reste de l'écosystème ; plus précis que `sylius-health-plugin`, qui prêterait à confusion avec la « santé commerciale » de `company-insights`. Disponibilité sur Packagist : non vérifiable proprement par la récupération web (la page de paquet n'a pas pu être lue), **non confirmée** — à vérifier par Thibaut à la soumission. |
| Rendu différé | **Live Component** avec `loading="defer"` | Voir question « rendu différé » ci-dessous. |
| Catégories de départ | `environment`, `data`, `scheduled_tasks`, `shop`, `plugins` (inchangé) | Aucune source n'impose un autre découpage. |
| Budget / TTL | 500 ms par sonde / 60 s, configurables (inchangé) | Règles d'EPIC-20. Réserve sur la nature du budget : voir « Estimations ». |
| Découverte des sondes | Tag autoconfiguré sur `HealthCheckInterface` | Même mécanisme que Liip (`liip_monitor.check`) et `calmfox/watch-sylius` (`calmfox_watch.health_check`) : pratique établie. |

## Question 1 — `liip/monitor-bundle` : maintenu, compatible, contrat suffisant ?

**Maintenance et compatibilité.**

- Dernière version **2.25.0, publiée le 2026-03-23**, exigeant PHP `^8.1`, `symfony/framework-bundle ^6.4|^7.0|^8.0` et `laminas/laminas-diagnostics ^1.27`. La 2.24.0 (2025-06-25) excluait Symfony 8 ; la 2.25.0 l'ajoute. Pas de stable 3.x (branche `3.x-dev` seulement). Aucun champ `abandoned` dans les métadonnées. — [Packagist `liip/monitor-bundle`](https://packagist.org/packages/liip/monitor-bundle) et [métadonnées p2](https://repo.packagist.org/p2/liip/monitor-bundle.json), consultés le 2026-11-22.
- Derniers commits sur `2.x` du 2026-03-23 (fusion de la PR #329 « php85-symfony8 »). — [API GitHub, commits](https://api.github.com/repos/liip/LiipMonitorBundle/commits?per_page=3&sha=2.x), consulté le 2026-11-22.
- `laminas/laminas-diagnostics` **1.30.2, 2026-08-27**, PHP 8.2 à 8.5, pas de marque d'abandon. — [Packagist](https://packagist.org/packages/laminas/laminas-diagnostics), consulté le 2026-11-22.
- Constat : maintenu, déclaré compatible Symfony 7.4 et 8 et PHP 8.5 **par contrainte Composer**. La compatibilité effective avec Sylius 2.3 / Symfony 8.1 n'a **pas** été testée (aucune installation dans ce spike) : **non confirmée**. Le README de Liip reste rédigé dans des conventions anciennes (`AppKernel.php`, `app/console`) — [README](https://raw.githubusercontent.com/liip/LiipMonitorBundle/2.x/README.md), consulté le 2026-11-22.

**Le contrat couvre-t-il le besoin ?** Le contrat de sonde est celui de Laminas :

```php
interface CheckInterface
{
    public function check();      // retourne un ResultInterface
    public function getLabel();   // libellé
}
```

— [source `CheckInterface.php`](https://raw.githubusercontent.com/laminas/laminas-diagnostics/1.31.x/src/Check/CheckInterface.php), consulté le 2026-11-22. Les classes de résultat sont `Success`, `Warning`, `Failure` et `Skip` — [API GitHub, `src/Result`](https://api.github.com/repos/laminas/laminas-diagnostics/contents/src/Result?ref=1.31.x), consulté le 2026-11-22.

| Exigence d'EPIC-20 | Couverture par Liip / Laminas |
|---|---|
| Identifiant stable de la sonde | **Partielle** : alias de tag ou identifiant de service (README Liip). Pas porté par le contrat. |
| Catégorie typée (5 valeurs) | **Partielle** : « groupes » de Liip, chaînes libres, configurés hors du contrat. |
| Statut à quatre valeurs `ok/warning/critical/unknown` | **Partielle** : `Success/Warning/Failure/Skip`. `Skip` (« ignorée ») n'a pas la sémantique de `unknown` (« non mesurable »). Pas de niveau `critical` distinct de `Failure`. |
| Lien d'action (route + paramètres) | **Non** : aucun champ dédié. Le résultat porte un message et des données libres. |
| Budget de temps par sonde (500 ms) | **Non confirmée** : le README ne cite qu'un `timeout` propre au check `pdo_connections` ; rien de général. |
| Cache court mutualisé (60 s) | **Non confirmée** : le README ne décrit aucun cache. |
| Exception d'une sonde jamais remontée | **Non confirmée** : le comportement de l'exécuteur Laminas n'a pas été lu ; à vérifier dans le code avant toute affirmation. |
| Aucune sortie sensible, aucun appel sortant par défaut | **Partielle** : aucun check n'est actif sans configuration, mais `symfony_version` et `security_advisory` supposent une consultation externe (déduction du README, **non confirmée**). |

Autres constats utiles :

- Liip expose ses propres routes HTTP (`/monitor/health/…`, `enable_controller: true`) ; le README **ne décrit pas** leur contrôle d'accès (**non confirmée**). Un plugin d'administration qui n'en a pas besoin n'a pas à les activer.
- Liip fournit une bibliothèque de sondes génériques utile : `disk_usage`, `php_extensions`, `doctrine_dbal`, `doctrine_migrations`, `messenger_transports` (seuils warning/critical, exige `MessageCountAwareInterface`), `redis`, `opcache_memory`, etc.

Réponse : le contrat ne couvre pas le besoin. Il est la **base d'un moniteur de disponibilité** (réponse HTTP pour Nagios, réponse 502 par défaut en cas d'échec), pas d'un écran d'administration où chaque rouge porte un lien de correction.

## Question 2 — Symfony : composant de premier rang et commandes réutilisables

**Composant officiel de health check.** Aucune page de symfony.com présentant un composant ou un bundle de health check de premier rang n'a été trouvée ; la recherche ne remonte que des paquets tiers (`macpaw/symfony-health-check-bundle`, `monsieurbiz/healthcheck-bundle`, `zucommunications/health-check-bundle`, `kiora/health-check-bundle`, etc.). — [recherche web](https://packagist.org/packages/macpaw/symfony-health-check-bundle), consultée le 2026-11-22. L'**absence** d'un tel composant est établie par défaut de résultat, pas par une source qui l'affirme : **non confirmée**.

Parmi ces paquets tiers, `monsieurbiz/healthcheck-bundle` v1.1.0 (2026-10-07, Symfony `^6.0|^7.0|^8.0`) est le plus proche d'un contrat, mais il se réduit à `healthcheck(): bool` (vrai/faux), sans catégorie, message ni lien ; il répond `200`/`503` en texte brut. — [Packagist](https://packagist.org/packages/monsieurbiz/healthcheck-bundle), consulté le 2026-11-22. Il ne couvre pas le besoin.

**Commandes du cœur comme sondes** (lecture du code installé) :

| Commande | Verdict | Détail |
|---|---|---|
| `doctrine:schema:validate` | **Réutilisable par le service**, pas par la commande | Code de sortie = somme de bits (1 : mapping, 2 : synchro). Le service `Doctrine\ORM\Tools\SchemaValidator` expose `validateMapping()` et `schemaInSyncWithMetadata()` : statut `critical` si mapping invalide, `warning` si écart de schéma. |
| `doctrine:migrations:up-to-date` | **Réutilisable par le service** | La commande (`migrations:up-to-date`, préfixe `doctrine:` du bundle) rend 0 / 1 / 2 selon les migrations nouvelles ou exécutées-indisponibles, en s'appuyant sur `getMigrationStatusCalculator()` (`getNewMigrations()`, `getExecutedUnavailableMigrations()`). |
| `messenger:stats` | **Réutilisable par le service** | Ne fonctionne que si le transport implémente `MessageCountAwareInterface` (`getMessageCount()`), sinon la commande l'ignore : la sonde doit rendre `unknown` pour un transport non comptable. Pour la file en échec, la commande `messenger:failed:show --stats` existe — [Symfony, Messenger](https://symfony.com/doc/current/messenger.html), consulté le 2026-11-22. |
| `cache:pool:list` | **Peu utile** | Liste les pools (`List available cache pools`), aucun état de santé ; utile seulement pour énumérer les pools à tester. |
| `debug:container --deprecations` | **Non retenue comme sonde d'exécution** | Affiche les dépréciations produites à la compilation et au préchauffage du conteneur : une information de build, pas d'exécution. Sa pertinence en production (journal de compilation présent ou non) n'a pas été vérifiée : **non confirmée**. |

Règle retenue : une sonde appelle un **service**, jamais `Application::run()` sur une commande (sortie à analyser, effets de bord possibles, pas de budget de temps).

## Question 3 — Sylius 2.x : `sylius:install:check` et tableau de bord

**La commande.** Le nom exact est `sylius:install:check-requirements` (et non `sylius:install:check`), classe `CheckRequirementsCommand`. Elle délègue à un `RequirementsCheckerInterface` (service `sylius.checker.installer.sylius_requirements`, `SyliusRequirementsChecker`) qui **écrit dans la sortie console** (tableaux) et rend un booléen ; elle lève une `RuntimeException` si une exigence obligatoire n'est pas satisfaite.

**Appelable en service ?** Oui, mais par la **source de données**, pas par le vérificateur : le service `sylius.requirements.installer.sylius` (`SyliusRequirements`, itérable de `RequirementCollection`) fournit des objets `Requirement` (`getLabel()`, `isFulfilled()`, `isRequired()`, `getHelp()`). Il suffit de les parcourir :

- exigence remplie → `ok` ; non remplie et `isRequired()` → `critical` ; non remplie et facultative → `warning` ;
- les trois collections sont `SettingsRequirements` (fuseau horaire, version de PHP, `detect_unicode`, `session.auto_start`), `ExtensionsRequirements` et `FilesystemRequirements` (répertoires de cache et de logs inscriptibles) ; ces contrôles sont en lecture seule.
- Réserve : le seuil de version de PHP codé dans le cœur (`RECOMMENDED_PHP_VERSION = '7.0'`) est périmé, la sonde « version de PHP » ne doit donc pas s'y fier ; elle est à écrire à part (EPIC-21).
- Réserve : les services sont privés dans le conteneur ; un plugin les injecte par référence, mais leur identifiant n'est pas une API publique documentée : **non confirmée** comme stable entre versions mineures (à protéger par un test).

Sources : lecture de `Console/Command/CheckRequirementsCommand.php`, `Installer/Checker/SyliusRequirementsChecker.php`, `Installer/Requirement/*.php` et `Resources/config/services/installer*.php` du paquet `sylius/sylius` v2.3.3, 2026-11-22.

**Tableau de bord admin : point d'extension.** C'est un **Twig hook**, aucune surcharge de gabarit n'est nécessaire. Le hook `sylius_admin.dashboard.index.content` (fichier `AdminBundle/Resources/config/app/twig_hooks/dashboard/index.yaml`) déclare ses entrées `header` (priorité 200), `statistics` (100, un Live Component `sylius_admin:dashboard:statistics`) et `latest_statistics` (0). Un plugin ajoute un encart par une entrée sous ce hook, avec une priorité entre 100 et 0 pour l'afficher entre les deux blocs, sans toucher aux gabarits du cœur (US-289). Le menu se prolonge de même par l'événement `sylius.menu.admin.main` (`MainMenuBuilder::EVENT_NAME`, sous-menu `configuration` existant), ce qui sert l'entrée « Santé de la plateforme » d'US-288.

## Question 4 — Packagist : plugins de supervision ou de diagnostic

Relevé fait le 2026-11-22 avec `sylius health`, `sylius monitor`, `sylius admin dashboard` (recherche Packagist) plus une recherche web ouverte. Les mots-clés sont ceux de l'analyse d'écosystème du 2026-11-12.

| Paquet | Constat |
|---|---|
| [`calmfox/watch-sylius`](https://packagist.org/packages/calmfox/watch-sylius) | v1.5.0 (2026-11-02), Sylius `^1.12 \|\| ^2.0`, MIT, **3 installations**, 0 favori. Intégration à un **service de supervision externe** (Calmfox Watch) : point d'entrée secret interrogé par ce service, sondes (base, disque, SMTP, file Messenger, cache, tunnel de commande, tâches planifiées par battement, Elasticsearch), contrôles de sécurité, historique des versions. Un contrat `HealthCheckInterface` (`run()` rend un `CheckResult` ou `null`) et un écran d'administration existent. Appels sortants : enregistrement et appairage vers l'API du service, connexion SMTP, auto-vérification en boucle locale. |
| [`monsieurbiz/healthcheck-bundle`](https://packagist.org/packages/monsieurbiz/healthcheck-bundle) | Bundle Symfony, point d'entrée `200/503`, aucun écran, aucune sonde fournie. |
| [`monsieurbiz/sylius-messenger-admin-plugin`](https://packagist.org/packages/monsieurbiz/sylius-messenger-admin-plugin) | Affiche les messages Messenger dans l'administration ; cible Sylius < 2.0 d'après le résultat de recherche (non vérifié directement). |
| [`acseo/sylius-prometheus-metrics-plugin`](https://packagist.org/packages/acseo/sylius-prometheus-metrics-plugin) | Métriques pour Prometheus ; fiche mise à jour en 2024 d'après le résultat de recherche (non vérifié directement). |
| [`setono/sylius-scheduler-plugin`](https://root.packagist.org/packages/setono/sylius-scheduler-plugin) | Planification et suivi de tâches ; autre périmètre (non lu en détail). |

**Constat** : un seul plugin approche l'idée (`calmfox/watch-sylius`), avec un positionnement différent (suivi **par un service externe**, point d'entrée interrogé à distance) et une adoption très faible. Aucun plugin Sylius de diagnostic **interne à l'administration**, à contrat de sonde extensible et sans appel sortant, n'a été trouvé. Cette absence repose sur une recherche limitée à trois requêtes et sur le seuil de résultats renvoyés par Packagist : elle est **non confirmée** comme exhaustive. Le recoupement avec `calmfox/watch-sylius` est signalé à Thibaut (point ouvert 3) ; le README du futur plugin ne le comparera pas.

## Question 5 — Recoupement Sylius Plus

- La page publique [sylius.com/plus](https://sylius.com/plus/), consultée le 2026-11-22, liste les modules (B2B Suite, RBAC, RFQ, etc.) et, sous les services d'accompagnement, des **« Audits and health checks »** : prestations d'experts ponctuelles. Aucun écran de santé, de supervision ou de diagnostic n'y est mentionné.
- La documentation `docs.sylius.com/sylius-plus` redirige vers une **authentification** (redirection 307 vers GitBook, `visitor-auth`) : son contenu n'a pas pu être lu. L'absence d'écran de santé dans cette documentation est donc **non confirmée**.
- Le cœur de Sylius embarque une commande `sylius:show-plus-info` (`ShowPlusInfoCommand`), sans rapport avec un diagnostic.
- Politique inchangée : si un recoupement est confirmé, le plugin n'est pas promu. À ce jour aucun recoupement n'est confirmé sur un **écran**, mais la documentation protégée n'a pas été vérifiée (point ouvert 2). Aucune comparaison n'est reprise dans les textes publics.

## Rendu différé : Live Component ou `fetch` sur route JSON

**Faits relevés** (lecture des dépôts frères et du code installé, 2026-11-22) :

- Contrairement à l'hypothèse de départ, **`sylius-command-palette-plugin` n'utilise pas de Live Component** : son endpoint est un contrôleur JSON (`Controller/Admin/SearchController`, `JsonResponse`) consommé par `Resources/public/command-palette.js`. Le Live Component est utilisé par `sylius-quick-order-plugin` (`MatrixOrderComponent`, avec `HookableLiveComponentTrait` de `sylius/twig-hooks`) et par le tableau de bord du cœur lui-même.
- `symfony/ux-live-component` (`^2.36 || ^3.0`) est une dépendance **obligatoire** de `sylius/sylius` v2.3.3 : aucune dépendance nouvelle pour un plugin d'administration.
- Le composant gère le chargement différé nativement : attribut `loading="defer"` (au chargement de la page) ou `loading="lazy"` (à l'entrée dans la fenêtre) via `DeferLiveComponentSubscriber`, avec un gabarit de chargement (`loading-template`).

**Décision : Live Component, `loading="defer"` sur la page, `loading="lazy"` sur l'encart du tableau de bord.**

| Critère | Live Component | `fetch` + route JSON |
|---|---|---|
| Rendu de la liste | HTML produit côté serveur, gabarit Twig unique réutilisé par la page et l'encart | HTML à reconstruire côté JS ou second gabarit |
| Accessibilité (US-291) | Tableau rendu en Twig, région `aria-live` sur le conteneur de résultats | À écrire à la main, risque de régression |
| JavaScript à maintenir | Aucun JS applicatif | Un contrôleur Stimulus ou script à tester |
| Rafraîchissement explicite | `LiveAction` | Bouton + gestion d'erreur à écrire |
| Dépendance | Déjà présente (cœur Sylius) | Aucune |
| Risque | Vérifier que l'état hydraté (checksum) ne porte que des scalaires ; l'exécution des sondes se fait dans un service appelé par le composant | Moins de dépendance au cycle de vie du composant |

Réserve : le composant ne doit **jamais** exécuter les sondes dans `mount()` appelé hors différé, sinon la page attend les sondes. Le critère d'US-288 « squelette rendu sans exécuter de sonde » le teste.

## Question 6 — Montée FrankenPHP 1.14 / PHP 8.6 : réexamen de l'ADR-0021

Question ajoutée le 2026-11-22 à la demande de Thibaut. [ADR-0021](../../docs/adr/0021-montee-php-8-6-reportee.md) (acceptée le 2026-11-20) maintient la démo en PHP 8.5 et pose deux conditions de réouverture : (1) un tag `dunglas/frankenphp` `*php8.6*` existe ; (2) aucun paquet verrouillé n'exclut PHP 8.6. Elle relève en outre trois constats : pas d'image 8.6, ZTS absent des images PHP 8.6, `league/oauth2-server` exclut 8.6.

### Condition 1 — image FrankenPHP pour PHP 8.6 : remplie

- **Version publiée** : FrankenPHP **v1.14.0, publiée le 2026-11-21 (17:31 UTC)** ; PHP 8.6 devient la version par défaut des tags sans suffixe PHP (`1.14`, `1`, `latest`). Les branches antérieures restent disponibles par tags `php8.x` (exemple de la note : `1.14-php8.5`). — [Releases php/frankenphp (API GitHub)](https://api.github.com/repos/php/frankenphp/releases?per_page=4), consulté le 2026-11-22. Le dépôt est désormais `php/frankenphp`.
- **Tags Docker Hub** : 96 tags `php8.6` au 2026-11-22, tous mis à jour le 2026-11-21 entre 08:48:31 et 08:49:02 UTC, dont `1-php8.6-bookworm` et `1.14-php8.6-bookworm` (variantes `bookworm`, `trixie`, `alpine`, et `builder`). — [Docker Hub, `dunglas/frankenphp`](https://hub.docker.com/v2/repositories/dunglas/frankenphp/tags?name=php8.6&page_size=50), consulté le 2026-11-22.
- **Digest épinglable** (`1-php8.6-bookworm` et `1.14-php8.6-bookworm` pointent sur le même) :
  - liste de manifestes : `sha256:322b7844203295c673ad0bba09fdbf807a547eb8f93455d26ac5ade729c9c808` ;
  - amd64 : `sha256:7483a7d2fdcbb550f0603fbd219dc729f4c7a25820918d9c634f828cf66548b6` ; arm64 : `sha256:425478b6af43c5a2ba5e1864c93b01e800a5f2902fbfb6903575de38d09af714`.
  - Vérifié : `docker run` sur `dunglas/frankenphp:1.14-php8.6-bookworm@sha256:322b7844…` a résolu exactement ce digest (2026-11-22, poste arm64).
- **Contenu mesuré** dans l'image épinglée (2026-11-22) : `FrankenPHP v1.14.0 PHP 8.6.0 Caddy v2.11.9`, PHP 8.6.0 compilé le 2026-11-19.
- **Changements notables des notes de version** (v1.13.4 à v1.14.0, mêmes sources) :
  - **Rupture annoncée (v1.14.0)** : les images PHP 8.6 n'incluent plus `mbstring` ni PECL ; installer les extensions par `install-php-extensions` ou rester sur les tags `php8.5`.
  - Extensions sans version compatible 8.6 pour les binaires statiques (amqp, apcu, igbinary, imagick, lz4, memcached, msgpack, redis, xlswriter) : construites depuis des branches de développement. Ne concerne que les binaires statiques, pas l'image Docker.
  - Nouveauté : sous-directive `file_server` avec options dans `php_server` (précompressés zstd/br/gzip). Facultatif.
  - **v1.13.5 (mode worker)** : entre deux requêtes, seul l'état dérivé de la requête est remis à zéro ; `strtok()`, `error_get_last()` et les erreurs libxml persistent. Sans objet en mode classique (ADR-0013), à retenir si le mode worker est un jour réactivé (`FRANKENPHP_WORKER_MODE`).
  - v1.13.6 : support de la compilation contre PHP non-ZTS ; `max_execution_time` compte le temps réel (comme documenté), pas le temps CPU.

### Condition 2 — verrous de paquets : remplie

- **`league/oauth2-server` 9.5.0, publiée le 2026-11-20 (19:37 UTC)** : `php: ~8.2.0 || ~8.3.0 || ~8.4.0 || ~8.5.0 || ~8.6.0`. La 9.4.1, verrouillée dans `composer.lock`, excluait 8.6. Pas de marque d'abandon. — [Packagist p2 `league/oauth2-server`](https://repo.packagist.org/p2/league/oauth2-server.json), consulté le 2026-11-22.
- **Compatible avec le plugin** : `tmonier/sylius-b2b-mcp-plugin` v1.2.3 exige `league/oauth2-server ^9.2` ; la 9.5.0 y répond. La contrainte PHP du plugin est `^8.2`. Lever le verrou demande donc un `composer update league/oauth2-server` (la démo reste en `^8.5` à la racine, qui accepte 8.6). Le comportement de 9.5.0 par rapport à 9.4.1 n'a pas été examiné (note de version non lue) : **non confirmée** sans incidence sur le verrou, à vérifier dans la story.
- **Autres paquets** (lecture de `composer.lock`, `jq`, sans installation) : seul `league/oauth2-server` 9.4.1 exclut 8.6. `laminas/laminas-stdlib` 3.22.0, `lcobucci/jwt` 5.7.0 et `sabberworm/php-css-parser` v9.5.0 l'autorisent explicitement ; `setasign/fpdi` v2.6.9 (`<=8.6.99999`) et `dasprid/enum` 1.0.7 (`<9.0`) aussi. Le résultat de l'ADR-0021 est confirmé, moins le verrou levé.
- **Réserve** : une contrainte `php` qui autorise 8.6 ne prouve pas que le paquet fonctionne en 8.6 (CI amont, dépréciations). La suite de tests de la démo est le seul juge : **non confirmée** avant son exécution.

### ZTS et architecture des ADR-0002 et ADR-0013 : verrou levé, par mesure

- Les notes v1.13.6 indiquent que les images PHP 8.6 « ne sont plus bâties sur une image de base avec variante `zts` » et que la compilation non-ZTS est prise en charge. La documentation de la version 1.14.0 écrit toujours que le ZTS est « requis pour FrankenPHP » (`docs/performance.md`), et la page de documentation principale ne mentionne ni 8.6 ni mode non-ZTS (`frankenphp.dev/docs`, consultées le 2026-11-22). **Mode non-ZTS documenté : non confirmé.**
- **Mesure directe dans l'image épinglée** (2026-11-22) : `php -v` rend `PHP 8.6.0 (cli) … (ZTS)` et `php -i` rend `Thread Safety => enabled`. L'image 8.6 **fournit donc un PHP ZTS**, compilé par le Dockerfile de FrankenPHP (une lecture du Dockerfile sur GitHub n'indique pas ses options de configuration : la preuve est la mesure, pas la lecture).
- **ADR-0002** (un conteneur : FrankenPHP + supercronic) : inchangée, supercronic est téléchargé par le `Dockerfile` de la démo et ne dépend pas de la base PHP. **ADR-0013** (mode classique, `FRANKENPHP_NUM_THREADS=8`, `FRANKENPHP_MAX_THREADS=16`) : le modèle de threads est le même (ZTS). Les mesures de mémoire et de latence de l'ADR portent sur PHP 8.4/8.5 : **non remesurées** sur 8.6 (l'ADR-0021 les disait « valides » tant que l'architecture était conservée ; la story de montée contient une passe de contrôle, sans promesse de gain).
- `session.cookie_samesite` vaut `Lax` et `session.use_strict_mode` vaut `1` dans l'image 8.6 (mesuré), conformément à l'ADR.

### Frictions nouvelles, mesurées (ni verrous, ni bloquantes)

Essai éphémère dans un conteneur jetable de l'image épinglée (poste arm64, 2026-11-22), avec la liste d'extensions du `docker/Dockerfile` actuel, sans modifier aucun fichier du dépôt :

| Extension | Résultat sur 8.6 |
|---|---|
| `intl`, `gd`, `zip`, `exif`, `pdo_mysql`, `bcmath`, `opcache` | Installées sans erreur par `install-php-extensions` |
| `mbstring` | **Absente de l'image** (liste des modules) ; installée sans erreur par `install-php-extensions mbstring`. À ajouter explicitement : `doctrine/doctrine-bundle`, `dompdf/dompdf`, `league/uri` et d'autres l'exigent (`ext-mbstring` dans `composer.lock`). |
| `apcu` (5.1.28) | **Échec de compilation** (`apc_persist.c`, avertissement traité en erreur, `make` en échec). Non utilisée : aucune référence dans `config/` ou `src/` hors la ligne commentée `cache.adapter.apcu` de `cache.yaml`, et `symfony/polyfill-apcu` suffit. À retirer de la liste d'extensions plutôt qu'à corriger. |

Non testé : l'architecture amd64 (production Railway), `wkhtmltox` `bookworm` (inchangé, base Debian bookworm identique), le lancement de l'application et des tests sous 8.6.

### Points « à vérifier le jour de la montée » (rappel de l'ADR-0021, complétés)

1. **Sessions** : `SameSite=Lax` par défaut (confirmé dans l'image). À éprouver sur les retours cross-site de PunchOut cXML (`setup/response` puis redirection). Option : déclarer `cookie_samesite` explicitement dans `config/packages/framework.yaml`, si l'essai l'impose.
2. **DOM** : visibilité asymétrique et sérialisation XML modifiée ; cXML (PunchOut) et Factur-X. Le module `lexbor` est désormais présent dans l'image (`php -m`) : constat, sans conséquence établie.
3. **Intl** : nouveaux `TypeError` (fuseaux, décalages, `MessageFormatter::parse()`).
4. **Nouveaux** : `mbstring` à ajouter ; `apcu` à retirer ; garde du mode worker (persistance de l'état libxml, v1.13.5) ; `wkhtmltox` et l'extension `exif` vérifiés après montée.
5. **Sans objet** : SOAP (`ext-soap` absente), PHP-FPM / PHP-CGI.

### Conclusion et story proposée (hors sprint 56)

**Rouvrir l'ADR-0021.** Aucun des trois verrous de l'ADR ne subsiste : image publiée et épinglable, ZTS fourni, `league/oauth2-server` 9.5.0. L'ADR devra être remplacée par une nouvelle (statut « Remplacée par ») au moment de la montée, pas avant la décision de Thibaut.

> **Proposition de story (à soumettre par Thibaut, non engagée dans le sprint 56)**
>
> **US-NNN — Montée de la démo vers PHP 8.6 sur FrankenPHP 1.14** · epic : socle de la démo · MoSCoW : **Should** · **estimation proposée : 5 points** (chiffre indicatif, à réviser à la décomposition ; le plan est un spike de compatibilité plus qu'un développement)
>
> Tâches principales : (1) épingler `dunglas/frankenphp:1.14-php8.6-bookworm@sha256:322b7844…` dans `docker/Dockerfile` ; ajouter `mbstring`, retirer `apcu` ; (2) `composer update league/oauth2-server` (9.5.0) et passer la contrainte `platform` à 8.6 ; (3) suite de tests complète (PHPUnit `unit` et `non-unit`, PHPStan, ECS, Rector) sous 8.6 ; (4) essais manuels PunchOut cXML (sessions `Lax`), Factur-X et cXML (DOM), pages localisées (Intl) ; (5) mesure des deux modes d'ADR-0013 (3 passes, écart entre passes) ; (6) nouvelle ADR remplaçant l'ADR-0021, mise à jour de la section de veille US-239 ; (7) vérification de la CI des plugins en matrice PHP 8.6 (hors périmètre de la démo, à planifier).
>
> Risques : extension PECL sans version 8.6 (apcu déjà identifiée, d'autres possibles dans les plugins) ; régression silencieuse de session sur PunchOut ; architecture amd64 non testée ici ; les mesures de l'ADR-0013 ne se transposent pas telles quelles ; réécriture de l'image par Dependabot (tag et digest évoluent ensemble). Retour arrière : le `Dockerfile` repasse sur l'image 8.5 épinglée, aucune migration de données.

### Lien avec EPIC-21 : sonde « version de PHP / de FrankenPHP »

La sonde `environment.runtime_versions` d'EPIC-21 peut lire `PHP_VERSION`, `PHP_ZTS` (constante PHP) et, côté serveur, `$_SERVER['SERVER_SOFTWARE']` (à confirmer pour FrankenPHP : **non confirmée**), sans appel sortant : les dates de fin de support de PHP (8.2 à 8.6) sont embarquées dans le code, comme le prévoit EPIC-20. Elle rendrait `warning` pour une branche proche de sa fin de support et `unknown` si la version ne se lit pas. Les trois sources ci-dessus (tags, notes de version, `league/oauth2-server`) ne sont **pas** lues par la sonde (aucune télémétrie sortante) : elles restent dans le rapport de veille hebdomadaire d'US-239.

## Sources et état de lecture

Chaque lien a été consulté le 2026-11-22. Lectures de code (démo, lecture seule) : `vendor/sylius/sylius` v2.3.3 (`CoreBundle`, `AdminBundle`), `vendor/doctrine/orm`, `vendor/doctrine/migrations`, `vendor/symfony/messenger`, `vendor/symfony/framework-bundle`, `vendor/symfony/ux-live-component`, dépôts frères `sylius-command-palette-plugin` et `sylius-quick-order-plugin`.

Aucun paquet n'a été installé dans le projet et aucun dépôt n'a été créé. Pour la question 6, l'image `dunglas/frankenphp` épinglée a été **tirée localement** (`docker run`, conteneurs jetables, sans modification du dépôt, du `Dockerfile` ni de `composer.lock`) afin de mesurer ZTS, modules et compilation des extensions ; aucune de ces opérations n'a laissé de trace dans le dépôt.

## Estimations d'US-287 à US-292

Les fiches US-289 à US-292 n'existent pas encore ; les estimations viennent du tableau d'EPIC-20.

| Story | Points | Verdict | Remarque |
|---|---|---|---|
| US-287 | 5 | **Confirmée** | Conclusion « écrire » : pas de reformulation. À corriger dans le critère « budget dépassé » : voir ci-dessous. |
| US-288 | 5 | **Confirmée** | Live Component déjà tranché ; l'hypothèse du sprint est confirmée. |
| US-289 | 3 | Confirmée | Twig hook sous `sylius_admin.dashboard.index.content`, aucune surcharge de gabarit. |
| US-290 | 3 | Confirmée | — |
| US-291 | 3 | Confirmée | — |
| US-292 | 3 | Confirmée | Mentionner dans le README la frontière avec Liip et `calmfox/watch-sylius` **sans comparaison**. |

**Total inchangé : 25 points.** Le sprint 56 reste à 13 points engagés.

**Point de vigilance sur US-287 (risque, pas de réestimation).** PHP est synchrone : un `Runner` ne peut pas **interrompre** une sonde qui dépasse 500 ms sans processus séparé ou signal asynchrone. Il peut seulement mesurer la durée **après coup** et rendre `unknown` « budget de temps dépassé », sans empêcher la sonde de consommer son temps. Le critère d'US-287 gagne à être reformulé ainsi : « le résultat d'une sonde dont la durée mesurée dépasse le budget est remplacé par `unknown` ; les sondes qui font des E/S doivent appliquer leur propre délai (délai de connexion, `timeout` du client HTTP, requête SQL bornée) ». C'est un choix de rédaction du critère, pas un travail supplémentaire. Une interruption stricte (processus enfant par sonde) serait un changement d'échelle de la story : à ne pas engager sans décision de Thibaut.

## Points ouverts pour Thibaut

1. **Passerelle Laminas/Liip** : facultative, hors sprint 56. À planifier plus tard ou à écarter ?
2. **Documentation Sylius Plus** protégée par authentification : vérifier à la main (compte partenaire) l'absence d'écran de santé avant toute promotion.
3. **`calmfox/watch-sylius`** : recoupement partiel sur l'idée (contrat de sonde, écran). Positionnement différent, mais à connaître avant la soumission à Packagist.
4. **Nom du paquet sur Packagist** : disponibilité de `tmonier/sylius-platform-health-plugin` non vérifiée ; à confirmer au GO de création du dépôt.
5. **Budget de temps** : accepter la mesure a posteriori (recommandé) ou exiger l'interruption stricte (impact sur US-287).
6. **Compatibilité de Liip avec Sylius 2.3 / Symfony 8.1** : non testée, sans effet sur la décision actuelle.
7. **Montée PHP 8.6** (question 6) : GO pour rouvrir l'ADR-0021 et inscrire la story proposée au backlog, hors sprint 56 ? Choix entre le tag `1.14-php8.6-bookworm` (même digest que `1-php8.6-bookworm`) et le maintien sur 8.5 jusqu'à la première version corrective 8.6.x.
8. **Timebox** : la question 6 a consommé du temps au-delà des 6 h initiales prévues ? Le report de +1 h indiqué dans US-286 reste à confirmer par Thibaut.
