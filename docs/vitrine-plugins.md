# Vitrine des plugins : classement Sylius Plus et charte de ton (US-063)

Document interne de suivi (sprint 13). Il peut nommer Sylius Plus ; les textes publiés des plugins, eux, suivent la charte de la section 4 et ne le font pas.

- Date de rédaction et de consultation des sources : **2026-10-17**.
- Décision D2 (2026-10-17) : tout est listé, ton neutre, pas de comparaison avec Plus, pas de « alternative gratuite », rien sur les canaux Sylius.
- Règle : aucune fonction de Plus n'est affirmée sans URL ; aucun classement ne s'appuie sur la mémoire.

## 1. Sources relevées (Sylius Plus)

| # | URL | Consultée le | Ce qu'elle apporte | Lisible |
|---|-----|--------------|--------------------|---------|
| S1 | https://sylius.com/plus/ | 2026-10-17 | Les 11 modules de Plus : B2B Suite, Marketplace Suite, Advanced Multi-store, Returns Management (RMA), Multi-source Inventory, Loyalty, RBAC, Partial Shipment, RFQ, Product Configurator, Subscription. Autres fonctions citées sans description : permissions avancées, administrateurs par canal, listes de prix multiples, recherche avancée, One Page Checkout, exécution partielle. Ni comptes d'entreprise isolés, ni module de service client. | oui |
| S2 | https://sylius.com/blog/sylius-plus-module-overview-b2b-suite/ | 2026-10-17 | B2B Suite : Elasticsearch, Advanced Order Management (créer, personnaliser et répéter des commandes), Organization Management (compte maître : facturation, historique, carnets d'adresses partagés), Multiple Pricing Lists (prix par organisation), Quick Shopping (commande en gros sur une page, suggestions par SKU), Multiple Wishlists, couche d'import/export PIM/ERP. **Ne mentionne ni validation d'achat ni crédit.** | oui |
| S3 | https://sylius.com/blog/news/requests-for-quotation-rfq/ | 2026-10-17 | RFQ : demande de devis depuis le panier, offre construite en admin (prix, notes, pièces jointes), acceptation ou négociation, transformation en commande au prix proposé ; fonctionne avec la B2B Suite. | oui |
| S4 | https://sylius.com/blog/sylius-plus-module-overview-advanced-role-based-access-control-module/ | 2026-10-17 | RBAC : rôles, restriction par canal, arbre de permissions, accès par catégorie, vues d'admin dédiées. | oui |
| S5 | https://sylius.com/blog/sylius-plus-module-overview-returns-management-rma/ | 2026-10-17 | RMA : demandes de retour et d'échange depuis le compte client, remboursement, réparation ou remplacement, chat de retour, étiquettes d'expédition. **Ne dit rien du droit de rétractation européen** (ni délai, ni obligation légale). | oui |
| S6 | https://sylius.com/blog/sylius-plus-module-overview-advanced-multi-store/ | 2026-10-17 | Multi-store : unités commerciales, administrateurs par canal, adresses de facturation distinctes, groupes de clientèle (customer pools) par domaine, pays ou unité. | oui |
| S7 | https://sylius.com/blog/2024-rewind-and-a-few-words-about-everything-next/ | 2026-10-17 | « Currently, Sylius Plus allows you to create orders on behalf of customers, delete orders, or manage users. » Cite aussi RFQ, l'édition de commande (annoncée), les abonnements, une couche d'import/export et un SDK d'IA **prévu**. | oui |
| S8 | https://docs.sylius.com/public | 2026-10-17 | Présentation des éditions ; liste RBAC, stock multi-sources, moteur de prix B2B, retours, support. Serveur MCP de la boutique et outils d'IA présentés pour Sylius 2.x sans mention « Plus ». Aucune page de fonction Plus. | oui |
| S9 | https://docs.sylius.com/sylius-plus | 2026-10-17 | Documentation technique de Plus : redirige vers une authentification (HTTP 307). | **non** |
| S10 | https://sylius.com/editions/ et https://sylius.com/plus/features/ | 2026-10-17 | HTTP 404. | **non** |
| S11 | https://sylius.com/wp-content/uploads/2021/06/Sylius-Features-Overview.pdf | 2026-10-17 | PDF de 2021, texte non extractible ; non utilisé. | **non** |

