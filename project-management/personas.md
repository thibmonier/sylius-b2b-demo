# Personas — Démo B2B Sylius

## Visiteurs (cibles de la démo)

### Hugo, intégrateur Sylius (agence)
- 34 ans, lead dev dans une agence e-commerce, a déjà livré deux projets Sylius.
- **Objectif** : savoir en 10 minutes si les plugins couvrent le besoin B2B d'un client (comptes entreprise, validation, crédit, PunchOut), à quel niveau de qualité, et comment ils s'installent.
- **Frustrations** : les démos sans accès admin, les plugins vus seulement en captures, l'absence de lien vers le code.
- **Ce qu'il attend** : badges qui renvoient vers GitHub, accès admin, page « Plugins », guide d'installation.

### Nadia, responsable e-commerce B2B (distributeur industriel)
- 42 ans, pilote le site B2B d'un distributeur de fournitures industrielles (aujourd'hui sous Magento B2B).
- **Objectif** : vérifier que Sylius couvre ses parcours (catalogue fermé, prix par client, validation, crédit, factures électroniques, PunchOut grands comptes).
- **Frustrations** : les démos B2C maquillées en B2B, le jargon technique.
- **Ce qu'elle attend** : parcours guidés, données crédibles (EPI, quincaillerie), français.

## Comptes de démo (rôles joués)

| Persona | Rôle | Entreprise | Ce qu'il montre |
|---------|------|------------|-----------------|
| **Claire** | Acheteuse (buyer) | Acme Industries | Catalogue fermé, prix négociés, quick-order, listes d'achat, devis, référence PO, limite de commande |
| **Paul** | Approbateur | Acme Industries | Validation des commandes, limites d'approbation |
| **Isabelle** | Administratrice d'entreprise | Acme Industries | Membres, rôles, limites, adresses de l'entreprise, crédit commercial, historique |
| **Marc** | Acheteur PunchOut | BigCorp (via simulateur) | Session PunchOut cXML / OCI, OrderRequest |
| **Sophie** | Back-office du marchand (admin restreint) | — | Création de commande, devis, timeline, audit, actions de masse, palette, IA, Factur-X |
| **Léa** | Cliente particulière | — (canal B2C) | GPSR, rétractation, flux IA |

## Mainteneur

### Thibaut, auteur des plugins
- **Objectif** : une démo qui tourne seule (remise à zéro chaque nuit, coût maîtrisé), à jour des versions des plugins, et qui sert de banc d'intégration.
- **Ce qu'il attend** : déploiement par Git, alertes en cas d'échec de la remise à zéro, mise à jour Renovate, budget plafonné.

## Acteur système

### Outil d'achat (simulateur)
- Envoie un `PunchOutSetupRequest` cXML ou une entrée OCI, reçoit le panier et poste un `OrderRequest`.
