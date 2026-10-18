# Task Board : Sprint 014 (Bloquants d'ouverture)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Calendaire | ⚠️ Sacrifiable

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Assigné |
|----|-----|------|-------|------------|---------|
| T-070-01 | US-070 | [TEST] | Test rouge : les clés `tmonier_punchout.ui.buyers` existent dans les 7 langues du plugi… | 0,5h | |
| T-070-02 | US-070 | [BE] | Ajouter `ui.buyers` aux 7 fichiers `translations/messages.*.yaml` | 1h | |
| T-070-03 | US-070 | [REV] | Revue | 0,5h | |
| T-071-01 | US-071 | [TEST] | Script `bin/measure-mobile` | 2h | |
| T-071-02 | US-071 | [FE-WEB] | Corriger les débordements : connexion | 1,5h | |
| T-071-03 | US-071 | [FE-WEB] | Cibles tactiles ≥ 44 x 44 px : boutons, pagination, favoris, champs | 1h | |
| T-071-04 | US-071 | [FE-WEB] | Libellé explicite du champ de recherche de catégorie | 0,5h | |
| T-071-06 | US-071 | [REV] | Revue | 0,5h | |
| T-072-01 | US-072 | [TEST] | Tests rouges d'audit : un `header`, un `main`, un `footer` uniques, un seul `h1`, lien … | 2h | |
| T-072-02 | US-072 | [FE-WEB] | Surcharge du layout : `header`, `main` | 2h | |
| T-072-03 | US-072 | [FE-WEB] | Lien « Aller au contenu » visible au focus | 1h | |
| T-072-04 | US-072 | [FE-WEB] | Région `aria-live` polie pour les confirmations | 1,5h | |
| T-072-05 | US-072 | [FE-WEB] | Un seul `h1` par page : connexion, pages d'erreur 403 et 404 avec mêmes repères | 1,5h | |
| T-072-06 | US-072 | [TEST] | Audit des repères et du lien d'évitement ajouté à la CI | 1h | |
| T-072-08 | US-072 | [REV] | Revue | 0,5h | |
| T-073-01 | US-073 | [DOC] | Mesure des contrastes actuels | 1h | |
| T-073-02 | US-073 | [TEST] | Contrôle des contrastes | 1,5h | |
| T-073-03 | US-073 | [FE-WEB] | Jeton CSS unique pour le brun | 1h | |
| T-073-04 | US-073 | [FE-WEB] | Corriger pagination, code des identifiants de démo, boutons favoris, textes grisés | 1,5h | |
| T-073-06 | US-073 | [REV] | Revue | 0,5h | |
| T-074-01 | US-074 | [TEST] | Tests rouges : pied B2B sans newsletter ni partenaires, URL https cliquable, absence d'… | 1,5h | |
| T-074-02 | US-074 | [BE] | Paramètres `site_url` et `contact_url` validés | 1,5h | |
| T-074-03 | US-074 | [FE-WEB] | Surcharge du pied de page : colonne « Cette démo », mention « réalisé par Thibaut Monier » | 1,5h | |
| T-074-04 | US-074 | [FE-WEB] | Traductions FR et EN, vérification du rendu à 375 et 1280 px | 0,5h | |
| T-074-06 | US-074 | [REV] | Revue | 0,5h | |
| T-075-01 | US-075 | [TEST] | Tests rouges : comptes en tête, groupés par société avec rôle | 1h | |
| T-075-02 | US-075 | [FE-WEB] | Réordonner la page de connexion : comptes groupés d'abord, formulaire classique dessous | 2h | |
| T-075-03 | US-075 | [FE-WEB] | Libellés FR et EN, connexion en un clic avec le rôle annoncé | 0,5h | |
| T-075-04 | US-075 | [REV] | Revue | 0,5h | |
| T-076-01 | US-076 | [TEST] | Tests rouges : une langue à la fois, lien contact présent ou masqué | 1h | |
| T-076-02 | US-076 | [FE-WEB] | Barre sur une ligne dans la langue courante | 1,5h | |
| T-076-03 | US-076 | [FE-WEB] | Lien contact depuis la configuration | 0,5h | |
| T-076-04 | US-076 | [TEST] | Mesure réelle : une ligne à 375 px et à 1280 px, FR et EN | 0,5h | |
| T-076-05 | US-076 | [REV] | Revue | 0,5h | |
| T-077-01 | US-077 | [DOC] | Inventaire des libellés accessibles restés en anglais | 1h | |
| T-077-02 | US-077 | [TEST] | Test de traduction : échoue en nommant toute clé sans traduction | 1h | |
| T-077-03 | US-077 | [BE] | Traductions FR | 1,5h | |
| T-077-04 | US-077 | [REV] | Revue | 0,5h | |
| T-079-01 | US-079 | [DOC] | Noms exacts des jobs de `ci.yaml` et lecture de l'état actuel de la protection | 0,5h | |
| T-079-02 | US-079 | [OPS] | Appliquer la protection de `main` via `gh api` | 1h | |
| T-079-03 | US-079 | [TEST] | Script de contrôle en lecture seule `bin/check-branch-protection` : échoue et demande l… | 1,5h | |
| T-079-04 | US-079 | [DOC] | Checklist [OPS] de Thibaut | 1h | |
| T-079-07 | US-079 | [DOC] | Rapport de vérification : conclusion par vérification, actions de Thibaut, sans valeur … | 0,5h | |
| T-079-08 | US-079 | [REV] | Revue | 0,5h | |
| T-080-01 | US-080 | [TEST] | Tests d'intégration rouges : seuls les mails des fixtures, boîte vide, 404 sur mail exc… | 1,5h | |
| T-080-02 | US-080 | [BE] | Filtre par liste blanche dans `DemoMailbox` | 1,5h | |
| T-080-03 | US-080 | [FE-WEB] | Message « aucun mail » FR et EN, rendu réel de la page | 0,5h | |
| T-080-04 | US-080 | [REV] | Revue | 0,5h | |
| T-086-01 | US-086 | [DOC] | Arbitrer et consigner : option de configuration + traduction neutre | 0,5h | |
| T-086-02 | US-086 | [TEST] | Test de fumée de la démo : coller l'exemple, aperçu à deux lignes valides | 1h | |
| T-086-03 | US-086 | [BE] | Plugin quick-order : option `placeholder` et défaut neutre, tests et README | 1,5h | |
| T-086-04 | US-086 | [BE] | Démo : paramètre des références d'exemple | 0,5h | |
| T-086-05 | US-086 | [REV] | Revue des deux dépôts | 0,5h | |
| T-087-01 | US-087 | [TEST] | Tests rouges : nombre calculé = 24 | 1,5h | |
| T-087-02 | US-087 | [BE] | Nombre de plugins calculé depuis `config/demo/plugins.yaml` | 1h | |
| T-087-03 | US-087 | [FE-WEB] | Mention « en cours » ou « à venir » pour chaque plugin non publié | 0,5h | |
| T-087-04 | US-087 | [FE-WEB] | Devis vide : message FR et EN à la place de la clé `sylius.ui.continue_shopping` | 0,5h | |
| T-087-05 | US-087 | [FE-WEB] | Admin en français : tableau de bord | 1,5h | |
| T-087-07 | US-087 | [REV] | Revue | 0,5h | |
| T-104-01 | US-104 | [DOC] | Consigner la décision D2 et sa date dans `US-104.md` | 0,5h | |
| T-104-02 | US-104 | [TEST] | Tests rouges : phrase factuelle FR et EN, plus d'encart | 0,5h | |
| T-104-03 | US-104 | [FE-WEB] | Remplacer l'encart et les traductions `plugins_plus.*` | 0,5h | |
| T-104-04 | US-104 | [REV] | Revue | 0,5h | |
| T-071-05 | US-071 | [TEST] | ⚠️ Contenu long | 0,5h | |
| T-072-07 | US-072 | [DOC] | ⚠️ Documentation du layout et de l'audit | 0,5h | |
| T-073-05 | US-073 | [FE-WEB] | ⚠️ Contraste élevé | 0,5h | |
| T-074-05 | US-074 | [DOC] | ⚠️ Documentation des deux paramètres d'URL | 0,5h | |
| T-087-06 | US-087 | [TEST] | ⚠️ Parcours de rendu réel des pages clés | 0,5h | |

