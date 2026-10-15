# Synthèse de recherche — démo B2B Sylius

> Phase d'analyse, 2026-10-15. Les rapports bruts sont dans `raw/` : A et B (audit des 21 plugins), C (thème, manques B2B, plugins communautaires), D (Railway). Le spike de résolution Composer a été fait par l'équipe.

## 1. Problème et objectifs

Les 21 plugins tmonier se testent chacun dans leur application de test et n'ont jamais été montrés ensemble. La démo doit :
- montrer un parcours B2B cohérent de bout en bout, du catalogue fermé jusqu'à la facture Factur-X, en passant par la validation, le crédit commercial, le devis et le PunchOut ;
- rendre visible la contribution de chaque plugin (badge sur chaque fonctionnalité) ;
- rester en ligne à faible coût, avec des données propres chaque matin (remise à zéro nocturne).

**Parties prenantes** : l'auteur (vitrine pour des missions et des clients), les prospects intégrateurs ou marchands B2B, les développeurs qui évaluent les plugins.

**Critères de succès** :
- les 21 plugins sont installés et actifs, et chacun a au moins un scénario visible ;
- un visiteur suit un scénario guidé sans aide, grâce aux comptes de démo pré-remplis ;
- les données sont remises à zéro chaque nuit sans intervention ;
- le coût reste sous un budget mensuel fixé (voir contraintes) ;
- aucun secret réel n'est exposé ; seuls des comptes de démo sont publiés.

## 2. Compatibilité des plugins (constats vérifiés)

- **Résolution Composer à blanc réussie** : Sylius 2.3.1, les 21 plugins, `sylius/invoicing-plugin` 2.3.0, `sylius/mcp-server-plugin` 0.2.0 (`symfony/mcp-bundle` 0.3), `symfony/ai-bundle` 0.14.1, `horstoeko/zugferd`. Cela marche avec Symfony 7.4.21 comme avec 8.0. Les deux versions de Symfony AI cohabitent.
- **agentic-feeds n'est pas sur Packagist** (404). Il faut le soumettre ou passer par un dépôt VCS.
- Les 21 plugins acceptent PHP 8.4 et Sylius 2.3. Aucun n'a d'assets JS à builder ; seul command-palette demande `assets:install`. Aucun ne touche `security.*`. Les migrations sont portables MySQL et PostgreSQL.
- **Bundles tiers à enregistrer** :
  - AiBundle ;
  - McpBundle et SyliusMcpServerPlugin, à charger avant b2b-mcp ;
  - KnpSnappy, KnpGaufrette, SyliusPdfGeneration et SyliusInvoicing, à charger avant Factur-X.
- **Pré-requis d'exécution** :
  - PHP 8.4 avec intl, gd, dom, zip, fileinfo, pdo et Argon2id ;
  - wkhtmltopdf (factures, et devis en PDF en option) ;
  - Symfony Workflow pour les graphes de commande (order-hold refuse winzou) ;
  - MySQL 8.0.16+ ou PostgreSQL 10+ avec un `server_version` réel (contrainte CHECK, SKIP LOCKED) ;
  - `router.default_uri` et `trusted_proxies` ;
  - HTTPS public (MCP, iframe PunchOut).
- **Tâches planifiées** : environ 12 commandes quotidiennes (purges, expirations, relances, recalculs), feeds toutes les 15 minutes, et l'envoi des webhooks (`tmonier:webhook:dispatch`) en boucle ou chaque minute. Aucun worker Messenger n'est obligatoire.
- **Le générateur IA `demo`** fait tourner le copilote sans clé d'API, ce qui convient à une démo publique.

## 3. Points d'interaction entre plugins (à traiter en conception)

| # | Point | Effet | Piste |
|---|-------|-------|-------|
| I1 | container-fill bloque le checkout (garde `complete` et redirection) | Bloque aussi les brouillons admin, quick-order, MCP et OrderRequest | Règles limitées à des produits ou un canal « palette », ou `workflow_guard: false` |
| I2 | Pendant une session punchout, `/quotes/{token}/checkout` et quick-order ne sont pas bloqués | Une commande peut être passée dans le shop pendant le PunchOut | Tester ; correctif dans punchout (ou quote / quick-order) si confirmé |
| I3 | Un OrderRequest cXML déclenche la réservation trade-credit, l'approbation et la garde quote | Comportement hors HTTP à vérifier | Scénario de test dédié |
| I4 | Avec company-accounts, le propriétaire du compte trade-credit est la société, alors que la fixture trade-credit est par client | Le crédit de démo ne couvre pas les membres | Jeu de données unifié |
| I5 | Les paliers tier-pricing modifient les totaux | Les seuils d'approbation (50 €) des fixtures n'ont plus de sens | Jeu de données unifié |
| I6 | Toutes les fixtures ont la priorité -100 | Ordre de chargement aléatoire | Suite de fixtures de la démo avec ses propres priorités |
| I7 | Hooks shop empilés (panier, fiche produit, boutons de commande) | Une surcharge applicative casserait les plugins | **Le thème ajoute, il ne remplace pas** ces hookables |
| I8 | Punchout exige `SameSite=None; Secure` pour l'iframe | Concerne toute l'application | Acceptable en HTTPS ; à décider (iframe montrée ou non) |
| I9 | withdrawal (rétractation) vise le B2C | Bouton présent sur toutes les commandes B2B | Canal B2C secondaire, ou désactivation par canal |
| I10 | `framework.lock` (flock) prependé par 6 plugins | Fusion à vérifier | `debug:config framework lock` au premier montage |
| I11 | README de punchout : `PUNCHOUT_SUPPLIER_ID` au lieu des variables imposées par son `config.yaml` | Doc fausse | Correctif dans le plugin punchout (README) |

