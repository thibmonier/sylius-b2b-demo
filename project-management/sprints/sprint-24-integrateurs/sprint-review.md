# Sprint Review - Sprint 24 (Outillage des intégrateurs)

> Préparée le 2026-10-23, le jour du sprint : périmètre vérifié, y compris en production.

## Informations

| Attribut | Valeur |
|----------|--------|
| Sprint | 24, `sprint-24-integrateurs` |
| Période | 2026-10-23 (lancé et terminé le même jour ; fin au plus tard prévue le 2026-11-06) |
| Points engagés | 19 (16 au lancement, puis US-175 entrée après le GO de Thibaut) |
| Points en done | **19** |
| PR de la démo | #74 (lancement), #75 (implémentation), #76 (PHPUnit 13) |
| Autres dépôts | deux nouveaux dépôts de packs (v0.1.0, Packagist) ; README de facturx, registration-approval et container-fill (PR #5 de chacun) |
| Mode | Orchestrateur (Claude) et workers : wA (US-179, US-172), wB (US-171), w175 (US-175), w132 et w130 (spikes) ; US-174 par l'orchestrateur |

## Sprint Goal

> Donner aux intégrateurs une matrice de compatibilité générée et un guide de surcharge tiré de la démo, monter la démo à PHPUnit 13, rendre le schéma d'architecture lisible sur mobile, et éclairer par deux spikes la facture électronique française et le connecteur ERP.

**Atteint**, avec en plus les deux premiers packs de plugins publiés.

## User stories livrées

| Story | Points | Livré | Commit rouge (TDD) | Preuve principale |
|-------|--------|-------|--------------------|-------------------|
| US-171 Matrice de compatibilité | 3 | `bin/compat-refresh`, `config/demo/compatibility.json` généré depuis les `composer.json` et les matrices de CI des 25 plugins ; page `/{locale}/compatibilite` (matrice, graphe obligatoire/optionnel, « testé seul » / « testé dans la démo ») ; `bin/proof-check` étendu | a1de90b | Production : pa11y WCAG2AAA sans problème FR et EN, 360 px sans défilement |
| US-172 Guide de surcharge | 3 | Page `/{locale}/surcharge` : gabarit, formulaire, entité, service, avec fichier, lignes, lien et commande ; chaque référence porte un texte témoin vérifié par un test ; liens dans trois README de plugins | 4a3abde, ce758fb | Production : pa11y sans problème ; README publiés en anglais et en français |
| US-179 Lisibilité mobile | 1 | Schéma dans une région à défilement focusable ; menus de la barre de démo qui passent à la ligne | 8a98bfe | Production : libellés de 13 px à 360 px, 704 px inchangés à 1280 px, barre sans dépassement |
| US-174 PHPUnit 13 | 3 | PHPUnit 10.5.66 → 13.4.3 ; 59 erreurs PHPStan corrigées sans aucune erreur ignorée ; 20 avertissements et 38 notices de PHPUnit levés ; CI sans couleurs | f8c4d2c, 72e11e3, 27c5c6c | CI de la PR #76 verte : 1 976 tests (1 968 de `main` + 8 nouveaux), couverture 93,31 % |
| US-175 Métapaquets des packs | 3 | `tmonier/sylius-pack-ai-headless` et `tmonier/sylius-pack-compliance-tooling`, vérificateur de composition, CI, README FR et EN | c49dbd1, 078c2c9 (dépôts des packs) | Releases v0.1.0, paquets sur Packagist (Thibaut) |
| US-132 Spike facture électronique | 3 | ADR 0016 proposé : module de transmission vers une plateforme fictive | sans objet | Décision : rien planifié au sprint 24 |
| US-130 Spike connecteur ERP | 3 | ADR 0017 proposé : contrat neutre et ERP fictif, périmètre réduit | sans objet | Décision : rien planifié au sprint 24 |

35 scénarios, chacun relié à un test ou à une preuve. Un écart justifié : la garde T-174-02 n'a pas été écrite (PHPUnit 13 fait déjà échouer un fournisseur déclaré en docblock).

## Décisions de Thibaut (2026-10-23)

- US-174 sur la branche de la PR #54, puis réalisée par l'orchestrateur dans l'arbre principal après le refus de `git` dans le worktree isolé du worker. La PR #54 ayant été fermée avant le sprint, la montée passe par la PR #76.
- US-175 : GO pour créer les deux dépôts (noms de l'ADR 0015), puis pour le tag v0.1.0 ; soumission à Packagist par Thibaut.
- ADR 0016 et 0017 : rien planifié au sprint 24, retour au planning du sprint 25.
- README de plugins : facturx, registration-approval et container-fill, **sans tier-pricing** (plugin qui recoupe une offre commerciale).
- Railway déploie `main` automatiquement : la tâche de déploiement par Thibaut était sans objet.

## Éléments non terminés

Aucun.

## Incidents et écarts de processus

| Incident | Effet | Suite |
|----------|-------|-------|
| `git` refusé dans le worktree isolé du worker w174 (toutes les commandes, même `git status`) | US-174 ne pouvait pas être déléguée ; non contourné | Réalisée par l'orchestrateur dans l'arbre principal, avec l'accord de Thibaut ; action 2 |
| PR Dependabot #54 fermée depuis le compte de Thibaut avant le sprint, branche supprimée | Plan de la story à adapter | Nouvelle branche `chore/phpunit-13`, PR #76 |
| Disque sous 5 Go à deux reprises (4,0 puis 4,3 Go) : bases de test des workers accumulées dans le volume MySQL | Runs refusés par `bin/run-tests` ; mesure des notices reportée sur la CI | `bin/clean-test-caches --all`, puis Thibaut libère de l'espace (7,5 Go) ; action 1 |
| CI de la PR #76 en échec alors que tous les tests passaient : PHPUnit 13 colore le rapport de couverture, le résumé du job ne trouvait plus ses lignes | Une passe de CI de plus | Test rouge, puis `--colors=never` |
| Deux phrases de README préparées par un worker annonçaient des exemples absents du guide | Relevé avant publication | Reformulées ; action 3 |
| Références du guide de surcharge par plages de lignes, décalables sans échec de test | Risque d'extrait faux en silence | Texte témoin par référence, vérifié par un test |
| Exécution directe de `phpunit` une fois par l'orchestrateur ; test de `DataSets` commité avec son code | Écart aux règles du projet | Rejoué par `bin/run-tests` ; action 4 |
| Commande de fond de merge en échec local (fichiers des spikes non commités au moment du `checkout`) | Merge distant fait, retour local à refaire | Branche recréée depuis `origin/main` |
| Refus d'outil : suppression d'un cache par wB | Non contourné | Nettoyage par `bin/clean-test-caches` |

## Métriques

| Métrique | Sprint 24 | Sprint 23 |
|----------|-----------|-----------|
| Points engagés / en done | 19 / 19 | 11 / 11 |
| Stories en done avec un scénario non livré | 0 | 0 |
| Stories de code avec commit rouge | 5 sur 5 | 3 sur 3 |
| Tâches à la clôture | 69 ✅, 1 🚫 justifiée, 0 🔲 | 40 ✅ |
| Fichiers de tests de la démo (preuve publiée) | 219 | 203 |
| Plantage de la machine | 0 | 0 |
| Espace libre minimal | 4,0 Go | 6,0 Go |
| Contournements de refus d'outil | 0 (3 refus) | 0 (1 refus) |
| Builds de `main` en échec à l'étape de la preuve | 0 | sans objet |

## Démonstration (ordre suggéré)

1. `/fr_FR/compatibilite` : la matrice, puis le graphe (trade-credit requiert order-hold, suggère facturx).
2. `/fr_FR/surcharge` : un cas, son lien vers les lignes exactes, sa commande de vérification.
3. `/fr_FR/architecture` sur téléphone : le schéma qui défile.
4. Packagist : `composer require tmonier/sylius-pack-ai-headless` et le README des gestes restants.
5. La PR #76 : PHPUnit 13 sans aucune erreur PHPStan ignorée.

## Impact sur le backlog

| Élément | Statut |
|---------|--------|
| US-176 (installation réelle d'un pack en CI), US-177 (recettes Flex), US-178 (page « Installer un pack ») | backlog, candidats du sprint 25 |
| ADR 0016 et 0017 (facture électronique, ERP) | proposés, à trancher au planning du sprint 25 |

## Prochaines étapes

1. Thibaut valide la revue, la rétrospective et la clôture du sprint 24.
2. Planning du sprint 25.
