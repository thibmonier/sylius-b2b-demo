# Tâches : US-198 : Refonte de la page de liste des produits dans la continuité de la home

## Informations US

- **Epic** : EPIC-12
- **Persona** : Claire (acheteuse d'Acme Industries) ; Camille (recruteuse IT)
- **Story Points** : 5 (Should, **protégée : sacrifiée en dernier**)
- **Sprint** : sprint-29-liste-panier-et-photos
- **Worker proposé** : w198 (orchestrateur pour la maquette)
- **Story** : [US-198](../../../backlog/user-stories/US-198.md)

## Résumé de la US

**En tant qu'** acheteuse B2B  
**Je veux** une liste de produits aussi claire que la home et la fiche produit  
**Afin de** comparer vite les références, les prix HT de ma société et la disponibilité avant d'ouvrir une fiche ou d'ajouter au panier

## Règles et contraintes

- **Maquette d'abord** : maquette Claude Design (bureau et téléphone) publiée par l'orchestrateur et **validée par Thibaut (T-198-04) avant toute intégration Twig**, comme US-186 et US-189. Les tâches de code T-198-05 à T-198-11 sont 📅 tant que son GO n'est pas donné et noté au registre.
- **Rendu réel d'abord** : les captures de la maquette sont faites sur un rendu du noyau en mode prod (`bin/render-page`), avec **un seul cache de rendu** et `bin/check-disk` avant chaque rendu ; T-TECH-29-01 précède la première capture.
- **Reprise d'US-186 et d'US-189** : jetons de `b2b.scss`, Fraunces et Inter auto-hébergées (CSP `font-src 'self'`), AAA (WCAG2AAA), cibles de 44 px, `prefers-reduced-motion`.
- Canal B2B seulement : **la liste B2C reste inchangée** (test) ; tous les nouveaux hooks portent `not is_granted('DEMO_CONSUMER_CHANNEL')`. Hooks existants conservés : `product_card_sku`, `product_card_from_price`, `product_card_price_note`, `product_card_meta`.
- Visiteur anonyme : aucun prix B2B, invitation à se connecter avec un compte de démo.
- **Vue liste** (pour comparer) : négociable, première partie sortie du périmètre si le budget de T-198-06 est dépassé (la grille refondue suffit à la story).
- **Cadrage des photos** : la carte réutilise le point focal d'US-197 (T-197-03) ; si T-197-03 n'est pas mergée, la carte garde le cadrage actuel et la dépendance est notée.
- **LCP mobile mesuré avant (T-198-02) et après (T-198-10), même protocole** (médiane de 5 passes, production en lecture seule) ; seuil d'US-189 : sous 2,5 s, ou au plus 10 % au-dessus de la mesure d'avant si celle-ci était déjà au-dessus. Le LCP « après » du catalogue de T-196-09 (sprint 28) peut servir de mesure d'avant s'il est fait au même protocole après la remise à zéro du 2026-10-26.
- Ton neutre (`PublicTextCharter`), aucune comparaison avec Sylius Plus, aucun chiffre non vérifiable ; aucun identifiant saisi en production ; aucun détail de sécurité dans le dépôt public.
- Tests uniquement par `WORKER_ID=w198 TEST_DB_NAME=b2b_w198 TEST_CACHE_DIR=var/cache-w198 bin/run-tests` ; preuve : `bin/proof-rebase` (T-TECH-29-02) ou `php bin/proof-refresh` puis `php bin/proof-check`.
- Règles du sprint et consigne de worker : voir [README.md](./README.md) et [../consigne-worker.md](../consigne-worker.md).

## Lieu de vérification

**Production, après le déploiement automatique de `main`** (lecture seule, sans connexion) : une catégorie de plus d'une page, une recherche, une recherche sans résultat, visiteur anonyme, FR et EN : pa11y WCAG2AAA, 360 et 1280 px, LCP mobile « après ». Les vues connectées (prix HT de la société, stock) sont vérifiées en local et sur l'image reconstruite. **Mesures consignées dans ce fichier le jour du déploiement ; la story reste en review jusque-là.**

## Fichiers principaux touchés

- `templates/shop/theme_b2b/` (gabarits de la liste : `product/index`, `product/_box`, filtres, pagination, état vide), `config/packages/demo_theme_b2b.yaml` (hooks)
- `assets/shop/styles/b2b.scss`, `translations/` (domaine dédié à la liste)
- `tests/Functional/Demo/`, `tests/Smoke/` (nouveaux fichiers ; tests existants du catalogue, des filtres et du tri relus)
- `config/demo/proof.json` (rafraîchi), `docs/theme-b2b.md`, `docs/images/product-list-*`, README

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-198-01 | [DOC] | Cadrage : inventaire de la liste relu contre le code (hooks `product_card_*`, gabarits de la grille, filtres, tri, pagination, recherche, état vide, canal B2C, tests existants du catalogue) ; trois pages types retenues (catégorie de plus d'une page, recherche, recherche sans résultat) ; plan des tests | 0,75 h | - | Claude | 🔲 |
| T-198-02 | [TEST] | Mesure « avant » en production (lecture seule) sur les trois pages types : LCP mobile (médiane de 5 passes), poids et requêtes, largeur à 360 px, pa11y, FR et EN ; chiffres notés dans ce fichier (reprise possible de T-196-09 s'il est fait au même protocole) | 0,75 h | T-198-01 | Claude | 🔲 |
| T-198-03 | [FE-WEB] | Maquette Claude Design (bureau et téléphone) : en-tête de catégorie, filtres et tri, carte produit (photo, nom, référence, prix HT de la société, paliers ou « à partir de », stock, action rapide), grille et vue liste, pagination, état vide, visiteur anonyme, B2C inchangé ; jetons de `b2b.scss`, Fraunces et Inter ; publiée en artefact privé, captures sur le rendu du noyau en mode prod | 2 h | T-198-01, T-TECH-29-01 | Orchestrateur | 🔲 |
| T-198-04 | [OPS] | 📅 Thibaut valide la maquette (ou demande des retouches) : **aucun gabarit Twig avant son GO**, noté au registre des décisions | 0,5 h | T-198-03 | Thibaut | 📅 |
| T-198-05 | [TEST] | Tests rouges, commités seuls et cités dans la PR : cartes B2B (photo, nom, référence, prix HT, stock), anonyme sans prix avec invitation, tri conservé au changement de page et page courante annoncée, B2C inchangé, état vide, 360 px, polices auto-hébergées et CSP, charte, cibles de 44 px | 1 h | T-198-04 | Claude | 📅 |
| T-198-06 | [FE-WEB] | Gabarits et hooks de la liste, styles `b2b.scss` (jetons d'US-186 réutilisés), responsive 360 et 1280 px, AAA, `prefers-reduced-motion` ; hooks sur le canal B2B seulement ; vue liste en dernier (négociable) ; point focal d'US-197 si T-197-03 est mergée | 3 h | T-198-05 | Claude | 📅 |
| T-198-07 | [DOC] | Textes FR et EN (domaine de traduction dédié), charte `PublicTextCharter`, `alt` des photos ; texte relu contre la maquette validée | 0,5 h | T-198-06 | Claude | 📅 |
| T-198-08 | [TEST] | Suite ciblée par `bin/run-tests`, PHPStan max sur tout le dépôt (`src` et `tests`), ECS, Rector, lint Twig et YAML, pa11y local FR et EN, `bin/measure-mobile` à 360 px ; commande exacte et dernière ligne citées | 0,75 h | T-198-06, T-198-07 | Claude | 📅 |
| T-198-09 | [DOC] | Captures `docs/images/product-list-*` sur un rendu du noyau en mode prod, `docs/theme-b2b.md` et README mis à jour | 0,5 h | T-198-08 | Claude | 📅 |
| T-198-10 | [TEST] | Vérification en production après le déploiement (lecture seule, trois pages types) : pa11y WCAG2AAA FR et EN, 360 et 1280 px, LCP mobile « après » au même protocole que T-198-02 ; **comparaison avant/après consignée le jour du déploiement** (le catalogue n'affiche les nouvelles photos qu'après la remise à zéro de 03:00 suivante) | 0,75 h | merge de la PR | Claude | 📅 |
| T-198-11 | [REV] | Revue (ton neutre, parité FR et EN, B2C inchangé) et table « scénario → test ou preuve » remplie ; `bin/proof-rebase` ou `php bin/proof-refresh` puis `php bin/proof-check` | 0,5 h | T-198-10 | Claude | 📅 |

**Total estimé : 11 h** (11 tâches ; 10,5 h pour Claude et l'orchestrateur, 0,5 h de Thibaut).

## Scénarios et preuves

Chaque scénario Gherkin de la fiche est relié à une tâche de test. Une ligne vide laisse la story en review.

| Scénario | Tâche de test | Test ou preuve |
|----------|---------------|----------------|
| Liste lisible et cohérente avec la fiche produit (nominal) | T-198-05 (rouge), T-198-08 | |
| Visiteur anonyme (alternatif) | T-198-05, T-198-10 (production, visiteur) | |
| Filtres, tri et pagination (alternatif) | T-198-05, T-198-08 | |
| Canal B2C (alternatif) | T-198-05 | |
| Recherche sans résultat (erreur) | T-198-05, T-198-10 | |
| Débordement sur téléphone (erreur) | T-198-05, T-198-08 (`bin/measure-mobile`), T-198-10 (360 px) | |
| Régression d'accessibilité ou de performance (erreur) | T-198-08 (pa11y local), T-198-10 (pa11y AAA FR et EN, LCP) | |

## Mesures de production (avant : T-198-02 ; après : jour du déploiement)

_Non consignées (sprint planifié le 2026-10-25)._ Protocole d'US-189 : Chromium mobile 360 × 740, ratio 2, processeur x4, Slow 4G, cache désactivé, 5 passes, médiane ; visiteur anonyme.
