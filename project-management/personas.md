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

### Didier, DSI / CTO d'un distributeur industriel (lecteur technique ; ex-prospect)
- 48 ans, DSI d'un distributeur de 200 personnes (ERP en place, site B2B vieillissant), décide du budget d'un projet de refonte.
- **Objectif** : savoir en 15 minutes ce qu'il faudrait intégrer (ERP, PunchOut, facturation électronique), avec quel effort et quels délais, et à qui s'adresser.
- **Frustrations** : les démos sans suite concrète, le jargon (cXML, OCI, MCP) non traduit en bénéfice, l'absence d'estimation.
- **Ce qu'il attend** : une page « Intégrer ces plugins », des cas d'usage de son secteur, un contact simple.

### Camille, recruteuse IT (nouvelle, 2026-10-21)
- 38 ans, chasseuse de têtes spécialisée en directions de la production et de projets IT ; arrive sur la démo depuis le profil LinkedIn de Thibaut.
- **Objectif** : vérifier en 5 minutes que ce que le profil affirme est vrai (25 ans de delivery, IA générative intégrée sans perte de qualité, de sécurité ni de conformité, expertise Symfony, Sylius et e-commerce B2B).
- **Frustrations** : les profils sans preuve, le jargon, les démos qui ressemblent à des plaquettes commerciales.
- **Ce qu'elle attend** : une démo qui fonctionne, des faits vérifiables (tests, CI, dépôts publics), un chemin simple vers le profil. Elle ne cherche ni prestataire ni devis.

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
- **Recherche d'emploi (décision du 2026-10-21, remplace le rôle de consultant)** : Delivery Manager, CTO, directeur de la production ou de projets IT, à Nantes ou en full remote, disponible immédiatement. La démo et les 24 plugins sont la **preuve** de ce que son profil affirme ; ils ne vendent plus de missions. Il communique uniquement par LinkedIn (son profil, point d'arrivée des CTA de la démo via `DEMO_PROFILE_URL`), jamais sur les canaux Sylius, et publie lui-même : Claude prépare les contenus. Le site `thibaut-monier.fr` n'existe pas.

## Acteur système

### Outil d'achat (simulateur)
- Envoie un `PunchOutSetupRequest` cXML ou une entrée OCI, reçoit le panier et poste un `OrderRequest`.
