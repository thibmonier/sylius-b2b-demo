# Sprint Review - Sprint 23 (Garde-fous et lisibilité de l'architecture)

> Préparée le 2026-10-23, le jour même du sprint : le périmètre est vérifié, y compris en production (action 6 de la rétro du sprint 22 : clôture dès le périmètre vérifié).

## Informations

| Attribut | Valeur |
|----------|--------|
| Sprint | 23, `sprint-23-garde-fous` |
| Période | 2026-10-23 (lancé et terminé le même jour ; fin au plus tard prévue le 2026-11-06) |
| Points engagés | 11 (US-168 1, US-166 3, US-169 2, US-170 3, US-173 2) |
| Points en done | **11** |
| PR | #69 (lancement), #70 (remaniement après la critique), #71 (implémentation), #72 (décisions des spikes) |
| Mode | Orchestrateur (Claude) et trois workers : w166 (US-166), w169 (spike US-169), w173 (spike US-173) ; US-168 et US-170 par l'orchestrateur |

## Participants

| Rôle | Personne |
|------|----------|
| Product Owner | Thibaut |
| Scrum Master, orchestrateur | Claude |
| Développeurs | Claude (orchestrateur et workers) |

## Sprint Goal

> Rendre automatiques les garde-fous qui ont manqué au sprint 22 (preuve en retard, tests sans attente, méthodes courtes), montrer l'architecture de la démo sur une page dédiée, puis décider de PHPUnit 13 et de la forme des distributions de plugins.

**Atteint.** Les trois garde-fous tournent dans la suite ou dans la CI, la page d'architecture est en production, et Thibaut a pris les deux décisions le jour même.

## User stories livrées

| Story | Points | Livré | Commit rouge (TDD) | Preuve principale |
|-------|--------|-------|--------------------|-------------------|
| US-168 Contrôle de retard de la preuve | 1 | `bin/proof-check` (sans réseau ni écriture), règle `TestFileRule` partagée, comptage local de la démo dans `bin/proof-refresh`, étape CI informative sur les PR et bloquante sur `main` | 77014ec (58 tests, 16 erreurs, 9 échecs) | Build de `main` après #71 : vert, `Proof up to date: 203 test files, 25 plugins` |
| US-166 Méthodes longues et `usleep` | 3 | 6 méthodes découpées, 7 `usleep` remplacés par des attentes sur condition (`tests/Support/Await.php`, horloge injectable), gardes `MethodLengthTest` et `NoSleepInTestsTest` | 493701d (4 tests, 2 échecs) | `RunTestsLockTest` vert 20 fois de suite ; suite complète verte hors US-170 en cours |
| US-169 Spike PHPUnit 13 | 2 | Rapport `project-management/audits/2026-10-23-spike-phpunit-13/rapport.md` | sans objet | Décision de Thibaut : story US-174 au sprint 24 |
| US-170 Page « Architecture de la démo » | 3 | `/{locale}/architecture` : schéma SVG accessible, couches, flux avec une commande B2B de bout en bout, exploitation, qualité, sécurité en termes généraux ; liens depuis le pied de page et le guide d'intégration | 377a55d (16 tests, 14 échecs, 2 erreurs) | Production : pa11y WCAG2AAA sans problème en FR et en EN, pas de défilement à 360 px |
| US-173 Spike distributions | 2 | ADR 0015 accepté : métapaquet puis recette Flex, deux packs sans recoupement avec une offre commerciale | sans objet | Décision de Thibaut : US-175 à US-178 au backlog |

Chaque fichier de tâches relie ses 5 scénarios à un test ou à une preuve : 25 scénarios, 0 sans preuve.

## Décisions de Thibaut (2026-10-23)

- **PHPUnit 13** : montée retenue, story [US-174](../../backlog/user-stories/US-174.md) (3 points) au sprint 24 ; la PR #54 reste ouverte ; repli si plus de 10 échecs de comportement au premier run.
- **Distributions** : [ADR 0015](../../../docs/adr/0015-distributions-de-plugins.md) accepté ; réalisation en quatre stories, 15 points : US-175 (métapaquets), US-176 (installation réelle en CI), US-177 (recettes Flex), US-178 (page dans la démo). Noms des paquets et pull request publique sur `symfony/recipes-contrib` à trancher au cadrage ; GO requis pour toute publication.

## Éléments non terminés

Aucun. Deux constats de la vérification en production sortent du périmètre et vont au backlog dans [US-179](../../backlog/user-stories/US-179.md) (1 point) :
- à 360 px, le schéma d'architecture est réduit à environ 52 % (libellés d'environ 7 px CSS) : conforme aux critères (pas de défilement, description textuelle), mais petit à lire ;
- en français, la barre commune de la démo dépasse de 4 px sur toutes les pages (aussi sur `/fr_FR/preuve`), sans défilement de la page.

## Détail par lot

### Garde-fous (US-168, US-166)

- Le scénario « Preuve en retard » d'US-084, non livré au sprint 22, est couvert : un `proof.json` en retard fait échouer le build de `main`.
- Choix du cadrage d'US-168 : la démo publie désormais le nombre de fichiers de tests de son arbre local. Sans ce choix, une PR ne pouvait pas embarquer une preuve à jour (l'arbre GitHub lu était celui de `main`, avant le merge) et `main` serait devenu rouge après chaque ajout de test.
- US-166 : les gardes sont des tests d'architecture et non des étapes de CI, ce qui a évité le conflit sur `ci.yaml` avec US-168. Les quatre boucles d'attente de port, dupliquées, passent par un seul assistant. Une attente qui expire échoue maintenant avec un message, alors que les anciennes boucles continuaient sans rien dire.

