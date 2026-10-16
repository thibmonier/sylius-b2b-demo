# Sécurité de l'admin de démo (US-035)

Le compte back-office `sophie@fournitures-pro.example` est public (identifiants affichés sur `/demo/accounts`). Sylius CE n'a pas de droits fins : la restriction est appliquée **côté serveur**, par `App\Demo\Admin\DemoAdminWriteGuard`, jamais par le masquage de boutons.

## Fonctionnement

- Tous les comptes back-office du catalogue `config/demo/accounts.yaml` (source unique) reçoivent `ROLE_DEMO_ADMIN` à chaque chargement des fixtures (fixture `demo_admin_role`, priorité -1, juste après `admin_user`). Aucune comparaison d'e-mail en dur.
- Le garde écoute `kernel.request` juste après le pare-feu (priorité 7) : le contrôleur n'est jamais atteint pour une écriture refusée, qu'elle vienne d'un formulaire, d'un POST construit à la main avec un jeton CSRF valide, d'une surcharge `_method` / `X-HTTP-Method-Override` ou d'une action de masse. Une requête est une écriture dès que sa méthode HTTP réelle **ou** effective n'est pas GET/HEAD/OPTIONS (un POST déguisé en GET compte comme une écriture).
- Décision par **liste blanche** de noms de route (`DemoAdminWritePolicy`) : toute écriture non classée, y compris une route ajoutée plus tard par un plugin, est **refusée par défaut**. Toute suppression (`DELETE`, y compris `bulk-delete`) est refusée.
- Refus : message de démo FR/EN selon la locale de l'admin (domaine `demo_admin`). Formulaire posté depuis une page de l'admin : redirection vers cette page avec un flash d'erreur ; sinon (pas de Referer, autre hôte) page 403 explicite (`templates/admin/demo/forbidden.html.twig`) ; requête AJAX ou `Accept: application/json` : 403 JSON.
- La lecture n'est jamais bloquée : Sophie consulte toutes les ressources, y compris les sensibles.

## Matrice des écritures

| Famille (routes) | POST/PUT/PATCH | DELETE / bulk-delete | Décision |
|---|---|---|---|
| Administrateurs, profil, avatar (`sylius_admin_admin_user_*`) | refusé | refusé | **Refus** : mot de passe, e-mail et locale de Sophie inchangés |
| Canaux (`sylius_admin_channel_*`), Factur-X du canal, mode d'accès du canal (US-019) | refusé | refusé | **Refus** : Sophie ne peut pas ouvrir le canal B2B (prix masqués) |
| Moyens de paiement / passerelles (`sylius_admin_payment_method_*`) | refusé | refusé | **Refus** |
| URL de webhooks, secrets, rejeu (`tmonier_webhook_admin_*`) | refusé | refusé | **Refus** (pas d'URL sortante saisie par le public) |
| PunchOut : acheteurs, secrets (`tmonier_punchout_admin_*`) | refusé | refusé | **Refus** |
| Clients et comptes boutique (`sylius_admin_customer_*`, `shop_user`) | refusé | refusé | **Refus** : le formulaire porte le mot de passe des comptes de démo |
| Entreprises, membres, crédit commercial, paliers, GPSR, flux, conteneurs, connexions MCP, paramètres | refusé | refusé | **Refus** (non classé) |
| Taxes, zones, pays, devises, langues, taux de change, expéditions, promotions | refusé | refusé | **Refus** (non classé) |
| Mots de passe oubliés (`sylius_admin_request_password_reset`, `sylius_admin_password_reset`) | refusé pour tous, y compris anonymes | n/a | **Refus** : sinon n'importe qui réinitialise le mot de passe du compte partagé |
| Catalogue : produits, variantes, attributs, options, associations, avis, taxons (`sylius_admin_product_*`, `sylius_admin_taxon_*`, `sylius_admin_ajax_product_*`) | autorisé | refusé | **Édition autorisée** |
| Commandes et workflow : édition, annulation, paiement, expédition, remboursement (`sylius_admin_order_*`, `sylius_admin_payment_complete`, `sylius_admin_shipment_ship`, `sylius_refund_*`, `tmonier_admin_order_*`) | autorisé | refusé | **Autorisé** |
| Notes, devis, rétractations, validation d'achat, suggestions IA de démo | autorisé | refusé | **Autorisé** |
| Inscriptions à valider (`tmonier_registration_approval_admin_decide`, US-020) | autorisé (approuver / refuser) | refusé | **Autorisé** : seule cette route du plugin est classée ; le jeton CSRF et le rôle d'administration restent contrôlés par le plugin, le reste du plugin est refusé par défaut |
| Actions de masse (`tmonier_bulk_actions_admin_*`) | codes `order_*`, `payment_*`, `shipment_*` seulement | n/a | Autorisé pour les commandes ; refus pour clients, produits-canaux |
| Composants live, autocomplétion, tableau de bord, usurpation boutique | autorisé | n/a | Autorisé (aucune persistance sensible) |
| API admin (`/api/v2/admin/*`) | refusé pour `ROLE_DEMO_ADMIN` | refusé | Défense en profondeur : Sophie n'a de toute façon pas `ROLE_API_ACCESS` |

Jetons API : le jeton JWT est sans état (rien n'est créé en base) et inutilisable, car Sophie n'a pas `ROLE_API_ACCESS` (pare-feu `api_admin`).

## Contenu édité par Sophie

Les champs modifiables (nom, descriptions, taxons, attributs) sont rendus par Twig avec l'échappement automatique : aucun `|raw` sur ces données dans le thème ni dans les plugins. Les données structurées JSON-LD sont encodées avec échappement de `<` (`<`). `tests/Functional/Demo/DemoAdminBypassTest` vérifie sur la page boutique analysée qu'aucun script ni gestionnaire d'événement injecté n'est présent et que le JSON-LD reste valide.

## Risques acceptés

- **Défiguration** : Sophie peut modifier produits, commandes et taxons (texte déformé, prix changés, produits désactivés, commandes annulées). La remise à zéro de 03:00 (Europe/Paris) répare tout, au plus tard le lendemain.
- **Contenu choquant** saisi par un visiteur : même réparation nocturne ; la démo est derrière la porte d'accès privée (US-032).
- **Lecture des secrets affichés** par l'admin (secret de signature de webhook à la création, par exemple) : valeurs de démonstration, jamais de secret réel dans le dépôt.
- **Verrouillage temporaire** : une action `PATCH` sur une ressource autorisée (ex. désactivation d'un produit) peut vider une page du catalogue jusqu'au reset.
- **Composants live** (`sylius_admin_live_component`) : autorisés car ils ne font que re-rendre des formulaires ; une future action live qui écrirait serait à reclasser.
- **Limite de taille** des champs riches : celle des colonnes et des contraintes Sylius, pas de limite applicative supplémentaire.
