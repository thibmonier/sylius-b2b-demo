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

## Cartes produit (liste)

SKU, conditionnement (attribut `packaging_unit`), disponibilité (variantes suivies : « En stock » / « Sur commande »), mention HT, et prix « dès » = prix unitaire le plus bas des paliers du plugin tier-pricing (`TierPriceCalculatorInterface::table()`, dernière ligne). Sans palier, sans attribut ou sans variante : la zone est omise (aucun libellé vide).
Pas de N+1 : le conditionnement est lu en une requête pour tous les produits, les règles de palier viennent du cache du plugin.

## Fiche produit

Bloc référence (SKU, conditionnement, mention HT). Les blocs des plugins (paliers, GPSR, etc.) restent en place. Pas de bloc « documents » : aucune source de documents produit n'existe dans les plugins installés (la sécurité GPSR est déjà affichée par le plugin).

## Tests

`tests/Unit/Theme/` : rendu des templates avec données factices (présent / absent / vide), calcul du prix « dès », lecture groupée du conditionnement, et garde-fou « aucun hookable remplacé ».
