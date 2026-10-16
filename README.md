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
- Contournement temporaire `App\PunchOut\AuthorizedPunchOutOrderFactory` : le plugin punchout-cxml crée ses paniers comme des commandes d'invité, ce qui empêchait le paiement sur compte. À retirer avec les correctifs du plugin (sprint 7).

## Boîte e-mail de démo / Demo mailbox

`/demo/mails` affiche les e-mails envoyés par la démo (confirmations de commande, approbations…), en lecture seule. Mailpit n'est **jamais exposé** : le serveur lit son API interne et masque tout e-mail sensible (sujet de type mot de passe, vérification, invitation, connexion, ou lien porteur d'un jeton), qui est aussi refusé par son identifiant.

## Variables d'environnement / Environment variables

| Variable | Rôle | Local (`compose.yaml`) | Production (Railway) |
|----------|------|------------------------|----------------------|
| `DEMO_MODE` | Active les pages `/demo/*`, le bandeau et le simulateur. Défaut `0` | `1` | `1` |
| `DEFAULT_URI` | URL publique (liens absolus, e-mails) ; son hôte HTTPS devient un domaine de retour PunchOut | `http://localhost:8080` | `https://<hôte public>` |
| `DEMO_MAILPIT_API_URL` | API HTTP **interne** de Mailpit, appelée côté serveur uniquement ; vide = boîte de démo désactivée | `http://mailpit:8025` | adresse sur le réseau privé, jamais publique (sprint 6) |
| `DEMO_MAILPIT_URL` | Interface Mailpit pour le développeur local ; jamais affichée aux visiteurs | `http://localhost:8025` | vide |
| `MAILER_DSN` | Envoi des e-mails (vers Mailpit) | `smtp://mailpit:1025` | à définir (sprint 6) |
| `DATABASE_URL` | Base MySQL 8.4 | service `mysql` | base Railway |
| `WKHTMLTOPDF_BINARY` | Binaire des factures PDF | `/usr/local/bin/wkhtmltopdf` (image) | idem |

Ne pas ouvrir la démo au public avant le sprint 6 (back-office de démo à accès complet, données personnelles).
