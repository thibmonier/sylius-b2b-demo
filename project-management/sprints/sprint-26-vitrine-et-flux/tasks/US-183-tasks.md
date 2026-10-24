# Tâches : US-183 : PA fictive de démonstration

## Informations US

- **Epic** : EPIC-14
- **Persona** : Thibaut ; lead développeur d'une agence
- **Story Points** : 5 (Should, sacrifice : non sacrifiable)
- **Sprint** : sprint-26-vitrine-et-flux
- **Worker proposé** : w183
- **Story** : [US-183](../../../backlog/user-stories/US-183.md)

## Résumé de la US

**En tant que** lead développeur qui évalue la démo  
**Je veux** voir une facture électronique déposée auprès d'une plateforme fictive qui répond par des statuts simulés  
**Afin de** comprendre le flux complet de transmission sans aucune plateforme réelle

## Règles et contraintes

- **Le travail est dans la démo**, sans nouveau dépôt : adaptateur et service de PA fictive dans `src/Demo/Einvoice/`, plugin de transmission v0.1.0 installé depuis Packagist (aucune modification du plugin).
- **Prérequis** : la soumission du plugin à Packagist par Thibaut (T-180-13 du sprint 25) est faite ; sinon la story est bloquée (🚫) et signalée, aucun dépôt VCS de contournement.
- PA fictive en processus (aucun appel sortant, ADR 0008) ; scénarios fixés par les données de démonstration, rejouables après la remise à zéro nocturne.
- Aucune plateforme réelle nommée ; statuts « à vérifier » (ADR 0016) ; bandeau « simulés, sans valeur réglementaire » ; ton neutre, aucune comparaison avec Sylius Plus.
- Catalogue, matrice de compatibilité et preuve à jour (`config/demo/plugins.yaml`, `compatibility.json`, `proof.json`) ; compteur de plugins et tests qui le citent relus.
- Tests uniquement par `bin/run-tests` ; commit de tests rouges seul ; règle 10 : `git` dans l'arbre principal.
- Règles du sprint, consigne de worker et actions de la rétro du sprint 25 : voir [README.md](./README.md).

## Lieu de vérification (action 4 de la rétro du sprint 23)

Pas de page de production à juger au sens d'US-186 ; la page « PA fictive » est vérifiée par ses tests fonctionnels (FR et EN) et, après déploiement, par un contrôle de réponse 200 en lecture seule.

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- `composer.json`, `composer.lock`, `config/bundles.php`, `config/packages/` (nouveau fichier du plugin), `migrations/`
- `src/Demo/Einvoice/` (nouveau), `src/Demo/Controller/` (page de la PA fictive), `templates/demo/` (nouveau gabarit), `translations/` (nouveau domaine)
- `fixtures/`, `src/Demo/DataFixtures/` (commandes payées par scénario)
- `config/demo/plugins.yaml`, `config/demo/compatibility.json`, `config/demo/proof.json`
- `tests/` (nouveaux tests)
- Lecture : plugin de transmission, `sylius-facturx-plugin`, `docs/adr/0016-*.md`

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-183-01 | [DOC] | Cadrage : plugin de transmission v0.1.0 (port, journal, `additional_statuses`), événement de `facturx`, génération des factures dans la démo (`src/Invoice`, fixtures), ADR 0016 (statuts « à vérifier »), forme de la PA fictive (service en processus), choix des scénarios par les données | 1 h | - | Claude | 🔲 |
| T-183-02 | [OPS] | Prérequis et installation : paquet résolu sur Packagist (T-180-13 du sprint 25) sinon arrêt de la story ; `composer require ^0.1`, bundle, migration, configuration ; contrôle de la concordance de version | 1 h | T-183-01 | Claude | 🔲 |
| T-183-03 | [TEST] | Tests rouges, commités seuls et cités dans la PR : dépôt et statut simulé, rejet avec motif, refus et encaissement, bandeau, événement rejoué, statut hors ensemble refusé, aucun appel sortant, charte | 1,5 h | T-183-02 | Claude | 🔲 |
| T-183-04 | [BE] | Adaptateur `FictitiousPlatformTransmitter` (`src/Demo/Einvoice/`) et service de PA fictive : réponse selon le scénario, motif simulé | 1,5 h | T-183-03 | Claude | 🔲 |
| T-183-05 | [BE] | Configuration de la démo : adaptateur choisi par service, statuts additionnels `refused` et `collected`, libellés FR et EN des statuts | 0,75 h | T-183-04 | Claude | 🔲 |
| T-183-06 | [DB] | Données de démonstration : commandes payées pour chaque scénario dans les fixtures, factures générées, rejouables après la remise à zéro nocturne (ADR 0007) | 1,25 h | T-183-05 | Claude | 🔲 |
| T-183-07 | [FE-WEB] | Page « PA fictive de démonstration » (FR et EN) avec le bandeau « statuts simulés, sans valeur réglementaire, à vérifier » et la liste des scénarios ; lien depuis la page Intégrer | 1,5 h | T-183-05 | Claude | 🔲 |
| T-183-08 | [DOC] | Catalogue `config/demo/plugins.yaml`, matrice `compatibility.json`, compteur de plugins et tests qui le citent, documentation de la démo | 0,75 h | T-183-06, T-183-07 | Claude | 🔲 |
| T-183-09 | [TEST] | Suite ciblée par `bin/run-tests` (`WORKER_ID=w183 …`), ECS, PHPStan max, Rector, lint Twig et YAML ; dernières lignes citées | 0,75 h | T-183-08 | Claude | 🔲 |
| T-183-10 | [REV] | Revue (aucune plateforme réelle nommée, ton neutre, parité FR et EN, texte relu contre le code et l'ADR 0016) et table « scénario → test ou preuve » remplie | 0,25 h | T-183-09 | Claude | 🔲 |

**Total estimé : 10,25 h** (10 tâches ; 10,25 h pour Claude et l'orchestrateur).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, run de CI). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Facture déposée puis statut simulé (nominal) | |
| Rejet avec motif (alternatif) | |
| Refus et encaissement (alternatif) | |
| Bandeau explicatif (alternatif) | |
| Événement rejoué (erreur) | |
| Plugin absent de Packagist ou statut hors ensemble (erreur) | |
