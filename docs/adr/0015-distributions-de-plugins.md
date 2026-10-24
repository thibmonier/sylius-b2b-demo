# ADR-0015 — Distributions de plugins (packs clé en main)

- **Statut** : Accepté (2026-10-23) par Thibaut : forme, composition des deux packs et découpage en quatre stories (US-175 à US-178)
- **Contexte projet** : sylius-b2b-demo, sprint 23, spike US-173 (borné à 4 h)

## Contexte

Installer un plugin demande quatre gestes à la main : `composer require`, déclaration du bundle dans `config/bundles.php`, import de la configuration et des routes, migrations. Pour un projet qui veut plusieurs plugins, c'est répétitif. US-173 demande de choisir la forme et la composition de « packs » qui regroupent des plugins.

Contraintes posées par Thibaut le 2026-10-23 :

- un pack ne contient aucun plugin classé « concurrent » ou « à confirmer » dans `docs/vitrine-plugins.md` (admin-order-creation, company-accounts, quick-order, quote-request, purchase-approval, tier-pricing, withdrawal) ; ces plugins restent installables un par un ;
- le volet commercial est écarté (licences, SLA, Packagist privé, place de marché) ;
- aucune publication et aucun nouveau dépôt pendant le spike.

L'ADR ne parle donc que de composition technique : les packs regroupent des plugins par usage, sans rapport avec une offre extérieure.

## Relevé des dépendances internes

Lu dans les `composer.json` des 18 plugins sans recoupement (dépôts voisins, 2026-10-23). Les `require` hors Sylius, Symfony et PHP sont seuls cités.

| Plugin | Dernière version | PHP | `require` interne | `suggest` interne |
|--------|------------------|-----|-------------------|-------------------|
| agentic-feeds | 1.0.0 | ^8.2 | - | webhook-outbox |
| ai-catalog-copilot | 1.0.0 | ^8.2 | - (symfony/ai-bundle et ai-platform `^0.14`) | audit-log |
| b2b-mcp | 1.2.2 | ^8.2 | - (sylius/mcp-server-plugin `^0.2`, mcp/sdk `^0.3`, league/oauth2-server) | quick-order, quote-request, trade-credit, company-accounts, purchase-approval, audit-log |
| webhook-outbox | 1.0.0 | ^8.2 | - | - |
| facturx | 1.1.0 | ^8.2 | - (sylius/invoicing-plugin `^2.2`, horstoeko/zugferd) | - |
| gpsr | 1.0.0 | ^8.2 | - | - |
| audit-log | 1.0.0 | ^8.2 | - | - |
| timeline | 1.0.0 | ^8.2 | - | - |
| command-palette | 1.1.0 | ^8.2 | - (Symfony 7.4 ou 8 pour lock et rate-limiter) | - |
| bulk-actions | 1.0.0 | ^8.2 | - | - |
| channel-access | 0.2.0 | **^8.3** | - | - |
| registration-approval | 0.1.2 | **^8.3** | **channel-access** `^0.1 \|\| ^0.2` | - |
| container-fill | 1.1.1 | ^8.2 | - | quick-order |
| order-hold | 1.0.0 | ^8.2 | - | - |
| trade-credit | 1.1.0 | ^8.2 | **order-hold** `^1.0` | facturx, sylius/invoicing-plugin |
| punchout-cxml | 1.3.0 | ^8.2 | - | company-accounts |
| customer-asset-management | 0.1.1 | ^8.2 | - | company-accounts |
| company-insights | 0.2.1 | ^8.2 | **company-accounts** `^1.1` | purchase-approval, trade-credit |

Quatre constats :

