# Sprint 33 : Indexation des pages vitrine et chaîne d'approvisionnement des dépôts de plugins

> **Planifié le 2026-10-28**, après la clôture du code du sprint 32 (9 points sur 9, PR #127 et contrôles de production du 2026-10-28). Périmètre décidé par Thibaut le 2026-10-28 : **13 points** ([registre des décisions](./registre-decisions.md)). La décomposition en tâches est faite : voir [tasks/README.md](./tasks/README.md). Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-33`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 33 |
| Début | 2026-10-28 (planning) |
| Fin | **2026-11-10** (10 jours ouvrés) au plus tard ; clôture dès que le périmètre est vérifié |
| Capacité | **13 points** ; 45 tâches, 35 h (34,25 h pour Claude, 0,75 h d'actions de Thibaut) pour 35,3 h de capacité (13 × 2,1 h + 8 h transverses), soit 1,05 h de marge |
| Vélocité de référence | S29 13, S30 13, S31 13, S32 9 (moyenne 12) |
| Branche | `feature/sprint-33-indexation-et-depots` (arbre principal, déjà créée ; pas de checkout dans l'arbre partagé) |

Le périmètre revient à 13 points après le sprint 32 (9 points, réduit par le disque). Le disque est à 5 Go libres, la mémoire à 1 Go et le swap à 3,5 Go au lancement : aucun test ni rendu local sous 5 Go, aucune suite complète sous 6 Go (registre du 2026-10-28). Les deux stories n'ont **pas de maquette** : la porte du sprint 32 disparaît, remplacée par deux autres : le GO de Thibaut sur la liste blanche des pages indexées (T-207-03) et, pour US-209, le GO déjà donné au registre (push, PR et fusion si la CI est verte, sans tag). L'ordre de sacrifice est **décidé** par Thibaut (registre) : US-209 tranche (b) Composer, puis tranche (a) workflows ; US-207 est protégée.

## Sprint Goal

> « Rendre les pages vitrine trouvables par les moteurs de recherche sans exposer le reste de la démo, et appliquer aux 29 dépôts de plugins la même hygiène de chaîne d'approvisionnement que la démo, avec un script commun et sans publier de version. »

## Périmètre engagé

Gherkin : au minimum 1 nominal, 2 alternatifs et 2 erreurs par story (US-207 : 1+2+3, US-209 : 1+2+2). « Fiche » = fichier `backlog/user-stories/US-XXX.md`.

| ID | Titre | Pts | MoSCoW | Statut | Sacrifice |
|----|-------|-----|--------|--------|-----------|
| [US-207](../../backlog/user-stories/US-207.md) | Indexer les pages vitrine : sitemap, hreflang, métadonnées et données structurées | 5 | Should | ready-for-dev | **protégée** (registre) |
| [US-209](../../backlog/user-stories/US-209.md) | Chaîne d'approvisionnement des dépôts de plugins : workflows durcis, audit Composer et politique explicite | 8 | Should | ready-for-dev | **n°1 : tranche (b) Composer ; n°2 : tranche (a) workflows** (registre) |

Total engagé : **13 points**.

- **US-207** remplace « tout le site en noindex » par une **liste blanche unique et versionnée** de pages vitrine (accueil FR et EN, Plugins, Architecture, Preuve technique, guides d'intégration, À propos), utilisée par `NoIndexSubscriber`, `RobotsController` et le sitemap. Tout le reste (catalogue fictif, fiches produit, compte, panier, commande, outils de démo) reste en `X-Robots-Tag: noindex, nofollow`, y compris toute nouvelle route (refus par défaut). Elle ajoute `sitemap.xml` avec `hreflang` FR/EN et `x-default`, des titres et descriptions par page, et des données structurées vérifiables (`WebSite`, `Person`, `SoftwareSourceCode`), sans plugin en recoupement ni import ERP. Les refontes US-204 à US-206 sont livrées : l'activation se fait par un réglage, confirmé par Thibaut (T-207-03). La déclaration du sitemap dans Search Console reste à Thibaut.
- **US-209** applique aux 27 plugins et 2 packs (le fork de la wishlist n'existe plus) les neuf contrôles d'US-208 : références figées par SHA, `permissions: {}`, `persist-credentials: false`, aucun `pull_request_target`, zizmor, `composer audit`, `config.policy`, `allow-plugins`, Dependabot. Un script commun (`bin/plugin-supply-chain`, dans la démo) génère et vérifie ; **une PR par dépôt**, fusionnée si la CI est verte, **aucun tag ni release**. Découpage : tranche (a) workflows, tranche (b) Composer.
- **Tâches techniques** (actions 1 à 5 de la rétro du sprint 32 et reliquats) : règle 27 « rouge prouvé » et table des invariants de test au cadrage (faites au planning), disque libéré par Thibaut, verrou des tests en file d'attente (T-TECH-32-01), avertissement de `public/build` périmé (T-TECH-32-03), rendu sans barre de débogage, script puppeteer de mesure versionné, images produit locales et capture du panier (T-TECH-32-04), contrôle connecté d'US-203 sur image reconstruite.

**Hors sprint** : US-128, US-103, US-121 (conditionnées), US-154 ; tag ou release de plugin ; restriction de la sortie réseau des runners ; catalogue et fiches produit dans l'index.

## Décisions de Thibaut (2026-10-28, registre)

1. Périmètre : US-207 (5) et US-209 (8), soit 13 points, plus les reliquats du sprint 32 (T-TECH-32-01, -03, -04) et le contrôle connecté d'US-203.
2. **GO dépôts de plugins (US-209)** : push d'une branche et PR par dépôt, fusion de chaque PR dont la CI est verte, **aucun tag ni release** ; toute exception (CI rouge préexistante, dépôt sans CI) est remontée à Thibaut.
3. Ordre de sacrifice : US-209 tranche (b), puis tranche (a) ; US-207 protégée.
4. Sitemap : Thibaut le déclare lui-même dans Google Search Console après le déploiement ; Claude fournit l'URL et les étapes, sans toucher à aucun compte.
5. Disque à 5 Go, mémoire 1 Go, swap 3,5 Go : au plus deux workers qui testent en même temps (règle 24), aucun test ni rendu sous 5 Go, suite complète laissée à la CI sous 6 Go.

## Estimation du sprint

La fiche annonce 5 + 8 points. La décomposition donne 10 h (US-207, dont 0,5 h de Thibaut), 16,5 h (US-209) et 8,5 h de transverses (dont 0,25 h de Thibaut), soit **34,25 h de Claude pour 35,3 h : les 13 points tiennent, avec 1,05 h de marge**. Franchement : la marge est faible et repose sur deux hypothèses. D'abord, la partie répétitive d'US-209 : quatre lots de sept dépôts à 1 h le lot (génération, vérification, commit, push, PR, suivi de la CI, fusion), soit environ 8 minutes par dépôt, possible parce que le script fait le travail ; une CI rouge préexistante ou un dépôt à branche protégée coûte vite plus. Ensuite, le disque : sans retour au-dessus de 5 Go, les tâches de test et de rendu d'US-207 (T-207-02, T-207-11, T-207-12) restent en attente, mais US-209 avance (script et dépôts, peu de disque). Une économie déjà prise : pas de maquette ni de captures nouvelles pour US-207 (le rendu des pages ne change pas, 0,25 h). Si la charge dépasse en cours de sprint, l'ordre de sacrifice décidé s'applique (voir [tasks/README.md](./tasks/README.md)).

## Politique Sylius Plus

Ton neutre partout. L'indexation de la page « Plugins » ne promeut pas les plugins en recoupement : ils restent listés sans texte promotionnel, **exclus des données structurées et des descriptions**, et un test vérifie leur absence ; le plugin d'import ERP (US-187) est exclu de la même façon. Aucun texte, titre, description, message de commit ou description de PR de plugin ne compare avec l'offre de l'éditeur Sylius ni ne la mentionne. Les PR des dépôts de plugins ne changent que la CI et la configuration Composer : aucun texte promotionnel, rien de sensible.

## Checklist de démarrage (2026-10-28)

### Avant le Sprint Planning

- [x] Sprint précédent terminé : code du sprint 32 fusionné (PR #127), contrôles de production du 2026-10-28 consignés (PR #128) ; US-205, US-206 et US-062 en done
- [x] Actions de la rétrospective du sprint 32 reprises :
  - action 1 : ✅ règle 27 « rouge prouvé » écrite au planning (T-TECH-33-01) ;
  - action 2 : ✅ table « invariants de test » au cadrage, préremplie au planning dans [tasks/US-207-tasks.md](./tasks/US-207-tasks.md) (T-TECH-33-02) ;
  - action 3 : T-TECH-33-03 (Thibaut libère le disque) et T-TECH-33-04 (runs autorisés annoncés, verrou en file d'attente) ;
  - action 4 : T-TECH-33-05 (rendu sans barre de débogage) et T-TECH-33-06 (script de mesure versionné) ;
  - action 5 : T-TECH-33-07 (contrôle connecté d'US-203) et T-TECH-33-09 (capture du panier), les contrôles de production du sprint 32 étant faits le 2026-10-28.
- [x] Backlog priorisé : périmètre décidé par Thibaut le 2026-10-28
- [x] Stories estimées et « Ready » : voir la Definition of Ready ci-dessous
- [x] Capacité calculée : 35,3 h pour 34,25 h de charge de Claude
- [x] Fiches US-207 et US-209 à jour (en-tête « Sprint 33 »)
- [x] Branche `feature/sprint-33-indexation-et-depots` créée (arbre principal)
- [ ] Disque et mémoire : `df -h .`, `bin/check-disk`, mémoire libre et swap relevés avant le premier lot (T-TECH-33-11) ; **aucun test ni rendu avant 5 Go libres**

### Après le Sprint Planning

- [x] Sprint backlog visible : [task-board.md](./task-board.md), [tasks/README.md](./tasks/README.md)
- [x] Dépendances : [sprint-dependencies.md](./sprint-dependencies.md)
- [x] Consigne de worker et checklist du sprint : [consigne-worker.md](./consigne-worker.md), [checklist-integration.md](./checklist-integration.md)
- [ ] GO de la liste blanche et de l'activation (T-207-03) ; disque libéré (T-TECH-33-03)

## Definition of Ready (contrôle du 2026-10-28)

| US | Fiche | Gherkin | Prérequis | État |
|----|-------|---------|-----------|------|
| US-207 | oui | oui (1+2+3) | refontes US-204 à US-206 livrées ; `NoIndexSubscriber`, `RobotsController`, `metatags.html.twig` (US-078) existants ; tests existants touchés relevés au planning (table des invariants) ; liste blanche à confirmer par Thibaut (T-207-03) | prête (porte : GO de la liste, avant l'activation seulement) |
| US-209 | oui | oui (1+2+2) | US-208 livrée (scripts `bin/check-pinned-refs` et `bin/check-lock-diff`, gabarit de workflow) ; copies locales des 29 dépôts sous `/Users/tmonier/Projects/sylius_plugins/` ; GO du registre du 2026-10-28 (push, PR, fusion si verte, sans tag) | prête (aucune porte) |

## Definition of Done (rappel)

- [ ] Chaque scénario Gherkin relié à un test ou à une preuve dans le fichier de tâches
- [ ] Stories de code : commit de tests rouges cité dans la PR, **avec l'échec observé de chaque test cité dans le message du commit** (règle 27)
- [ ] US-207 : liste blanche unique et versionnée utilisée par les trois consommateurs ; pa11y WCAG2AAA FR et EN, `bin/measure-mobile` à 320 et 375 px ; tests existants touchés décidés au registre **avant** la modification ; données structurées validées par un outil (résultat noté au registre) ; documents et vérifications du runbook mis à jour
- [ ] US-209 : relevé de départ refait sur les branches par défaut **distantes** et joint à la PR de la démo ; script testé (rouge prouvé) ; une PR par dépôt, CI verte, fusionnée, **aucun tag** ; rapport final « 29 dépôts conformes » ou écarts justifiés dépôt par dépôt ; mémoire du socle qualité des plugins mise à jour
- [ ] **Captures réelles** par `bin/render-page` quand le disque le permet, sinon captures de production relues et dites dans la PR ; **capture à 1280 px de la home connectée et de chaque page touchée relue par l'orchestrateur avant la PR** (règle 23)
- [ ] **Tout état écrit hors base** (règle 16) : le nouveau réglage d'activation de l'indexation est déclaré dans l'infrastructure (`preserve()` des variables posées à la main) ; sitemap servi par l'application, pas par un fichier
- [ ] **Mesures de production consignées le jour du déploiement** pour ce qui est public (robots.txt, en-têtes, sitemap, pa11y, largeurs)
- [ ] PHPStan max sur **tout le dépôt**, tests compris, ECS et Rector verts, rejoués avant le push ; aucune nouvelle entrée `ignoreErrors` ; aucun compteur écrit en dur ; **suite complète locale avant le push ou attente explicite dite dans la PR** (T-TECH-33-13)
- [ ] Preuve publiée par `bin/proof-rebase` ; PR fusionnées en série, aucune avec une question ouverte à Thibaut
- [ ] Aucun détail de sécurité dans les dépôts publics (démo et plugins) ; ton neutre, aucune comparaison avec Sylius Plus ; plugins en recoupement et import ERP absents des données structurées et des descriptions
- [ ] Texte public relu contre sa source ; documentation à jour ; numéro de story des commits relu ; aucun commit embarquant le travail d'un autre worker (règle 21) ; dans chaque dépôt de plugin, `git fetch` et branche courante contrôlés avant tout commit (règle 29)
- [ ] CI verte (« Quality and smoke tests », « Docker image », « Dependency audit », zizmor), run terminé avant le merge ; `main` protégée (PR requise)
- [ ] Aucun identifiant saisi en production ; aucun compte de moteur de recherche touché par Claude

## Actions de Thibaut

- Confirmer la liste blanche des pages indexées et l'activation de l'indexation (T-207-03), noté au registre.
- Libérer le disque (cible 10 Go libres, commande en chemins absolus précédée d'un `du -sh` fournie par l'orchestrateur) avant le lancement des tests (T-TECH-33-03).
- Déclarer le sitemap dans Google Search Console après le déploiement (T-207-14) : Claude fournit l'URL et les étapes.
- Décider des exceptions d'US-209 remontées par Claude (dépôt à CI rouge préexistante, sans CI, à branche protégée) : T-209-11.

## Risques

| Risque | Probabilité | Impact | Mitigation |
|--------|-------------|--------|------------|
| Page privée indexée par erreur (liste blanche trop large, nouvelle route, mode privé) | Faible | Élevé | Refus par défaut ; liste unique testée (les trois consommateurs) ; test « route non listée » ; mode privé : tout reste `noindex` et `Disallow: /` ; vérifications du runbook en production |
| Tests existants cassés par le passage de `noindex` à indexable (home, Plugins, Architecture, guides) | Élevée | Moyen | Table des invariants (T-207-01) ; modifications décidées au registre avant l'action, citées dans la PR ; tests rouges ajoutés d'abord |
| Hôte du sitemap et des liens `hreflang` faux (hôte de la requête au lieu de l'hôte canonique) | Moyenne | Moyen | `DEFAULT_URI` / hôte canonique ; test sur un hôte de requête différent ; contrôle de production |
| Plugin en recoupement ou import ERP dans les données structurées | Faible | Élevé | Test d'absence sur Plugins et accueil ; source de la liste d'exclusion relevée au cadrage |
| Réglage d'activation écrasé par `config apply` (variable posée à la main) | Moyenne | Moyen | `preserve()` dans l'infrastructure ; vérifié par le runbook |
| CI rouge préexistante ou dépôt sans CI parmi les 29 | Moyenne | Moyen | Relevé de départ (T-209-01) ; remontée à Thibaut (T-209-11) ; le dépôt n'est pas fusionné tant que la décision manque |
| `config.policy` ou `composer audit` non supportés par la version de Composer de la CI d'un plugin | Moyenne | Moyen | Pilote sur un dépôt (T-209-06) avant les lots ; version de Composer relevée au départ |
| Branche protégée ou revue obligatoire qui bloque la fusion automatique | Moyenne | Moyen | Relevé de départ ; exception remontée ; aucun contournement des protections |
| Dépôt édité sur une branche périmée (copie locale en retard) | Moyenne | Moyen | Règle 29 : `git fetch` et contrôle de la branche courante avant tout commit ; script qui refuse sinon |
| Fuite d'un détail sensible ou ton non neutre dans un dépôt public | Faible | Élevé | Revue dédiée (T-209-15, T-207-15) ; descriptions de PR relues ; rien de sensible dans le relevé joint |
| Machine saturée (disque, swap) | Élevée | Moyen | Règle 24 ; `bin/check-disk` ; aucune suite complète sous 6 Go ; US-209 peu gourmande en disque |

## Cérémonies

Calées sur le travail réel : la revue et la rétro se tiennent dès que le périmètre est vérifié, au plus tard le 2026-11-10. Les fichiers `sprint-review.md` et `sprint-retro.md` sont créés à la clôture (T-TECH-33-15), pas au planning.

| Cérémonie | Quand | Durée |
|-----------|-------|-------|
| Sprint Planning (Parts 1 et 2) | 2026-10-28 (périmètre décidé par Thibaut, décomposition faite) | fait |
| Point d'étape | Après le pilote d'US-209 (T-209-06) et le GO de la liste blanche (T-207-03) | 30 min |
| Sprint Review | Dès le périmètre vérifié, au plus tard le 2026-11-10 | 1 h |
| Rétrospective | Le même jour que la revue | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
