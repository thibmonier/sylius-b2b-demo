# Tâches — Sprint 007 (Plugins et intégrations)

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Statut |
|----|-------|--------|--------|--------|--------|
| [US-019](./US-019-tasks.md) | Mode d'accès par canal (plugin publié) | 8 | 18 | 41.5h | 🔲 |
| [US-020](./US-020-tasks.md) | Inscription validée par l'admin (plugin publié) | 8 | 17 | 40h | 🔲 |
| [US-038](./US-038-tasks.md) | Correctifs du plugin punchout-cxml | 8 | 16 | 30.5h | 🔲 |
| [US-039](./US-039-tasks.md) | Correctif du plugin container-fill | 3 | 8 | 9.5h | 🔲 |

**Total** : 59 tâches | 121.5h | 27 points (4.5 h/point).

Aucune tâche n'est transverse : `technical-tasks.md` n'est pas créé (les tâches de CI, de dépôt et de publication sont rattachées à leur story).

## Arbitrage de capacité

- Décomposition initiale : 9 stories, 103 tâches, 210h pour 34 points, soit environ 167 % de la capacité de référence (34 pts × 3,7 h/pt ≈ 126 h).
- **Décision de l'utilisateur (2026-10-16)** : le sprint 7 est recentré sur les plugins, **27 points, 121.5h** (US-019, US-020, US-038, US-039). Capacité de référence correspondante : 27 pts × 3,7 h/pt ≈ 100 h, soit environ 122 %.
- **Passent au sprint 8** (« sprint-8-demo-et-durcissement », planifié, voir [sa synthèse](../../sprint-8-demo-et-durcissement/tasks/README.md)) : US-006, US-029, US-030, US-040, US-041, soit 17 points et 88.5h (44 tâches). Leurs fichiers de tâches y ont été déplacés.
- Dont **9h d'actions externes** (8 tâches, soumises au GO de l'utilisateur) : elles créent des attentes, pas de la charge de développement.
- Leviers si le sprint dérive, par ordre de sacrifice : intégration de démo d'US-020 (T-020-15/16, 4.5h) si le GO de publication n'arrive pas, puis US-039. Ordre de priorité : US-019, US-020, US-038, US-039.

## Répartition par type

| Type | Nb tâches | Heures | % |
|------|-----------|--------|---|
| [DB] | 2 | 5h | 4% |
| [BE] | 21 | 50.5h | 42% |
| [FE-WEB] | 3 | 9h | 7% |
| [TEST] | 8 | 25.5h | 21% |
| [DOC] | 4 | 4.5h | 4% |
| [REV] | 5 | 10h | 8% |
| [OPS] | 16 | 17h | 14% |
| **TOTAL** | **59** | **121.5h** | 100% |

[FE-MOB] : aucun (pas d'application mobile dans la démo).

## Traçabilité des constats reportés → tâches

| Constat | Tâche(s) | Preuve attendue (comportement rendu) |
|---------|----------|------|
| Commande PunchOut « invitée » + contournement (S5) | T-038-01, T-038-02, T-038-13, T-038-15 | Commande Acme payable sur compte sans AuthorizedPunchOutOrderFactory |
| R1 interfaces Doctrine (S3) | T-038-03, T-038-04, T-038-12 | `doctrine:schema:validate` vert sans resolve_target_entities |
| R2 événement de création (S3) | T-038-05 | Événement dispatché une fois avec l'orderID |
| R6 libellé des notes (S3) | T-038-05, T-038-14 | Aucun parsing des notes dans la démo |
| R3 guard `complete` (S3) | T-038-06, T-038-07 | PATCH complete refusé, commande reste au panier ; boutique normale inchangée |
| R5 container-fill (S3) | T-039-01 à T-039-08 | Panier mixte accepté avec règle « tous produits » |

Les constats du sprint 6 (M2, M4, M5, M6, M8, S2-R3 à R10, S4-R3, T-033-05, T-034-02) et les stories US-006, US-029, US-030 sont tracés dans le sprint 8.

## Actions externes (soumises au GO de l'utilisateur)

Marquées « action externe — après GO explicite de l'utilisateur ». Aucune commande externe (gh, tag, Packagist, railway, `composer require` depuis Packagist) sans accord. Sans GO : plugin non tagué, intégration par chemin local, contournement punchout conservé.

| ID | Action | GO |
|----|--------|----|
| T-019-14 | Créer le dépôt GitHub public `tmonier/sylius-channel-access-plugin` et les topics | Plugin d'accès |
| T-019-15 | Tag v0.1.0, release GitHub et publication Packagist | Plugin d'accès |
| T-019-18 | Démo : passer du chemin local à la version Packagist (contrainte `^0.1`) et relancer les tests | Plugin d'accès |
| T-020-13 | Créer le dépôt GitHub public `tmonier/sylius-registration-approval-plugin` et les topics | Plugin d'inscription |
| T-020-14 | Tag v0.1.0, release GitHub et publication Packagist | Plugin d'inscription |
| T-020-17 | Démo : passer à la version Packagist du plugin et relancer les tests | Plugin d'inscription |
| T-038-11 | Tag, release et Packagist du plugin punchout-cxml | punchout-cxml |
| T-039-07 | Tag, release et Packagist du plugin container-fill | container-fill |

## Fichiers

- [US-019 - Mode d'accès par canal (plugin publié)](./US-019-tasks.md)
- [US-020 - Inscription validée par l'admin (plugin publié)](./US-020-tasks.md)
- [US-038 - Correctifs du plugin punchout-cxml](./US-038-tasks.md)
- [US-039 - Correctif du plugin container-fill](./US-039-tasks.md)

## Conventions

- **ID** : T-[US]-[Numéro] (ex : T-019-05)
- **Taille** : 0.5h - 8h max
- **Statuts** : 🔲 À faire | 🔄 En cours | 👀 Review | ✅ Done | 🚫 Bloqué | ⏳ GO utilisateur
- Aucun build d'image Docker lourd, aucune commande Railway/gh/push/Packagist depuis le poste sans accord ; avant tout commit dans un dépôt de plugin : `git fetch` et vérification de la branche courante.
- US-020 démarre sur le contrat d'US-019 (T-019-04), pas sur sa publication.
- Chemin critique : T-019-04 → T-020-01 → T-020-05 → T-020-08 → T-020-10 → GO → T-020-14 → T-020-17.
