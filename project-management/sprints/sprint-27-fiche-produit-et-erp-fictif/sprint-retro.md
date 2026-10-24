# Rétrospective - Sprint 27

## Informations

| Attribut | Valeur |
|----------|--------|
| Date | 2026-10-24, préparée en fin de sprint |
| Durée | 1 h |
| Format | Starfish, comme aux sprints 21 à 26, pour comparer les indicateurs |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), développeurs Claude (orchestrateur et workers w189, w193, w188, w185) |

## Directive Fondamentale

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth

## Rappel du sprint

Le Sprint Goal est atteint : **13 points livrés** (12 engagés au planning, plus US-194). Vélocité : S23 11, S24 19, S25 17, S26 29, **S27 13** (moyenne des quatre précédents : 19). Le sprint était volontairement court en points parce que les cinq actions de la rétro du sprint 26 pesaient 8 h de tâches transverses et que la fiche produit passait par une maquette.

Les écarts du sprint :
- un défaut visible seulement en production : l'échange ERP disparaissait à chaque déploiement (#100) ;
- l'espace libre tombé à 3-4 Go, et un worker qui a continué à générer des rendus malgré le seuil ;
- des conflits de `proof.json` entre PR parallèles ;
- des commits du correctif #100 libellés « US-195 » par erreur.

Source : [sprint-review.md](./sprint-review.md).

## Observations (Starfish)

### Continuer

- La maquette avant le code (Claude Design, validée par Thibaut), puis l'inventaire des vraies données avant le style : c'est lui qui a montré que les noms de 61 caractères et les sections vides exigeaient une fiche qui se masque proprement.
- Les décisions de Thibaut prises au fil de l'eau : US-194, US-195 et US-196, resserrage de la fiche après les captures.
- Une PR par story, fusionnées en série, avec le commit de tests rouges cité.
- Le contrôle de production systématique : il a trouvé le défaut de #100, comme il avait trouvé `/demo/accounts` au sprint 26.
- Le rendu du noyau en mode prod (`bin/render-page`, `bin/local-server`) pour les captures : plus de HTML jetable.

### Commencer

- Se demander, pour tout ce qui écrit sur disque, « que devient cet état après un déploiement ? » (conteneur neuf, base qui survit).
- Vérifier le numéro de story dans chaque commit contre la branche et la PR.
- Remesurer le LCP de la home à chaque sprint qui touche le thème.

### Arrêter

- Générer des rendus en série sans nettoyer ni vérifier l'espace libre entre deux.
- Résoudre à la main les conflits de `proof.json`.

### Plus de

- Contrôles de production le jour du déploiement, avec le résultat consigné dans le fichier de tâches.

### Moins de

- Dépendance au nettoyage manuel du disque par Thibaut (les `rm` des workers sont refusés).

## Thèmes et analyse

### Thème 1 : l'état écrit sur disque ne survit pas au déploiement (priorité haute)

`var/erp-exchange` est dans le système de fichiers du conteneur, neuf à chaque déploiement, alors que la base survit. L'entrypoint ne recharge pas les fixtures quand le marqueur est présent, donc `demo_erp_export` ne tournait qu'à la remise à zéro de 03:00, et le journal d'export du plugin, en base, marquait les événements comme déjà exportés. Les tests passaient (le répertoire est sous le cache du noyau) ; seul le contrôle de production l'a vu : page « extrait non disponible » après chaque déploiement. Corrigé par #100, avec un test rouge seul (`fb1b346`). Rien dans la checklist ne posait la question.

### Thème 2 : disque (priorité haute)

La cible de 5 Go n'est tenue sur aucun des trois derniers sprints (3,9 Go, 2-3 Go, 3-4 Go). Causes de ce sprint : 12 caches de rendu de 150 Mo d'un worker, puis des applications ouvertes ; un worker a poursuivi ses rendus malgré le seuil, parce que `bin/render-page` ne consulte pas `bin/check-disk`. Les `rm` des workers sont refusés : c'est Thibaut qui nettoie.

### Thème 3 : PR parallèles et `proof.json` (priorité moyenne)

Chaque PR rafraîchit `config/demo/proof.json` : deux PR ouvertes en même temps entrent en conflit. La parade du sprint (rebase et régénération scriptés, fusion en série) a fonctionné mais reste un geste manuel de l'orchestrateur.

### Thème 4 : numéros de story dans les commits (priorité basse)

Les commits `88b26a3` et `881be80` de #100 portent « US-195 » (sur la branche partagée) alors que la PR s'intitule correctement US-193/US-188. Effet limité (squash merge, la PR et le message de fusion sont exacts), mais l'historique de la branche trompe.

### Thème 5 : LCP de la home (priorité moyenne)

1576 ms contre 1324 ms au sprint 26 (+19 %), sous le seuil de 2,5 s, mesuré avec le protocole de w186. Les fiches, elles, n'ont pas régressé (1408 à 1444 ms). La cause n'est pas identifiée.

## Suivi des actions de la rétrospective du sprint 26

