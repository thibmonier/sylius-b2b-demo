# Tâches — Sprint 006 (Railway et sécurité)

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Statut |
|----|-------|--------|--------|--------|--------|
| [US-032](./US-032-tasks.md) | Déploiement Railway | 5 | 12 | 22h | 🔲 |
| [US-033](./US-033-tasks.md) | Remise à zéro nocturne supervisée | 5 | 10 (+1 glissée) | 17h | 🔲 |
| [US-034](./US-034-tasks.md) | Budget et mesure du coût | 1 | 2 (+1 glissée) | 1.5h | 🔲 |
| [US-035](./US-035-tasks.md) | Admin de démo restreint | 8 | 10 | 19h | 🔲 |
| [US-036](./US-036-tasks.md) | Durcissement de la démo publique | 8 | 13 (+4 glissées) | 31.5h | 🔲 |
| [US-037](./US-037-tasks.md) | Revue de sécurité avant ouverture | 2 | 7 | 16.5h | 🔲 |
| Constats reportés hors story | — | — | 0 | 0h | — |
| *Reporté au sprint 7* : [US-006](./US-006-tasks.md) (2 pts, 5 tâches, 7h) et 6 tâches glissées (13h) | — | — | 11 | 20h | ➡️ |

**Total dans le sprint** : 54 tâches | 107.5h restantes | 29 points (127.5h estimées au départ, dont 20h glissées au sprint 7).

Aucun constat reporté n'est orphelin : les 6 constats du sprint 4, les 4 du sprint 5 et les 10 du sprint 2 sont tous rattachés à une story (voir ci-dessous). `technical-tasks.md` n'est donc pas créé.

## Alerte de capacité

- Estimation initiale : 127.5h (65 tâches) pour 21 points. Après l'arbitrage du 2026-10-16 : **107.5h restantes** (54 tâches) pour **29 points**, soit 3.7 h/point. Capacité de référence : 55 à 60 h (≈ 2,6 h/point au rythme des sprints précédents) : le sprint reste largement chargé (environ 180 % de la référence), les points ayant été ré-estimés à la hausse (US-033 3 → 5, US-035 5 → 8, US-036 3 → 8) pour refléter la charge réelle.
- Dont **10.5h d'actions externes** (Railway, vérification dynamique) qui dépendent de l'accès au compte.
- **Glissements appliqués au sprint 7 (20h)** :

| Élément | Heures | Justification |
|---------|--------|---------------|
| US-006 entière (T-006-01 à T-006-05) | 7h | Spike de performance : repli « mode classique » ; T-032-03 utilise le mode classique |
| T-036-12, T-036-13, T-036-14 (S2-R3 à S2-R10, latents) | 7.5h | Aucune perte (210 produits chargés) ; consignés dans review-findings |
| T-036-09 (S4-R3, prix « dès » en lot) | 3h | Coût CPU, pas une faille ; acceptation écrite, atténué par la limitation de débit T-036-02 |
| T-033-05 (S4-H, échéance du bandeau) | 2h | Confort d'affichage, ne bloque pas le GO |
| T-034-02 (relevé du coût à J+7) | 0.5h | Calendaire : US-034 se termine au sprint 7 |

- US-037 reste dans le sprint (condition du GO). La démo reste privée (authentification HTTP basique) jusqu'au GO explicite de l'utilisateur. Ordre de priorité si la capacité manque encore : US-032, US-035, US-036 (cœur), US-033, puis US-037.

## Répartition par type

| Type | Nb tâches | Heures | % |
|------|-----------|--------|---|
| [BE] | 22 | 52h | 48% |
| [DOC] | 6 | 7h | 6% |
| [FE-WEB] | 2 | 3h | 3% |
| [OPS] | 10 | 13.5h | 13% |
| [REV] | 7 | 12.5h | 12% |
| [TEST] | 7 | 19.5h | 18% |
| **TOTAL** | **54** | **107.5h** | 100% |

## Traçabilité des constats reportés → tâches

