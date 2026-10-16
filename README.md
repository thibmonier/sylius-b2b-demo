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
| `marc@techparts.example` | Marc Lefebvre | Acheteur (TechParts) + Approbateur (BuildCo) | TechParts SAS + BuildCo SA |
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

- **Multi-entreprises** : Marc Lefebvre est membre de TechParts SAS (acheteur) **et** BuildCo SA (approbateur).
- **Compte désactivé** : Thomas Bernard — connexion refusée (ShopUser désactivé).
- **Back-office restreint** : Sophie Martin accède à `/admin/` avec des droits limités.
