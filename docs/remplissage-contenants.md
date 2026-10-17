# Remplissage des contenants (container-fill) dans la démo

US-044. Le plugin `tmonier/sylius-container-fill-plugin` vit dans la démo grâce à deux fixtures de la suite `demo` : les presets du plugin, puis des données dérivées des CSV du catalogue.

## Ce qui est chargé

| Fixture | Priorité | Contenu |
|---|---|---|
| `tmonier_container_fill_container_types` (plugin, telle quelle) | -5 | 13 presets indicatifs : palettes EUR, demi, UK et US ; semi-remorques standard et méga ; US 53 ft ; conteneurs 20 ft, 40 ft, 40 ft HC ; cartons S, M, L. Noms en `fr_FR` et `en_US`. |
| `demo_container_fill` (démo) | -70 | Données logistiques et capacités des 210 variantes, mode « couches » sur la palette EUR, deux règles sur le canal B2B. |

`tmonier_container_fill_demo` du plugin n'est **pas** chargée : produits en anglais seulement hors arborescence, règle sur tous les canaux (B2C compris) et acheteur `pallet@example.com` avec un mot de passe connu.

`demo_container_fill` passe après `demo_orders` (-60) : les commandes de l'historique sont terminées par la transition `complete`, que le garde-fou du plugin refuserait dès que les règles existent (petits colis, aucune palette complète). Les règles ne concernent que les paniers à venir ; les commandes passées restent valides.

Idempotence : chaque ligne est retrouvée avant d'être créée (variante, couple variante et type, règle par nom et canal). Recharger la fixture, ou `app:demo:reset` (purge puis rechargement de la suite), donne les mêmes nombres : 13 types, 210 données logistiques, 776 capacités, 2 règles. Durée ajoutée au rechargement : environ 0,4 s.

## Unités et conversions

Le plugin stocke tout en **millimètres et en grammes** ; les CSV sont en **centimètres et en kilogrammes** (la configuration `units` du plugin reste `cm` / `kg`, les valeurs des variantes Sylius ne sont pas converties).

- cm vers mm : ×10, arrondi à l'entier ;
- kg vers g : ×1000, arrondi à l'entier.

## Formule de capacité

`App\Demo\Logistics\ContainerCapacityCalculator` :

```
capacité = min( ⌊ volume utile × rendement ÷ volume unitaire ⌋ , ⌊ charge utile ÷ poids unitaire ⌋ )
volume utile = longueur × largeur × hauteur de chargement (palette) ou hauteur intérieure (carton)
unités par couche = ⌊ longueur × largeur × rendement ÷ (largeur × profondeur du produit) ⌋
```

Un produit sans poids ou sans dimension, ou plus grand que le contenant, n'a pas de capacité (jamais de division par zéro).

Exemples vérifiés à la main (tests `ContainerCapacityCalculatorTest`) :

| Produit | Unité (mm, g) | Palette EUR (1200 × 800 × 1656 mm, 1000 kg, 90 %) |
|---|---|---|
| VISS-001, boîte de vis | 80 × 50 × 50, 350 g | volume 7153, charge 2857 : **2857** |
| EMBA-001, carton A4 | 350 × 120 × 250, 2500 g | volume 136, charge 400 : **136** (9 par couche) |
| EPI-CASK-001, casque | 220 × 140 × 280, 380 g | volume 165, charge 2631 : **165** |

Carton S (300 × 200 × 150 mm, 10 kg, 90 %) pour VISS-001 : volume 40, charge 28 : **28**.

## Données logistiques par variante

- `unitsPerCase` : nombre lu dans `packaging_unit` (« boîte de 100 » : 100 ; « unité » : 1) ; poids du colis = unités × poids de la variante ;
- vente par colis entiers (`caseMultipleRequired`) pour les boîtes et lots de **50 à 100 unités** : les petits conditionnements restent achetables à l'unité ;
- capacités pour la palette EUR et les cartons S, M, L (les semi-remorques et conteneurs en dérivent) ;
- mode « couches » sur la palette EUR, avec hauteur de couche et unités par couche pour la famille **emballage**.

## Règles (canal B2B uniquement)

| Règle | Portée | Mode | Contenants |
|---|---|---|---|
| Emballage : palettes complètes ou camion complet | taxon `emballage` | `full_only` (bloquante) | palette EUR, semi-remorque 13,6 m |
| Visserie : cartons remplis à 20 % au moins | taxon `visserie` | `min_fill_percent` 20 | cartons S, M, L |

Aucune règle sur le canal B2C : ni jauge ni blocage. Le rôle `ROLE_TMONIER_CONTAINER_FILL_OVERRIDE` n'est accordé à aucun administrateur (testé).

## Bouton de commande du panier

Les plugins PunchOut et container-fill déclarent tous deux le hookable `checkout` du panier. Sans correctif, celui de PunchOut gagnait et le bouton n'était jamais désactivé par les règles. `config/packages/tmonier_plugins_overrides.yaml` (chargé après `tmonier_plugins.yaml`) pointe vers `templates/shop/container_fill/cart_checkout.html.twig`, qui reprend la logique de PunchOut (pas de bouton pendant une session) et, sinon, le bouton du plugin (désactivé avec la raison).

## Parcours de démonstration

Compte : Claire (`claire@acme.example`), canal B2B. Catalogue des plugins : `/fr_FR/plugins`.

1. 10 × EMBA-001 (carton A4) : jauge de la palette EUR à 8 %, « limité par la hauteur de pile », commande désactivée avec la raison, bouton « Arrondir à la couche (18) » et vue de profil de la pile.
2. 117 × EMBA-001 (13 couches de 9) : palette complète, règle respectée, commande possible ; le plan de chargement apparaît sur la commande dans l'administration.
3. 5 × EMBA-008 (neuf par palette, sans données de couche) : le bouton propose d'ajouter 4 unités pour compléter la palette.
4. VISS-001 : quantité par pas de 100 sur la fiche produit ; 150 est refusé et le message cite le multiple attendu.
5. Léa (`lea@demo.example`), canal B2C : mêmes produits, aucune jauge, aucun blocage.

Limites : les presets sont des chiffres indicatifs ; le plan n'est pas un plan de chargement physique.

## Tests

- `tests/Unit/Demo/Logistics/` : calculateur et lecture de `packaging_unit` ;
- `tests/Functional/Demo/ContainerFillFixturesTest.php` : presets, règles, idempotence, rôle de dérogation ;
- `tests/Functional/Demo/ContainerFillDemoTest.php` : HTML servi du panier et de la fiche produit, transition `complete` refusée ou acceptée, plan de chargement dans l'administration, canal B2C, catalogue des plugins. Les paniers et commandes sont créés dans une transaction annulée.
