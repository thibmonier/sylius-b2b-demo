# Task Board : Sprint 031 (chaîne d'approvisionnement, comptes des plugins et page Plugins)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Dépend d'une action de Thibaut ou d'un déploiement | ⚠️ Sacrifiable | 🟡 Faite avec écart | ⊘ Sans objet

Total décomposé : **47 tâches, 34,25 h** (13 points). État au planning (2026-10-27) : **1 ✅, 31 🔲, 15 📅**. Les 📅 attendent un GO de Thibaut (planche du relevé, maquette « Plugins », contrôles requis de `main`) ou le déploiement de `main` (vérifications de production).

Règle (action 5 de la rétro du sprint 22) : une tâche passe en ✅ dans le commit qui la livre.

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-208-01 | US-208 | DOC | Cadrage : **contrôle « si vous avez été touché »** (historique de `composer.lock` contre les alertes de … | 1 h | Claude |
| T-208-02 | US-208 | TEST | Tests rouges, commités seuls et cités dans la PR : fixtures de verrou (mise à jour ordinaire, tag réécrit … | 1,5 h | Claude |
| T-208-03 | US-208 | BE | `bin/check-lock-diff` : compare `composer.lock` à la branche de base ; échoue en nommant paquet, version … | 1,5 h | Claude |
| T-208-04 | US-208 | OPS | `composer.json` : `config.policy` explicite (blocage des paquets malveillants, sans `cooldown`), … | 1 h | Claude |
| T-208-05 | US-208 | OPS | Workflows : `permissions: {}` au niveau du workflow et `contents: read` par job ; `persist-credentials: … | 0,75 h | Claude |
| T-208-06 | US-208 | OPS | Job zizmor obligatoire : version figée, image figée par condensat, `--offline` ; alertes corrigées ou … | 1,25 h | Claude |
| T-208-07 | US-208 | DOC | `docs/durcissement-demo.md` (ligne « chaîne d'approvisionnement », sans détail sensible) ; **inventaire … | 0,5 h | Claude |
| T-208-08 | US-208 | TEST | Suite ciblée par `bin/run-tests` (scripts, fixtures, workflows, Dockerfile), puis suite complète si le … | 0,75 h | Claude |
| T-208-09 | US-208 | REV | Revue (aucun secret ni détail d'infrastructure dans le dépôt, la PR et les messages ; plugins de la démo … | 0,5 h | Claude |
| T-203-01 | US-203 | DOC | Cadrage : relire contre le code les quatre templates du `vendor/` (devis liste et détail, relevé, … | 0,75 h | Claude |
| T-203-02 | US-203 | TEST | État de référence « avant » : tests du compte et des menus verts sur `main` ; captures connectées locales … | 0,5 h | Claude |
| T-203-03 | US-203 | FE-WEB | **Planche de maquette du relevé** dans le canevas unique : tableau de relevé et tranches d'ancienneté dans … | 1,25 h | Orchestrateur |
| T-203-05 | US-203 | TEST | **Test de dérive** : empreinte de chaque template d'origine du `vendor/` comparée à une valeur versionnée, … | 0,5 h | Claude |
| T-203-06 | US-203 | TEST | Tests rouges, commités seuls et cités dans la PR : devis (liste, détail) et assistants IA dans le cadre du … | 0,75 h | Claude |
| T-203-07 | US-203 | FE-WEB | Coquille commune et surcharges des devis (liste, détail) et des assistants IA sous `templates/bundles/` : … | 2 h | Claude |
| T-203-08 | US-203 | FE-WEB | ⚠️ **Négociable** : page de consentement du plugin MCP, cadre B2B seul (en-tête, charte, polices, … | 0,75 h | Claude |
| T-203-11 | US-203 | DOC | Textes FR et EN (domaine `account_plugins`), charte `PublicTextCharter` ; texte relu contre la maquette … | 0,5 h | Claude |
| T-203-12 | US-203 | TEST | Suite ciblée par `bin/run-tests` (compte, menus, devis, relevé, MCP, dérive), PHPStan max sur tout le … | 0,75 h | Claude |
| T-203-13 | US-203 | DOC | Captures `docs/images/account-quotes-*`, `account-statement-*`, `account-connections-*` par … | 0,5 h | Claude |
| T-204-01 | US-204 | DOC | Cadrage : gabarit (61 lignes) et contrôleur relus ; données et familles de plugins disponibles ; badges … | 0,5 h | Claude |
| T-204-02 | US-204 | TEST | État de référence « avant » : les quatre tests existants verts sur `main` ; captures locales (bureau 1280 … | 0,5 h | Claude |
| T-204-03 | US-204 | FE-WEB | Maquette Claude Design (bureau 1440 px et téléphone 390 px), nouvelle page « Plugins » du canevas : … | 1,25 h | Orchestrateur |
| T-TECH-31-01 | Transverse | OPS | **Parallélisme réglé sur la machine** : avant de lancer les workers, relevé de `df -h /`, de la mémoire … | 0,75 h | Orchestrateur |
| T-TECH-31-02 | Transverse | OPS | **Suite complète locale avant le push de la PR de sprint**, ou attente explicite : si le disque l'empêche, … | 0,5 h | Orchestrateur |
| T-TECH-31-03 | Transverse | TEST | **Cohérence HT et TTC des montants** : un test vérifie que les commandes d'historique sont toutes sans TVA … | 0,75 h | Orchestrateur |
| T-TECH-31-04 | Transverse | OPS | **Outils de rendu et de console** : `bin/render-page` génère les vignettes pour les URL d'images relatives … | 0,75 h | Orchestrateur |
| T-TECH-31-06 | Transverse | REV | Tenue du registre des décisions et des GO de Thibaut (planche du relevé, page « Plugins », réglage des … | 0,75 h | Orchestrateur |
| T-TECH-31-07 | Transverse | OPS | **Disque et machine** : avant chaque lot `df -h .`, `bin/check-disk`, mémoire libre et swap ; après chaque … | 0,5 h | Orchestrateur |
| T-TECH-31-08 | Transverse | OPS | ECS (`src tests`), PHPStan max sur **tout le dépôt** et Rector rejoués sur l'arbre exact avant chaque push … | 0,75 h | Orchestrateur |
| T-TECH-31-09 | Transverse | TEST | **Capture à 1280 px relue avant la PR** (règle 23) : home connectée et chaque page touchée (devis, relevé, … | 0,5 h | Orchestrateur |
| T-TECH-31-10 | Transverse | DOC | Revue et rétrospective du sprint 31 : indicateurs (0 arrêt de worker pour cause de disque, 0 CI rouge … | 1 h | Orchestrateur |
| T-TECH-31-11 | Transverse | TEST | LCP mobile de la home revenu sous 1 456 ms (ressources comparées au sprint 26, correction, remesure en production) | 2 h | Claude |

## 📅 En attente (GO de Thibaut ou déploiement)

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-208-10 | US-208 | OPS | Après le déploiement de `main` : image reconstruite et conteneur neuf (règle 16 : rien hors base), … | 0,5 h | Claude |
| T-208-11 | US-208 | OPS | 📅 Thibaut ajoute le job zizmor aux contrôles requis de `main` (« Quality and smoke tests », « Docker image … | 0,25 h | Thibaut |
| T-203-04 | US-203 | OPS | 📅 Thibaut valide la planche (ou demande des retouches) : **aucun Twig du relevé avant son GO**, noté au … | 0,5 h | Thibaut |
| T-203-09 | US-203 | TEST | 📅 Tests rouges du relevé, commités seuls : tableau et tranches d'ancienneté lisibles à 320 px sans … | 0,25 h | Claude |
| T-203-10 | US-203 | FE-WEB | 📅 Surcharge du relevé de paiements à terme selon la planche validée : tableau, tranches d'ancienneté, … | 1,25 h | Claude |
| T-203-14 | US-203 | TEST | Image reconstruite et conteneur neuf : les quatre pages et la page de consentement pour Claire, Paul et … | 0,5 h | Claude |
| T-203-15 | US-203 | REV | Revue (ton neutre, parité FR et EN, B2C inchangé, plugins intacts, aucun accès élargi) et table « scénario … | 0,25 h | Claude |
| T-204-04 | US-204 | OPS | 📅 Thibaut valide la maquette (ou demande des retouches) : **aucun gabarit Twig avant son GO**, noté au … | 0,5 h | Thibaut |
| T-204-05 | US-204 | TEST | 📅 Tests rouges, commités seuls et cités dans la PR : cartes avec nom, rôle, badges et liens vers le code, … | 0,5 h | Claude |
| T-204-06 | US-204 | FE-WEB | 📅 Gabarit et styles de la page (partial `_plugins.scss`, classes `b2b-home-*`), regroupement par famille, … | 1,75 h | Claude |
| T-204-07 | US-204 | DOC | 📅 Textes FR et EN (domaine `plugins`), charte `PublicTextCharter` ; texte relu contre la maquette validée | 0,25 h | Claude |
| T-204-08 | US-204 | TEST | 📅 Suite ciblée par `bin/run-tests` (les quatre tests existants **non modifiés**, nouveaux tests), PHPStan … | 0,75 h | Claude |
| T-204-09 | US-204 | DOC | 📅 Captures `docs/images/plugins-*` par `bin/render-page` (FR et EN, bureau et 320 px), section de … | 0,5 h | Claude |
| T-204-10 | US-204 | TEST | Image reconstruite et conteneur neuf (règle 16 : rien hors base) ; production **le jour du déploiement**, … | 0,5 h | Claude |
| T-204-11 | US-204 | REV | Revue (ton neutre, parité FR et EN, contenu inchangé, plugins en recoupement neutres) et table « scénario … | 0,25 h | Claude |

## 🔄 En cours

_Aucune tâche._

## 👀 En review

_Aucune tâche._

## 🚫 Bloqué ou écarté

_Aucune tâche._

## 🟡 Faites avec écart

_Aucune tâche._

## ✅ Terminé

| ID | US | Type | Tâche | Estimation | Réalisé par |
|----|-----|------|-------|------------|-------------|
| T-TECH-31-05 | Transverse | DOC | **Échanges avec les workers** : règles 24 (parallélisme) et 25 (chaque réponse de l'orchestrateur cite … | 0,25 h | Orchestrateur |

## Métriques

| Mesure | Valeur |
|--------|--------|
| Points engagés | 13 (US-208 5, US-203 5, US-204 3) |
| Tâches | 47 (US-208 11, US-203 15, US-204 11, transverses 10) |
| Heures | 34,25 h (33 h Claude, 1,25 h Thibaut) pour 35,3 h de capacité, marge de 2,3 h |
| Ordre de sacrifice | US-204, puis T-TECH-31-11, puis US-203 (T-203-08 d'abord) ; US-208 protégée |
