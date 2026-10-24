# Tâches : US-186 : Refonte du design de la home

## Informations US

- **Epic** : EPIC-12
- **Persona** : Camille (recruteuse IT), puis Isabelle (acheteuse B2B)
- **Story Points** : 8 (Should, sacrifice : non sacrifiable (la refonte est la demande principale de Thibaut))
- **Sprint** : sprint-26-vitrine-et-flux
- **Worker proposé** : w186
- **Story** : [US-186](../../../backlog/user-stories/US-186.md)

## Résumé de la US

**En tant que** visiteuse de la démo  
**Je veux** une page d'accueil claire, chaleureuse et illustrée de vraies photos, avec les liens de présentation de la démo dans l'en-tête, le menu et au fil du défilement  
**Afin d'**avoir envie de la parcourir et de me projeter dedans comme dans un site réel

## Règles et contraintes

- **Maquette validée par Thibaut le 2026-10-24** : https://claude.ai/artifact/PgeyScVjkQSZN1oyhK4XDj (privée ; photos en emplacements légendés, sélection en cours, téléchargements autorisés par Thibaut : Unsplash et Pexels, 20 aperçus au plus).
- **Maquette d'abord** : maquette Claude Design (desktop et mobile) publiée par l'orchestrateur et validée par Thibaut (T-186-05) avant toute intégration Twig ; les tâches T-186-06 à T-186-13 sont bloquées tant que l'accord n'est pas donné.
- Photos de banques libres (Unsplash ou Pexels, licence sans attribution obligatoire) ; **un fichier de crédits** note pour chaque photo la source, l'auteur, la licence et l'URL de la licence ; un test échoue si une photo n'y figure pas.
- La home sert aussi le canal B2C (même route, canal choisi par cookie, ADR 0011) : **la home B2C du cœur reste inchangée** (test) ; tous les nouveaux hooks portent `not is_granted('DEMO_CONSUMER_CHANNEL')`.
- Accessibilité WCAG2AAA (pa11y en FR et en EN), 360 px sans défilement horizontal, cibles de 44 px, `prefers-reduced-motion` respecté.
- Images optimisées : formats modernes, `srcset`, dimensions, chargement différé, poids borné par un test.
- **LCP mobile mesuré avant (T-186-02) et après (T-186-14), même protocole**, production en lecture seule ; seuil : sous 2,5 s, ou au plus 10 % au-dessus de la mesure d'avant si celle-ci était déjà au-dessus.
- Ton neutre (`PublicTextCharter`), aucune comparaison avec Sylius Plus, aucune offre commerciale, aucun chiffre non vérifiable ; aucun détail de sécurité dans le dépôt public.
- Tests de la démo uniquement par `bin/run-tests` ; `php bin/proof-refresh` puis `php bin/proof-check` (nouveaux fichiers de tests). Règle 10 : `git` dans l'arbre principal.
- Règles du sprint, consigne de worker et actions de la rétro du sprint 25 : voir [README.md](./README.md).

## Lieu de vérification (action 4 de la rétro du sprint 23)

