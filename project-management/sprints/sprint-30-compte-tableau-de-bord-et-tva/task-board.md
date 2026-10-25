# Task Board : Sprint 030 (compte, tableau de bord et TVA)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Dépend d'une action de Thibaut | ⚠️ Sacrifiable | 🟡 Faite avec écart | ⊘ Sans objet

Total décomposé : **41 tâches, 34,5 h** (13 points). État au planning : **1 ✅, 24 🔲, 16 📅, 0 🔄, 0 👀, 0 🚫**. Les 16 tâches 📅 attendent le GO de la maquette « Mon compte » (T-199-04) ou celui du tableau de bord (T-201-04) : aucun Twig avant. Les tâches T-TECH-30-01 à 04 passent en tête, avant toute story.

Règle (action 5 de la rétro du sprint 22) : une tâche passe en ✅ dans le commit qui la livre.

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-TECH-30-01 | Transverse | OPS | Un seul cache de rendu par worker : `bin/render-page` réutilise le même cache, refuse d'en créer un second … | 1,25 h | Orchestrateur |
| T-TECH-30-02 | Transverse | OPS | `bin/proof-rebase` : rebase sur `origin/main`, régénère `config/demo/proof.json` par `bin/proof-refresh`, … | 1 h | Orchestrateur |
| T-TECH-30-03 | Transverse | OPS | Vignettes `b2b_photo*` dans `bin/render-page` : le rendu du noyau génère les vignettes des filtres … | 0,75 h | Orchestrateur |
| T-TECH-30-04 | Transverse | TEST | `ErpDemoExchangeFixtureTest` isolé : le test écrit et lit dans un dossier propre au test (paramètre du … | 0,5 h | Orchestrateur |
| T-TECH-30-06 | Transverse | REV | Tenue du registre des décisions et des GO de Thibaut (maquettes « Mon compte » et tableau de bord, éventuelle … | 0,75 h | Orchestrateur |
| T-TECH-30-07 | Transverse | OPS | Disque et machine : avant chaque lot `df -h .`, `bin/check-disk`, mémoire libre et swap ; après chaque lot … | 0,5 h | Orchestrateur |
| T-TECH-30-08 | Transverse | OPS | ECS (`src tests`), PHPStan max sur tout le dépôt et Rector rejoués sur l'arbre exact avant chaque push ; CI … | 0,75 h | Orchestrateur |
| T-TECH-30-09 | Transverse | TEST | Capture à 1280 px relue avant la PR (règle 23) : home connectée et chaque page touchée (compte des trois … | 0,5 h | Orchestrateur |
| T-TECH-30-10 | Transverse | DOC | Revue et rétrospective du sprint 30 : indicateurs (0 régénération manuelle de la preuve, 0 capture simulée, 0 … | 1 h | Orchestrateur |
| T-199-01 | US-199 | DOC | Cadrage : inventaire du tableau de bord du compte relu contre le code (hooks … | 0,75 h | Claude |
| T-199-02 | US-199 | TEST | État de référence « avant » : tests du compte et des menus verts sur `main` ; captures connectées locales … | 0,5 h | Claude |
| T-199-03 | US-199 | FE-WEB | Maquette Claude Design (bureau 1440 px et téléphone 390 px), page « Mon compte » du canevas : accueil par … | 2 h | Orchestrateur |
| T-201-01 | US-201 | DOC | Cadrage : gabarits et hooks du plugin relus contre le code (`dashboard`, `widgets`, `monthly`, `rankings`, … | 0,75 h | Claude |
| T-201-02 | US-201 | TEST | État de référence « avant » : tests du tableau de bord verts sur `main` ; captures connectées locales (bureau … | 0,5 h | Claude |
| T-201-03 | US-201 | FE-WEB | Maquette Claude Design (bureau 1440 px et téléphone 390 px), page « Tableau de bord des achats » du canevas : … | 2 h | Orchestrateur |
| T-202-01 | US-202 | DOC | Cadrage : affectation de la catégorie (fixtures CSV, import ERP, historique en masse), mode de calcul … | 0,75 h | Claude |
| T-202-02 | US-202 | TEST | Tests rouges, commités seuls et cités dans la PR : panier HT, livraison, TVA 20 %, TTC ; prix affichés … | 0,75 h | Claude |
| T-202-03 | US-202 | DB | Catégorie `standard` affectée aux produits des fixtures et aux produits de l'import ERP simulé ; historique … | 1 h | Claude |
| T-202-04 | US-202 | FE-WEB | Récapitulatif du panier : ligne TVA et total TTC réaffichés à la place de la maquette validée ; récapitulatif … | 0,75 h | Claude |
| T-202-05 | US-202 | BE | Limite d'approbation comparée au HT par décoration dans la démo (contrôle, politique, montant de la demande, … | 0,75 h | Claude |
| T-202-06 | US-202 | TEST | Suite ciblée par `bin/run-tests` (panier, paliers, approbation, tunnel de commande de bout en bout, import … | 0,75 h | Claude |
| T-202-07 | US-202 | DOC | Captures `docs/images/cart-*` mises à jour par `bin/render-page`, section du panier de `docs/theme-b2b.md` | 0,5 h | Claude |
| T-202-08 | US-202 | TEST | Image reconstruite et conteneur neuf : panier et commande avec TVA ; production après la remise à zéro de … | 0,5 h | Claude |
| T-202-09 | US-202 | REV | Revue (ton neutre, parité FR et EN, prix inchangés, plugin non modifié) et table « scénario → test ou preuve … | 0,25 h | Claude |

## 📅 En attente d'une action de Thibaut (ou de ce qui en dépend)

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-199-04 | US-199 | OPS | Thibaut valide la maquette (ou demande des retouches) : aucun gabarit Twig avant son GO, noté au registre des … | 0,5 h | Thibaut |
| T-199-05 | US-199 | TEST | Tests rouges, commités seuls et cités dans la PR : accueil de Claire, de Paul (compteur d'approbations en … | 1 h | Claude |
| T-199-06 | US-199 | FE-WEB | Gabarits et hooks du compte (fichier de hooks propre), partial `_account.scss`, menu latéral et accès … | 3 h | Claude |
| T-199-07 | US-199 | DOC | Textes FR et EN (domaine `account`), charte `PublicTextCharter` ; texte relu contre la maquette validée | 0,5 h | Claude |
| T-199-08 | US-199 | TEST | Suite ciblée par `bin/run-tests` (compte, menus, approbations, devis, listes, badges), PHPStan max sur tout … | 0,75 h | Claude |
| T-199-09 | US-199 | DOC | Captures `docs/images/account-*` par `bin/render-page` (connecté, local, trois comptes, bureau et 360 px), … | 0,5 h | Claude |
| T-199-10 | US-199 | TEST | Image reconstruite et conteneur neuf : compte des trois rôles vérifié (règle 16 : rien hors base) ; … | 0,5 h | Claude |
| T-199-11 | US-199 | REV | Revue (ton neutre, parité FR et EN, B2C inchangé) et table « scénario → test ou preuve » remplie | 0,5 h | Claude |
| T-201-04 | US-201 | OPS | Thibaut valide la maquette (ou demande des retouches) : aucun gabarit Twig avant son GO, noté au registre des … | 0,5 h | Thibaut |
| T-201-05 | US-201 | TEST | Tests rouges, commités seuls et cités dans la PR : indicateurs en premier avec période de comparaison, … | 1 h | Claude |
| T-201-06 | US-201 | FE-WEB | Surcharge dans la démo : fichier de hooks propre, gabarits, partial `_insights.scss`, graphique accessible … | 3 h | Claude |
| T-201-07 | US-201 | DOC | Textes FR et EN (domaine `insights`), charte `PublicTextCharter` ; texte relu contre la maquette validée | 0,5 h | Claude |
| T-201-08 | US-201 | TEST | Suite ciblée par `bin/run-tests` (tableau de bord, classements, export, droits), PHPStan max sur tout le … | 0,75 h | Claude |
| T-201-09 | US-201 | DOC | Captures `docs/images/insights-*` par `bin/render-page` (Isabelle, local, bureau et 360 px), section du … | 0,5 h | Claude |
| T-201-10 | US-201 | TEST | Image reconstruite et conteneur neuf : tableau de bord d'Isabelle vérifié (règle 16 : rien hors base) ; … | 0,5 h | Claude |
| T-201-11 | US-201 | REV | Revue (ton neutre, parité FR et EN, plugin non modifié) et table « scénario → test ou preuve » remplie | 0,5 h | Claude |

## 🔄 En cours

_Aucune tâche._

## 👀 En review

_Aucune tâche._

## 🚫 Bloqué ou écarté

_Aucune tâche._

## ✅ Terminé

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-TECH-30-05 | Transverse | DOC | Règles 21 à 23 de la consigne (fichiers partagés déclarés et attribués ; aucun test depuis un worktree au … | 0,5 h | Orchestrateur |

## Métriques

| Mesure | Valeur |
|--------|--------|
| Points engagés | 13 (US-199 5, US-201 5, US-202 3) |
| Tâches | 41 (US-199 11, US-201 11, US-202 9, transverses 10) |
| Heures | 34,5 h (33,5 h Claude, 1 h Thibaut) pour 35,3 h de capacité, marge de 1,8 h |
| Ordre de sacrifice | US-202, puis US-201 ; US-199 protégée |
