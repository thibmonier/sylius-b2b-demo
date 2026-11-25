# Revue du sprint 57 — Sondes d'environnement et sécurité de l'écran de santé

**Date** : 2026-11-25 (sprint exécuté du 2026-11-22 au 2026-11-25 ; créneau prévisionnel 2026-12-10 → 2026-12-24)
**Objectif** : la page de santé de la plateforme affiche ses premières vraies sondes, celles de l'environnement, derrière un rôle dédié, sans valeur sensible à l'écran et lisible sans la couleur, et le plugin entre dans la démo.

**Objectif atteint.** 17 points livrés sur 17 engagés (capacité 15, 113 %), les cinq stories sont « done », toutes les PR sont fusionnées par Thibaut. Trois versions du plugin publiées (v0.2.0, v0.2.1, v0.3.0). Sur la démo déployée, la page et l'encart du tableau de bord montrent un constat réel : Symfony 8.1.11 atteint sa fin de support le 2027-01-31 (sonde en avertissement). Le point de contrôle du 2026-12-16 est sans objet : US-293 et US-290 ont été fusionnées le 2026-11-23, US-289 est maintenue et livrée.

## Stories

| Story | Points | PR | Rouge prouvé en CI | Statut |
|-------|--------|----|--------------------|--------|
| US-293 sondes d'environnement | 5 | [health #6](https://github.com/thibmonier/sylius-platform-health-plugin/pull/6) | run 45124873228 : unitaire 45 échecs, non unitaire 4 ; vert run 45127022540 | **Done** |
| US-290 rôle dédié, masquage | 3 | [health #7](https://github.com/thibmonier/sylius-platform-health-plugin/pull/7) | run 45128277404 (`15c7784`) ; vert run 45129919746 | **Done** |
| US-291 accessibilité AA, mode sombre | 3 | [health #8](https://github.com/thibmonier/sylius-platform-health-plugin/pull/8) | run 45140922021 (`ba7efc4`, contrastes) ; vert sur `0221bda` | **Done** ; passe VoiceOver de Thibaut OK le 2026-11-25 |
| US-289 encart du tableau de bord | 3 | [health #12](https://github.com/thibmonier/sylius-platform-health-plugin/pull/12), #13 (v0.3.0), #14 (captures) ; démo [#218](https://github.com/thibmonier/sylius-b2b-demo/pull/218) | runs 45369525576 et 45369516082 (`4e57d2b`) : unitaire 1 erreur + 2 échecs, non unitaire 12 échecs ; vert run 45371335152 | **Done** |
| US-292 traductions, README, démo | 3 | [health #10](https://github.com/thibmonier/sylius-platform-health-plugin/pull/10), #11 (v0.2.1) ; démo [#212](https://github.com/thibmonier/sylius-b2b-demo/pull/212), #216, [#221](https://github.com/thibmonier/sylius-b2b-demo/pull/221) | health : run 45145963900 (`1c8de77`) ; démo : run 45150666701 (`7189940`, rôle non accordé : 6 échecs d'assertion) | **Done** |

**Livré : 17/17 points done.** Vélocité : 17 (sprint 56 : 13).

## Démonstration (démo déployée, compte Sophie)

1. **Page « Santé de la plateforme »** (Configuration) : une carte « Environnement » en avertissement, six contrôles dépliables (versions PHP, Symfony et Sylius avec leur fin de support et la version de table, extensions, OPcache, mode debug). Lien « Corriger » sur la seule sonde en avertissement. Captures FR/EN, clair/sombre, à 1280 px : `docs/images/platform-health-*-1280.png`.
2. **Encart du tableau de bord** : « Santé de la plateforme », une ligne par catégorie avec son statut écrit, lien « Voir le détail » ; rendu différé, le tableau de bord n'attend aucune sonde. Captures dans le README du plugin.
3. **Rôle dédié** : la page et l'encart ne s'affichent qu'avec `ROLE_PLATFORM_HEALTH_VIEW` (403 sinon) ; rôle accordé aux comptes publics par les fixtures, et à Sophie en production par `sylius:user:promote` le 2026-11-24 (la fixture ne joue qu'au reset de 03:00).
4. **Accessibilité** : statut jamais porté par la seule couleur, contraste mini 6,41:1 (clair) et 6,18:1 (sombre) ; arbre d'accessibilité contrôlé sur la démo, VoiceOver validé par Thibaut.

## Tâches techniques

| Tâche | PR | Statut |
|-------|----|--------|
| T-TECH-57-01 planification | [#204](https://github.com/thibmonier/sylius-b2b-demo/pull/204) | Fait |
| T-TECH-57-02 (A-56-4) quatre tests instables | [#205](https://github.com/thibmonier/sylius-b2b-demo/pull/205), rouge run 45046913965 | Fait ; 5 passages verts dont un sous `LC_ALL=fr_FR.UTF-8` ; `CheckDiskTest` révélait un vrai bug de `bin/check-disk` |
| T-TECH-57-03 (A-56-1) suite non unitaire après échec unitaire | [health #5](https://github.com/thibmonier/sylius-platform-health-plugin/pull/5), preuve run 45041887301 | Fait |
| T-TECH-57-04 règles 26 à 29 | consigne des workers | Fait |
| T-TECH-57-05 Dependabot #188 | | Fait (#188 fermée, remplacée par #201 puis #211) |
| T-TECH-57-06 veille des semaines 47 et 48 | #206, #207, #211, #213, #215 fusionnées ; #208, #209, #210 fermées | Fait (non planifiée, signalée par Thibaut) |
| T-TECH-57-07 imprévus du 2026-11-25 | [#217](https://github.com/thibmonier/sylius-b2b-demo/pull/217) (16 avis Twig et Symfony UX), [#219](https://github.com/thibmonier/sylius-b2b-demo/pull/219) (couverture gardée, marquée périmée), [#220](https://github.com/thibmonier/sylius-b2b-demo/pull/220) (versions lues dans `repo.packagist.org/p2`) | Fait (non planifiée) |

## Écarts à signaler

1. **Tag v0.2.0 posé avant les traductions et le README (T-292-01/02)** : une v0.2.1 de documentation a suivi le lendemain, et la démo est passée par ^0.2 puis ^0.2.1.
2. **#220 : premier commit rouge arrêté à PHPStan** (run 45599147837, 9 erreurs masquées par un `| tail` local), contre la règle 26 ; corrigé par un commit de tests seul, rouge PHPUnit run 45599572188, puis vert run 45602751787.
3. **`proof.json` dépendait d'un cache CDN de 12 h et perdait la couverture après une release de documentation** : corrigés par #219 et #220, plus d'attente d'une demi-journée.
4. **Avis de sécurité révélés par la CI de #216** (Twig, Symfony UX) : #217 fusionnée avant, conflit de `composer.lock` résolu par fusion de `main` et nouvelle mise à jour ciblée (pas de force-push).
5. **Outils hors Docker, justifiés** : `bin/proof-refresh`, `bin/compat-refresh` et `bin/proof-check` en PHP local (besoin de `gh`) ; T-TECH-57-02 : outils de qualité hors Docker (pas de service PHP dans le compose de la démo).
6. **Écritures en production, sur GO de Thibaut** : rôle accordé à Sophie (2026-11-24) ; langue de Sophie passée en `en_US` le temps des captures anglaises, par une requête SQL sur `locale_code` seule, puis remise en `fr_FR` et vérifiée (2026-11-25).
7. **Captures prises par Claude in Chrome** : cadre de 1232 px pour une fenêtre de 1280 px, images enregistrées par zoom (1456 à 1568 px de large), non pas en PNG pleine page comme celles du README de la v0.2.0.
8. **Constats hors plugin** : pas de landmark `main` autour du H1 de l'admin Sylius ; les H3 de Sylius se rangent sous le H2 de l'encart ; « Average order value » sur deux lignes à 1280 px en anglais.

## Hors sprint

- Railway : `mailpit` ≈ −45 % après `MP_MAX_MESSAGES` (une seule journée, à confirmer le 2026-12-01) ; les autres projets de l'espace de travail coûtent 4,21 $, plus que prévu, à auditer.
- bear-craft mis à jour en 10.1.2.
- Accès `railway ssh` configuré (clé dans l'agent, empreinte de l'hôte acceptée) ; commandes console en `www-data`.

## Reste à faire (actions humaines)

1. Fusionner la PR des documents du sprint 57 (revue, rétro, clôture).
2. 2026-12-01 : relevé Railway par service (`mailpit` sur une semaine entière) et audit des autres projets.
3. Planifier le sprint 58 : candidates du backlog US-294 (sondes de données), US-295 (files et cache), US-296 (battement des tâches planifiées), US-303 (sondes facultatives des plugins), 20 points pour une capacité de 15 ; US-313 (PHP 8.6) reste au backlog.