1. Trois `require` internes : registration-approval vers channel-access, trade-credit vers order-hold, company-insights vers company-accounts. Composer les résout seul (voir l'essai).
2. company-insights exige company-accounts, plugin exclu des packs. Il ne peut donc entrer dans aucun pack : l'installer tire un plugin classé « concurrent ». Il reste installé seul.
3. Les `suggest` de b2b-mcp visent surtout des plugins exclus (quick-order, quote-request, company-accounts, purchase-approval). Ce sont des suggestions : elles n'entrent pas dans le pack et ne le bloquent pas.
4. Quatre plugins sont avant 1.0 (channel-access, registration-approval, customer-asset-management, company-insights) et deux demandent PHP 8.3 : un pack qui les contiendrait relèverait le minimum de tout le pack à PHP 8.3 et hériterait d'une API encore mouvante.

## Essai (T-173-02)

Projet jetable hors dépôt (`.../scratchpad/w173/`), dépôts Composer de type `path` vers les plugins voisins (versions fixées par `options.versions` aux dernières étiquettes), métapaquet de type `metapackage` lui-même en dépôt `path`, racine reprenant les contraintes de la démo (`sylius/sylius ^2.3.2`, `extra.symfony.require 8.1.*`, plateforme PHP 8.5.12). Résolution seule, rien de téléchargé dans `vendor/` :

```bash
COMPOSER_HOME=<jetable>/composer-home composer update --no-install --no-scripts --no-plugins --no-interaction
```

| Essai | Contenu | Résultat |
|-------|---------|----------|
| 1 | métapaquet `tmonier/sylius-pack-ai-headless` : agentic-feeds, ai-catalog-copilot, b2b-mcp, webhook-outbox | Résolu, 206 paquets verrouillés (dont symfony/ai-bundle et ai-platform v0.14.1, sylius/mcp-server-plugin v0.2.0, mcp/sdk v0.3.0, league/oauth2-server 9.4.1) ; `Writing lock file`, aucun conflit |
| 2 | les deux métapaquets ensemble (ajout de `tmonier/sylius-pack-compliance-tooling` : facturx, gpsr, audit-log, timeline, command-palette, bulk-actions) | Résolu, 227 paquets ; sylius/invoicing-plugin v2.3.0 et horstoeko/zugferd v1.0.133 tirés par facturx ; aucun conflit |
| 3 | trade-credit + company-insights + registration-approval seuls | Résolu : order-hold, channel-access et company-accounts ajoutés automatiquement aux versions attendues |

Ce que l'essai **ne prouve pas** : l'installation réelle (`composer install`), le démarrage du noyau, les migrations. Il ne vérifie que la résolution des contraintes. C'est la première tâche de la réalisation (US-R2).

### Ce qui reste à la main après `composer require` du métapaquet

Relevé dans les sections Installation des README. Un métapaquet n'ajoute ni bundle ni fichier.

| Plugin | Bundles à déclarer | Configuration | Routes | Migrations | Autre |
|--------|--------------------|---------------|--------|------------|-------|
| agentic-feeds | 1 | 1 import | 1 import | oui | cron de génération |
| ai-catalog-copilot | 2 (AiBundle + plugin) | 1 import | 1 import | oui (2 tables) | choisir et installer le pont de fournisseur (`symfony/ai-*-platform`), clé d'API en variable d'environnement |
| b2b-mcp | 3 (McpBundle, SyliusMcpServerPlugin, plugin) | 2 imports | 2 imports | oui | HTTPS sur un nom public, proxys de confiance, purge quotidienne |
| webhook-outbox | 1 | 1 import | 1 import | oui | processus worker, purge, `framework.router.default_uri` |
| facturx | 2 (invoicing-plugin déjà configuré, puis plugin) | 1 import | 1 import | oui | réglages Factur-X par canal en administration |
| gpsr, audit-log, timeline | 1 chacun | 1 import chacun | 1 import chacun | oui | purge (audit-log), rattrapage facultatif (timeline) |
| command-palette | 1 | 1 import | 1 import | non | `assets:install` |
| bulk-actions | 1 | 1 import | 1 import | non | - |

Soit, pour le pack « IA et headless », 7 bundles, 5 imports de configuration, 5 imports de routes, 4 jeux de migrations et 3 tâches planifiées ou processus. Les recettes sont absentes : les README disent « Symfony Flex le fait automatiquement une fois qu'une recette existe » et aucune n'existe. Les gestes sur des classes de l'application (trait à ajouter à l'entité canal pour channel-access, à l'entité client pour registration-approval) ne s'automatisent ni par métapaquet ni par recette.

## Comparaison des trois formes (T-173-03)

| Critère | Métapaquet Composer | Recettes Symfony Flex | Squelette `create-project` |
|---------|--------------------|-----------------------|----------------------------|
| Effet | une commande `composer require` ; versions cohérentes entre les membres du pack | en plus : bundles déclarés, fichiers de configuration et de routes créés | projet Sylius prêt à démarrer avec le pack déjà configuré |
| Reste à la main | bundles, imports, routes, migrations, tâches planifiées | migrations et réglages ; les gestes sur les entités de l'application | presque rien, tant que le squelette suit Sylius |
| Coût de création | deux fichiers `composer.json` et un README par pack ; aucun code | un manifeste et des fichiers de configuration par paquet, testés dans un projet jetable | un dépôt complet (Docker, `.env`, Flex, thème), repris de Sylius-Standard |
| Maintenance | faible : une borne `^` suit les versions mineures ; à relire à chaque version majeure d'un membre | moyenne : chaque changement de fichier de config d'un plugin demande une recette à jour | forte : à réaligner sur chaque version de Sylius-Standard, sur PHP et sur Symfony |
| Publication | un paquet Packagist par pack, soumis par Thibaut, un dépôt par paquet | pull request publique sur `symfony/recipes-contrib`, relue par d'autres ; délai et décision hors de notre contrôle ; l'utilisateur doit accepter les recettes « contrib » (`extra.symfony.allow-contrib`, déjà à `true` dans le `composer.json` de la démo) | un paquet de type `project` sur Packagist, un dépôt de plus |
| Risque principal | donne l'impression d'un clé en main alors que cinq gestes restent | recette erronée publiée sous le nom du pack ; retour arrière lent | dérive par rapport à Sylius-Standard, surface de support large |
| Preuve apportée par le spike | essai 1 à 3 verts (résolution) | non essayé (publication publique, hors périmètre) | non essayé (dépôt à créer) |

Les recettes Flex s'attachent à un nom de paquet, y compris un métapaquet (c'est le cas de plusieurs packs de Symfony). Une recette sur `tmonier/sylius-pack-ai-headless` déclarerait donc les sept bundles et créerait les fichiers de configuration et de routes en une fois, sans qu'il faille une recette par plugin. Ce point découle de l'usage connu de Flex et n'a pas été vérifié dans cet essai.

