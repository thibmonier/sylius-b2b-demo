# Options techniques — démo B2B Sylius

Chaque option est notée ✅ (recommandée), 🔶 (possible) ou ❌ (écartée).

## T1. Socle applicatif
- ✅ **Sylius-Standard 2.3** (`composer create-project sylius/sylius-standard`), plugins installés depuis Packagist, configuration de la démo dans `config/`. C'est le chemin d'un intégrateur.
- ❌ Réutiliser une TestApplication de plugin : non représentatif, couplé aux tests.

## T2. Thème B2B
- ✅ **Ajouts dans les Twig Hooks + surcharges ciblées + feuille Encore de la démo** : le shop officiel est conservé (compatible avec les hooks des plugins), avec une apparence B2B (listes denses, SKU, conditionnement, stock, paliers visibles, accès rapide quick-order et devis).
- 🔶 Paquet de thème réutilisable (bundle « b2b-theme ») si on veut le publier. Peut venir après la démo.
- ❌ SyliusThemeBundle ou fork complet du shop : maintenance lourde et risque de casser les hooks des plugins.

## T3. Badges « fonctionnalité du plugin »
- ✅ **Composant Twig `PluginBadge`** (icône, infobulle et lien GitHub) injecté par des hooks de la démo à côté des blocs de chaque plugin, à partir d'un registre YAML (hook → plugin). Mode « montrer les badges » activable (bandeau de démo). Également dans l'admin.
- ❌ Modifier les plugins pour qu'ils affichent eux-mêmes un badge : pollue les plugins.

## T4. Catalogue fermé (connexion obligatoire configurable)
- ✅ **Nouveau plugin `sylius-b2b-access-plugin`** :
  - réglage par canal : ouvert, prix masqués ou fermé ;
  - listener `kernel.request` (routes exemptées configurables) ;
  - masquage des prix et des boutons d'achat par hooks ;
  - inscription avec validation par l'admin en option.
  Réutilisable et publié.
- 🔶 Code dans la démo : plus rapide, mais non réutilisable.

## T5. Autres manques B2B
- ✅ **Nouveau plugin `sylius-order-reference-plugin`** : référence de commande acheteur (PO) et centre de coût au checkout, obligatoires ou non par canal ou par entreprise. Reprise dans Factur-X (BT-10 BuyerReference), dans les e-mails et dans l'admin ; synergie avec PunchOut (orderID).
- ✅ **Listes d'achat** : spike de la wishlist officielle Sylius sur 2.3. Si elle est incompatible ou ne gère pas la notion d'entreprise, **nouveau plugin `sylius-shopping-list-plugin`** (listes partagées par l'entreprise, ajout au panier en un clic, pont quick-order).
- 🔶 Carnet d'adresses de l'entreprise : évolution de company-accounts (v1.2), à prioriser en planification.

## T6. Conteneurisation
- ✅ **FrankenPHP (base `dunglas/symfony-docker`)** : un seul processus HTTP, image plus simple, worker mode possible ; supercronic intégré pour les tâches planifiées.
- 🔶 nginx + php-fpm (template Railway Sylius) : éprouvé, mais deux processus (supervisord).
- Build multi-stage : Composer, puis Node (Encore), puis image finale (PHP, wkhtmltopdf, cache préchauffé).

## T7. Topologie Railway et remise à zéro
- ✅ **2 services, plus un service Mailpit en option** :
  1. `web` : l'image de la démo, avec FrankenPHP et supercronic. Les tâches des plugins et l'envoi des webhooks tournent dans le même conteneur. La remise à zéro (03:00 Europe/Paris) recharge la base, les fixtures et les médias **localement**, ce qui règle la contrainte des volumes.
  2. `mysql` : la base MySQL managée.
  3. `mailpit` (en option) : interface publique en lecture seule des e-mails de la démo.
- 🔶 Un service cron Railway séparé qui lance `railway redeploy web` (la remise à zéro se fait au démarrage) : plus « natif », mais un jeton Railway et une indisponibilité de quelques minutes.
- ❌ Un service cron Railway qui lance les fixtures directement : les médias écrits dans le conteneur cron ne sont pas servis par le web.
- Pas de mise en veille par défaut : une remise à zéro au réveil effacerait les données d'une session en cours. À réévaluer selon le coût réel.

## T8. Base de données
- ✅ **MySQL 8.4** (Railway managé). C'est la base de dev de tous les plugins.
- 🔶 PostgreSQL : supporté par les plugins (CI), sans avantage ici.

## T9. E-mails
- ✅ **Mailpit**, sans relais sortant, interface exposée en lecture seule (opportunité O4).
- ❌ SMTP réel : bloqué sur Hobby, et risque de spam via les comptes publics.

## T10. Données de démo
- ✅ **Fixtures de la démo, à partir de CSV versionnés**, en remplacement de la suite par défaut :
  - catalogue industriel d'environ 150 à 250 produits (EPI, quincaillerie, fournitures, consommables), avec SKU, MPN, EAN, conditionnements, poids et dimensions ;
  - 2 ou 3 entreprises avec rôles, crédit et limites, paliers par groupe, devis, types de palettes, données GPSR, acheteurs PunchOut et commandes historiques ;
  - priorités de fixtures explicites (I6) ;
  - illustrations produits générées (pas de photos sous licence incertaine).
- Les fixtures des plugins ne sont reprises que là où elles sont cohérentes avec ces données ; les autres sont remplacées par celles de la démo.

## T11. Simulateur PunchOut
- ✅ Page « Outil d'achat (simulateur) » dans la démo, inspirée du simulateur de la TestApplication du plugin : envoi d'un setup cXML ou d'une entrée OCI et réception du panier. Opportunité O3.

## T12. Canal B2C secondaire
- 🔶 Un petit canal B2C pour withdrawal, GPSR et agentic-feeds (rétractation, sécurité des produits, flux IA), qui visent le grand public. Sinon, withdrawal désactivé sur le canal B2B. **À décider** en planification.

## T13. CI/CD
- ✅ GitHub Actions : build de l'image, tests fonctionnels de la démo (pages clés, scénarios), push. Railway déploie depuis GitHub (branche main), migrations en pre-deploy.