| Constat | Tâche(s) | Preuve attendue (comportement rendu) |
|---------|----------|------|
| S4-R1 | T-035-01 à T-035-08, T-033-02 | Écriture sensible refusée, mot de passe inchangé, XSS non exécuté |
| S4-R2 | T-036-08 | Avertissement présent dans le HTML rendu (FR/EN) |
| S4-R3 | T-036-09 (sprint 7) | Nombre de requêtes constant sur une liste |
| S4-R4 | T-036-10 | Pas de cookie de session sur GET / anonyme |
| S4-R5 | T-036-11 | 404 pour une locale hors canal |
| S4-R6 | T-032-02, T-032-06 | Set-Cookie demo_badges avec Secure derrière proxy de confiance |
| S4-H | T-033-05 (sprint 7) | Bandeau affiche l'échéance 03:00 Paris |
| S5-R1 | T-036-03, T-033-03 | Rejeu refusé, plafond Acme, reset purge Acme |
| S5-R2 | T-032-02, T-036-01 | Hôte non listé = 400, IP visiteur non remplacée par celle du proxy |
| S5-R3 | T-036-04 | Un visiteur ne bloque pas les autres (429 isolé par IP) |
| S5-R4 | T-036-05 | E-mail hors comptes de démo absent de la boîte |
| S2-R1 | T-035-03, T-036-02, T-036-06 | Écritures refusées, 429 login et /demo/accounts, DSN externe interdit |
| S2-R2 | T-033-01, T-033-02, T-033-04 | Reset OK en prod avec DEMO_MODE=1, échec = données intactes |
| S2-R3 à S2-R6 | T-036-12 (sprint 7) | Lignes fautives signalées, BOM lu |
| S2-R7 | T-036-13 (sprint 7) | Requêtes du reset réduites de 50 % |
| S2-R8 à S2-R10 | T-036-14 (sprint 7) | Tests de vérification verts |

## Actions externes (compte Railway)

Marquées « action externe — exécutée par l'orchestrateur après accord déjà donné ». La démo reste privée (authentification HTTP basique par variable) jusqu'au GO explicite de l'utilisateur après US-037.

| ID | Action |
|----|--------|
| T-032-09 | Création du projet Railway, des services (web, MySQL, Mailpit) et connexion GitHub |
| T-032-10 | Variables d'environnement, secrets et domaine HTTPS généré |
| T-032-11 | Premier déploiement et vérifications : 401 sans identifiants, migrations, bascule après healthcheck, Mailpit injoignable |
| T-033-09 | Notifications e-mail Railway activées (échec de déploiement / crash) |
| T-033-10 | Reset manuel sur Railway : durée < 10 min, démo de nouveau opérationnelle, échec simulé visible dans le journal |
| T-034-01 | Limite d'usage Railway ≤ 25 $/mois et alerte de seuil |
| T-034-02 | Relevé du coût réel après une semaine d'exploitation (sprint 7) |
| T-037-03 | Vérification dynamique sur la démo privée déployée : 401, en-têtes, cookies Secure, 429, hôtes, Mailpit |

## Fichiers

- [US-006 - Spike : mode worker FrankenPHP](./US-006-tasks.md) (reporté au sprint 7)
- [US-032 - Déploiement Railway](./US-032-tasks.md)
- [US-033 - Remise à zéro nocturne supervisée](./US-033-tasks.md)
- [US-034 - Budget et mesure du coût](./US-034-tasks.md)
- [US-035 - Admin de démo restreint](./US-035-tasks.md)
- [US-036 - Durcissement de la démo publique](./US-036-tasks.md)
- [US-037 - Revue de sécurité avant ouverture](./US-037-tasks.md)

## Conventions

- **ID** : T-[US]-[Numéro] (ex : T-035-03)
- **Taille** : 0.5h - 8h max
- **Statuts** : 🔲 À faire | 🔄 En cours | 👀 Review | ✅ Done | 🚫 Bloqué
- Aucun build d'image Docker, aucune commande Railway/gh/push depuis le poste de développement : build en CI ou par Railway.

## Arbitrage de capacité (décision utilisateur, 2026-10-16)

Estimation initiale 127,5 h pour 21 pts. Retenu : ré-estimation et glissement de l'optionnel au sprint 7.

- Points ré-estimés : US-033 3 → 5, US-035 5 → 8, US-036 3 → 8 ; US-006 (spike worker) entière au sprint 7. Sprint 6 = **29 pts** (US-032 5, US-033 5, US-034 1, US-035 8, US-036 8, US-037 2).
- Tâches glissées (statut « ➡️ sprint 7 ») : T-006-*, T-033-05, T-034-02 (relevé du coût à J+7, calendaire : US-034 se termine au sprint 7), T-036-09 (S4-R3), T-036-12/13/14 (S2-R3 à R10).
- US-037 reste au sprint : c'est la condition du GO d'ouverture publique ; la démo reste privée d'ici là.
