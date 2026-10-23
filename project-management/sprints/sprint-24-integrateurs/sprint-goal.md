# Sprint 24 : Intégrateurs, compatibilité et outils à jour

> **Lancé le 2026-10-23**, le jour de la clôture du sprint 23. Périmètre décidé par le PO le 2026-10-23 : **16 points engagés**, plus **3 points conditionnels** (US-175). La décomposition en tâches est faite : voir [tasks/README.md](./tasks/README.md). Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-24`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 24 |
| Début | 2026-10-23 (lancement) |
| Fin | au plus tard le 2026-11-06 ; clôture dès que le périmètre est vérifié (action 6 de la rétro du sprint 22) |
| Capacité | **16 points engagés** ; 60 tâches, 39,5 h (38,25 h pour Claude, 1,25 h d'actions de Thibaut) pour 41,6 h de capacité (16 × 2,1 h + 8 h transverses), soit 3,35 h de marge. Avec US-175 : 19 points, 70 tâches, 45,75 h pour 47,9 h |
| Vélocité de référence | S19 25, S20 22, S21 17, S22 23, S23 11 (moyenne 19,6) |
| Branche | `feature/sprint-24-integrateurs` (PR du sprint) ; US-174 sur la branche de la PR Dependabot #54, dans une PR séparée |

Le périmètre est sous la vélocité de référence (19,6) : 16 points engagés, 19 si US-175 entre.

## Sprint Goal

> « Aider un développeur d'agence à intégrer les plugins sans surprise (matrice de compatibilité, graphe des dépendances, guide de surcharge), rendre l'architecture lisible sur téléphone, passer les tests à PHPUnit 13 et cadrer deux chantiers futurs (facture électronique France, connecteur ERP). »

## Périmètre engagé

Gherkin : au minimum 1 nominal, 2 alternatifs et 2 erreurs par story. « Fiche » = fichier `backlog/user-stories/US-XXX.md`.

| ID | Titre | Pts | MoSCoW | Statut | Sacrifice |
|----|-------|-----|--------|--------|-----------|
| [US-174](../../backlog/user-stories/US-174.md) | Montée de la démo à PHPUnit 13 | 3 | Could | backlog | non sacrifiable |
| [US-171](../../backlog/user-stories/US-171.md) | Matrice de compatibilité et graphe des dépendances des plugins | 3 | Should | backlog | non sacrifiable |
| [US-172](../../backlog/user-stories/US-172.md) | Guide de surcharge des plugins | 3 | Should | backlog | non sacrifiable |
| [US-179](../../backlog/user-stories/US-179.md) | Schéma d'architecture lisible sur mobile et barre de la démo sans dépassement | 1 | Could | backlog | non sacrifiable |
| [US-132](../../backlog/user-stories/US-132.md) | Spike P7 : facture électronique France (PA/PDP) et e-reporting | 3 | Should | backlog | n°2 |
| [US-130](../../backlog/user-stories/US-130.md) | Spike P5 : connecteur ERP de référence | 3 | Should | backlog | n°1 |

Total engagé : **16 points**.

**Conditionnelle** :

| ID | Titre | Pts | MoSCoW | Statut | Sacrifice |
|----|-------|-----|--------|--------|-----------|
| [US-175](../../backlog/user-stories/US-175.md) | Métapaquets des packs « IA et headless » et « Conformité et outillage » | 3 | Should | backlog | n°3 |

US-175 n'entre que si Thibaut donne son GO pour créer les deux dépôts et valide les noms des paquets (tâche T-175-01, 📅 en tête de la story). Sans ce GO, elle reste au backlog. Avec elle : **19 points**.

**Ordre de sacrifice** (décidé par le PO) : US-130, puis US-132, puis US-175.
- US-174 répond à la décision de Thibaut à la fin du spike US-169.
- US-171 et US-172 répondent à la critique de l'architecte Sylius et du client B2B (2026-10-23).
- US-179 reprend les constats de la vérification en production d'US-170.
- US-132 et US-130 sont les spikes sortis du sprint 23 au profit d'US-170 et d'US-173.
- US-175 est la première story de réalisation de l'ADR 0015.

**Hors sprint** : US-176, US-177 et US-178 (suite de l'ADR 0015, dépendent d'US-175) ; US-128 attend ses données (bilan J+30 du 2026-11-18, fin du calendrier éditorial le 2026-12-15) ; US-103 et US-121 restent conditionnées au transcript T-103-03 ; US-154 et US-062 sont au backlog.

## Checklist de démarrage (2026-10-23)

### Avant le Sprint Planning

- [x] Sprint précédent terminé : sprint 23 clos le 2026-10-23 (11 points sur 11, revue et rétro écrites)
- [x] Actions de la rétrospective du sprint 23 reprises :
  - actions 1 et 2 : ✅ faites par l'orchestrateur au planning (règle 9 de `consigne-worker.md`, section « Pièges connus » de `docs/tests-locaux.md`) ;
  - action 3 : T-TECH-24-08 (ligne déjà ajoutée à `checklist-integration.md`) ;
  - action 4 : procédure de production ajoutée à `docs/tests-locaux.md`, lieu de vérification écrit dans chaque story d'interface.
- [x] Backlog priorisé par le PO : périmètre décidé le 2026-10-23
- [x] Stories candidates estimées et « Ready » : voir la Definition of Ready ci-dessous
- [x] Capacité calculée : 41,6 h pour 38,25 h de charge de Claude (47,9 h pour 44 h avec US-175)
- [x] Branche `feature/sprint-24-integrateurs` créée ; `.bmad/sprint-status.yaml` mis à jour par l'orchestrateur
- [x] Disque : environ 6 Go libres le 2026-10-23, au-dessus du seuil de 5 Go de `bin/run-tests` ; sous les 10 Go qui permettraient de construire l'image en local

### Après le Sprint Planning

- [x] Sprint backlog visible : [task-board.md](./task-board.md), [tasks/README.md](./tasks/README.md)
- [x] Dépendances : [sprint-dependencies.md](./sprint-dependencies.md)
- [ ] GO de Thibaut pour US-175 (création des deux dépôts, noms des paquets), à demander au plus tôt pour décider de l'entrée de la story
- [ ] Point d'étape avec Thibaut à caler sur ses créneaux de relecture (premier ADR de spike)

## Definition of Ready (contrôle du 2026-10-23)

| US | Fiche | Gherkin (1+2+2 min.) | Prérequis d'environnement | État |
|----|-------|----------------------|----------------------------|------|
| US-174 | oui | oui | branche de la PR #54 ; CI comme instrument de mesure | prête |
| US-171 | oui | oui | accès `gh` en lecture aux 25 dépôts ; lieu de vérification du rendu : production après déploiement | prête |
| US-172 | oui | oui | aucun ; lieu de vérification du rendu : production après déploiement ; GO de Thibaut pour les PR de README | prête |
| US-179 | oui | oui | aucun ; lieu de vérification du rendu : production après déploiement | prête |
| US-132 | oui | oui | accès web en lecture aux sources officielles | prête |
| US-130 | oui | oui | accès web en lecture à la documentation publique des éditeurs | prête |
| US-175 | oui | oui | GO de Thibaut et noms des paquets validés | conditionnelle |

## Definition of Done (rappel)

- [ ] Chaque scénario Gherkin relié à un test ou à une preuve dans le fichier de tâches (action 1 de la rétro du sprint 22)
- [ ] Stories de code : commit de tests rouges cité dans la PR (action 2)
- [ ] Stories d'interface : rendu vérifié au lieu écrit dans leur fichier de tâches (pa11y WCAG2AAA en FR et en EN, 360 px) ; la story reste en review jusque-là (action 4 de la rétro du sprint 23)
- [ ] PR qui ajoute ou supprime un fichier de tests : `php bin/proof-refresh` puis `php bin/proof-check` (action 3)
- [ ] ECS, PHPStan max et Rector verts, rejoués avant le push ; aucune nouvelle entrée `ignoreErrors`
- [ ] Aucun secret ni détail de sécurité exploitable dans le dépôt
- [ ] Documentation à jour
- [ ] CI verte (« Quality and smoke tests », « Docker image »), run terminé avant le merge ; `main` protégée (PR requise)
- [ ] Spikes : sources citées avec date de consultation, temps consigné (4 h au plus), décision de Thibaut notée, aucun avis juridique (US-132)

## Actions de Thibaut

- GO pour US-175 : création des deux dépôts et noms des paquets (T-175-01).
- Lire les ADR 0016 et 0017 et décider (T-132-06, T-130-06).
- Relire les diffs des README de plugins et donner le GO des PR (T-172-08).
- Accord de repli de la PR #54, seulement si la montée échoue (T-174-11).
- Déployer `main` sur Railway après le merge du sprint (T-TECH-24-09).
- Soumettre les paquets à Packagist si US-175 entre (T-175-10).
- Transcript T-103-03, s'il veut faire entrer US-103 et US-121.

## Risques

| Risque | Probabilité | Impact | Mitigation |
|--------|-------------|--------|------------|
| PHPUnit 13 révèle plus de 10 échecs de comportement au premier run | Faible | Moyen | Repli prévu par la story : arrêt, fermeture de la PR #54, Dependabot réglé avec l'accord de Thibaut ; US-174 mergée en dernier, aucune autre story bloquée |
| Conflits de `config/demo/proof.json` entre les PR qui ajoutent des tests | Élevée | Faible | Fichier régénéré après chaque rebase par `php bin/proof-refresh`, jamais fusionné à la main ; merges séquencés |
| Réécriture de `tests/` par US-174 en conflit avec les tests des autres stories | Moyenne | Moyen | US-174 mergée en dernier ; les nouveaux tests évitent l'accès par clé après `assertIsArray` |
| Rendu non vérifiable en local (environ 6 Go libres) | Élevée | Moyen | Vérification en production après déploiement par Thibaut ; stories d'interface en review jusque-là ; déploiement à demander dès le merge du sprint |
| Disque qui repasse sous 5 Go : `bin/run-tests` refuse de démarrer | Moyenne | Moyen | Relevé avant chaque lot, un seul serveur lourd à la fois, aucun second `vendor/` (US-174 mesurée par la CI) |
| Source officielle introuvable ou contradictoire (US-132) | Moyenne | Moyen | Règle non confirmée marquée « à vérifier », source la plus récente retenue, écart signalé ; aucun avis juridique |
| Capacité d'un ERP cible affirmée sans source (US-130) | Moyenne | Moyen | Documentation publique de l'éditeur citée avec sa date, sinon « à vérifier » |
| Matrice de compatibilité présentée comme « validée ensemble » | Moyenne | Élevé | Deux colonnes distinctes (CI du plugin, démo) ; relecture du ton à la revue de US-171 |
| Texte de page, d'ADR ou de README qui compare avec une offre commerciale | Faible | Élevé | `PublicTextCharter`, ton neutre, relecture à chaque revue ; packs filtrés par le classement US-063 |
| PR de README de plugins ou dépôts de packs sans GO | Faible | Élevé | T-172-08 et T-175-01 avant toute écriture distante ; aucun tag ni dépôt sans GO |
| Écriture de la preuve en retard sur `main` | Moyenne | Faible | T-TECH-24-08 ; build de `main` surveillé à chaque merge |

## Cérémonies

Calées sur le travail réel (action 6 de la rétro du sprint 22) : la revue et la rétro se tiennent dès que le périmètre est vérifié, au plus tard le 2026-11-06.

| Cérémonie | Quand | Durée |
|-----------|-------|-------|
| Sprint Planning (Parts 1 et 2) | 2026-10-23 (périmètre décidé par le PO, décomposition faite) | fait |
| Point d'étape | À la première relecture de Thibaut (ADR 0016 ou 0017, ou GO d'US-175) | 30 min |
| Sprint Review | Dès le périmètre vérifié, au plus tard le 2026-11-06 | 1 h |
| Rétrospective | Le même jour que la revue | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
