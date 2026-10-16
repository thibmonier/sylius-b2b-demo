# Sylius B2B Demo

B2B demo of Sylius 2.3 with the [tmonier plugins](https://packagist.org/packages/tmonier/): company accounts, purchase approval, trade credit, tier pricing, quick order, quotes, PunchOut cXML/OCI, Factur-X and more. Each plugin feature carries a badge that links to its plugin.

> Work in progress. Specification and roadmap: [`project-management/`](project-management/) ; decisions: [`docs/adr/`](docs/adr/).

## Comptes de démo / Demo accounts

Page `/demo/accounts` liste tous les identifiants en temps réel.

### Comptes B2B (mot de passe : `Demo1234!`)

| Email | Nom | Rôle | Entreprise |
|-------|-----|------|------------|
| `claire@acme.example` | Claire Dupont | Acheteuse / Buyer | Acme Industries |
| `paul@acme.example` | Paul Martin | Approbateur / Approver | Acme Industries |
| `isabelle@acme.example` | Isabelle Renard | Administratrice / Admin | Acme Industries |
| `marc@techparts.example` | Marc Lefebvre | Acheteur / Buyer | TechParts SAS |
| `nathalie@techparts.example` | Nathalie Girard | Administratrice / Admin | TechParts SAS |
| `thomas@buildco.example` | Thomas Bernard | Acheteur — ⚠️ compte **désactivé** | BuildCo SA |
| `antoine@buildco.example` | Antoine Moreau | Approbateur / Approver | BuildCo SA |
| `henri@globalmaint.example` | Henri Dubois | Acheteur / Buyer | GlobalMaint SA |
| `celine@globalmaint.example` | Céline Lambert | Administratrice / Admin | GlobalMaint SA |
| `yves@induspro.example` | Yves Fontaine | Acheteur / Buyer | IndusPro SARL |
| `martine@induspro.example` | Martine Rousseau | Approbatrice / Approver | IndusPro SARL |

### Autres comptes

| Email | Nom | Rôle | Mot de passe |
|-------|-----|------|--------------|
| `lea@demo.example` | Léa Petit | Cliente B2C | `Demo1234!` |
| `sophie@fournitures-pro.example` | Sophie Martin | Admin back-office (restreint) | `demo-sophie` |

### Scénarios de démo

- **Cloisonnement des entreprises** : un client appartient à une seule entreprise ; Marc (TechParts) ne voit ni les listes, ni les adresses, ni les commandes d'Acme.
- **Compte désactivé** : Thomas Bernard — connexion refusée (ShopUser désactivé).
- **Back-office restreint** : Sophie Martin accède à `/admin/` avec des droits limités.

## Accès par canal / Channel access (US-019)

Le canal **B2B** est en mode **prix masqués** (plugin [channel-access](https://github.com/thibmonier/sylius-channel-access-plugin)) : un visiteur non connecté voit le catalogue mais ni prix, ni promotion, ni bouton d'achat (un lien « Connectez-vous pour voir vos prix » les remplace), ni panier ; l'API boutique ne renvoie aucun prix et refuse la création de panier (401). Le canal **B2C** reste ouvert. Le mode est réglé par la fixture `demo_channel_access` (jamais par Sophie : l'édition des canaux est refusée à l'admin de démo).

- **Masqué aussi** (hors pages du plugin) : le prix « dès » et la mention HT des cartes du thème B2B (elles sont dans le bloc prix du cœur, désactivé), le formulaire de devis et l'API des paliers de prix (`/api/v2/shop/product-variants/{code}/tier-prices`) — routes ajoutées par `App\DependencyInjection\Compiler\RestrictPriceRoutesPass` — les données structurées JSON-LD (`"price"`) et la matrice de quantités des fiches produit, les routes d'ajout de la commande rapide et de la matrice, le consentement des assistants IA (MCP), et les flux produits du canal B2B (jeton d'accès aléatoire à chaque chargement des fixtures, fixture `demo_hidden_prices_feeds`).
- **Canal fermé** : si on passe un canal en « fermé », un anonyme n'atteint que l'accueil, la connexion, l'inscription, l'administration et la liste blanche `config/packages/demo_channel_access.yaml` (PunchOut, MCP, flux, pages `/demo/*`), sans produit ni menu du catalogue (testé par `ClosedChannelDemoTest`).
- **Limites connues** : le tri par prix des listes reste possible (l'ordre est visible, pas les montants) ; les images et fichiers statiques (`/media`) ne sont pas protégés ; un accès par le serveur MCP ou PunchOut se fait par un compte authentifié, donc avec prix ; un client connecté mais non approuvé (US-020) n'a pas accès aux prix.

## Inscription validée / Registration approval (US-020)

Sur le canal **B2B**, une inscription (formulaire de la boutique, API boutique) crée un compte **en attente** (plugin [registration-approval](https://github.com/thibmonier/sylius-registration-approval-plugin)) : l'internaute est déconnecté, voit une page « inscription reçue », puis, une fois connecté, reste comme un visiteur anonyme (prix masqués, panier et devis refusés en 403 avec une page d'attente). **Sophie** (admin restreint) approuve ou refuse dans *Clients > Inscriptions* ; le client reçoit un e-mail (visible dans la boîte de démo, dans la langue par défaut du canal) et accède aux prix à sa requête suivante. Les comptes de démo existants sont **approuvés**.

- Toute inscription en libre-service est « en attente », quel que soit le canal ; la restriction ne s'applique que sur les canaux listés dans `config/packages/demo_registration_approval.yaml` (B2B). `bin/console tmonier:registration-approval:check` signale un canal listé resté en mode « ouvert ».
- Un client en attente ou refusé n'a pas non plus accès au serveur MCP (`App\Demo\Mcp\ApprovedRegistrationChecker`, branché sur `AccountUsabilityCheckerInterface` du plugin b2b-mcp) ni au consentement des assistants IA ; les jetons obtenus avant sont refusés (403).
- La boîte de démo `/demo/mails` masque l'adresse du destinataire (`c***@acme.example`).
- Une inscription avec une adresse déjà utilisée donne la même réponse qu'une nouvelle (pas d'énumération de comptes), page et API.
- Sophie n'a que la route de décision (`tmonier_registration_approval_admin_decide`, jeton CSRF vérifié) : voir `docs/securite-admin-demo.md`.
- `decided_by` (identifiant de l'administrateur) est une donnée personnelle : à inclure dans le registre RGPD de l'hébergement de la démo.

## Historique de commandes / Order history

Chaque réinitialisation recrée les mêmes 12 commandes (fixture `demo_orders`, plan dans `src/Demo/Order/DemoOrderPlan.php`), passées par les vrais services de Sylius et des plugins : même contenu, mêmes dates relatives (J-n à 10 h), chacune avec sa référence de commande d'achat. Les factures Factur-X (PDF/A-3) sont datées comme leur commande et se téléchargent depuis *Mon compte > Commandes*.

| Entreprise | Commandes |
|------------|-----------|
| Acme Industries | 6 : Claire (expédiée J-172, approuvée par Paul puis expédiée J-131, annulée J-61, **en attente d'approbation** J-4) et Paul (expédiée J-94, payée J-33) ; centres de coût `ATELIER-01` et `MAINT-02` |
| TechParts SAS | 3 (Marc) : expédiée J-150, payée J-78, annulée J-19 |
| GlobalMaint SA | 3 (Henri) : expédiée J-110, **remboursée** J-52, en attente de paiement J-9 ; centre de coût `SITE-LYON` |
| BuildCo SA, IndusPro SARL | aucune (cas « entreprise sans commande ») |

Limite connue : la facture de la commande approuvée par Paul est datée du jour de la réinitialisation (le plugin purchase-approval ne suit pas l'horloge figée de la fixture).

## Carnet d'adresses partagé / Shared address book

Acme Industries a trois adresses de livraison partagées (company-accounts 1.2) : « Usine de Lyon », « Entrepôt de Saint-Priest » et « Atelier de Grenoble ».

- **Isabelle** (administratrice) les gère dans *Mon compte > Mon entreprise > Adresses* (ajout, modification, suppression).
- **Claire** et **Paul** les consultent sans pouvoir les modifier, et au checkout (étape adresse) cliquent sur *Livrer ici* : l'adresse est copiée dans la commande, le panier est recalculé et le checkout reprend à l'adresse.
- Les membres des autres entreprises ne les voient pas.

## Simulateur d'outil d'achat / Procurement-tool simulator

`/demo/punchout-simulator` (mode démo uniquement, 404 sinon) joue l'outil e-procurement d'Acme (type Ariba) contre le vrai point d'entrée PunchOut cXML de la boutique :

1. *Démarrer* envoie un `PunchOutSetupRequest` ; le visiteur arrive sur la boutique connecté comme Claire, dans une session PunchOut.
2. Il remplit le panier puis clique sur *Transférer* : la boutique renvoie le panier (`PunchOutOrderMessage`) au simulateur, qui l'affiche.
3. *Envoyer la commande* transmet un `OrderRequest` avec un n° de commande d'achat : la boutique crée la commande de Claire, payée sur compte (crédit commercial d'Acme) et soumise à l'approbation comme une commande de la boutique.

Les cas d'erreur sont démontrables depuis la page : mauvais secret (401), domaine de retour hors liste (403), session expirée, panier vide. Le secret partagé ne quitte jamais le serveur.

Limites :

- Le retour du panier exige une URL **HTTPS** dont l'hôte est un domaine de retour du buyer. La fixture ajoute l'hôte de `DEFAULT_URI` quand il est en HTTPS ; en local (`http://localhost:8080`), la session est refusée avec un message qui nomme l'hôte.
- **OCI n'est pas simulé** : le plugin le prend en charge, mais la démo n'a pas de buyer OCI ; le simulateur ne joue que cXML.
- Un panier ouvert par une session PunchOut ne peut pas être finalisé par le checkout de la boutique : il se transfère à l'outil d'achat (guard du plugin punchout-cxml 1.2).

## Boîte e-mail de démo / Demo mailbox

`/demo/mails` affiche les e-mails envoyés par la démo (confirmations de commande, approbations…), en lecture seule. Mailpit n'est **jamais exposé** : le serveur lit son API interne et masque tout e-mail sensible (sujet de type mot de passe, vérification, invitation, connexion, ou lien porteur d'un jeton), qui est aussi refusé par son identifiant.

## Variables d'environnement / Environment variables

| Variable | Rôle | Local (`compose.yaml`) | Production (Railway) |
|----------|------|------------------------|----------------------|
| `DEMO_MODE` | Active les pages `/demo/*`, le bandeau et le simulateur. Défaut `0` | `1` | `1` |
| `DEFAULT_URI` | URL publique (liens absolus, e-mails) ; son hôte HTTPS devient un domaine de retour PunchOut | `http://localhost:8080` | `https://<hôte public>` |
| `DEMO_MAILPIT_API_URL` | API HTTP **interne** de Mailpit, appelée côté serveur uniquement ; vide = boîte de démo désactivée | `http://mailpit:8025` | `http://<mailpit privé>:8025` (réseau privé Railway) |
| `DEMO_MAILPIT_URL` | Interface Mailpit pour le développeur local ; jamais affichée aux visiteurs | `http://localhost:8025` | vide |
| `MAILER_DSN` | Envoi des e-mails (vers Mailpit) | `smtp://mailpit:1025` | `smtp://<mailpit privé>:1025` |
| `DATABASE_URL` | Base MySQL 8.4 | service `mysql` | base Railway |
| `WKHTMLTOPDF_BINARY` | Binaire des factures PDF | `/usr/local/bin/wkhtmltopdf` (image) | idem |
| `DEMO_BASIC_AUTH` | Porte privée (auth HTTP basique) en mode démo. Défaut `1` | `0` | `1` jusqu'au GO d'ouverture |

Liste complète (plafonds de débit, hôtes sortants, secrets de la porte) : [`docs/durcissement-demo.md`](docs/durcissement-demo.md).

## Déploiement / Deployment

La démo est déployée sur Railway, **privée** (auth HTTP basique) tant que l'ouverture publique n'est pas validée. Runbook : [`docs/deploiement-railway.md`](docs/deploiement-railway.md) ; remise à zéro nocturne (03:00, heure de Paris) : [`docs/remise-a-zero-demo.md`](docs/remise-a-zero-demo.md) ; admin de démo restreint : [`docs/securite-admin-demo.md`](docs/securite-admin-demo.md).
