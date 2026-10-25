# Tâches : US-199 : Refonte de la page « Mon compte » dans la continuité de la home

## Informations US

- **Epic** : EPIC-12
- **Persona** : Claire (acheteuse), Paul (approbateur) et Isabelle (administratrice) d'Acme Industries ; Camille (recruteuse IT)
- **Story Points** : 5 (Should, **protégée**)
- **Sprint** : sprint-30-compte-tableau-de-bord-et-tva
- **Worker proposé** : w199 (orchestrateur pour la maquette)
- **Story** : [US-199](../../../backlog/user-stories/US-199.md)

## Résumé de la US

**En tant qu'** utilisatrice B2B connectée  
**Je veux** une page « Mon compte » qui montre d'abord ce qui m'attend selon mon rôle  
**Afin de** retrouver en un coup d'œil mes commandes, mes approbations, mes devis et les réglages de ma société

## Règles et contraintes

- **Maquette d'abord** : maquette Claude Design (bureau et téléphone, trois rôles) dans une nouvelle page « Mon compte » du canevas unique, publiée par l'orchestrateur et **validée par Thibaut (T-199-04) avant toute intégration Twig**. Les tâches de code T-199-05 à T-199-11 sont 📅 tant que son GO n'est pas donné et noté au registre.
- **Rendu réel d'abord** : captures connectées en local seulement (127.0.0.1, comptes des fixtures), `bin/render-page` à cache unique (T-TECH-30-01), vignettes réelles (T-TECH-30-03).
- **Reprise d'US-186, d'US-189 et du menu d'en-tête du sprint 26** : jetons de `b2b.scss`, polices auto-hébergées (CSP `font-src 'self'`), AAA, cibles de 44 px, `prefers-reduced-motion`, 360 et 1280 px sans défilement horizontal ; cohérence avec le menu « Bonjour <prénom> » (Mon compte, Approbations, Déconnexion).
- Canal B2B seulement : **le compte B2C reste inchangé** ; les hooks portent `not is_granted('DEMO_CONSUMER_CHANNEL')`.
- **Entrées des plugins reprises par leurs hooks existants, sans modifier les plugins** ; un raccourci n'est affiché que si le rôle y a accès (aucun lien vers un refus d'accès).
- Sous-pages du compte (profil, adresses, commandes) dans la même charte : **négociables** (hors périmètre si la marge est consommée).
- Fichiers communs (règle 21) : hooks dans `config/packages/demo_theme_b2b_account.yaml` (fichier propre à la story), styles dans `assets/shop/styles/_account.scss` (une ligne d'import dans `b2b.scss`), domaine de traduction `account` ; `docs/theme-b2b.md` : seulement la section « Mon compte », `git diff` relu avant le commit.
- Ton neutre, aucune comparaison avec Sylius Plus ; aucun identifiant saisi en production ; aucun détail de sécurité dans le dépôt public.
- Tests uniquement par `WORKER_ID=w199 TEST_DB_NAME=b2b_w199 TEST_CACHE_DIR=var/cache-w199 bin/run-tests`, dans l'arbre partagé (règle 22) ; preuve : `bin/proof-rebase`.

## Lieu de vérification

**Local et image reconstruite** pour les trois comptes (Claire, Paul, Isabelle) et le compte B2C (Léa) : pa11y WCAG2AAA FR et EN, 360 et 1280 px. **Production** (lecture seule, sans connexion) : sans objet pour la page connectée ; relevé que l'adresse du compte renvoie bien vers la connexion.

## Fichiers principaux touchés

- `templates/shop/theme_b2b/account/` (nouveau), `config/packages/demo_theme_b2b_account.yaml` (nouveau)
- `assets/shop/styles/_account.scss` (nouveau) et une ligne d'import dans `b2b.scss`, `translations/account.{fr,en}.yaml`
- `tests/Functional/Demo/` (nouveaux fichiers ; tests existants du compte, des menus et des badges relus)
- `config/demo/proof.json` (par `bin/proof-rebase`), `docs/theme-b2b.md` (section « Mon compte »), `docs/images/account-*`

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-199-01 | [DOC] | Cadrage : inventaire du tableau de bord du compte relu contre le code (hooks `sylius_shop.account.dashboard.*`, entrées des plugins : société et membres, approbations, devis, listes d'achat, liste Atelier, actifs client, relevé de crédit, tableau de bord des achats, connexions MCP ; `account_menu` ; droits de chaque rôle ; sous-pages ; badges « propulsé par ») ; données par rôle (commandes, devis, approbations en attente) ; plan des tests | 0,75 h | - | Claude | ✅ |
| T-199-02 | [TEST] | État de référence « avant » : tests du compte et des menus verts sur `main` ; captures connectées locales (bureau et 360 px) des trois comptes et du compte B2C | 0,5 h | T-199-01, T-TECH-30-01 | Claude | ✅ |
| T-199-03 | [FE-WEB] | Maquette Claude Design (bureau 1440 px et téléphone 390 px), page « Mon compte » du canevas : accueil par rôle (Claire : dernières commandes, devis, listes ; Paul : approbations en attente et compteur ; Isabelle : membres, limites, crédit), cartes de raccourcis groupées (Achats, Validation, Société, Profil), menu latéral et accès compact, vraies données des fixtures ; table « Acquis » mise à jour | 2 h | T-199-01, T-TECH-30-01 | Orchestrateur | ✅ |
| T-199-04 | [OPS] | 📅 Thibaut valide la maquette (ou demande des retouches) : **aucun gabarit Twig avant son GO**, noté au registre des décisions | 0,5 h | T-199-03 | Thibaut | ✅ |
| T-199-05 | [TEST] | 📅 Tests rouges, commités seuls et cités dans la PR : accueil de Claire, de Paul (compteur d'approbations en premier) et d'Isabelle, raccourcis absents sans droit, B2C inchangé, 360 px, charte des textes, cibles de 44 px | 1 h | T-199-04 | Claude | ✅ |
| T-199-06 | [FE-WEB] | 📅 Gabarits et hooks du compte (fichier de hooks propre), partial `_account.scss`, menu latéral et accès compact, AAA, `prefers-reduced-motion` ; entrées des plugins par leurs hooks ; canal B2B seulement ; sous-pages négociables | 3 h | T-199-05 | Claude | ✅ |
| T-199-07 | [DOC] | 📅 Textes FR et EN (domaine `account`), charte `PublicTextCharter` ; texte relu contre la maquette validée | 0,5 h | T-199-06 | Claude | ✅ |
| T-199-08 | [TEST] | 📅 Suite ciblée par `bin/run-tests` (compte, menus, approbations, devis, listes, badges), PHPStan max sur tout le dépôt, ECS, Rector, lint Twig et YAML, pa11y local FR et EN pour les trois comptes, 360 et 1280 px ; commande exacte et dernière ligne citées | 0,75 h | T-199-06, T-199-07 | Claude | ✅ |
| T-199-09 | [DOC] | 📅 Captures `docs/images/account-*` par `bin/render-page` (connecté, local, trois comptes, bureau et 360 px), section « Mon compte » de `docs/theme-b2b.md` | 0,5 h | T-199-08 | Claude | ✅ |
| T-199-10 | [TEST] | 📅 Image reconstruite et conteneur neuf : compte des trois rôles vérifié (règle 16 : rien hors base) ; production après le déploiement, lecture seule : l'adresse du compte renvoie vers la connexion ; mesures consignées le jour du déploiement | 0,5 h | merge de la PR | Claude | 📅 production |
| T-199-11 | [REV] | 📅 Revue (ton neutre, parité FR et EN, B2C inchangé) et table « scénario → test ou preuve » remplie | 0,5 h | T-199-10 | Claude | 📅 production |

**Total estimé : 10,5 h** (11 tâches ; 10 h pour Claude et l'orchestrateur, 0,5 h de Thibaut).

## Table « scénario → test ou preuve »

| Scénario | Tâche | Test ou preuve |
|----------|-------|----------------|
| Accueil de l'acheteuse (nominal) | T-199-05, T-199-09 | `AccountDashboardTest::testClaireSeesFirstWhatIsWaitingForHer`, `…testClaireReadsHerLatestOrdersWithTheStateInPlainWords`, `…testEachOrderCanBeOrderedAgainWithAPostedForm` ; captures `docs/images/account-buyer-fr.jpg` |
| Accueil de l'approbateur (alternatif) | T-199-05 | `AccountDashboardTest::testThePendingApprovalOfPaulIsFirstAndCountedInTheMenu`, `…testPaulIsWarnedOfHisOwnOverdueOrder` |
| Accueil de l'administratrice (alternatif) | T-199-05 | `AccountDashboardTest::testIsabelleGetsTheShortcutsAndEntriesOfAnAdministrator`, `…testTheMenuIsGroupedInFourBlocksAndStaysOnOtherAccountPages` ; `AccountNavGrouperTest` |
| Canal B2C (alternatif) | T-199-05 | `AccountDashboardTest::testTheConsumerChannelKeepsTheThemeOfTheShop` |
| Entrée de plugin sans droit (erreur) | T-199-05 | `AccountDashboardTest::testThereAreExactlyFourShortcutsAndNoneForAPageClaireCannotOpen` |
| Débordement sur téléphone (erreur) | T-199-05, T-199-08 | `AccountStylesTest`, `…testTheOrdersTableScrollsInsideItsOwnRegionOnAPhone` ; relevé à 360 px : aucun élément de la page ne dépasse (hors tiroir du panier de l'en-tête, existant) |
| Régression d'accessibilité (erreur) | T-199-08 | pa11y WCAG2AAA en local, FR et EN, Claire, Paul, Isabelle et Nathalie : « No issues found! » (8 pages) |

## Cadrage (T-199-01) et état « avant » (T-199-02)

Instantané du 2026-10-25, relevé sur la base locale (lecture seule) et par `bin/render-page` (`APP_ENV=test`, 127.0.0.1, un seul cache) ; les commandes des fixtures sont posées en jours relatifs, les dates ci-dessous changent donc avec le calendrier. Données complètes : `maquette-data.json` du dossier temporaire de w199.

### Tableau de bord actuel

Aucune donnée métier : titre « Mon compte », statut « Vérifié », identité, boutons « Modifier » et « Changer votre mot de passe », carte société (nom, « Votre rôle », lien « Mon entreprise ») et badge « propulsé par Company Accounts ». Tout le reste est dans le menu latéral (`account_menu`), de 11 à 15 liens sans groupes, dans l'ordre : Tableau de bord, Informations personnelles, Changer votre mot de passe, Carnet d'adresses, Historique des commandes, Listes de l'entreprise, Mon entreprise, Assistants IA, Approbations*, Plafonds d'achat*, Commande rapide, Mes devis, Paiement à terme, Vos listes de favoris, Mon matériel (* selon le droit).

### Contenus et droits par rôle

| Compte | Rôle | Contenu aujourd'hui |
|--------|------|---------------------|
| Claire (Acme) | acheteuse | 19 commandes (dernière #000000006, 813,00 €, en attente d'approbation, plafond de commande 500 €), devis Q-2026-001 « proposition envoyée », liste « Atelier » (5 articles), pas de favoris, 3 machines ; 403 sur approbations, plafonds, tableau de bord des achats |
| Paul (Acme) | approbateur | 13 commandes ; « Approbations » dans le menu et l'en-tête avec pastille « 1 » ; 403 sur plafonds et tableau de bord des achats |
| Isabelle (Acme) | administratrice | 10 commandes ; approbations, plafonds (Claire et Sébastien à 500 €, Paul sans plafond), tableau de bord des achats |
| Nathalie (TechParts) | administratrice sans commande | états vides : commandes, devis, listes, approbations, pièces ouvertes ; crédit 5 000 € disponible ; les chiffres du tableau de bord des achats sont ceux de la société |
| Léa | B2C | tableau de bord du thème, sans carte société |

Antoine (BuildCo) a 4 commandes et Thomas est désactivé : ils ne servent pas de compte vide.

### Hooks et menus

- Tableau de bord : `sylius_shop.account.dashboard.index.content.main` (`section` priorité 0 ; carte société du plugin company-accounts, priorité -50 ; badge de la démo `demo_badge_company_accounts`, priorité -60) ; sous-hooks `...main.header`, `...main.section`, `...section.user_info`, `...section.buttons` du cœur.
- Menu : listeners `AccountMenuListener` des plugins company-accounts, purchase-approval, quote-request, trade-credit, customer-asset-management et b2b-mcp ; menu d'en-tête `sylius_shop.base.header.content.security.logged_in_user.mobile.menu` via `shop/theme_b2b/account_menu.html.twig`.
- Pages liées : hooks `tmonier_company_accounts_shop.account.company.*` (onglets `tmonier_company_accounts.shop.account.company.tabs`), `tmonier_purchase_approval_shop.account.*`, `tmonier_company_insights_shop.account.company.insights.*`, `tmonier_equipment_shop.account.equipment_list.*`.

### Point B2C de Léa

Léa voit déjà dans son menu les entrées des plugins (Mes devis, Paiement à terme, Mon matériel, Assistants IA, Commande rapide, favoris) et le badge « propulsé par Company Accounts », sans société. Comportement actuel, à conserver : le B2C reste inchangé (hooks conditionnés par `not is_granted('DEMO_CONSUMER_CHANNEL')`).

### État « avant » et tests

Rendus HTML FR (et EN pour les tableaux de bord) de Claire, Paul, Isabelle, Nathalie, Léa dans le dossier temporaire de w199 ; aucune capture image. Tests existants à reprendre en T-199-05 : `tests/Functional/Demo/HeaderAccountMenuTest.php` et `AccountMenuTest.php`. **Non exécutés** : `bin/run-tests` a refusé de partir (verrou tenu par un autre run, disque à 5 Go).
