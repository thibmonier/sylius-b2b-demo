# Rétrospective du sprint 57

**Directive fondamentale** : « Quoi que nous découvrions, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »

Entrée principale : [sprint-review.md](./sprint-review.md). 17 points sur 17 « done » en quatre jours (2026-11-22 → 2026-11-25), cinq stories fusionnées, v0.2.0, v0.2.1 et v0.3.0 publiées, et le plugin tourne dans la démo avec un constat réel (fin de support de Symfony 8.1).

Format : ce qui a marché, ce qui a coûté, actions SMART (comme au sprint 56).

## Suivi des actions du sprint 56

| ID | Appliquée ? | Preuve |
|----|-------------|--------|
| A-56-1 (suite non unitaire même si l'unitaire échoue ; rouge lu dans le run du SHA) | Oui, avec une exception | health #5 (run 45041887301). Les cinq stories ont un rouge d'assertion vu en CI, sur les deux suites quand elles sont concernées (US-293 : 45 et 4 échecs ; US-289 : 3 et 12). Exception : #220, premier commit rouge arrêté à PHPStan (run 45599147837), corrigé par un commit de tests seul (run 45599572188). Mesure « zéro rouge vu en local seulement » : tenue |
| A-56-2 (pas d'empilement sans procédure) | Oui | Aucune PR empilée. Seul conflit : `composer.lock` entre #216 et #217, deux PR parallèles ; résolu par fusion de `main` et mise à jour ciblée, sans force-push |
| A-56-3 (outils de qualité par Docker, PHP local justifié) | Oui | Composer, PHPStan, ECS, Rector et PHPUnit des PR par Docker ; PHP local noté et justifié : `bin/proof-refresh`, `bin/compat-refresh`, `bin/proof-check` (besoin de `gh`), T-TECH-57-02 (pas de service PHP dans le compose) |
| A-56-4 (quatre tests instables) | Oui | #205, rouge run 45046913965, 5 passages verts dont un sous `LC_ALL=fr_FR.UTF-8` ; `CheckDiskTest` révélait un vrai bug de `bin/check-disk` |
| A-56-5 (écart signalé tout de suite) | En grande partie | Écarts inscrits dans `.bmad/sprint-status.yaml` avec leur raison (US-290 : pas de commande d'attribution ; US-291 : jetons dans un `<style>` en ligne ; US-289 : échec simulé par une horloge de test ; T-TECH-57-03 : pas de gabarit de plugin, US-314 ; tag v0.2.0 avant T-292-01/02). Pas de registre des décisions distinct : la date d'inscription n'est pas vérifiable écart par écart |

## Ce qui a bien marché

- **Engagement au-dessus de la capacité tenu sans sacrifice** : 17 points pour 15, ordre de sacrifice prévu (US-289 d'abord), point de contrôle rendu sans objet dès le 2026-11-23.
- **Les tests instables corrigés avant la première sonde** : aucune relance de CI « pour voir » pendant le sprint.
- **La démo dit la vérité** : la sonde Symfony est en avertissement en production, pas une valeur de test ; la page sert déjà.
- **Rouge prouvé en CI pour chaque story**, avec un squelette pour que les tests échouent sur des assertions et non sur une classe absente.
- **Les imprévus ont été traités à la racine** : 16 avis de sécurité (#217) ; `proof.json` ne perd plus la couverture après une release de documentation (#219) ni n'attend 12 h le cache de Packagist (#220).
- **Écritures en production cadrées** : chaque fois un GO, la plus petite écriture possible (rôle par `sylius:user:promote`, langue par la seule colonne `locale_code`), retour à l'état initial vérifié.
- **Captures et contrôle d'accessibilité sur la démo déployée** avec Claude in Chrome, puis VoiceOver validé par Thibaut.

## Ce qui a coûté

- **Tag v0.2.0 avant la documentation de la story** : une v0.2.1 de traductions et de README a suivi, et la démo est passée deux fois par une mise à jour de version.
- **Un rouge arrêté à PHPStan (#220)** : la sortie de PHPStan était coupée par un `| tail`, les 9 erreurs ne se sont vues qu'en CI. Règle 26 connue, vérification locale mal lue.
- **Chaîne de la preuve fragile face aux releases** : cache CDN de 12 h de l'API Packagist, couverture absente sur un commit de CHANGELOG (la CI des plugins ignore `*.md`). Deux PR imprévues, une demi-journée d'attente avant la correction.
- **Fixtures et production** : le rôle accordé par les fixtures n'existe en production qu'après le reset de 03:00, d'où une écriture manuelle.
- **Mise en route des outils** : `railway ssh` (clé, empreinte de l'hôte, phrase de passe de la clé) et Claude in Chrome (choix du navigateur Dia, capture refusée au premier essai) ont pris plusieurs allers-retours avec Thibaut.
- **Admin anglaise inaccessible sans écrire en production** : la démo n'a qu'un compte admin, en `fr_FR`.

## Actions (SMART)

| ID | Action | Responsable | Échéance | Mesure |
|----|--------|-------------|----------|--------|
| A-57-1 | Une release de plugin n'est taguée qu'une fois **toutes** les tâches de documentation de la story fusionnées (traductions, README, captures, CHANGELOG) ; la fiche de tâches liste la release comme dernière tâche | orchestrateur | planification du sprint 58 | zéro release de documentation seule au sprint 58 |
| A-57-2 | Avant de pousser un commit rouge : PHPStan et ECS lancés sans filtre de sortie, code de retour lu (`echo $?`), jamais à travers `| tail` ou `| head` ; règle 26 de la consigne complétée en ce sens | orchestrateur, workers | consigne du sprint 58 | zéro run rouge arrêté à PHPStan ou ECS au sprint 58 |
| A-57-3 | Après une release de plugin, `bin/proof-refresh` lancé dans la foulée et `proof.json` relu (version et couverture) avant la PR de la démo | orchestrateur | à chaque release du sprint 58 | aucune PR de la démo avec une version ou une couverture périmée dans `proof.json` |
| A-57-4 | Relevé Railway par service et audit des autres projets de l'espace de travail (4,21 $ à expliquer) | Thibaut | 2026-12-01 | relevé inscrit dans `sprint-goal.md` du sprint en cours, décision sur chaque projet |

## Proposition au backlog (non créée)

- Un second compte admin de démo en `en_US`, créé par les fixtures, pour les captures et la démonstration en anglais sans écriture en production. À arbitrer par Thibaut à la planification du sprint 58.

## ROTI

Non mesuré : rétrospective rédigée par l'orchestrateur, sans tour de table.
