# Audit fonctionnel B2B de la démo « Fournitures Pro » et des 24 plugins

Périmètre : captures connecté (claire / paul / sophie), captures anonymes, `config/demo/plugins.yaml`, `accounts.yaml`, `sprint-status.yaml`. Les audits accessibilité / UX / UI déjà réalisés (58 / 68 / 66) ne sont pas refaits : ils sont cités quand ils pèsent sur le verdict fonctionnel. Aucun détail de sécurité.

---

## 1. Verdict

La démo est une **bonne vitrine pour un développeur Sylius** (la couverture de briques B2B est large, la page Plugins et les parcours guidés de l'accueil sont clairs), mais **pas encore pour un décideur métier** : les écrans clés sont vides ou pauvres (panier vide, devis vide, crédit à 0, brouillons vides, suggestions IA vides, prix dégressifs invisibles sur la fiche produit), le catalogue est peu crédible (photos qui ne correspondent pas aux produits, ~80 références sans stock/délai) et l'ensemble n'apporte aucune histoire métier ni appel à l'action. Objectif 1 (donner envie de mettre les plugins en œuvre) : **partiellement rempli**, surtout pour des profils techniques. Objectif 2 (faire solliciter le consultant) : **quasi non rempli** : rien ne dit « voici ce que je peux faire pour votre projet » ni « voici comment me contacter » (liens à venir, mais aussi absence de contenu de conversion indépendant des liens).

| Critère | Note | Justification |
|---|---|---|
| 1. Démonstration fonctionnelle et technologique | **6,5/10** | Très large couverture (comptes société, devis, commande rapide CSV, approbation, encours, PunchOut cXML + OCI avec simulateur, MCP avec guide pas à pas, webhooks, Factur-X, GPSR, container-fill). Points forts réels : simulateur PunchOut, guide MCP, jauge de remplissage palette. Points faibles : plusieurs fonctions sont invisibles côté acheteur (paliers non affichés sur la fiche, pas de stock/délai), beaucoup d'écrans montrent un état vide, le copilote IA est un « générateur de démonstration » sans IA réelle (bandeau explicite en admin), le journal d'audit est noyé par 1 006 lignes de création issues de la remise à zéro. |
| 2. Attractivité visuelle | **5/10** | Charte propre (bandeau, logo « Fournitures Pro », cartes d'accès rapide, ligne orange), mais : photos hors sujet (une « cheville nylon » montre une équerre ornementale en fonte, la « scie à métaux » une scie ancienne en bois, la « clé » un gros plan de clé sans lecture produit), « Dernières offres » et « Derniers produits » affichent les mêmes articles en double, pied de page Sylius générique (newsletter -20 %, partenaires paiement), simulateur PunchOut sans habillage (page HTML brute, lien « English »). |
| 3. Attractivité fonctionnelle | **6/10** | Parcours bien nommés sur l'accueil (devis, commande rapide, validation des achats, PunchOut). Mais l'acheteur ne voit ni prix négocié ni palier ni disponibilité sur la fiche, le panier de départ est vide (il faut construire le scénario soi-même), et les scénarios longs (container-fill : 117 cartons, 13 couches) sont écrits comme des notices et non joués. |

Remarque transverse : « Les 23 plugins » est affiché sur l'accueil et À propos alors que le périmètre est de 24 (et Agentic Feeds est « Non publié »). Incohérence à lever, elle entame la crédibilité.

---

## 2. Parcours d'un prospect (DSI ou directeur e-commerce d'un distributeur industriel)

**Arrivée (anonyme, accueil)** : titre « Fournitures Pro, la démo B2B de Sylius », bouton « Se connecter avec un compte de démo », 3 cartes d'accès rapide, 4 « Parcours B2B à essayer ». Prix B2B invisibles en anonyme (point déjà connu et voulu : Channel Access), barre de démo sur deux lignes bilingues.

**30 secondes** : il comprend « boutique de fournitures industrielles + devis, commande rapide, validation, PunchOut ». Il ne comprend pas : qui a fait ça, ce que ça lui coûterait de l'avoir chez lui, pourquoi Sylius plutôt qu'un autre moteur. Le mot « démo de Sylius » prête à confusion : le prospect peut croire à une démo éditeur, pas à une réalisation d'un intégrateur indépendant. La mention « 23 plugins » est un bon signal de richesse, mais sans hiérarchie (qu'est-ce qui compte pour moi ?).

**5 minutes (connecté en Claire)** :
- Accueil connecté : même page avec « Dernières offres » = « Derniers produits » (doublon), grandes photos peu lisibles pour un catalogue industriel (pas de ligne de prix négocié, pas de stock).
- Catégorie Quincaillerie (≈ 9 pages) : prix HT, conditionnement (« boîte de 100 »), mention « dès 8,89 € HT par palier » : bon signal de tarification dégressive, mais l'écart (8,90 → 8,89) est ridicule et dessert le message. Photos incohérentes (voir §1).
- Fiche produit (ex. OUTI-019) : bloc « Remplissage palette et camion (indicatif) » de 9 lignes dont 7 à « 0 % » : lecture pénible, ressemble à un jeu de données de test ; bloc « Besoin de grandes quantités ? » avec ajout au devis : très bien, c'est le meilleur élément B2B de la fiche. Absents : tableau de paliers, prix contractuel, stock/délai, fiches techniques, produits associés/compatibles, avis (0 avis).
- Commande rapide : écran propre et compréhensible (coller SKU;qté ou CSV, aperçu, « tout ou rien »), l'exemple « SKU-001;2 » ne correspond pas aux vraies références (VISS-001…) : le prospect copie, obtient des erreurs, perd la confiance. À corriger en priorité.
- Devis : « Demander un devis » vide (message technique brut `sylius.ui.continue_shopping` visible), « Mes devis » montre un seul devis (Q-2024-001, « Proposition envoyée », date de 2026-09, numérotation 2024 incohérente). Pas de négociation visible (contre-proposition, historique, expiration).
- Mon entreprise : fiche société pauvre (nom, TVA, SIRET, 3 membres) : l'organigramme (rôles, plafonds, adresses) est là mais pas mis en valeur.
- Paiement à terme : plafond 10 000 €, encours 0 €, « Aucune pièce ouverte » : le plugin existe mais ne raconte rien (pas de factures échues, pas d'échéancier, pas de dépassement).
- Panier : vide. Le prospect n'y verra la jauge palette, les règles de remplissage ou l'approbation que s'il suit la notice.
- Page Plugins : très complète (6 catégories, scénario, compte conseillé, GitHub/Packagist), c'est **l'écran qui convainc le plus un technique**. Mais c'est une page de catalogue de dépôts, pas une page de solutions ; elle se termine par un bloc « Sylius Plus, l'offre officielle … leur compatibilité est à vérifier » qui, malgré sa neutralité de ton, ouvre exactement la comparaison que le positionnement veut éviter (voir §6).
- MCP : guide en 6 étapes, bien écrit, **vrai différenciateur** (rare chez les intégrateurs). L'URL affichée est `http://localhost:8080/_mcp` dans la capture (environnement local, à valider en production), page « Assistants IA » vide (« Aucun assistant connecté »).
- Simulateur PunchOut : très convaincant pour un acheteur grand compte, mais présenté comme un outil de test (page nue, jargon cXML/OCI/OrderRequest sans explication).
- Paul (approbateur) : « Approbations 1 en attente » : bon scénario court. Plafonds : 403 pour l'approbateur, normal (réservé à l'admin société) mais le menu ne devrait pas proposer ce qui renvoie un 403 sans explication.
- Admin (Sophie) : tableau de bord avec 7 commandes payées, 2 891 € de ventes, libellés « Sales / Paid orders count » en anglais ; menu admin riche et lisible (Entreprises, Inscriptions, Comptes de paiement à terme, Connexions assistants IA, Prix dégressifs, Rétractations, Commandes en préparation). Mais : commandes en préparation vide, Suggestions IA vide, Journal d'audit pollué (1 006 « Création » par « Ligne de commande (app:demo:reset) », libellés produits en anglais), « tmonier_punchout.ui.buyers » en clé brute (US-070 déjà prévue), demandes de devis : 2 lignes seulement.

**Ce qui convainc** : étendue de la couverture, page Plugins avec scénarios, commande rapide CSV, devis depuis la fiche produit, PunchOut simulé, parcours MCP, rôles société, back-office riche et cohérent, comptes préremplis et un-clic.

**Ce qui fait partir** : photos non crédibles, doublons, écrans vides, pas d'histoire (qui est ce distributeur, que veut-il résoudre ?), jargon (cXML, OCI, MCP, OrderRequest) non traduit en bénéfice, pied de page générique, mobile/contrastes (audits existants), clés de traduction brutes.

**Ce qui pousse (ou non) à contacter** : aujourd'hui rien ne pousse : aucune offre, aucune étude de cas, aucune question du type « Votre ERP est… ? ». Un DSI sort avec « beau travail technique » mais sans prochaine étape. Le CTA doit être pensé comme du contenu (voir §6) et pas seulement comme un lien.

---

## 3. Couverture fonctionnelle B2B

Légende : **Couvert** = un plugin répond de bout en bout et est visible ; **Partiel** = brique présente mais incomplète ou peu démontrée ; **Absent**. Importance pour un acheteur de projet B2B : Élevée / Moyenne / Faible.

| Capacité attendue | Statut | Plugin(s) / constat | Importance |
|---|---|---|---|
| Comptes société, membres, rôles | Couvert | company-accounts (admin / acheteur / approbateur), registration-approval | Élevée |
| Hiérarchie multi-niveaux (filiales, centres de coût) | Absent | Société à un seul niveau | Moyenne |
| Catalogue / assortiment par client ou groupe | Partiel | channel-access (accès par canal, prix masqués) ; pas d'assortiments par société | Élevée |
| Prix spécifiques par client / listes de prix contractuelles | Partiel | tier-pricing (règles par canal, groupe, variante) ; pas de prix négocié par société visible côté acheteur | Élevée |
| Paliers de quantité | Couvert (côté admin) | tier-pricing ; affichage « dès X € par palier » en liste, **tableau de paliers absent de la fiche produit** | Élevée |
| Devis / négociation | Partiel | quote-request (demande, proposition envoyée, checkout du devis) ; pas de contre-proposition ni de fil de négociation démontrés | Élevée |
| Commande rapide / réassort par SKU, CSV | Couvert | quick-order, B2B MCP (commande par SKU via assistant) | Élevée |
| Listes d'achat partagées | Couvert | Listes de l'entreprise (company-accounts / wishlists « Atelier », 5 articles) | Moyenne |
| Réassort automatique, abonnement, commandes récurrentes | Absent | — | Moyenne |
| Circuit d'approbation | Couvert | purchase-approval + order-hold (plafond, e-mail, attente) | Élevée |
| Encours, plafond, paiement à terme | Couvert | trade-credit (plafond, encours, pièces ouvertes, comptes admin) ; relances, échéances, blocage auto non démontrés | Élevée |
| Conditions de paiement par client (30 j fin de mois…) | Partiel | « Conditions de paiement » dans l'admin ; peu mis en scène | Élevée |
| Multi-adresses / multi-sites de livraison | Partiel | Carnet d'adresses de la société (onglet Adresses) ; pas de livraisons multi-sites sur une commande | Moyenne |
| E-procurement PunchOut | Couvert | punchout-cxml (cXML + OCI), simulateur, acheteurs / journal admin | Élevée (grands comptes) |
| Facturation électronique | Partiel | facturx (Factur-X EN 16931 depuis le plugin Invoicing) ; pas de transmission PA/PDP (France 2026-2027), pas d'e-reporting | Élevée (France) |
| Intégration ERP / PIM (webhooks, API) | Partiel | webhook-outbox (signatures, relances, échecs définitifs) ; pas de connecteur ERP exemple, pas d'import PIM/prix/stock | Élevée |
| Self-service après-vente (retours, RMA) | Partiel | withdrawal (rétractation B2C) ; pas de retour/RMA B2B, pas de réclamation | Moyenne |
| Factures, avoirs, relevés dans l'espace client | Partiel | trade-credit (relevé), Factur-X admin ; téléchargement facture côté acheteur non démontré | Élevée |
| Suivi de commande / expédition côté acheteur | Partiel | timeline (admin : chronologie commandes et clients) ; suivi transporteur absent | Moyenne |
| Stock multi-entrepôts, disponibilité, délais | Absent | Stock simple, aucun affichage de disponibilité ni de délai sur la fiche | Élevée |
| Logistique : conditionnement, palettes, camions | Couvert | container-fill (jauge palette/camion/conteneur, règles B2B, expédition par conteneur) ; conditionnement « boîte de 100 » | Moyenne |
| Configurateur / variantes complexes / produits à options | Absent | — | Moyenne (selon secteur) |
| Contrats / accords-cadres (volumes, prix négociés sur durée) | Absent | — | Élevée (industrie, grossiste) |
| Commande pour le compte du client (commercial, téléphone) | Couvert | admin-order-creation (création et réplication) ; « impersonation » client non couvert | Élevée |
| Recherche par référence, références croisées, pièces compatibles | Partiel | Recherche catalogue standard, quick-order par SKU ; plugin parc d'équipements en cours | Élevée (pièces détachées) |
| IA / agents côté acheteur | Couvert | b2b-mcp (lecture commandes, réassort, devis, crédit, OAuth 2.1 PKCE) | Moyenne (différenciateur) |
| IA côté catalogue | Partiel | ai-catalog-copilot (démo sans fournisseur IA réel) | Moyenne |
| Flux produits vers assistants d'achat / comparateurs | Couvert | agentic-feeds (non publié) | Faible en B2B pur |
| Conformité GPSR | Couvert | gpsr | Moyenne |
| Rétractation | Couvert | withdrawal (B2C) | Faible en B2B |
| Opérations back-office (audit, actions en masse, palette de commandes) | Couvert | audit-log, bulk-actions, command-palette, timeline | Moyenne |
| Inscription contrôlée (validation KYB, N° TVA) | Partiel | registration-approval ; vérification TVA/SIRENE/VIES non couverte | Élevée |
| Tarification multi-devises, TVA intracom, exonérations | Absent / non démontré | — | Élevée (export) |
| Reporting acheteur (dépenses par centre de coût, budgets) | Absent | — | Moyenne |
| Tableau de bord société (budgets, plafonds par membre) | Partiel | Plafonds en admin société (403 pour l'approbateur), pas de visualisation | Moyenne |

---

## 4. Manques prioritaires

### 4.1 Améliorations de la démo (sans nouveau plugin)

| # | Amélioration | Valeur pour le prospect | Effort |
|---|---|---|---|
| D1 | Afficher le tableau de paliers (et le prix négocié de la société, ex. « Prix Acme : −12 % ») sur la fiche produit, au catalogue et au panier | Rend le prix B2B tangible : c'est la question n° 1 d'un acheteur | S |
| D2 | Données de catalogue crédibles : photos cohérentes, ≈ 150 références réalistes (visserie, EPI, outillage), stock, délais, familles, fiches techniques PDF | Crédibilité immédiate, base pour recherche/filtres | M |
| D3 | Pré-remplir un panier de départ (liste Atelier) et des commandes historiques, un devis en négociation, des échéances de crédit, une commande en attente d'approbation : chaque écran montre un état « vivant » | Fin des écrans vides ; chaque fonction se comprend en un regard | M |
| D4 | Corriger l'exemple de commande rapide (références réelles VISS-001…) et le message `sylius.ui.continue_shopping`, la clé `tmonier_punchout.ui.buyers`, les libellés admin en anglais (Sales / Paid orders count, noms de produits) | Supprime les signaux « prototype » | S |
| D5 | Supprimer le doublon « Dernières offres / Derniers produits », remplacer par « Vos références habituelles », « Réassort rapide » et « Promotions négociées » | Page d'accueil B2B crédible | S |
| D6 | Journal d'audit : masquer les créations de la remise à zéro et ajouter 5-6 événements métier (changement de prix, validation d'inscription, plafond modifié) | L'audit devient démontrable | S |
| D7 | Habiller le simulateur PunchOut (thème Ariba / SAP fictif, étapes numérotées, retour OrderRequest lisible, schéma du flux) | Le morceau le plus vendeur pour grands comptes devient présentable | S |
| D8 | Rendre la page « Assistants IA » et l'admin IA utiles : vidéo ou transcript d'une conversation Claude réelle, dataset de suggestions pré-générées | Évite l'impression de coquille vide | S |
| D9 | Documenter / afficher les 403 comme des messages « votre rôle ne permet pas… » et masquer les entrées de menu inaccessibles | Montre la gouvernance des rôles au lieu de la cacher | S |
| D10 | Aligner « 23 » / « 24 » plugins et marquer clairement « publié / en cours / à venir » | Cohérence et confiance | S |
| D11 | Scénarios « rejouables » sous forme de visites guidées (étapes, bouton « Je lance ce scénario ») au lieu de paragraphes | Réduit le temps de découverte ; indispensable pour container-fill | M |
| D12 | Fiche produit : condenser la jauge palette (n'afficher que les conteneurs pertinents, le reste en « détails ») | Lisibilité | S |
| D13 | Ajouter le suivi des commandes et téléchargement facture PDF/Factur-X côté acheteur, relevé de compte | Scénarios SAV et comptabilité fournisseur | M |

### 4.2 Nouveaux plugins ou extensions de plugins

| # | Plugin / extension | Valeur pour le prospect | Effort |
|---|---|---|---|
| P1 | **Parc d'équipements client** (customer-asset-management, en cours : US-056..060) | Réponse directe au besoin de pièces détachées : « mes machines → pièces compatibles » ; très fort pour fabricants et distributeurs de maintenance | M (déjà engagé) |
| P2 | **Contrats / accords-cadres** : prix et volumes négociés sur une période, suivi de consommation, alertes d'expiration | Pilier du B2B industriel et des appels d'offres | L |
| P3 | **Assortiments et listes de prix par société** (catalogue dédié, prix client, réf. client ↔ réf. fournisseur) | Réponse à « mes prix, mon catalogue » ; très demandé | M |
| P4 | **Stock multi-entrepôts et délais** (disponibilité par site, date de livraison promise, livraison partielle) | Absent aujourd'hui et pourtant décisif dans la décision d'achat | L |
| P5 | **Connecteur ERP de référence** (modèle d'export commandes / import prix, stock, clients ; ex. fichier plat + API ; contrats d'événements) au-dessus de webhook-outbox | Rend la brique « intégration » tangible, base de missions | M |
| P6 | **Retours / RMA B2B** (demande de retour par ligne, motifs, avoir, étapes) | Self-service SAV complet | M |
| P7 | **Facturation électronique France (PA/PDP) et e-reporting** au-dessus de facturx | Obligation 2026-2027, sujet porteur de missions (voir §5) | L |
| P8 | **Budgets et centres de coût** (budget par membre/service, ventilation analytique, rapports de dépenses) | Valeur directe pour acheteurs des grands comptes | M |
| P9 | **Réassort automatique / commandes récurrentes** (planification, seuils, validation) | Forte rétention, usage MRO | M |
| P10 | **Impersonation / vente assistée** (le commercial se connecte « en tant que » avec traçabilité) | Cas d'usage commerciaux terrain | S |
| P11 | **Vérification d'inscription** (VIES, SIRENE/SIRET) en extension de registration-approval | Qualification KYB automatisée | S |
| P12 | **Configurateur / produits à options** (longueur, matière, usinage à la demande, devis automatique) | Utile pour fabricants ; effort élevé | L |
| P13 | **Livraison multi-sites** (répartir une commande sur plusieurs adresses) | Distributeurs, chaînes de chantier | M |

---

## 5. Idées « bankables » pour le profil du consultant

Classées par potentiel de mission payante.

| Rang | Offre | Pourquoi c'est vendable | Client type | Mise en scène dans la démo |
|---|---|---|---|---|
| 1 | **Intégration ERP / PIM / WMS** (commandes, prix, stock, clients, factures) | Premier budget de tout projet B2B ; douleur n° 1 ; mission longue et récurrente | Distributeur ou grossiste 50-500 M€ avec ERP (Sage, Divalto, Odoo, SAP B1, Microsoft Dynamics) | Page « Intégration » : schéma ERP ↔ Sylius, événements webhook visibles (outbox), fichier d'exemple d'import prix/stock, journal des échanges en admin |
| 2 | **Facture électronique 2026-2027 en France (Factur-X + PA/PDP, e-reporting)** | Échéance légale = budget et urgence ; peu d'intégrateurs Sylius outillés | Toute PME/ETI vendant en France | Page « Conformité » avec calendrier réglementaire, facture Factur-X téléchargeable côté acheteur, validation EN 16931, flux vers une PA fictive |
| 3 | **PunchOut cXML/OCI pour grands comptes** (Ariba, Coupa, SAP) | Tickets élevés, peu de concurrence chez les intégrateurs Sylius, cycle de vente long mais récurrent | Fabricants et distributeurs fournissant des grands comptes industriels | Simulateur habillé en mode Ariba/Coupa, pas à pas, timing de session, journal admin, schéma du flux |
| 4 | **Migration vers Sylius depuis Magento / Adobe Commerce, PrestaShop, Shopware, plateformes propriétaires** | Fin de vie, coûts de licence, fatigue de maintenance ; mission cadrée et facturable | ETI sur plateforme vieillissante | Page « Migrer » : étapes (audit, mapping, reprise de données, redirections 301, bascule), checklist de reprise, comparatif de modèles de données neutre |
| 5 | **IA agentique / MCP pour acheteurs et commerciaux** | Sujet porteur, effet vitrine, accès à la direction ; rares références | Distributeurs innovants, directions digitales | Vidéo de conversation Claude/ChatGPT réelle, transcript ; page « Assistants » expliquant périmètre (jamais de paiement) et traçabilité |
| 6 | **Audit B2B / cadrage « Sprint 0 »** (2 à 5 jours : cartographie des besoins, architecture, chiffrage) | Porte d'entrée faible risque pour le client, forte conversion en mission d'intégration | Tout porteur de projet B2B | Questionnaire interactif « Évaluer mon projet » sur la démo, rapport de cadrage type téléchargeable |
| 7 | **Packs d'intégration sectoriels** (MRO / visserie, emballage, matériel électrique, BTP, pièces détachées / parc machines) | Réduit le time-to-value, tarif forfaitaire, argument clair | Secteurs ciblés | « Cas d'usage par secteur » avec jeu de données et scénarios dédiés (voir §6) |
| 8 | **Reprise et stabilisation de projet Sylius** (audit de code, performance, montée de version 1.x → 2.x) | Marché de la dette technique ; missions courtes | Sociétés avec un Sylius existant | Fiche « Audit de qualité » (outillage qualité des plugins : ECS, PHPStan, Rector déjà en place) |
| 9 | **Place de marché / multi-vendeurs et portail fournisseurs** | Gros projets, plus lourd à produire | Groupes de distribution | Idée à consigner, pas à mettre en scène maintenant |
| 10 | **Support et maintenance sous contrat** (forfait, SLA, mises à jour des plugins) | Revenu récurrent, justifie l'open source | Clients de l'intégration | Mention sobre dans la page « Intégrer ces plugins » |

---

## 6. Mise en scène commerciale de la démo (ton neutre, sans comparaison)

Règles : jamais de « alternative à… » ni de comparatif avec une offre éditeur ; supprimer ou neutraliser le bloc final de la page Plugins qui renvoie vers l'offre commerciale de Sylius (la formule « à vérifier selon votre projet » reste une comparaison implicite). Remplacer par une phrase factuelle : « Ces plugins s'installent sur Sylius 2.3. »

### 6.1 Où insérer le site et le contact (liens à venir, US-068/069)

| Emplacement | Forme |
|---|---|
| Bandeau haut de la démo (barre noire) | Remplacer « Démo B2B / B2B demo » + 3 liens par un seul lien discret « Un projet B2B ? Parlons-en » vers la page contact (URL en configuration, bloc masqué si vide, comme prévu par US-069) |
| Pied de page (remplace le pied Sylius générique : newsletter, partenaires paiement) | Colonne « Cette démo » : à propos, plugins, parcours, intégrer, contact ; mentions « réalisé par [nom] » avec lien site ; supprimer partenaires de paiement et réseaux sociaux génériques |
| Page Plugins, haut et bas | Bandeau « Vous avez un projet ? » + bouton « Échanger sur votre projet » après les filtres |
| Page À propos | Court paragraphe « Qui a conçu cette démo » avec lien et contact |
| Chaque fiche plugin (carte) | Ligne « Besoin d'une intégration ? Contact » |
| Fin de chaque parcours guidé | Écran de sortie : « Ce que vous venez de voir peut être déployé chez vous : en savoir plus / contact » |
| README des 24 dépôts | Bloc standard (US-068) |

### 6.2 Propositions concrètes

1. **Parcours guidés par persona** (Acheteuse, Approbateur, Directeur financier, Directeur e-commerce, Responsable informatique) : 4 à 6 étapes avec barre de progression, « Je lance ce scénario » qui pré-remplit le panier / crée un devis, « Suivant » : remplace les scénarios en paragraphes de la page Plugins.
2. **« Cas d'usage par secteur »** : quatre pages (visserie MRO, emballage, matériel électrique, pièces détachées) listant besoin → plugins → scénario → résultat chiffré fictif. Chaque page se termine par un appel à l'action.
3. **Page « Intégrer ces plugins dans votre projet »** : trois formules neutres (Cadrage / Intégration / Accompagnement), questions préalables (ERP ? PunchOut ? facturation électronique ? volumétrie ?), architecture type, délais indicatifs, formulaire contact (URL à venir). Ne contient ni prix ni comparatif avec d'autres éditions.
4. **Études de cas fictives** (clairement signalées « cas fictif ») : « Distributeur de visserie : −40 % de saisie de commandes grâce à la commande rapide + PunchOut », « Grossiste : approbation multi-niveaux », etc., avec schéma avant/après.
5. **Vidéos / GIF courts (30-60 s)** : un GIF par plugin phare (PunchOut, devis, commande rapide, approbation, MCP, container-fill) intégrés à la page Plugins ; une vidéo de 3 minutes « Visite complète ». La captation actuelle est déjà un bon matériau.
6. **Page d'accueil réorganisée** : bandeau « Ce que vous allez voir en 5 minutes » en 3 colonnes (Acheter, Valider, S'intégrer) ; « Pourquoi cette démo » ; « Choisissez votre rôle » avec connexion en un clic.
7. **Mode « Présentation commerciale »** (activable par le consultant) : cache les barres techniques, joue un script, remet la démo à zéro à la demande (pas seulement à 03:00).
8. **Page « Intégrations »** : schéma ERP, PIM, transporteurs, PA/PDP, Ariba/Coupa, assistants IA, avec état « disponible / sur mesure / à venir » ; sert de pont entre plugins et missions.
9. **Bloc de preuve technique** : tableau qualité (PHPStan niveau max, tests, couverture), compatibilité Sylius 2.3, versions publiées, Packagist, licences ; rassure les DSI.
10. **Mini-diagnostic en ligne** (6 questions) qui recommande les plugins utiles et se termine par « Recevoir ma recommandation » (lead qualifié).
11. **Newsletter -20 %** (bandeau B2C par défaut) : à retirer du canal B2B, incohérent pour un acheteur professionnel.

---

## 7. Top 10 des actions (impact sur les objectifs 1 et 2)

| # | Action | Objectif | Impact | Effort |
|---|---|---|---|---|
| 1 | Rendre le catalogue crédible : photos cohérentes, 150 références, stock, délais, fiches techniques (D2) | 1, 2 | Très fort | M |
| 2 | Afficher paliers et prix négocié sur fiche, liste et panier (D1) | 1 | Très fort | S |
| 3 | Données de démo « vivantes » : panier préparé, devis en négociation, approbation en attente, échéances de crédit, commandes historiques (D3) | 1, 2 | Très fort | M |
| 4 | Parcours guidés par persona avec écran de sortie vers le contact (6.2.1) | 1, 2 | Très fort | M |
| 5 | Page « Intégrer ces plugins dans votre projet » + cas d'usage sectoriels + mini-diagnostic (6.2.2, 3, 10) | 2 | Très fort | M |
| 6 | Réécrire l'accueil (supprimer doublon, rôles, promesse en 3 colonnes), pied de page dédié, suppression de la newsletter B2C sur le canal B2B (D5, 6.1, 6.2.11) | 1, 2 | Fort | S |
| 7 | Habiller le simulateur PunchOut et expliquer le flux (D7) ; ajouter la vidéo de conversation MCP | 1 | Fort | S |
| 8 | Neutraliser le bloc « Sylius Plus » de la page Plugins et aligner « 24 plugins » (D10) | 2 (positionnement) | Fort | S |
| 9 | Corriger les signaux « prototype » : exemple SKU, clés brutes, libellés anglais, journal d'audit pollué (D4, D6) | 1 | Moyen | S |
| 10 | Lancer le connecteur ERP de référence et le plugin parc d'équipements (P5, P1) pour fournir des scénarios « pièces compatibles » et « flux ERP » | 1, 2 | Fort | M |

---

## Résumé (10 lignes)

1. Démo large (24 plugins, parcours devis / commande rapide / approbation / crédit / PunchOut / MCP) mais surtout convaincante pour un profil technique.
2. Notes : fonctionnel/technique 6,5/10, visuel 5/10, attractivité fonctionnelle 6/10 ; la conversion vers le consultant est quasi nulle faute de contenu commercial.
3. Freins majeurs : photos et catalogue peu crédibles, doublons à l'accueil, paliers invisibles sur la fiche, écrans vides (panier, devis, crédit, IA, brouillons), clés de traduction brutes, « 23 » vs 24 plugins.
4. Manques fonctionnels B2B : stock multi-entrepôts et délais, contrats/accords-cadres, assortiments par société, RMA, ERP de référence, e-facture France de bout en bout, budgets.
5. Plugins à créer en priorité : parc d'équipements (en cours), contrats-cadres, assortiments/prix par société, stock et délais, connecteur ERP.
6. Offres bankables classées : intégration ERP/PIM, facture électronique 2026-2027, PunchOut grands comptes, migration vers Sylius, IA agentique/MCP, audit/cadrage.
7. Mise en scène : parcours par persona, cas d'usage sectoriels, page « Intégrer ces plugins », mini-diagnostic, vidéos/GIF, pied de page dédié.
8. Positionnement : retirer le bloc final renvoyant vers l'offre commerciale de Sylius sur la page Plugins ; ton neutre partout.
9. Liens du consultant : bandeau haut, pied de page, page Plugins, fin de chaque parcours, README (URL en configuration).
10. Suite donnée : EPIC-11 à EPIC-14 et la feuille de route `project-management/backlog/roadmap-ouverture-et-communication.md`.