### Lisibilité de l'architecture (US-170)

- Aucun chiffre n'est écrit à la main : un test vérifie que tout nombre affiché vient de `proof.json` ou de la configuration, et que les traductions n'en contiennent aucun.
- La rubrique Sécurité est vérifiée par un test : aucun chiffre, aucune route, aucun terme de réglage.

### Décisions outillées (US-169, US-173)

- Les deux spikes ont tenu largement sous leur budget de 4 h (environ 45 min pour US-173). Aucune installation lourde : résolution Composer sans `vendor/`, logs de CI et notes de version.

## Vérification finale (Scrum Master)

| Contrôle | Résultat |
|----------|----------|
| CI de la PR #71 | 5 vérifications vertes, dont « Quality and smoke tests » (14 min 52) et « Docker image » |
| Build de `main` après #71 | vert, y compris l'étape bloquante « Published proof is up to date » |
| CI de la PR #72 | verte |
| Avant le push de #71 | `ecs check src tests`, PHPStan max, Rector : verts ; dernière ligne citée dans la PR |
| Production, page Architecture | FR et EN en 200, six rubriques, chiffres sourcés, `noindex`, pa11y WCAG2AAA sans problème, 360 px sans défilement |

## Incidents et écarts de processus

| Incident | Effet | Suite |
|----------|-------|-------|
| Erreur 500 de la page Architecture au premier run vert attendu : dans Twig, `fact.verified` appelle la fabrique statique `ProofFact::verified()` et non `isVerified()` | Détectée par les tests rouges devenus « encore rouges » ; environ 30 min pour trouver la cause | Corrigé avant le push ; piège noté pour les prochaines pages |
| Commande de fond bloquée : `rm` est un alias de `rm -i`, qui attendait une confirmation ; un `sed -n 1,25p` avait aussi coupé la sortie de PHPUnit | Environ 10 min perdues, tâche arrêtée par `TaskStop` | Action 2 de la rétro |
| Lecture filtrée : RTK a masqué des commentaires et des docblocks à la lecture par `cat` et `sed` | w166 a dû restaurer 5 fichiers de `src` et refaire ses découpages sur le texte réel ; 2 commentaires de section retirés | Action 1 de la rétro |
| `lint:container` impossible en local (limite de 128 Mo du PHP local) | Contrôle laissé à la CI, qui l'a passé | Aucune |
| Rendu réel d'US-170 impossible en local (pas d'image de la démo, 6 à 7 Go libres) | Vérification reportée après le déploiement, faite en production le jour même | Action 4 de la rétro |
| Refus d'outil : suppression du projet jetable de w173 | Non contourné ; environ 25 Mo restent dans le scratchpad | Suppression à la main |

## Métriques

| Métrique | Sprint 23 | Sprint 22 |
|----------|-----------|-----------|
| Points engagés / en done | 11 / 11 | 27 / 23 (2 stories sacrifiées) |
| Stories en done avec un scénario non livré | 0 | 1 (US-084) |
| Stories de code avec commit rouge cité dans la PR | 3 sur 3 | 0 |
| Tâches | 40 sur 40 en ✅ à la clôture | 9 livrées restées 🔲 |
| Fichiers de tests de la démo (preuve publiée) | 203 | 188 |
| Plantage de la machine | 0 | 1 |
| Espace libre minimal | 6,0 Go | environ 2,6 Go |
| Contournements de refus d'outil | 0 (1 refus) | 0 (4 refus) |

## Démonstration (ordre suggéré)

1. `/fr_FR/architecture` : le schéma, puis les rubriques ; montrer que chaque rubrique renvoie à un fichier du dépôt.
2. Changer de langue (`/en_US/architecture`), puis ouvrir le guide d'intégration et suivre le lien vers la page.
3. Dans le dépôt : `php bin/proof-check`, puis le résumé du job de CI d'une PR qui ajoute un test.
4. Lancer `tests/Unit/Architecture` : les deux gardes (méthodes de plus de 45 lignes, `usleep` dans les tests).
5. L'ADR 0015 et le rapport PHPUnit 13, avec les stories qui en découlent.

## Feedback à collecter

- La page Architecture répond-elle à la critique de l'architecte Sylius ? Manque-t-il une rubrique ?
- Le contrôle de preuve bloquant sur `main` est-il acceptable au quotidien (rafraîchir la preuve dans chaque PR qui touche aux tests) ?
- Priorité du sprint 24 entre US-174, US-171, US-172, US-132, US-130 et US-175.

## Impact sur le backlog

| Story | Points | Statut | Origine |
|-------|--------|--------|---------|
| US-174 Montée à PHPUnit 13 | 3 | backlog, cible sprint 24 | décision T-169-05 |
| US-175 à US-178 Packs | 3 + 5 + 5 + 2 | backlog | décision T-173-05, ADR 0015 |
| US-179 Lisibilité mobile du schéma et de la barre de démo | 1 | backlog | vérification T-170-06 |

## Prochaines étapes

1. Thibaut valide la revue et la rétrospective, et la clôture du sprint 23.
2. Planning du sprint 24 : candidats US-174, US-171, US-172, US-132, US-130, puis US-175 et US-179 selon la capacité.