| # | Action | Résultat au sprint 27 |
|---|--------|------------------------|
| 1 | Commits limités aux chemins nommés | 🟡 Règle 13 écrite (T-TECH-27-01) et appliquée ; aucun commit embarquant des fichiers d'un autre worker relevé dans les PR consultées, mais deux commits portent un mauvais numéro de story (thème 4) |
| 2 | Contrôle complet avant de rendre la main | ✅ Atteinte : 0 CI rouge après des contrôles locaux verts sur les runs de la période ; compteurs en dur supprimés et gardés (#101) |
| 3 | Seuils disque et mémoire | 🟡 `bin/check-disk --full` refuse sous 6 Go (fait, testé), mais DoD non atteinte : espace libre minimal de 3 à 4 Go pour 5 Go visés, et un worker a poursuivi ses rendus malgré le seuil |
| 4 | Rendu local en mode prod | ✅ Atteinte : cause trouvée (`variables_order = GPCS`), `bin/local-server` et `bin/render-page`, captures de la fiche sur le rendu du noyau |
| 5 | Mesures de production consignées le jour du déploiement | 🟡 0 tâche de contrôle ouverte à la clôture et 0 page à défilement horizontal parmi celles contrôlées, LCP de la home remesuré ; mais US-194 sans mesure propre et page d'administration non vérifiée en production |

## Bilan des indicateurs

| Indicateur | Cible | Sprint 27 | Sprint 26 |
|------------|-------|-----------|-----------|
| Plantages de la machine | 0 | 0 (aucun relevé) | 0 |
| Espace libre minimal | 5 Go | **3 à 4 Go** | 2 à 3 Go |
| Suite complète lancée sous le seuil | 0 | 0 relevé, mais rendus poursuivis sous le seuil par un worker | 1 |
| CI rouges après tests ciblés verts | 0 | 0 | 3 |
| Commit embarquant les fichiers d'un autre worker | 0 | 0 relevé | 1 |
| Tâches de contrôle en production ouvertes à la clôture | 0 | 0 | 6 |
| Page de la démo à défilement horizontal à 360 px | 0 | 0 parmi les pages contrôlées | 1 |
| Défauts trouvés en production après merge | 0 | 1 (#100, corrigé le jour même) | 4 |
| Push, tag ou dépôt sans confirmation | 0 | 0 | 0 |

## Actions (SMART)

### Action 1 : Un seul cache de rendu par worker, et le seuil disque aussi pour les rendus
| Attribut | Valeur |
|----------|--------|
| Description | `bin/render-page` réutilise un seul cache de rendu par worker (au lieu d'en créer un par rendu) et appelle `bin/check-disk` avant chaque rendu ; `bin/clean-test-caches --all` supprime aussi les caches de rendu, pour que le nettoyage de fin de lot soit un seul geste que Thibaut peut lancer ; test dans `tests/Unit/Bin`. |
| Responsable | Orchestrateur (Claude) pour les scripts, Thibaut pour le nettoyage et les applications ouvertes |
| Deadline | Avant le premier lot du sprint 28 |
| DoD | Espace libre minimal d'au moins 5 Go au sprint 28 (3 à 4 Go au sprint 27) et aucun rendu lancé sous le seuil. |
| Priorité | Haute |

### Action 2 : Rebase et régénération de `proof.json` en une commande
| Attribut | Valeur |
|----------|--------|
| Description | Une commande (`bin/proof-rebase` ou option de `bin/proof-refresh`) qui rebase la branche sur `origin/main`, régénère `config/demo/proof.json` et commite le résultat ; la règle « PR fusionnées en série » est écrite dans `consigne-worker.md`. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 28 |
| DoD | 0 conflit de `proof.json` résolu à la main au sprint 28. |
| Priorité | Moyenne |

### Action 3 : « Que devient cet état après un déploiement ? » dans la checklist
| Attribut | Valeur |
|----------|--------|
| Description | Ajouter à `checklist-integration.md` et à `consigne-worker.md` : pour tout fichier, répertoire, cache ou état écrit hors base, dire où il vit (conteneur ou volume), ce qui le recrée au démarrage, et le vérifier dans un conteneur neuf (image reconstruite) avant le merge. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 28 |
| DoD | 0 défaut visible seulement en production après un déploiement au sprint 28 (1 au sprint 27) ; chaque story qui écrit sur disque cite sa vérification en conteneur neuf. |
| Priorité | Haute |

### Action 4 : Numéro de story vérifié avant chaque commit
| Attribut | Valeur |
|----------|--------|
| Description | Ajouter à la règle 13 de `consigne-worker.md` : le numéro de story du message de commit est relu contre la branche et le titre de la PR (`git show --stat` plus `git log -1`) avant le push ; pour un correctif hors story, le message cite la PR ou la story d'origine. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Planning du sprint 28 |
| DoD | 0 commit au mauvais numéro de story au sprint 28 (2 au sprint 27). |
| Priorité | Basse |

### Action 5 : LCP de la home sous contrôle
| Attribut | Valeur |
|----------|--------|
| Description | Remesurer le LCP mobile de la home en production au début du sprint 28 (protocole de w186, 5 passes) et, s'il reste au-dessus de 1456 ms (10 % au-dessus de 1324 ms), identifier la cause en comparant les ressources chargées entre le sprint 26 et aujourd'hui ; consigner la mesure dans `sprint-27-fiche-produit-et-erp-fictif/tasks/US-189-tasks.md` ou le fichier de tâches de la première story du sprint 28. US-196 (photos) touche au catalogue : la remesure se refait à sa livraison. |
| Responsable | Orchestrateur (Claude) |
| Deadline | Premier lot du sprint 28 |
| DoD | LCP de la home à 1456 ms au plus, ou cause identifiée et chiffrée dans le fichier de tâches. |
| Priorité | Moyenne |

## Intégration au sprint 28

Les actions 1 à 5 sont à reprendre au planning du sprint 28 avec US-195 (2 points) et US-196 (3 points), candidates décidées par Thibaut le 2026-10-24. US-187 reste au backlog.

## Points positifs à conserver

- 13 points sur 13, sans sacrifice, avec une maquette validée avant le code et un resserrage demandé après les vraies données.
- Un défaut de déploiement trouvé et corrigé le jour même, avec test rouge seul.
- Aucune CI rouge après des contrôles locaux verts, aucune publication sans GO.
- Les décisions de Thibaut (US-194, US-195, US-196) prises au fil de l'eau, sans bloquer le sprint.
