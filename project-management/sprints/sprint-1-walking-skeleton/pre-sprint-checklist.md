# Checklist de démarrage — Sprint 1

## Prérequis (sprint précédent)

- [x] Pas de sprint précédent — sprint initial
- [x] Backlog priorisé et estimé (phase de planification terminée le 2026-10-15)
- [x] PRD validé (`project-management/prd.md`)
- [x] Spécification technique rédigée (`project-management/tech-spec.md`)
- [x] 10 ADR enregistrés (`docs/adr/`)
- [x] Stories US-001 à US-007 et US-031 au statut `ready-for-dev`

## Environnement technique

- [ ] Accès au compte Packagist (pour soumettre agentic-feeds — US-031)
- [ ] Dépôt `sylius-b2b-demo` créé sur GitHub avec les topics : `ecommerce`, `sylius`, `sylius-plugin`, `symfony`, `b2b-demo`
- [ ] Dépôt public (README minimal présent)
- [ ] PHP 8.4 disponible localement (ou via Docker uniquement)
- [ ] Composer 2.7+ installé
- [ ] Docker Desktop (ou équivalent) opérationnel
- [ ] Node LTS disponible (pour vérification Encore hors Docker)

## Dépôts des plugins à corriger (US-031)

- [ ] Accès en écriture sur `thibmonier/sylius-punchout-plugin`
- [ ] Accès en écriture sur `thibmonier/sylius-agentic-feeds-plugin`

## Sprint Planning Part 1 — Quoi

- [x] Sprint Goal défini : « Walking skeleton : Sylius 2.3 + 21 plugins dans l'image FrankenPHP, environnement local, tâches planifiées, CI, canaux B2B/B2C, correctifs des plugins publiés »
- [x] 7 stories sélectionnées, 26 points
- [x] Dépendances inter-stories identifiées
- [x] Ordre de réalisation recommandé établi

## Sprint Planning Part 2 — Comment

- [x] Décomposition en tâches effectuée (voir `tasks/`)
- [x] Estimations posées (0,5 h à 4 h par tâche)
- [x] Graphe de dépendances Mermaid créé (`task-board.md`)
- [x] Task Board initialisé (`task-board.md`)

## Critères de fin de sprint (Definition of Done global)

- [ ] Les 26 points sont livrés
- [ ] `docker compose up` lance l'application en local
- [ ] Page d'accueil répond 200 en HTTPS local
- [ ] `/healthz` répond 200
- [ ] 21 plugins enregistrés, `lint:container` vert
- [ ] `doctrine:migrations:migrate` sans migration en attente
- [ ] Canaux B2B et B2C configurés avec fixtures
- [ ] supercronic visible dans les logs du conteneur
- [ ] CI GitHub Actions verte sur main (build + fumée)
- [ ] README punchout corrigé, patch release taguée
- [ ] agentic-feeds disponible sur Packagist
- [ ] ECS et PHPStan passent sur le code de la démo
- [ ] Aucun secret dans le dépôt

## Outils et ressources

| Ressource | URL / Commande |
|-----------|---------------|
| Sylius-Standard 2.3 | `composer create-project sylius/sylius-standard:~2.3` |
| dunglas/frankenphp | `docker pull dunglas/frankenphp:1-php8.4-bookworm` |
| wkhtmltox 0.12.6.1 | https://github.com/wkhtmltopdf/packaging/releases/tag/0.12.6.1-3 |
| supercronic | https://github.com/aptible/supercronic/releases |
| Mailpit | `docker pull axllent/mailpit` |
| MySQL 8.4 | `docker pull mysql:8.4` |
| Tech-spec | `project-management/tech-spec.md` §2, §4 |
| ADR socle | `docs/adr/0001-socle-sylius-standard-et-plugins-packagist.md` |
| ADR conteneur | `docs/adr/0002-frankenphp-et-supercronic-dans-un-seul-conteneur.md` |