Conséquence : seule la documentation publique et marketing a pu être lue. « Non couvert » signifie « non documenté publiquement », pas « absent » (même réserve que `docs/plus-overlap.md` du plugin de parc d'équipements). La documentation technique de Plus (S9) reste à relire par l'utilisateur s'il y a accès.

## 2. Inventaire et classement des 24 plugins

Classes : **concurrent** (une fonction de Plus décrite recoupe le plugin), **complémentaire** (le plugin prolonge un composant officiel ou voisin de Plus sans le recouper), **hors périmètre** (aucune fonction de Plus décrite ne recoupe), **à confirmer** (les pages ne tranchent pas ; traité comme « concurrent » pour le ton).

| # | Plugin | Fonction (README) | Écrans | Point de contact avec Plus | Classe | Sources |
|---|--------|-------------------|--------|----------------------------|--------|---------|
| 1 | admin-order-creation | Créer des commandes pour un client depuis l'admin, par le même tunnel que la boutique | admin | Création de commande au nom du client | **concurrent** | S7 |
| 2 | agentic-feeds | Flux produits (Google Merchant, OpenAI, Stripe) et JSON-LD | admin | aucun | hors périmètre | S1 |
| 3 | ai-catalog-copilot | Suggestions d'IA relues (descriptions, SEO, traductions) | admin | SDK d'IA de Plus seulement **prévu** (S7) | hors périmètre | S1, S7 |
| 4 | audit-log | Journal des modifications de l'admin avec diff par champ | admin | aucun journal d'audit décrit | hors périmètre | S1, S4 |
| 5 | b2b-mcp | Outils B2B pour le serveur MCP officiel de Sylius (compte client) | aucun écran propre (outils MCP) | S8 : serveur MCP de la boutique présenté sans mention « Plus » | complémentaire | S8 |
| 6 | bulk-actions | Actions groupées sur les grilles de l'admin | admin | « exécution partielle » n'est pas une action groupée | hors périmètre | S1 |
| 7 | channel-access | Catalogue ouvert, prix masqués ou fermé derrière connexion, par canal | boutique | accès par canal côté admin seulement (S4, S6), pas côté visiteur | hors périmètre | S4, S6 |
| 8 | command-palette | Palette de commandes Ctrl+K dans l'admin | admin | aucun | hors périmètre | S1 |
| 9 | company-accounts | Comptes d'entreprise : acheteurs, rôles, espace entreprise | admin, boutique | Organization Management | **concurrent** | S2 |
| 10 | container-fill | Palettes et camions : règles de remplissage au paiement, jauge | admin, boutique | aucun | hors périmètre | S2 |
| 11 | customer-asset-management | Parc d'équipements du client (conception ; rien de fonctionnel) | aucun | verdict US-054 repris : non couvert, sous réserve de la doc technique | hors périmètre (**à confirmer**) | S2, S9 ; `docs/plus-overlap.md` du dépôt |
| 12 | facturx | Factures Factur-X / ZUGFeRD sur le plugin de facturation officiel | admin (fichiers de facture) | s'appuie sur le plugin officiel ; Plus ne documente pas la facture électronique | complémentaire | S1 |
| 13 | gpsr | Informations GPSR sur les fiches produit | admin, boutique | aucun | hors périmètre | S1 |
| 14 | order-hold | Noyau partagé : commandes impayées retenues sans annulation | aucun | aucun | hors périmètre | S2 |
| 15 | punchout-cxml | PunchOut cXML/OCI depuis les systèmes d'achat | admin, simulateur | l'import/export PIM/ERP de S2 est un autre sujet | hors périmètre | S2 |
| 16 | purchase-approval | Validation d'achat par un approbateur de l'entreprise | admin, boutique | « Advanced Order Management » (S2) ne mentionne pas de validation | **à confirmer** | S2 |
| 17 | quick-order | Commande par code produit, CSV, matrice, recommande | boutique | Quick Shopping et commandes répétées | **concurrent** | S2 |
| 18 | quote-request | Demande de devis B2B et négociation | admin, boutique | Request for Quotation | **concurrent** | S3 |
| 19 | registration-approval | Inscription validée par un administrateur | admin | aucun | hors périmètre | S1, S6 |
| 20 | tier-pricing | Prix dégressifs par quantité | admin, boutique | « Multiple Pricing Lists » : les paliers par quantité ne sont pas décrits | **à confirmer** | S1, S2 |
| 21 | timeline | Chronologie des commandes et clients, notes internes | admin | aucune chronologie décrite | hors périmètre | S1, S7 |
| 22 | trade-credit | Paiement sur compte (net 30) avec plafond de crédit | admin, boutique | S2 ne mentionne pas de crédit | hors périmètre | S2 |
| 23 | webhook-outbox | Webhooks sortants fiables (outbox, signatures, rejeu) | admin | couche d'import/export vers PIM/ERP (S2) : autre mécanisme | complémentaire | S2 |
| 24 | withdrawal | Bouton de rétractation européen (directive 2023/2673) | boutique, admin | RMA (retours) ; le droit de rétractation n'est pas mentionné | **à confirmer** | S5 |

Totaux : concurrent 4 (1, 9, 17, 18), à confirmer 3 (16, 20, 24 ; traités comme concurrent), complémentaire 3 (5, 12, 23), hors périmètre 14 dont la ligne 11 reste « à confirmer » en attente de la documentation technique. Chaque classe cite au moins une source ci-dessus.

### Questions ouvertes pour l'utilisateur

1. **purchase-approval, tier-pricing, withdrawal** : les pages publiques ne tranchent pas (S2, S5). Traités comme « concurrent » pour le ton. Si vous avez accès à la documentation technique de Plus (S9), un coup d'œil lèverait le doute.
2. **customer-asset-management** : reste « non couvert, à confirmer » tant que S9 n'est pas relue.
3. **Phrases existantes qui comparent** : six README publics contiennent déjà une comparaison que la charte interdit (voir section 5). Les corriger est hors du périmètre de ce sprint tant que vous ne l'avez pas décidé ; l'outil les signale en avertissement.

## 3. Critères d'image et plugins sans image

- Vraies captures, prises depuis la démo locale (boutique et admin), ou depuis l'application de test du plugin s'il est absent de la démo (décision D3).
- Un plugin avec écran porte au moins une capture de cet écran, dans `docs/images/` du dépôt ; format JPEG ou PNG, 1280 px de large au plus, sans donnée réelle ni secret (protocole de capture : T-065-02, à affiner dans US-065).
- **Seule exception : `order-hold`** (D7). C'est un noyau sans entité, sans route et sans écran : rien à photographier. `punchout-cxml`, `channel-access`, `registration-approval` et `b2b-mcp` ont un écran (admin, boutique, simulateur) ou une surface visible et sont photographiés ; `customer-asset-management` n'a encore aucun écran (conception) et suit la décision de l'US-065.

## 4. Charte de ton

### Règles communes (toutes classes)

- Décrire ce que fait le plugin, ses limites et sa licence (MIT) ; ne jamais le définir par rapport à un autre produit commercial.
- Interdit dans tout texte publié (README, bloc standard, descriptions de dépôt) :

| FR | EN |
|----|----|
| alternative gratuite, alternative libre, alternative open source à Sylius Plus | free alternative, open-source alternative to Sylius Plus |
| sans payer Sylius Plus, sans Sylius Plus, à la place de Sylius Plus | without paying for Plus, without Sylius Plus, instead of Sylius Plus |
| moins cher, gratuit contrairement à | cheaper, free unlike |
| équivalent de / version libre de (un module de Plus) | equivalent of / open version of (a Plus module) |

- Autorisé : « gratuit et open source », « licence MIT », « natif pour Sylius 2.2 / 2.3 ».
- Autorisé : une mention neutre déjà existante (par ex. « le RBAC de Sylius Plus n'est pas intégré », limite factuelle de compatibilité).
- Aucun lien vers le site de l'utilisateur ni vers sa page contact avant US-068. Rien sur les canaux Sylius.

### Par classe

| Classe | Ton | Formulation autorisée (FR) | Formulation autorisée (EN) |
|--------|-----|----------------------------|----------------------------|
| concurrent, à confirmer | Sobre, factuel, centré sur la fonction ; aucune mention de Plus | « Demandes de devis B2B pour Sylius 2 : le client demande, le marchand négocie, la commande suit. » | "B2B request for quote for Sylius 2: the customer asks, the merchant negotiates, the order follows." |
| complémentaire | Dire ce que l'on étend, avec le nom du composant **officiel ou ouvert** | « Étend le plugin de facturation officiel de Sylius avec le format Factur-X. » | "Extends the official Sylius Invoicing Plugin with the Factur-X format." |
| hors périmètre | Libre, fonctionnel ; aucune précaution particulière | « Journal des modifications de l'admin Sylius 2. » | "Audit log for the Sylius 2 admin." |

Pour « complémentaire », on ne nomme que le composant officiel ou ouvert (plugin de facturation, serveur MCP) ; Plus n'est jamais cité.

### Glossaire FR (cohérence des 24 traductions)

| EN | FR |
|----|----|
| plugin | plugin |
| back office / admin | back-office / administration |
| storefront / shop | boutique |
| channel | canal |
| cart | panier |
| checkout | tunnel de commande |
| order | commande |
| customer | client |
| buyer | acheteur |
| merchant | marchand |
| quote / request for quote (RFQ) | devis / demande de devis |
| company account | compte d'entreprise |
| trade credit | crédit client (paiement sur compte) |
| approval | validation |
| webhook | webhook |
| audit log | journal d'audit |
| grid | grille |
| bulk action | action groupée |
| feature flag | indicateur de fonctionnalité |
| rate limiting | limitation de débit |
| requirements | prérequis |
| installation | installation |
| configuration | configuration |
| testing | tests |
| licence | licence |
| limits / known limitations | limites / limitations connues |
| free and open source | gratuit et open source |

Règles de traduction : accents obligatoires ; guillemets français « » en prose ; blocs de code, commandes, identifiants (classes, routes, services, variables, clés de configuration), URL, badges et noms de fichiers restent identiques à l'anglais ; titres traduits mais même hiérarchie ; liens d'ancrage réécrits vers les titres français ; tableaux : mêmes colonnes et mêmes lignes.

## 5. Phrases existantes qui comparent (à décider)

| Dépôt | Ligne du README anglais | Texte |
|-------|-------------------------|-------|
| admin-order-creation | 10 | « …the feature is otherwise only part of the paid Sylius Plus. » |
| company-accounts | 9-10 | « …an MIT alternative … for merchants without Sylius Plus… » |
| purchase-approval | 11-12 | « An MIT alternative … for merchants without Sylius Plus. » |
| quote-request | 11 | « …the only quote feature is the paid Sylius Plus RFQ module… » |
| tier-pricing | 10 et 285 | « …the Sylius Plus / Elesto engines are proprietary » ; limite sur les listes de prix de Plus |
| customer-asset-management | 10 et 17 | « Promotion … suspended … Sylius Plus » ; lien vers `docs/plus-overlap.md` (« comparison with Sylius Plus ») |

Les traductions françaises restent fidèles au texte anglais. Si vous décidez de neutraliser ces phrases, ce sera une modification des README anglais (et des traductions) dans la même branche `docs/readme-fr-demo`.

Les lignes « Sylius Plus RBAC is not integrated » (limites de compatibilité) sont des mentions neutres et restent.

## 6. Outil `bin/vitrine-readme` et état de la livraison (US-064, US-067)

### Outil

- `php bin/vitrine-readme apply <dépôt>...` : ajoute le lien de langue (`> English: ...` / `> Français : ...`, en tête) et le bloc standard « Démo B2B » / « B2B demo » (avant la section Licence, sinon en fin de fichier) à `README.md` et `README.fr.md`. Idempotent ; le `README.fr.md` doit exister. Le bloc est borné par `<!-- vitrine:demo-b2b:start -->` et `<!-- vitrine:demo-b2b:end -->`.
- `php bin/vitrine-readme --check [--root=DIR] [--ref=REF] [--strict] [plugin...]` : lecture seule, sur les 24 dépôts. `--ref=docs/readme-fr-demo` lit les fichiers de la branche sans rien extraire.
- Contrôles : `README.fr.md` présent ; lien de langue ; bloc présent, unique et identique au texte de référence (un seul lien autorisé : le dépôt public de la démo, `https://github.com/thibmonier/sylius-b2b-demo`, aucun lien de site ni de contact) ; mêmes niveaux de titres ; blocs de code identiques ; mêmes tableaux (lignes et colonnes) ; mêmes cibles de liens dans le même ordre ; ancres internes qui visent un titre existant ; termes interdits de la charte (erreur, avec fichier et ligne) ; phrases existantes qui comparent avec Plus (avertissement, erreur avec `--strict`).
- Code : `src/Demo/Vitrine/` (hors conteneur Symfony), tests : `tests/Unit/Bin/VitrineReadmeTest.php`.

### Branches `docs/readme-fr-demo` (locales, rien n'est poussé)

Chaque branche part de `origin/main` après `git fetch`, contient un commit (`docs(readme): add French README and demo reference`) et deux fichiers (`README.md`, `README.fr.md`). Elles ont été créées avec `git worktree` : la branche courante des dépôts n'a pas changé (9 sur `main`, 15 sur une branche de travail non mergée, inchangées). Les worktrees ont été retirés ; les branches restent.

| Plugin | Commit |
|--------|--------|
| admin-order-creation | `056811a` |
| agentic-feeds | `2b47b9e` |
| ai-catalog-copilot | `3af13aa` |
| audit-log | `e4fe7ce` |
| b2b-mcp | `dc5fa7d` |
| bulk-actions | `def3bf9` |
| channel-access | `0481517` |
| command-palette | `077cef7` |
| company-accounts | `500cd32` |
| container-fill | `49b1e5b` |
| customer-asset-management | `899d96e` |
| facturx | `9f95233` |
| gpsr | `9b7343b` |
| order-hold | `b5df65a` |
| punchout-cxml | `7275135` |
| purchase-approval | `c92f72c` |
| quick-order | `539f7eb` |
| quote-request | `5dbbb1b` |
| registration-approval | `64a92a1` |
| tier-pricing | `5fa54ad` |
| timeline | `2efc283` |
| trade-credit | `a49fcf8` |
| webhook-outbox | `22626e1` |
| withdrawal | `b0cfd2c` |

`bin/vitrine-readme --check --ref=docs/readme-fr-demo` : 24 dépôts, 0 erreur, 11 avertissements (phrases existantes qui comparent avec Plus, section 5). Avec `--strict`, ces 11 lignes deviennent des erreurs : elles tombent si la section 5 est neutralisée.

### Note de GO (T-067-04)

- **CI attendue** : les 24 dépôts n'ont qu'un workflow `build.yaml` (plus `symfony-ai-next.yaml` pour ai-catalog-copilot), avec `paths-ignore: ["*.md"]` sur `push` et `pull_request`. Une PR qui ne change que `README.md` et `README.fr.md` ne lance donc **aucun** contrôle. Si la protection de branche d'un dépôt exige le contrôle `build`, il restera « en attente » et le merge demandera l'accord de l'utilisateur. La protection de branche n'a pas été lue (aucune action GitHub dans cette étape) : à relever avant le GO.
- **Ordre de merge** : indifférent, les 24 branches sont indépendantes (aucune ne touche au code). Ordre alphabétique proposé.
- **Avant l'ouverture des PR** : rien n'est poussé. Les captures (US-065, T-065-06) s'ajouteront sur ces mêmes branches.
- **Dépôts écartés** : aucun. Quatre dépôts (`command-palette`, `gpsr`, `timeline`, `withdrawal`) contiennent des `.DS_Store` non suivis ; ils n'ont pas été touchés (le travail s'est fait dans un worktree séparé).
- **Phrases à décider** : section 5 (11 lignes dans 6 dépôts).
