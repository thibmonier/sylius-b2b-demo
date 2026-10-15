# PRD — Démo B2B Sylius (sylius-b2b-demo)

> Phase 2 (Planification), workflow claude-craft Enterprise, 2026-10-15. Statut : **brouillon à valider**.
> Sources : `analysis/*` (synthèse, contraintes, risques, options techniques, rapports bruts A à D) et décisions de la porte d'analyse (`workflow-status.yaml`).
> Personas : `personas.md`. Backlog : `backlog/`.

## 1. Vision

Une boutique B2B Sylius 2.3 crédible, en ligne en permanence, où un intégrateur ou un marchand découvre en quelques minutes ce que les plugins tmonier apportent : acheter comme un acheteur d'entreprise (catalogue fermé, prix négociés, commande rapide, devis, validation, crédit, PunchOut, factures électroniques) et piloter comme un back-office (création de commande, timeline, audit, actions de masse, IA). Chaque fonctionnalité porte un badge qui renvoie au plugin. Les données sont remises à zéro chaque nuit.

## 2. Problème

- Les 21 plugins ne se voient que dans leurs applications de test, une par une, et leur valeur combinée (le parcours B2B complet) n'est montrée nulle part.
- Un prospect doit installer Sylius et des plugins pour les évaluer, une barrière forte.
- Plusieurs manques B2B (catalogue fermé, référence de commande, listes d'achat, adresses d'entreprise) rendraient la démo incohérente face aux suites B2B du marché.

## 3. Objectifs et non-objectifs

**Objectifs**
1. Installer les **21 plugins** depuis Packagist sur Sylius-Standard 2.3, sans conflit, chacun avec au moins **un scénario visible**.
2. Proposer un **thème B2B** dérivé du thème officiel :
   - catalogue fermé configurable ;
   - présentation produit B2B (SKU, conditionnements, paliers, stock) ;
   - page d'accueil et page « Plugins » ;
   - **badges** de plugin dans le shop et l'admin.
3. Fournir un **jeu de données B2B cohérent** (catalogue industriel FR/EN, entreprises, rôles, crédit, limites, paliers, devis, palettes, GPSR, acheteurs PunchOut, historique) et un **canal B2C** secondaire.
4. Créer les **plugins manquants**, publiés comme les autres (tests, CI, revue de sécurité) :
   - accès B2B (catalogue fermé) ;
   - référence de commande (PO) ;
   - listes d'achat partagées (après spike de la wishlist officielle) ;
   - adresses d'entreprise (évolution de company-accounts).
5. Livrer un environnement **Docker** (FrankenPHP / Caddy), identique en local et sur **Railway**, avec les tâches planifiées et une **remise à zéro nocturne**, pour un budget de **25 $/mois au plus**.
6. Ouvrir une démo **sûre** : comptes publics sans danger, aucun envoi ni appel sortant réel, admin de démo restreint.

**Non-objectifs**
- Promotion sur les canaux Sylius.
- Thème publié comme paquet séparé (éventuellement plus tard).
- Commandes récurrentes.
- CMS de pages.
- Paiement en ligne réel.
- Haute disponibilité (une seule instance).
- RBAC admin complet (seulement des restrictions de démo).

## 4. Utilisateurs