**Production, après le déploiement automatique de `main` par Railway** (lecture seule, aucune commande Railway) : pa11y WCAG2AAA en FR et en EN, 360 px et 1280 px, LCP mobile avant/après, liens de présentation (`docs/tests-locaux.md`, « Vérifier le rendu en production »). **La story reste en review jusque-là.**

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- `templates/shop/theme_b2b/home_*.html.twig`, `top_bar.html.twig`, `logo.html.twig`, `cta_hook.html.twig` (réécrits ou supprimés)
- `config/packages/demo_theme_b2b.yaml`, `config/packages/demo_cta.yaml` (hooks de la home et de l'en-tête)
- `assets/shop/styles/b2b.scss`, `assets/shop/images/home/` (photos optimisées et fichier des crédits)
- `translations/` (nouveau domaine dédié à la home)
- `templates/shared/_author_method.html.twig`, `templates/demo/banner.html.twig` (lecture, liens)
- `tests/Functional/Demo/`, `tests/Smoke/` (nouveaux tests ; `HomeAuthorTest`, `PriceBreaksAndHomeSmokeTest` adaptés)
- `config/demo/proof.json` (rafraîchi), `docs/theme-b2b.md`, `docs/images/home-*`, README

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-186-01 | [DOC] | Cadrage : inventaire de la home relu contre le code (hooks, gabarits, canal B2C, menu catalogue du cœur, tests existants `PriceBreaksAndHomeSmokeTest`, `HomeAuthorTest`, `PluginsAndHomepageTest`) ; destination de chaque lien de présentation ; plan des tests | 1 h | - | Claude | ✅ |
| T-186-02 | [TEST] | Mesure « avant » en production (lecture seule) : LCP mobile (médiane de 5 passes, même protocole que l'après), poids et nombre de requêtes de la home, largeur à 360 px, FR et EN ; chiffres notés dans ce fichier | 0,75 h | - | Claude | ✅ |
| T-186-03 | [DOC] | Photos : sélection de 8 à 10 photos de banques libres (Unsplash ou Pexels), licence relue page par page, fichier des crédits (source, auteur, licence, URL de la licence, URL d'origine) ; aucune photo avec visage identifiable sans nécessité | 1,25 h | - | Claude | ✅ |
| T-186-04 | [FE-WEB] | Maquette Claude Design (desktop et mobile) : hero éditorial, sections au défilement, en-tête et menu « La démo » ; publiée en artefact privé | 1,5 h | T-186-01, T-186-03 | Orchestrateur | ✅ |
| T-186-05 | [OPS] | 📅 Thibaut valide la maquette (ou demande des retouches) : **aucun gabarit Twig avant son accord** | 0,5 h | T-186-04 | Thibaut | ✅ |
| T-186-06 | [TEST] | Tests rouges, commités seuls et cités dans la PR : sections et ordre, liens de présentation (200), connecté (blocs produits conservés), B2C inchangé, crédit de chaque photo, budget des images (poids, dimensions, `alt`, `srcset`), charte, 360 px | 1,25 h | T-186-05 | Claude | ✅ |
| T-186-07 | [FE-WEB] | Images optimisées : conversion (WebP, AVIF si pris en charge), tailles responsives, dimensions explicites, chargement différé hors première vue, image du hero préchargée | 1 h | T-186-06 | Claude | ✅ |
| T-186-08 | [FE-WEB] | Hero éditorial et système visuel : palette, typographie, fond coloré, photo, bouton principal et secondaire (`b2b.scss`, `home_hero.html.twig`) ; contrastes AAA | 2 h | T-186-06, T-186-07 | Claude | ✅ |
| T-186-09 | [FE-WEB] | Sections au défilement : parcours, plugins, s'intégrer, preuve par le code, auteur, rôles ou produits ; réécriture et suppression des gabarits `home_*` redondants, hooks de `demo_theme_b2b.yaml` et `demo_cta.yaml` | 2 h | T-186-08 | Claude | ✅ |
| T-186-10 | [FE-WEB] | En-tête et menu : entrée « La démo » (liens de présentation) et « Essayer la démo », accessibles au clavier et à 360 px ; liens du hero ; pied conservé | 1 h | T-186-08 | Claude | ✅ |
| T-186-11 | [DOC] | Textes FR et EN (domaine de traduction dédié), charte `PublicTextCharter`, légendes et `alt` des photos ; texte relu contre la maquette validée | 1 h | T-186-09 | Claude | ✅ |
| T-186-12 | [TEST] | Suite ciblée par `bin/run-tests` (`WORKER_ID=w186 …`), `bin/measure-mobile` à 360 px en local, ECS (`src tests`), PHPStan max, Rector, lint Twig et YAML ; dernières lignes citées | 1 h | T-186-09, T-186-10, T-186-11 | Claude | ✅ |
| T-186-13 | [DOC] | Captures `docs/images/home-*` refaites, `docs/theme-b2b.md` et README mis à jour (accueil, crédits des photos) | 0,5 h | T-186-12 | Claude | ✅ |
| T-186-14 | [TEST] | Vérification en production après le déploiement automatique de `main` (lecture seule) : pa11y WCAG2AAA en FR et en EN, 360 px et 1280 px, LCP mobile « après » (même protocole que T-186-02), liens de présentation ; comparaison avant/après notée | 1,25 h | merge de la PR | Claude | 🔲 |
| T-186-15 | [REV] | Revue (ton neutre, parité FR et EN, crédits complets, aucune comparaison avec une offre commerciale) et table « scénario → test ou preuve » remplie | 0,5 h | T-186-14 | Claude | 🔲 |

**État à la clôture (2026-10-24) : T-186-14 et T-186-15 restent 🔲.** La PR #87 est fusionnée (maquette validée par Thibaut, T-186-05 ✅) ; les retours de Thibaut sur la home en production ont donné #89 (menu dans la rangée du logo, logo sur deux lignes). La mesure « après » du LCP mobile, pa11y et 360/1280 px en production, la table « scénario → test ou preuve » ci-dessous et la revue ne sont pas consignées dans le dépôt : à faire ou à confirmer.

**Total estimé : 16,5 h** (15 tâches, dont 0,5 h de Thibaut (1 tâche) ; 16 h pour Claude et l'orchestrateur).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, run de CI). Une ligne vide laisse la story en review.

Mesures de LCP mobile (avant, après), poids et requêtes de la home : à noter ici (T-186-02, T-186-14).

**Avant (T-186-02, production `sylius-b2b.thibaut-monier.fr`, lecture seule, 2026-10-24).** Protocole : Chromium, mobile 360 × 740, ratio 2, processeur ralenti x4, réseau Slow 4G (1,6 Mbit/s, 150 ms), cache désactivé, 5 passes, médiane ; script `scratchpad/w186/lcp.cjs`.

| Page | LCP médian | LCP des 5 passes (ms) | Poids transféré | Requêtes | CLS | Largeur à 360 px |
|------|-----------:|-----------------------|----------------:|---------:|----:|------------------|
| `/fr_FR/` | 1324 ms | 2080, 1328, 1296, 1304, 1324 | 349 Ko | 11 | 0 | 360 / 360, aucun débordement |
| `/en_US/` | 1324 ms | 1440, 1304, 1304, 1324, 1512 | 349 Ko | 11 | 0 | 360 / 360, aucun débordement |

Seuil après : sous 2,5 s (la mesure d'avant est sous le seuil).

**Rendu local (T-186-12, 2026-10-24, pages rendues par le noyau puis servies en statique, Chromium).** Largeur à 360 px : `scrollWidth` 360 sur 360 en FR et en EN, aucun élément ne dépasse. Cibles : plus de contrôle de la home sous 44 px hors cœur (sélecteur de langue 51 x 31, lien « Connexion » 80 x 24, boutons du cœur de Sylius). pa11y WCAG2AAA : « No issues found! » sur la home FR, la home EN et la home de Claire connectée. Polices chargées : InterLatin, FrauncesLatin. Suite ciblée : 149 tests, 0 échec (dernière ligne : `OK, but there were issues! Tests: 149, Assertions: 910, Deprecations: 5`).

**Cadrage (T-186-01).** Home relue contre le code : neuf blocs hookés (hero, promesse, accès rapides, parcours, auteur, produits ou rôles, plus le bandeau de contact). Tests touchés : `PluginsAndHomepageTest`, `PriceBreaksAndHomeSmokeTest`, `HomeAuthorTest`, `ContactCtaTest`, `DemoBannerTest`, `ThemeHooksTest`, `ClosedChannelDemoTest`, `ThemeLandmarksTest` (un `header`, un `main`, un `h1`), `ImageCreditsPageTest` (compte `[data-image-credit]`). Choix : sections = hookables (ordre par priorité) ; promesse en trois colonnes supprimée (la maquette ne la garde pas) ; bandeau de démonstration en hook `sylius_shop.base.header` (50) sur tous les écrans des deux canaux ; menu démo en `<details>` natif ; blocs produits connectés après les engagements.

| Scénario | Test ou preuve |
|----------|----------------|
| Une home éditoriale qui donne envie de défiler (nominal) | |
| Acheteur connecté (alternatif) | |
| Canal B2C inchangé (alternatif) | |
| Téléphone à 360 px (alternatif) | |
| Photo sans crédit (erreur) | |
| Image hors budget (erreur) | |
| Texte contraire à la charte (erreur) | |
| Régression en production (erreur) | |
