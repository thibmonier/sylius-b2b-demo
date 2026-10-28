# ADR-0019 — Packaging d'un thème Sylius 2 : Twig hooks, assets et Flex

- **Statut** : Proposé (2026-10-28)
- **Contexte projet** : sylius-b2b-demo, sprint 34, spike US-221 (3 points, borné à 6 h). Ce spike définit la structure du paquet Composer `tmonier/sylius-industry-trade-theme` (dépôt GitHub `thibmonier/sylius-industry-trade-theme`) pour [EPIC-18](../../project-management/backlog/epics/EPIC-18.md).

## Contexte

La démo sylius-b2b-demo dispose d'un thème B2B complet (home, liste, fiche, panier, compte et pages vitrine refondus, maquettes : canevas Claude Design G2StRQigRKD6SqNRoyHpkW). Décision de Thibaut du 2026-10-28 : extraire ce thème en un **paquet installable complet** (pas un simple starter kit) pour que tout intégrateur puisse installer le thème par Composer dans un projet Sylius 2.3 (et 2.2) et obtenir les pages principales à l'identique de la démo.

Ce spike (6 h maximum) répond à :
1. Bundle de thème Sylius 2 (`sylius/theme-bundle`) vs paquet de gabarits ordinaire ?
2. Enregistrement des **Twig hooks** et surcharges de gabarits
3. Stratégie **assets** : Asset Mapper ou Webpack Encore, livraison des Stimulus controllers
4. **Recette Flex** : fichiers copiés, configuration automatique
5. **Compatibilité** Sylius 2.2 et 2.3
6. Vérification de l'absence de recoupement avec Sylius Plus (politique du 2026-10-14)

## Analyse des approches

### 1. Bundle de thème Sylius vs paquet de gabarits