Voir `personas.md` :
- visiteurs : **Hugo** (intégrateur), **Nadia** (responsable e-commerce B2B) ;
- comptes de démo : **Claire** (acheteuse), **Paul** (approbateur), **Isabelle** (administratrice d'entreprise), **Sophie** (back-office), **Léa** (cliente B2C) ;
- **Thibaut** (mainteneur de la démo) ;
- l'**outil d'achat** (acteur système PunchOut).

## 5. Parcours cibles

1. **Découverte** : Hugo arrive sur la page d'accueil, lit le bandeau de démo et la page « Plugins » (21 cartes, chacune avec son scénario et son badge), puis choisit « Se connecter en tant que Claire ».
2. **Acheteuse** : Claire voit le catalogue fermé s'ouvrir à la connexion. Elle consulte ses prix négociés et ses paliers, commande par SKU (quick-order), ajoute une liste d'achat de l'entreprise et saisit sa référence PO au checkout. Sa commande dépasse sa limite et part en validation.
3. **Approbateur** : Paul valide la commande. Le paiement sur compte réserve le crédit de l'entreprise, et la facture Factur-X est générée.
4. **Devis** : Claire demande un devis depuis le panier. Sophie y répond dans l'admin, et Claire l'accepte au prix figé.
5. **Palettes** : un acheteur remplit une palette (jauge de remplissage) sur le catalogue des produits palettisables.
6. **PunchOut** : depuis le simulateur d'outil d'achat, Hugo ouvre une session cXML ou OCI, remplit le panier et le renvoie, puis l'outil envoie un bon de commande (OrderRequest).
7. **Back-office** : Sophie crée une commande pour un client, consulte la timeline et le journal d'audit, utilise la palette de commandes (Ctrl+K), les actions de masse et le copilote IA (générateur de démo).
8. **B2C** : Léa achète sur le canal grand public, voit les informations GPSR et exerce son droit de rétractation.
9. **E-mails** : tous les e-mails envoyés se lisent dans la boîte Mailpit publique.
10. **Remise à zéro** : chaque nuit à 03:00 (Europe/Paris), les données reviennent à leur état initial.

## 6. Exigences fonctionnelles

### Socle et environnement
- **FR-1** Sylius-Standard 2.3 avec les 21 plugins depuis Packagist (versions taguées) et les plugins communautaires requis (sylius/invoicing-plugin, sylius/mcp-server-plugin, symfony/ai-bundle), configurés et migrés.
- **FR-2** Image Docker FrankenPHP multi-stage (Composer, puis Node / Encore, puis image finale avec wkhtmltopdf et cache préchauffé) ; `docker compose` local avec MySQL 8.4 et Mailpit.
- **FR-3** Tâches planifiées dans le conteneur (supercronic) : tâches des plugins, envoi des webhooks, remise à zéro nocturne.

### Données
- **FR-4** Canal **B2B** (principal) et canal **B2C** (secondaire), en français et en anglais, EUR, zones et taxes FR/UE.
- **FR-5** Catalogue industriel généré depuis des CSV versionnés :
  - 150 à 250 produits, taxons, attributs (normes, conditionnements), SKU, MPN, EAN ;
  - poids et dimensions ;
  - illustrations générées.
- **FR-6** Entreprises fictives, membres et rôles, comptes de démo documentés.
- **FR-7** Données des plugins cohérentes entre elles : crédit au niveau de l'entreprise, limites d'approbation adaptées aux paliers, devis, types de palettes et produits palettisables, GPSR, acheteurs PunchOut, suggestions IA, historique de commandes et factures.
- **FR-8** Commande de remise à zéro idempotente (base, fixtures, médias) avec priorités de fixtures explicites.

### Thème et expérience de démo
- **FR-9** Thème B2B dérivé du thème officiel, par ajouts dans les twig hooks et surcharges ciblées. Les hookables des plugins ne sont jamais remplacés.
- **FR-10** Page d'accueil B2B ; page « Plugins » qui liste les 21 plugins et les nouveaux (description, scénario, lien GitHub, badge).
- **FR-11** Bandeau de démo : comptes avec connexion en un clic, lien vers Mailpit, heure de la prochaine remise à zéro.
- **FR-12** Badges « Plugin » (icône, infobulle et lien) à côté de chaque fonctionnalité de plugin, dans le shop et dans l'admin, à partir d'un registre ; affichage activable.
- **FR-13** Mentions légales et page « À propos de la démo » (données fictives, remise à zéro, pas d'e-mail réel).

### Nouveaux plugins
- **FR-14** **Accès B2B** : mode par canal (ouvert, prix masqués, fermé), routes exemptées, masquage des prix et des boutons d'achat, inscription avec validation par l'admin en option.
- **FR-15** **Référence de commande** : référence acheteur (PO) et centre de coût au checkout, obligatoires ou non par canal (ou par entreprise si company-accounts est présent). Reprise dans l'admin, les e-mails, Factur-X (BT-10) et le PunchOut OrderRequest.
- **FR-16** **Listes d'achat** : listes partagées par l'entreprise et ajout au panier en un clic. Spike de la wishlist officielle d'abord.
- **FR-17** **Adresses d'entreprise** : carnet d'adresses partagé par les membres (company-accounts 1.2).

### Intégration et scénarios
- **FR-18** Corriger les interactions relevées (I1 à I3) : périmètre de container-fill, blocage pendant une session PunchOut, effets d'un OrderRequest. Les correctifs vont dans les plugins concernés (patch releases).
- **FR-19** Simulateur d'outil d'achat (cXML et OCI, réception du panier, envoi d'OrderRequest).
- **FR-20** Mailpit public en lecture seule.
- **FR-21** Récepteur de webhooks interne à la démo (aucun appel sortant).
- **FR-22** Guide de connexion MCP (Claude) au compte de démo.
- **FR-23** Correctifs des plugins publiés : agentic-feeds sur Packagist, README de punchout.

### Hébergement et sécurité
- **FR-24** Déploiement Railway (web, MySQL, Mailpit) depuis GitHub, migrations en pre-deploy, healthcheck, domaine HTTPS.
- **FR-25** Remise à zéro nocturne sur Railway, supervisée (journal, échec visible).
- **FR-26** Admin de démo restreint : configuration critique en lecture seule (administrateurs, canaux, passerelles, URLs de webhooks, clés).
- **FR-27** Durcissement : rate limiting, aucun envoi ni appel sortant, en-têtes de sécurité, aucune donnée réelle.

## 7. Exigences non fonctionnelles

- **NFR-1 Performance** : page catalogue ou fiche produit en moins de 800 ms au p95 sur Railway (cache chaud).
- **NFR-2 Remise à zéro** : moins de 10 minutes, coupure de 5 minutes au plus, entre 03:00 et 03:15 (Europe/Paris).
- **NFR-3 Coût** : 25 $/mois au plus (limite d'usage Railway posée).
- **NFR-4 Sécurité** : comptes publics sans privilège dangereux, aucun secret dans le dépôt, revue de sécurité indépendante avant ouverture (OWASP 2025).
- **NFR-5 Qualité** : CI verte (build, tests de fumée des pages clés et des scénarios), ECS / PHPStan sur le code de la démo, nouveaux plugins au même niveau que les existants.
- **NFR-6 Accessibilité** : WCAG 2.2 AA pour le thème (contrastes, navigation clavier des badges et du bandeau).
- **NFR-7 i18n** : français et anglais pour tout contenu de la démo.
- **NFR-8 Maintenabilité** : mise à jour Sylius ou plugins en moins d'une heure (thème par ajouts, versions épinglées, Renovate ou Dependabot).

## 8. Métriques de succès

- 21 plugins sur 21 actifs, chacun avec au moins un badge et un scénario.
- 10 remises à zéro nocturnes consécutives sans intervention.
- Coût mesuré au premier mois inférieur ou égal à 25 $.
- Les 9 parcours du §5 passent en test de fumée CI.
- Aucun constat critique ni majeur ouvert à l'ouverture.

## 9. Hypothèses et dépendances

- Les plugins communautaires requis restent compatibles avec Sylius 2.3 (résolution validée le 2026-10-15).
- La wishlist officielle Sylius peut ne pas convenir (Sylius 2.0 à 2.2 annoncé) : plugin maison en repli.
- Railway Hobby autorise trois services (non documenté) et une limite d'usage.
- Le domaine Railway par défaut suffit (domaine personnalisé facultatif).

## 10. Questions ouvertes

- Q1 : Domaine personnalisé (ex. `demo-b2b.<domaine>`) ou domaine Railway ?
- Q2 : Les nouveaux plugins sont-ils publiés en open source sur Packagist comme les autres ? (Hypothèse : oui, MIT.)
- Q3 : Faut-il montrer l'iframe PunchOut (SameSite=None sur toute l'application) ou seulement la fenêtre ? (Hypothèse : fenêtre.)