## 4. Marché : manques B2B (référence Sylius Plus B2B Suite, Shopware, Oro)

Couvert par les plugins : comptes entreprise et rôles, validation d'achats, crédit commercial, prix dégressifs par groupe, commande rapide et réassort, devis, PunchOut, palettes, Factur-X, création de commande par l'admin, timeline, audit, IA.

| Manque | Importance pour la cohérence | Couverture possible |
|--------|------------------------------|---------------------|
| **Catalogue fermé** (connexion obligatoire configurable, prix masqués) | Très haute (demandé explicitement) | Aucun plugin communautaire Sylius 2 identifié : **nouveau plugin** ou code de la démo |
| **Référence de commande / PO number** au checkout (et centre de coût) | Haute (attendu par tout acheteur ; alimente Factur-X BT-10, PunchOut, devis) | **Nouveau plugin** |
| **Listes d'achat partagées par l'entreprise** | Haute | Wishlist officielle Sylius (2.0 à 2.2, 2.3 non confirmé) ou **nouveau plugin** orienté entreprise |
| **Carnet d'adresses de l'entreprise** | Moyenne | Évolution de company-accounts, ou adresses par client (Sylius standard) |
| Prix par client ou organisation | Haute | Couvert par tier-pricing (palier 1 par groupe de clients) |
| Factures PDF et historique | Haute | Invoicing et Factur-X |
| Commandes récurrentes | Basse | Hors périmètre (slide) |
| CMS | Basse | Page d'accueil du thème |

## 5. Thème

- Shop Sylius 2 : Bootstrap 5, Twig Hooks, Symfony UX, Webpack Encore. AssetMapper n'est pas confirmé.
- Recommandation : **pas de SyliusThemeBundle**. On ajoute des hooks, on surcharge quelques templates ciblés et on ajoute une feuille de style Encore propre à la démo.
- Démo Sylius Plus : aucune convention de badge vérifiable (le site affiche des liens commerciaux). On crée **notre propre badge** (icône et infobulle « Plugin tmonier/… », lien vers GitHub), sans imiter un marquage Plus.
- Catalogue fermé : listener qui redirige les visiteurs anonymes vers la connexion (exceptions : accueil, connexion, inscription, endpoints PunchOut, MCP, feeds), plus masquage des prix et des boutons d'achat.

## 6. Hébergement Railway Hobby (synthèse)

- 5 $/mois avec 5 $ d'usage inclus. Au-delà, facturation à l'usage : environ 20 $ par vCPU et 10 $ par Go de RAM et par mois (blog tiers, non confirmé). **Un web et une base allumés en permanence coûtent environ 15 à 30 $/mois** : le crédit inclus ne suffit pas.
- Cron natif (UTC, toutes les 5 minutes au minimum, le processus doit se terminer). La veille s'active après 10 minutes sans trafic sortant (502 au réveil).
- Volumes de 5 Go non partageables entre services : les images uploadées par les fixtures doivent être écrites **dans le conteneur web**.
- Pas de SMTP sortant sur Hobby : Mailpit (boîte de démo visible) ou une API HTTPS.
- Pre-deploy command pour les migrations ; healthcheck HTTP.
- Le template Railway Sylius existant utilise nginx, php-fpm, worker, Redis, MySQL et Mailpit, donc trop de services pour Hobby.

## 7. Plugins communautaires

- **Nécessaires** : `sylius/invoicing-plugin` 2.3.0 (via Factur-X) et `sylius/mcp-server-plugin` 0.2.0 (via b2b-mcp).
- **Candidat** : wishlist officielle Sylius (compatibilité 2.3 à vérifier).
- Écartés : bitbag/wishlist (Sylius 1.x).
- Non vérifiés : CMS, refund, recherche Elasticsearch. Hors du cœur B2B, on ne les ajoute pas sans besoin avéré.