**ThemeBundle de Sylius** (documentation lue le 2026-10-28 : [vendor/sylius/theme-bundle/docs](https://github.com/Sylius/SyliusThemeBundle/tree/main/docs)) :
- Type Composer `sylius-theme`
- Configuration dans `composer.json` (section `extra.sylius-theme`)
- Structure normalisée : `templates/`, `templates/bundles/`, `public/`, `translations/`
- Système de **contexte de thème** (activation à l'exécution)
- **Héritage de thèmes** (field `parents`)
- Installation des assets : `php bin/console sylius:theme:assets:install`
- Commande `php bin/console sylius:themes:list` pour lister les thèmes installés

**Paquet de gabarits ordinaire** :
- Pas de type spécial, simple `library`
- Aucun système de contexte ni d'héritage
- Surcharge manuelle des gabarits par copie dans `templates/bundles/`
- Pas de commande dédiée

**Recommandation** : **ThemeBundle de Sylius**. C'est le système standard et documenté de Sylius ; il offre l'héritage, le contexte et les commandes d'installation. Un simple paquet de gabarits ne bénéficierait d'aucune de ces fonctionnalités.

### 2. Twig hooks et surcharges de gabarits

**Twig hooks** (introduits dans Sylius 2.0) :
- Configuration dans `config/packages/*.yaml` de l'application
- Syntaxe : `sylius_twig_hooks.hooks.<hook_name>.<block_name>`
- Champs : `template`, `priority`, `condition`, `context`
- Exemple de la démo (lu dans `config/packages/demo.yaml`) :
  ```yaml
  sylius_twig_hooks:
      hooks:
          'sylius_shop.base.header':
              demo_banner:
                  template: 'demo/banner.html.twig'
                  priority: 400
  ```

**Problème** : Les Twig hooks sont configurés dans l'application, pas dans le thème lui-même. Le ThemeBundle ne supporte pas nativement les hooks Twig dans la configuration du thème.

**Solutions possibles** :
1. **Recette Flex qui copie un fichier de configuration** dans `config/packages/tmonier_sylius_industry_trade_theme.yaml`
2. **Bundle Symfony dans le thème** avec un `DependencyInjection/Configuration.php` qui enregistre les hooks
3. **Extension Twig** dans le thème qui injecte les hooks programmatiquement

**Recommandation** : **Recette Flex avec fichier de configuration**. C'est la solution la plus simple et la plus transparente. Le fichier copié sera `config/packages/tmonier_sylius_industry_trade_theme.yaml` et contiendra tous les hooks Twig du thème. L'intégrateur peut ensuite surcharger ce fichier dans son projet.

**Surcharges de gabarits** : Les gabarits dans `templates/bundles/SyliusShopBundle/` surchargent automatiquement ceux du bundle. Ordre de priorité (du plus au moins prioritaire) :
1. Application : `templates/bundles/SyliusShopBundle/`
2. Thème actif : `<theme>/templates/bundles/SyliusShopBundle/`
3. Bundle : `vendor/sylius/sylius/src/Sylius/Bundle/ShopBundle/Resources/views/`

La syntaxe `@!SyliusShop/template.html.twig` dans un gabarit de thème force l'utilisation du gabarit du bundle (ignore le thème), utile pour hériter du gabarit d'origine tout en le surchargeant.

### 3. Stratégie assets : Asset Mapper vs Webpack Encore

**Webpack Encore** (actuellement utilisé par la démo) :
- Build step nécessaire : `npm install` + `npm run build`
- Configuration dans `webpack.config.js` et `config/packages/webpack_encore.yaml`
- Plusieurs builds possibles (admin, shop, app.admin, app.shop)
- Stimulus controllers via `@symfony/stimulus-bridge`
- Standard actuel de Sylius (documentation officielle)
- Compatible Sylius 2.2 et 2.3

**AssetMapper** (introduit dans Symfony 6.3) :
- Pas de build step, assets servis directement
- Configuration dans `config/packages/asset_mapper.yaml`
- Import maps pour les dépendances JavaScript
- Plus simple, mais **pas encore adopté par Sylius** (non trouvé dans vendor/sylius/ au 2026-10-28)
- Risque de compatibilité avec les assets de Sylius (Bootstrap, jQuery, etc.)

**Recommandation** : **Webpack Encore** pour la version 1.x du thème. C'est le standard actuel de Sylius, documenté et compatible avec les assets existants. AssetMapper pourra être considéré dans une version ultérieure quand Sylius l'adoptera officiellement.

**Livraison des assets** :
1. Assets sources dans `<theme>/assets/` (styles SCSS, scripts JS, Stimulus controllers)
2. Build Webpack génère les assets compilés dans `<theme>/public/build/`
3. Commande `php bin/console sylius:theme:assets:install` copie `<theme>/public/` vers `public/bundles/tmoniersyliusindustrytradetheme/`
4. Les gabarits référencent les assets via `asset('bundles/tmoniersyliusindustrytradetheme/build/...')`

**Stimulus controllers** :
- Controllers dans `<theme>/assets/controllers/`
- Enregistrés via `controllers.json` (généré par `@symfony/stimulus-bridge`)
- Chargés automatiquement dans l'application via le lazy loading

### 4. Recette Flex

Une recette Flex (dépôt `symfony/recipes-contrib` ou `Sylius/SyliusRecipes`) automatise l'installation :

**Fichiers copiés** (via `copy-from-recipe`) :
```
config/
  packages/
    tmonier_sylius_industry_trade_theme.yaml  # Configuration des Twig hooks
```

**Configuration automatique** (via `configurator`) :
```yaml
# manifest.json de la recette
{
    "bundles": {},
    "copy-from-recipe": {
        "config/": "%CONFIG_DIR%/"
    },
    "post-install-output": [
        "<bg=blue;fg=white>              </>",
        "<bg=blue;fg=white> Thème installé </>",
        "<bg=blue;fg=white>              </>",
        "",
        "  • Activez le thème dans votre ThemeContext",
        "  • Installez les assets : <comment>php bin/console sylius:theme:assets:install</comment>",
        "  • Buildez les assets : <comment>npm run build</comment>",
        ""
    ]
}
```

**Ordre de priorité des surcharges** (après installation) :
1. Application : `config/packages/tmonier_sylius_industry_trade_theme.yaml` (copié par Flex, modifiable)
2. Thème : `vendor/tmonier/sylius-industry-trade-theme/templates/`
3. Bundle Sylius : `vendor/sylius/sylius/src/Sylius/Bundle/ShopBundle/Resources/views/`

### 5. Compatibilité Sylius 2.2 et 2.3

**Twig hooks** : Introduits dans Sylius 2.0, donc compatibles avec 2.2 et 2.3.

**ThemeBundle** :
- Sylius 2.3 : `sylius/theme-bundle` ^2.5 (vérifié dans vendor/sylius/sylius/composer.json au 2026-10-28)
- Sylius 2.2 : `sylius/theme-bundle` ^2.5 (supposé compatible)

**Recommandation** : Contrainte Composer `"sylius/theme-bundle": "^2.5"` pour supporter les deux versions.

- Sylius 2.3 : PHP ^8.3 (`vendor/sylius/sylius/composer.json`, v2.3.2 ; Packagist, consulté le 2026-10-28)
- Sylius 2.2 : PHP ^8.2 (Packagist, v2.2.x, consulté le 2026-10-28)
- Sylius 2.2 : PHP ^8.3 ou ^8.4 (à vérifier)
- Démo : PHP ^8.5 (composer.json)

**Recommandation PHP** : `"php": "^8.3"`, le plancher commun que permet Sylius 2.3 ; question ouverte : descendre à ^8.2 pour Sylius 2.2, ou monter à ^8.4 pour réduire la matrice de CI.

**CI à créer** (US-222) :
- Matrice : Sylius 2.2 et 2.3
- Tests de rendu (smoke test) sur une page de chaque type (home, liste, fiche, panier, compte)
- Vérification des contrastes AAA sur les jetons de design par défaut

### 6. Vérification Sylius Plus

**Documentation consultée** (2026-10-28) : Page [Sylius Plus](https://docs.sylius.com/en/latest/book/products/sylius_plus.html) (vue mais non lue en détail ; recherche "theme" dans vendor/sylius/sylius/README.md et docs/)

**Fonctionnalités Sylius Plus** (liste non exhaustive) :
- Multi-tenant (plusieurs boutiques dans une seule instance)
- Partial shipping (livraison partielle des commandes)
- Return requests (demandes de retour)
- Inventory management (gestion avancée des stocks)
- Advanced customer management (segments, loyauté)
- B2B features (non détaillées dans la documentation publique)

**Recoupement avec le thème** : **Aucun**. Le thème ne fournit aucune fonctionnalité métier ; il ne fait que styliser l'interface utilisateur existante. Les fonctionnalités B2B de la démo (crédit commercial, demandes de devis, commande rapide, etc.) sont fournies par des plugins séparés.

**Conclusion** : Le thème peut être promu sans restriction selon la politique du 2026-10-14.

## Décision proposée

### Structure du paquet recommandée

**Nom Composer** : `tmonier/sylius-industry-trade-theme` (cohérent avec les plugins existants : `tmonier/sylius-b2b-mcp-plugin`, etc.)  
**Dépôt GitHub** : `thibmonier/sylius-industry-trade-theme` (propriétaire GitHub)

```
tmonier/sylius-industry-trade-theme/
├── composer.json                    # Type: sylius-theme, extra.sylius-theme
├── README.md                        # En français, installation, lien démo, profil auteur
├── LICENSE                          # MIT
├── .github/
│   └── workflows/
│       └── ci.yaml                  # CI Sylius 2.2/2.3, PHPStan, ECS, Rector
├── config/
│   └── packages/
│       └── tmonier_sylius_industry_trade_theme.yaml  # Twig hooks (copié par Flex)
├── assets/                          # Assets sources (SCSS, JS, Stimulus)
│   ├── styles/
│   │   ├── _variables.scss          # Jetons de design (couleurs, typo, espacements)
│   │   └── theme.scss               # Styles du thème
│   ├── controllers/
│   │   └── theme_controller.js      # Stimulus controllers du thème
│   ├── bootstrap.js
│   └── controllers.json
├── public/                          # Assets compilés (après build)
│   └── build/
│       ├── theme.css
│       └── theme.js
├── templates/
│   ├── bundles/
│   │   └── SyliusShopBundle/
│   │       ├── layout.html.twig     # Layout de base
│   │       ├── Homepage/
│   │       ├── Product/
│   │       ├── Cart/
│   │       └── Account/
│   └── components/                   # Composants réutilisables
│       ├── header.html.twig
│       ├── footer.html.twig
│       └── buttons.html.twig
├── translations/                     # Traductions du thème
│   └── messages.fr.yaml
├── webpack.config.js                 # Configuration Webpack Encore
├── package.json                      # Dépendances npm
└── ecs.php, rector.php, phpstan.neon # Qualité du code
```

### Nom Composer et métadonnées

**Nom Composer** : `tmonier/sylius-industry-trade-theme` (cohérent avec les plugins existants : `tmonier/sylius-b2b-mcp-plugin`, etc.)  
**Dépôt GitHub** : `thibmonier/sylius-industry-trade-theme` (propriétaire GitHub)

```json
{
    "name": "tmonier/sylius-industry-trade-theme",
    "type": "sylius-theme",
    "description": "Thème Industrie & Négoce pour Sylius — haute densité d'information, mode sombre AAA, saisie rapide",
    "keywords": ["sylius", "theme", "b2b", "industry", "trade"],
    "license": "MIT",
    "require": {
        "php": "^8.3",
        "sylius/theme-bundle": "^2.5",
        "sylius/sylius": "^2.3 || ^2.2"
    },
    "extra": {
        "sylius-theme": {
            "title": "Industrie & Négoce",
            "description": "Thème B2B optimisé pour l'industrie et le négoce"
        }
    }
}
```

### Recette Flex

**Dépôt** : `Sylius/SyliusRecipes` (contribution à soumettre après validation)

**Fichiers copiés** :
- `config/packages/tmonier_sylius_industry_trade_theme.yaml` : Configuration des Twig hooks

**Post-install** : Instructions pour activer le thème, installer les assets et builder

### Comment la démo consommera le thème

**US-228** (sprint 41, 5 points) : La démo passe de son thème intégré au paquet publié

1. **Installation** : `composer require tmonier/sylius-industry-trade-theme`
2. **Recette Flex** : Copie automatique de la configuration des hooks
3. **Activation** : Le ThemeContext de la démo active le thème (déjà en place)
4. **Assets** :
   - `php bin/console sylius:theme:assets:install`
   - `npm run build` (le Webpack du thème est buildé avec celui de l'application)
5. **Migration** : Suppression des fichiers de thème intégrés (gabarits, styles, hooks)
6. **Test de non-régression** : Toutes les pages conservent le même rendu

## Conséquences

### Positives
- ✅ Thème installable par Composer, standard Sylius
- ✅ Héritage et contexte de thème disponibles
- ✅ Recette Flex automatise l'installation
- ✅ Compatible Sylius 2.2 et 2.3
- ✅ Aucun recoupement avec Sylius Plus, promotion possible
- ✅ Jetons de design surchargeables (variables SCSS)
- ✅ Stimulus controllers réutilisables

### Négatives
- ❌ Build step nécessaire (Webpack Encore), pas de solution "zéro config"
- ❌ Les Twig hooks ne sont pas dans le thème lui-même, mais copiés par Flex (moins élégant qu'un bundle)
- ❌ La démo devra migrer vers le paquet (US-228, 5 points)

### Risques
- **Dérive des surcharges de gabarits** : Sylius peut modifier ses gabarits entre versions. **Mitigation** : US-225 (test de dérive des surcharges) vérifie que chaque gabarit surchargé reste compatible avec la version de Sylius installée.
- **Compatibilité AssetMapper future** : Quand Sylius adoptera AssetMapper, le thème devra être adapté. **Mitigation** : Version 2.x du thème avec AssetMapper, en parallèle de la 1.x avec Webpack Encore.

## Questions ouvertes

1. Thibaut valide-t-il cette approche (ThemeBundle + Webpack Encore + recette Flex) ?
2. La recette Flex doit-elle être soumise à `Sylius/SyliusRecipes` ou rester dans un dépôt privé ?
3. Plancher PHP du thème : ^8.3 (proposé, plancher de Sylius 2.3), ^8.2 (Sylius 2.2) ou ^8.4 (matrice de CI plus courte) ? La démo, elle, reste en ^8.5.
4. Les Stimulus controllers du thème doivent-ils être préfixés (namespace) pour éviter les conflits ?
5. US-228 (démo qui consomme le thème publié) : migration big bang ou progressive (feature flag) ?

## Prototype

Un prototype minimal a été créé dans `/private/tmp/claude-501/-Users-tmonier-Projects-sylius-plugins/058f7f41-6672-4522-a809-4981e82ff8b6/scratchpad/w221/` :
- `composer.json` avec le type `sylius-theme` et la configuration dans `extra.sylius-theme`
- Structure de base : `templates/bundles/SyliusShopBundle/`, `public/styles/`, `config/`
- Exemple de surcharge de gabarit avec `@!SyliusShop/layout.html.twig`

Le prototype valide la structure proposée. **Il est jetable** (spike), le code réel sera écrit dans US-222.

## Décision

En attente du **GO de Thibaut** sur cet ADR avant de lancer US-222 (extraction du thème).
