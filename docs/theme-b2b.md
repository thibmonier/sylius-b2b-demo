# Thème B2B du shop (US-013)

Thème **additif** au thème officiel Sylius 2.3, actif uniquement sur le canal B2B (le B2C garde le thème par défaut).
Règle : on **ajoute** des hookables, on ne remplace jamais ceux des plugins (le logo officiel n'est que conditionné au canal B2C).

## Où modifier

| Quoi | Où |
|------|----|
| Feuille de style (variables Bootstrap, marine/ambre) | `assets/shop/styles/` (entrée Encore `app-shop-b2b-entry`, `webpack.config.mjs`) |
| Hooks ajoutés | `config/packages/demo_theme_b2b.yaml` (tous préfixés `b2b_`, condition `not is_granted('DEMO_CONSUMER_CHANNEL')`) |
| Templates | `templates/shop/theme_b2b/` |
| Données (conditionnement, prix « dès ») | `src/Demo/Theme/` (fonctions Twig `app_b2b_packaging_unit`, `app_b2b_tier_from_price`) |
| Libellés FR/EN | `translations/messages.{fr,en}.yaml`, clés `app.theme_b2b.*` |

## Couleurs et composants

- **Palette** (`assets/shop/styles/_variables.scss`, toutes les paires ≥ 4,5:1) : marine `#1f3a5f` (couleur principale), **brun du logo** `#9a3f00` en 3e couleur (survols, liens au survol, icônes et filets des tuiles, barre des titres de section, mention HT), gris `#4b5563` pour le texte secondaire.
- **Boutons** : le thème officiel dessine des boutons menthe à texte noir ; le thème B2B redéfinit toutes les couleurs de texte (texte blanc). Survol du bouton plein : fond brun ; survol du bouton contour : fond marine.
- **Header** : le logo B2B est dans sa propre colonne (`.col`), comme le logo officiel, pour que connexion, liste d'envies et panier restent alignés à droite.
- **Tuiles de l'accueil** (`.b2b-tile`) : icône Tabler dans une pastille brune, titre marine, filet brun, lien d'action avec flèche ; la tuile entière est cliquable quand elle n'a qu'un lien.
- **Liens externes** : `templates/shared/_external_link.html.twig` ajoute l'icône du service (GitHub, Packagist, LinkedIn, X, sinon « lien externe ») ; à utiliser pour tout lien sortant.
- **Icônes** : importées dans `assets/icons/tabler/` (`bin/console ux:icons:import tabler:<nom>`), servies sans téléchargement à l'exécution. Importer toute nouvelle icône.
- Le bandeau de démo, la connexion en un clic et la page Plugins existent aussi sur le canal B2C, où la feuille B2B n'est pas chargée : ils n'utilisent que des utilitaires Bootstrap.

## Cartes produit (liste)

SKU, conditionnement (attribut `packaging_unit`), disponibilité (variantes suivies : « En stock » / « Sur commande »), mention HT, et prix « dès » = prix unitaire le plus bas des paliers du plugin tier-pricing (`TierPriceCalculatorInterface::table()`, dernière ligne). Sans palier, sans attribut ou sans variante : la zone est omise (aucun libellé vide).
Pas de N+1 : le conditionnement est lu en une requête pour tous les produits, les règles de palier viennent du cache du plugin.

## Fiche produit

Bloc référence (SKU, conditionnement, mention HT). Les blocs des plugins (paliers, GPSR, etc.) restent en place. Pas de bloc « documents » : aucune source de documents produit n'existe dans les plugins installés (la sécurité GPSR est déjà affichée par le plugin).

## Tests

`tests/Unit/Theme/` : rendu des templates avec données factices (présent / absent / vide), calcul du prix « dès », lecture groupée du conditionnement, et garde-fou « aucun hookable remplacé ».

## Accueil B2B et page Plugins (US-014)

- **Accueil B2B** : hookables `b2b_hero`, `b2b_quick_access`, `b2b_journeys` sur `sylius_shop.homepage.index` (canal B2B uniquement) ; la bannière du cœur (`banner`) et le bloc « Nouvelle collection » (`new_collection`, US-045, condition dans `config/packages/tmonier_plugins_overrides.yaml` car channel-access pose aussi la sienne) ne sont conservés que sur B2C, les autres blocs Sylius restent. Gabarits `templates/shop/theme_b2b/home_*.html.twig`, libellés `app.home_b2b.*`.
- **Page Plugins** : `/{_locale}/plugins` (route `app_demo_plugins`, publique, les deux canaux), `templates/shop/plugins/index.html.twig`.
- **Registre unique** : `config/demo/plugins.yaml` (paramètre `demo.plugin_catalog`) lu par `App\Demo\Plugin\DemoPluginCatalog` (`all()`, `get($slug)`, `byCategory()`). Ajouter un plugin : une entrée `slug` (= nom du dépôt), `name`, `category` (purchasing, billing, catalog, integration, compliance, admin), `description.fr`/`description.en` (tirées de son composer.json) et `demo_route` (nom d'une route boutique existante, ou `null`). Une entrée incomplète lève une exception citant le plugin ; le test unitaire vérifie 23 entrées = `composer.json`.
