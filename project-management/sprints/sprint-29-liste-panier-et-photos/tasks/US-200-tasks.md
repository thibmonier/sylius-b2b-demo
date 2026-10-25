# Tâches : US-200 : Refonte de la page panier dans la continuité de la home

## Informations US

- **Epic** : EPIC-12
- **Persona** : Claire (acheteuse d'Acme Industries) ; Camille (recruteuse IT)
- **Story Points** : 5 (Should, **sacrifice : n°2**)
- **Sprint** : sprint-29-liste-panier-et-photos
- **Worker proposé** : w200 (orchestrateur pour la maquette)
- **Story** : [US-200](../../../backlog/user-stories/US-200.md)

## Résumé de la US

**En tant qu'** acheteuse B2B  
**Je veux** un panier qui récapitule clairement les lignes, les remises de palier, les totaux HT et TVA et ce qui se passera à la validation  
**Afin de** passer commande ou de demander un devis sans hésiter

## Règles et contraintes

- **Maquette d'abord** : maquette Claude Design (bureau et téléphone) publiée par l'orchestrateur et **validée par Thibaut (T-200-04) avant toute intégration Twig**. Les tâches de code T-200-05 à T-200-11 sont 📅 tant que son GO n'est pas donné et noté au registre.
- **Rendu réel d'abord** : captures connectées en local seulement (127.0.0.1, comptes des fixtures), rendu du noyau en mode prod, **un seul cache de rendu**, `bin/check-disk` avant chaque rendu.
- **Reprise d'US-186 et d'US-189** : jetons de `b2b.scss`, polices auto-hébergées (CSP `font-src 'self'`), AAA, cibles de 44 px, `prefers-reduced-motion`, 360 et 1280 px sans défilement horizontal.
- Canal B2B seulement : **le panier B2C reste inchangé et le tunnel de commande reste vert** (test de commande de bout en bout) ; les hooks portent `not is_granted('DEMO_CONSUMER_CHANNEL')`. Hooks existants conservés : `cart_item`, `cart_tier_discount`.
- **Blocs des plugins repris par leurs hooks existants, sans modifier les plugins** : référence de commande (PO), limite et circuit d'approbation, conversion en devis, enregistrement en liste d'achat, commande rapide. Les actions secondaires aboutissent comme avant la refonte.
- Récapitulatif collant sur bureau, en bas d'écran sur téléphone ; le total reste visible à 360 px. La commande rapide peut rester à sa place actuelle (négociable).
- Page connectée : **aucune connexion en production** ; vérification en local et sur l'image reconstruite (conteneur neuf, règle 16 : le panier vit en base, rien d'autre n'est écrit hors base). En production : seulement les pages publiques accessibles sans connexion (panier vide anonyme, si la page est servie).
- Ton neutre, aucune comparaison avec Sylius Plus ; aucun identifiant saisi en production ; aucun détail de sécurité dans le dépôt public.
- Tests uniquement par `WORKER_ID=w200 TEST_DB_NAME=b2b_w200 TEST_CACHE_DIR=var/cache-w200 bin/run-tests` ; preuve : `bin/proof-rebase` ou `php bin/proof-refresh` puis `php bin/proof-check`.
- Règles du sprint et consigne de worker : voir [README.md](./README.md) et [../consigne-worker.md](../consigne-worker.md).

## Lieu de vérification

**Local et image reconstruite** pour le panier connecté (trois lignes, palier, dépassement de limite, panier vide, tunnel de commande de bout en bout, pa11y WCAG2AAA FR et EN, 360 et 1280 px). **Production** (lecture seule, sans connexion) : seulement ce qui est public, notée « sans objet » sinon. Mesures consignées dans ce fichier le jour du déploiement.

## Fichiers principaux touchés

- `templates/shop/theme_b2b/cart/` (gabarits du panier : `summary`, `_item`, état vide), `config/packages/demo_theme_b2b.yaml` (hooks du panier)
- `assets/shop/styles/b2b.scss`, `translations/` (domaine dédié au panier)
- `tests/Functional/Demo/`, `tests/Smoke/` (nouveaux fichiers ; tests existants du panier, des paliers, des approbations et du tunnel relus)
- `config/demo/proof.json` (rafraîchi), `docs/theme-b2b.md`, `docs/images/cart-*`, README

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-200-01 | [DOC] | Cadrage : inventaire du panier relu contre le code (hooks `cart_item` et `cart_tier_discount`, blocs des plugins : PO, limite et approbation, devis, liste d'achat, commande rapide, tunnel de commande, canal B2C, tests existants) ; scénarios types retenus (trois lignes, palier, dépassement de limite, panier vide) ; plan des tests | 0,75 h | - | Claude | 🔲 |
| T-200-02 | [TEST] | État de référence « avant » : tests du panier et du tunnel de commande de bout en bout verts sur `main`, captures connectées locales (bureau et 360 px) des quatre cas ; production sans objet pour la page connectée (relevé de ce qui est public) | 0,5 h | T-200-01 | Claude | 🔲 |
| T-200-03 | [FE-WEB] | Maquette Claude Design (bureau et téléphone) : tableau des lignes (photo, nom, référence, quantité, prix unitaire HT, remise de palier, total), récapitulatif collant (sous-total HT, remises, TVA, total TTC), messages d'approbation et de limite, actions secondaires groupées, panier vide, récapitulatif en bas d'écran sur téléphone, B2C inchangé ; publiée en artefact privé | 2 h | T-200-01, T-TECH-29-01 | Orchestrateur | 🔲 |
| T-200-04 | [OPS] | 📅 Thibaut valide la maquette (ou demande des retouches) : **aucun gabarit Twig avant son GO**, noté au registre des décisions | 0,5 h | T-200-03 | Thibaut | 📅 |
| T-200-05 | [TEST] | Tests rouges, commités seuls et cités dans la PR : panier lisible (référence, quantité, prix HT, total, récapitulatif HT, TVA, TTC), remise de palier après changement de quantité sans perdre les autres lignes, message d'approbation au dépassement, devis et liste d'achat aboutissent, panier vide, 360 px avec total visible, B2C inchangé, tunnel de commande de bout en bout | 1 h | T-200-04 | Claude | 📅 |
| T-200-06 | [FE-WEB] | Gabarits et hooks du panier, styles `b2b.scss`, récapitulatif collant (bureau) et en bas d'écran (téléphone), AAA, cibles de 44 px, `prefers-reduced-motion` ; blocs des plugins repris par leurs hooks ; canal B2B seulement | 3 h | T-200-05 | Claude | 📅 |
| T-200-07 | [DOC] | Textes FR et EN (domaine de traduction dédié), charte `PublicTextCharter` ; texte relu contre la maquette validée | 0,5 h | T-200-06 | Claude | 📅 |
| T-200-08 | [TEST] | Suite ciblée par `bin/run-tests` (panier, paliers, approbation, devis, liste, tunnel de commande de bout en bout), PHPStan max sur tout le dépôt, ECS, Rector, lint Twig et YAML, pa11y local FR et EN (quatre cas), `bin/measure-mobile` à 360 px ; commande exacte et dernière ligne citées | 1 h | T-200-06, T-200-07 | Claude | 📅 |
| T-200-09 | [DOC] | Captures `docs/images/cart-*` sur un rendu du noyau en mode prod (connecté, local), `docs/theme-b2b.md` et README mis à jour | 0,5 h | T-200-08 | Claude | 📅 |
| T-200-10 | [TEST] | Image reconstruite et conteneur neuf : panier connecté et tunnel de commande vérifiés (règle 16) ; production après le déploiement, lecture seule : ce qui est public, FR et EN, pa11y et 360 px ; mesures consignées le jour du déploiement | 0,5 h | merge de la PR | Claude | 📅 |
| T-200-11 | [REV] | Revue (ton neutre, parité FR et EN, B2C et tunnel inchangés) et table « scénario → test ou preuve » remplie ; `bin/proof-rebase` ou `php bin/proof-refresh` puis `php bin/proof-check` | 0,5 h | T-200-10 | Claude | 📅 |

**Total estimé : 10,75 h** (11 tâches ; 10,25 h pour Claude et l'orchestrateur, 0,5 h de Thibaut).

## Scénarios et preuves

| Scénario | Tâche de test | Test ou preuve |
|----------|---------------|----------------|
| Panier lisible (nominal) | T-200-05 (rouge), T-200-08 | |
| Remise de palier (alternatif) | T-200-05, T-200-08 | |
| Commande soumise à approbation (alternatif) | T-200-05 | |
| Actions B2B secondaires (alternatif) | T-200-05, T-200-08 | |
| Panier vide (erreur) | T-200-05, T-200-08 | |
| Débordement sur téléphone (erreur) | T-200-05, T-200-08 (`bin/measure-mobile`), T-200-10 | |
| Régression d'accessibilité ou du tunnel (erreur) | T-200-08 (pa11y AAA FR et EN, tunnel de bout en bout), T-200-10 (image reconstruite) | |

## Mesures (jour du déploiement)

_Non consignées (sprint planifié le 2026-10-25)._ Panier connecté : local et image reconstruite, aucune connexion en production.