## 📅 Calendaire (GO ou action de Thibaut, hors chemin critique)

| ID | US | Tâche | Estimation | Dépend de |
|----|-----|-------|------------|-----------|
| T-070-04 | US-070 | [OPS] Ouverture de la PR du plugin avec la démo, merge puis release de patch | 0,5h | T-070-03 |
| T-079-05 | US-079 | [OPS] Thibaut : vérifier l'exposition des services de l'hébergement | 1h | T-079-04 |
| T-079-06 | US-079 | [OPS] Thibaut : relire les fichiers de configuration versionnés et confirmer la clé de produc… | 1h | T-079-04 |
| T-086-06 | US-086 | [OPS] PR du plugin avec la démo, merge puis release de patch | 0,5h | T-086-05 |
| T-052-03 à T-052-07 | US-052 | [OPS] Moniteur, bascule, `X-Real-IP`, MCP, coût Railway (reportées) | n/a | GO et comptes de Thibaut |

## 🔄 En Cours
| ID | US | Tâche | Démarré | Assigné |
|----|-----|-------|---------|---------|

## 👀 En Review
| ID | US | Tâche | Reviewer |
|----|-----|-------|----------|

## ✅ Terminé
| ID | US | Tâche | Réel | Terminé |
|----|-----|-------|------|---------|

## 🚫 Bloqué
| ID | US | Raison | Action |
|----|-----|--------|--------|

## Métriques
- **Tâches** : {ntasks} total | 0 terminées (0 %)
- **Heures** : {fmt(tot_all)} estimées (dont {fmt(tot_cal)} calendaires) | 0 h consommées | {fmt(tot_all)} restantes (ordre de sacrifice dans [tasks/README.md](./tasks/README.md))
