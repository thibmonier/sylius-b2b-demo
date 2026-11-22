# Revue du sprint 56 — Santé de la plateforme : le contrat de sonde et le premier écran

**Date** : 2026-11-22 (sprint planifié et exécuté le même jour, fin prévue le 2026-12-10)
**Objectif** : Un administrateur ouvre une page et voit, catégorie par catégorie, l'état de sa plateforme ; un intégrateur y ajoute sa propre sonde en quinze lignes, sans configuration et sans pouvoir casser l'administration.

**Objectif atteint côté code, sous réserve des fusions par Thibaut.** 13 points livrés sur 13 engagés, mais **seule US-286 est « done »** (3 points). US-287 et US-288 (10 points) sont en **review** : PR brouillon, CI verte, **non fusionnées**. Aucune sonde réelle n'est livrée, comme prévu : le plugin n'embarque que le contrat, l'exécuteur et la page.

## Stories

| Story | Points | PR | Rouge prouvé en CI | Vert | Statut |
|-------|--------|----|--------------------|------|--------|
| US-286 spike | 3 | [note de cadrage](../../analysis/2026-11-22-spike-sante-plateforme.md), démo [#197](https://github.com/thibmonier/sylius-b2b-demo/pull/197) (fusionnée) | sans objet | sans objet | **Done** : GO de Thibaut le 2026-11-22 sur la conclusion « écrire » et le nom du paquet ; timebox 7 h confirmée (+1 h pour la question 6) |
| US-287 contrat et exécution à sécurité passive | 5 | [health #1](https://github.com/thibmonier/sylius-platform-health-plugin/pull/1) (brouillon) | `12dffab`, run 45019801042 : échecs d'assertion, **suite unitaire seulement** ; le rouge « identifiant en double » (non unitaire) n'a été vu **qu'en local** | tête `5e9a6f5`, 23 checks verts | **Review** |
| US-288 page d'administration | 5 | [health #2](https://github.com/thibmonier/sylius-platform-health-plugin/pull/2) (brouillon, empilée sur #1) | `a36e445` (non unitaire, run 45023843443) et `e34785a` (unitaire, run 45024118308) : échecs d'assertion sur **les deux suites** | tête `cfb7dd1`, 23 checks verts | **Review** |

**Livré : 13/13 points de code, dont 3 points done et 10 en review (fusion attendue).** Vélocité comptée « done » : 3 points.

## Démonstration

- **US-286** : conclusion « on écrit le contrat, on enveloppe les sources de données ». `liip/monitor-bundle` ne couvre nativement que 2 des 8 exigences d'EPIC-20 ; le rendu différé passe par un Live Component (`loading="defer"`), dépendance déjà présente dans Sylius 2.3. Aucun recoupement confirmé avec Sylius Plus sur un écran, sous réserve de la documentation protégée (non lue).
- **US-288**, page « Santé de la plateforme » sous *Configuration* : captures réelles [français](https://github.com/thibmonier/sylius-platform-health-plugin/blob/feature/us-288-page-admin/docs/screenshots/platform-health-fr.png) et [anglais](https://github.com/thibmonier/sylius-platform-health-plugin/blob/feature/us-288-page-admin/docs/screenshots/platform-health-en.png) (branche `feature/us-288-page-admin`).
- **US-287** : pas d'écran ; le README (EN/FR) montre une sonde maison en quinze lignes. Le test « sonde qui lève une exception » est un critère d'acceptation vert, en réponse à la leçon de la PR #31 du thème.

## Tâches techniques

| Tâche | PR | CI | Statut |
|-------|----|----|--------|
| T-TECH-56-01 | [#197](https://github.com/thibmonier/sylius-b2b-demo/pull/197) | verte | Fait, fusionnée |
| T-TECH-56-02 (#188) | [démo #200](https://github.com/thibmonier/sylius-b2b-demo/pull/200) (brouillon) | tête `4f056d2`, 6 checks verts | **Review**. Rouge vu **en local seulement** (CI arrêtée à PHPStan sur le SHA rouge, run 45021551169) : écart à la règle « rouge valide = échec d'assertion vu en CI » |
| T-TECH-56-03 | | | Annulée (sans objet : #191 et #192 fusionnées le 2026-11-21) |
| T-TECH-56-04 | [thème #32](https://github.com/thibmonier/sylius-industry-trade-theme/pull/32) (brouillon) | tête `26d0957`, 15 checks verts | **Review** |
| T-TECH-56-05 | dépôt ajouté à `plugin-repositories.json` | | **En cours** : `bin/plugin-supply-chain report` après le premier `apply` |
| Débloquage de T-TECH-56-02 | thème [#31](https://github.com/thibmonier/sylius-industry-trade-theme/pull/31) (fusionnée), [#33](https://github.com/thibmonier/sylius-industry-trade-theme/pull/33) (release, fusionnée, tag **v0.3.2**) | 15 checks verts chacune | Fait |

État de la PR #199 (documents du sprint, brouillon) au moment de la revue : tête `b42277d`, mergeable, CI en cours (zizmor, audit des dépendances et GitGuardian verts ; build et tests rapides en cours).

La CI de `main` de la démo était **rouge depuis le 2026-11-20** sur la preuve publiée ; le rafraîchissement de `proof.json` (355 → 357) dans #200 la corrige. Rien n'est vérifié sur `main` tant que #200 n'est pas fusionnée.

## Écarts à signaler

1. **Question de conception ouverte pour Thibaut** : `identifier()` doit être une **constante**, car il est lu sur une instance construite sans constructeur (unicité vérifiée à la compilation). Contrainte à confirmer ou à lever avant que les sondes des sprints 57+ ne la figent.
2. **Dépendances ajoutées** par rapport au squelette : `symfony/translation`, `ux-live-component`, `clock`, `cache-contracts`, `routing`, `yaml`.
3. **Rouges non conformes** : US-287 (doublon d'identifiant vu en local seulement), T-TECH-56-02 (local seulement). Voir le tableau.
4. **Outils de qualité exécutés avec PHP local au lieu de Docker** : T-TECH-56-02 (worker) et, pour US-287, le passage de supply-chain, lancé avec `--allowed-root` et PHP local.
5. **`register.html.twig` conservé dans la démo** (écart à T-TECH-56-02, prévu pour suppression) : des canaux n'ont pas le thème. Justifié, à confirmer.
6. **US-288 : tests et squelettes dans un même commit**, un force-push ayant été refusé.
7. **Deux force-push refusés à l'orchestrateur** ; les conflits ont été résolus par des commits de fusion : #199 après la fusion de #197 (SHA nouveaux), thème #32 après la fusion en squash de #33.
8. **Tests instables relevés** : `CleanTestCachesTest::testAStaleLockDoesNotBlockARealRun` (échec en CI, vert au second passage) ; `CheckDiskTest` échoue sous une locale française (virgule décimale) ; `WebhookDeliveryTest` et `RenderPageTest` dépassent le délai sous charge. À traiter avant l'ajout des sondes, sans quoi l'exécuteur hérite de la fragilité.

## Hors sprint

- **ADR-0021 rouverte** (2026-11-22) : FrankenPHP 1.14 et PHP 8.6 remplissent les conditions ; la démo **reste en PHP 8.5**. [US-313](../../backlog/user-stories/US-313.md) ajoutée au backlog (Should, 5 points). Deux frictions de build connues : `mbstring` absent, `apcu` ne compile pas.
- EPIC-20 à EPIC-23 : 27 stories, 113 points, sprints 56 à 61 proposés.

## Reste à faire (actions humaines)

1. Fusionner dans l'ordre : health **#1** puis **#2** ; démo **#199**, **#200** ; thème **#32**.
2. Ensuite, Dependabot rebase la PR **#188** (la partie thème y est remplacée par #200).
3. **Aucun tag ni Packagist du plugin de santé sans GO de Thibaut.**
4. Railway : `railway config plan` puis `railway config apply` pour `MP_MAX_MESSAGES: "50"` (jamais par Claude) ; 48 h après le déploiement, `railway usage` par service.
5. Trancher la question de l'identifiant constant, le point sur `register.html.twig`, et la disponibilité du nom du paquet sur Packagist (non vérifiable depuis la récupération web).
6. Finir T-TECH-56-05 (rapport de la chaîne d'approvisionnement du nouveau dépôt).