## Décision proposée

1. **Forme retenue : métapaquet d'abord, recette Flex sur le métapaquet ensuite (combinaison des deux).** Le métapaquet seul laisse la déclaration des bundles à la main : la recette comble ce manque pour un coût moindre que celui d'un squelette. Le squelette de projet n'est pas retenu : il ajoute un dépôt à aligner sur chaque version de Sylius-Standard pour un gain déjà couvert par la démo, qui sert de projet de référence et montre une intégration complète.
2. **Deux packs**, sans plugin classé « concurrent » ou « à confirmer » :
   - `tmonier/sylius-pack-ai-headless` : agentic-feeds, ai-catalog-copilot, b2b-mcp, webhook-outbox ;
   - `tmonier/sylius-pack-compliance-tooling` : facturx, gpsr, audit-log, timeline, command-palette, bulk-actions.
   Nommage à confirmer (voir questions).
3. **Dépendances** : toute dépendance `require` entre plugins reste dans le même pack ou le pack est refusé. Aucun pack ne contient de plugin avec un `require` vers un plugin exclu (c'est la raison de l'exclusion de company-insights). Les `suggest` internes sont recopiés dans le champ `suggest` du métapaquet, jamais dans `require` : audit-log, suggéré par ai-catalog-copilot et b2b-mcp, figure dans le pack « Conformité et outillage » ; le pack « IA et headless » le suggère.
4. **Répartition des 8 autres plugins sans recoupement** : tous restent seuls, sans pack.
   - channel-access et registration-approval : version avant 1.0, PHP 8.3, gestes sur des entités de l'application ; registration-approval tire déjà channel-access par Composer. À reconsidérer à la 1.0.
   - order-hold et trade-credit : trade-credit tire order-hold ; un pack de deux plugins n'apporte rien de plus qu'une seule commande.
   - container-fill, punchout-cxml : usages isolés, `suggest` vers des plugins exclus.
   - customer-asset-management : avant 1.0, `suggest` vers un plugin exclu.
   - company-insights : exige company-accounts, plugin exclu des packs.
5. **Aucune publication tant que Thibaut n'a pas donné son GO** : chaque métapaquet est un nouveau dépôt et un nouveau paquet, soumis à Packagist par Thibaut.

## Réalisation proposée (stories de 8 points au plus)

| Ordre | Story | Points | Contenu | Dépend de |
|-------|-------|--------|---------|-----------|
| 1 | Métapaquets « IA et headless » et « Conformité et outillage » | 3 | deux dépôts (GO requis), `composer.json` de type `metapackage` avec bornes `^` et `suggest`, README FR et EN listant les gestes restants (tableau ci-dessus), `composer validate` | GO de Thibaut |
| 2 | Installation réelle d'un pack en CI | 5 | projet Sylius vierge, `composer require` du métapaquet, gestes du README appliqués, migrations, `lint:container`, `doctrine:schema:validate` ; matrice Sylius 2.2 et 2.3 ; échec si un membre sort du pack ou casse la résolution | 1 |
| 3 | Recettes Flex des deux métapaquets | 5 | manifeste (bundles, `config/packages`, `config/routes`), essai dans un projet jetable avec Flex, pull request sur `symfony/recipes-contrib` (GO requis, par Thibaut) | 2 publiée sur Packagist |
| 4 | Page « Installer un pack » dans la démo | 2 | page de documentation et lien depuis le guide d'intégration, ton neutre (`PublicTextCharter`), sans comparaison | 1 |

Total : 15 points, dont 5 conditionnés à une décision de publication. L'ordre 1, 2, 4 se tient sans aucune publication de recette.

## Conséquences

- Un pack se construit à moindre coût (deux fichiers par pack) et se met à jour par simple relèvement de bornes.
- Le pack n'est « clé en main » qu'avec la recette (story 3) : avant elle, le README doit dire franchement les gestes restants.
- La maintenance d'un pack devient un coût régulier : un plugin qui sort une version majeure impose de relire la borne du pack et de rejouer la CI de la story 2.
- Les packs fixent un plancher commun : PHP 8.2 et Sylius 2.2 pour les membres retenus. Entrer un plugin PHP 8.3 relèverait ce plancher pour tous.
- Le classement US-063 devient une entrée de la CI des packs : changer la classe d'un plugin demande de revoir la composition.
- ai-catalog-copilot pose `symfony/ai-bundle ^0.14` : le pack « IA et headless » ne s'installe pas à côté d'un paquet qui impose une autre version mineure de Symfony AI, tant que celle-ci n'est pas stable.

## Questions ouvertes

1. Noms des paquets (`sylius-pack-ai-headless`, `sylius-pack-compliance-tooling`) et dépôts : un par pack, soumis à Packagist par Thibaut. À valider.
2. Forme : accepter la combinaison métapaquet puis recette, ou s'arrêter au métapaquet (stories 1, 2 et 4, 10 points) ?
3. La recette sur un métapaquet est-elle bien appliquée par Flex pour un paquet venu de Packagist hors contrib ? Point non vérifié : à tester dans la story 3 avant toute pull request.
4. Les pull requests sur `symfony/recipes-contrib` sont publiques : y a-t-il un accord pour les ouvrir sous le nom de Thibaut ?
5. Ouvrir un troisième pack (accès et inscription : channel-access, registration-approval) quand les deux plugins passent en 1.0 ?
6. Le spike n'a pas essayé d'installation réelle (voir « Ce que l'essai ne prouve pas ») : accepter de le reporter à la story 2 ?

## Décision (2026-10-23)

Thibaut accepte la décision proposée : métapaquet d'abord, recette Flex ensuite ; les deux packs tels que composés ci-dessus ; la réalisation en quatre stories, créées au backlog : [US-175](../../project-management/backlog/user-stories/US-175.md) (métapaquets, 3), [US-176](../../project-management/backlog/user-stories/US-176.md) (installation réelle en CI, 5), [US-177](../../project-management/backlog/user-stories/US-177.md) (recettes Flex, 5), [US-178](../../project-management/backlog/user-stories/US-178.md) (page dans la démo, 2). Les questions ouvertes 1 (noms des paquets) et 4 (pull request sur recipes-contrib) restent à trancher au cadrage d'US-175 et d'US-177 ; toute création de dépôt, tag, publication ou pull request publique attend le GO de Thibaut.

## Résultat de l'essai Flex (US-177, 2026-10-24)

Réponse à la question ouverte 3, consignée par T-177-03 : **poursuite**, la recette n'est pas abandonnée.

- Flex applique la recette d'un paquet de type `metapackage` : bundles déclarés, fichiers de `config/packages` et `config/routes` écrits, le tout consigné dans `symfony.lock`. `composer remove` retire bundles et fichiers. Un fichier déjà présent dans l'application n'est pas écrasé à l'installation.
- Essai fait avec le paquet en dépôt `path` et la recette servie par un endpoint local, et non avec un paquet venu de Packagist par `symfony/recipes-contrib`. Ce dernier chemin ne sera vérifié qu'après la publication de la recette.
- Limite observée : à `composer remove`, Flex supprime aussi un fichier que l'application avait écrit sous le même nom avant l'installation, parce qu'il le consigne dans `symfony.lock`. Les README des deux packs le disent.
- La CI des deux packs rejoue la recette (job `recipe`, Sylius 2.2 et 2.3) et l'aller-retour installation puis retrait (job `flex-trial`).
- Pull request sur `symfony/recipes-contrib` (question ouverte 4) : ouverte le 2026-10-24 après le GO de Thibaut ([#2094](https://github.com/symfony/recipes-contrib/pull/2094)). Les recettes sont rangées sous `0.1`, la version publiée des packs.
