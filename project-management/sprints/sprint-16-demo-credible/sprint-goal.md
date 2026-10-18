# Sprint 16 : Démo crédible (images persistantes, appel à l'action, mesure d'audience, catalogue et données vivantes)

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 16 |
| Début | 2027-01-22 (revue et rétro du sprint 15 ; démarrage effectif le 2027-01-25) |
| Fin | 2027-02-05 |
| Durée | 10 jours ouvrés (2027-01-25 au 2027-02-05) |
| Capacité | 27 points engagés (décision D1), 57 h décomposées dont 2 h calendaires (T-145-08, T-142-09, T-143-08, T-092-10 : 0,5 h chacune, actions de Thibaut), soit 55 h de charge, contre 73,5 h au sprint 15 (35 points tenus avec trois développeurs en parallèle) |
| Branche | `feature/sprint-16-demo-credible` |

## Sprint Goal

> « Rendre la démo crédible avant de la montrer : images produit persistantes sur Railway, photos cohérentes avec les produits et crédits visibles, données vivantes (panier, historique, approbation, devis, crédit, journal d'audit), appel à l'action « Me contacter » partout et mesure d'audience sans cookie. »

## Sprint Backlog

| ID | Titre | Points | MoSCoW | Heures | Statut |
|----|-------|--------|--------|--------|--------|
| US-145 | Images produit persistantes sur Railway | 3 | Must | 7 h (dont 0,5 h de Thibaut) | ready-for-dev |
| US-142 | Appel à l'action « Me contacter » mis en avant dans toute la démo | 5 | Must | 10,5 h (dont 0,5 h de Thibaut) | ready-for-dev |
| US-143 | Mesure d'audience de la démo avec Umami | 3 | Must | 7 h (dont 0,5 h de Thibaut) | ready-for-dev |
| US-092 | Catalogue crédible, tranche 1 : photos cohérentes avec les produits | 5 | Must | 10,5 h (dont 0,5 h de Thibaut) | ready-for-dev |
| US-096 | Données vivantes (1/2) : panier de départ, historique et approbation en attente | 5 | Must | 10 h | ready-for-dev |
| US-097 | Données vivantes (2/2) : devis, échéances de crédit, brouillons, suggestions IA et journal d'audit | 5 | Must | 10 h | ready-for-dev |
| US-061 | Crédits des images de démo | 1 | Should | 2 h | ready-for-dev |

Total : 27 points, 58 tâches, 57 h. Écart avec l'estimation de cadrage (≈ 34 points) : les sept stories décidées par Thibaut totalisent 27 points ; les 7 points manquants ne correspondent à aucune story décidée et ne sont pas comblés (YAGNI) : la marge est un tampon de capacité, signalée à Thibaut.

Ordre de sacrifice (détail et charges restantes dans [tasks/README.md](./tasks/README.md)) : US-061, puis les tâches de finition ; les Must ne sont pas sacrifiables (US-145 en tête).

## Décisions de Thibaut (2026-10-18)

- **D1** : périmètre « démo d'abord », 27 points (≈ 34 annoncés au cadrage). US-093 (environ 150 références, 8 points), US-116, US-118, US-122 (communication) et US-126 (site) sont décalées (US-116, US-118, US-122 au sprint 17 ; US-126 au sprint 18 ; US-093 au sprint 19, report en cascade documenté dans la [feuille de route](../../backlog/roadmap-ouverture-et-communication.md)). Ordre de sacrifice : US-061 d'abord, puis les tâches de finition ; les Must ne sont pas sacrifiables, US-145 en tête. Toute tâche sacrifiée devient une dette datée dans la revue.
- **D2** (US-145) : volume Railway monté sur le dossier des médias (`public/media`, chemin exact des images Sylius et du cache LiipImagine vérifié par le spike, droits `www-data` au premier démarrage), déclaré dans l'IaC `.railway/railway.ts` (SDK `railway` 3.16.0). `railway config plan` / `apply` sont lancés par Thibaut. Gestion du premier démarrage (volume vide, la remise à zéro le remplit) et purge des images orphelines. Critère : deux redéploiements successifs sans remise à zéro gardent les images.
- **D3** (US-142, US-143) : les URL (Calendly, formulaire de contact, script Umami, identifiant du site Umami de thibaut-monier.fr) ne sont pas encore fournies. Développement avec des **paramètres vides** (composants masqués, aucune requête vers Umami, CSP inchangée) et des **valeurs factices** dans les tests. La pose des valeurs dans Railway est une action de Thibaut (T-142-09, T-143-08).
- **D4** : rappels. `main` est protégée : PR et contrôles requis, attendre la fin du run CI avant le merge. Tests via `bin/run-tests` seulement (jamais `phpunit` en direct, `MIN_FREE_GB` jamais abaissé, `bin/clean-test-caches` avant un lot de tests). Seul l'orchestrateur reconstruit l'image Docker. Rendu réel vérifié (`bin/measure-mobile`, pa11y AAA). Aucun détail exploitable dans les documents (dépôt public). Rapports de fin ≤ 100 mots, placés en premier.
- **Actions de la rétro du sprint 15** reprises ([sprint-retro.md](../sprint-15-dossier-de-go/sprint-retro.md)) : (1) volume des médias = US-145 ; (2) rapport de fin ≤ 100 mots dans le gabarit de brief, écrit avant la dernière vérification ; (3) règle de gel disque sans contournement ; (4) relancer un test après mise à jour du dépôt avant de signaler un échec partagé. Détail dans [tasks/README.md](./tasks/README.md).
- **Journal d'audit** : vide depuis le sprint 14 (relevé au sprint 14). Quelques événements métier sont ajoutés par US-097 (T-097-06) ; le masquage du reset et le journal lisible restent à US-088 (sprint 19).

## Choix du Tech Lead

- **Ordre d'exécution** : trois pistes parallèles qui ne partagent pas de fichiers. (1) Images : US-145 (infra) puis US-061 → US-092 ; (2) thème : US-142 → US-143 ; (3) fixtures : US-096 → US-097. US-145 d'abord et en priorité (prérequis bloquant du dossier de GO).
- **US-145** : le spike (T-145-01) tranche la déclaration du volume dans le SDK `railway` 3.16.0 et le chemin exact avant d'écrire du code ; l'image Docker n'est reconstruite que par l'orchestrateur.
- **US-142 et US-143** : un composant partagé unique ; les événements Umami (`data-umami-event`) sont posés sur les liens du composant par US-143 après US-142. URL vides par défaut, tests avec valeurs factices.
- **US-096 et US-097** : mêmes fichiers de fixtures et même commande de remise à zéro : un seul worker à la fois ; la durée du reset est mesurée (`bin/profile-reset`, < 10 minutes).
- **US-092 et US-061** : la page Crédits (US-061) est générée depuis le manifeste ; US-092 la réutilise (T-092-06). Aucun appel externe à l'exécution, photos CC0 seulement.

## Dépendances

- US-145 avant le dossier de GO final (prérequis bloquant, ligne 14) ; ses fichiers (`.railway/railway.ts`, `docker/Dockerfile`, `docs/deploiement-railway.md`) ne sont partagés qu'avec US-142 et US-143 (variables) : US-145 d'abord.
- US-061 (page Crédits) avant US-092 (T-092-06).
- US-142 avant US-143 (événements des appels à l'action).
- US-096 avant US-097 (fixtures et reset).
- US-092 (images) après US-145 pour la vérification de persistance sur la démo.

## Risques

| Risque | Mitigation |
|--------|------------|
| Déclaration du volume non prise en charge par le SDK 3.16.0 | Spike T-145-01 avant tout code ; repli documenté (volume créé par Thibaut, procédure) |
| Volume monté en `root` : droits `www-data` perdus, démarrage cassé | Test du premier démarrage (T-145-02), correction des droits à l'entrée du conteneur |
| Reset trop long avec plus de fixtures | `bin/profile-reset` en fin de US-096 et US-097 ; objectif < 10 minutes |
| Photos CC0 introuvables pour certains produits | Visuel neutre de catégorie plutôt qu'une photo trompeuse (T-092-04) |
| URL de contact, Calendly et Umami non fournies | Paramètres vides, composants masqués, valeurs factices en test (D3) |
| CSP trop large ou cassée par Umami | Hôtes ajoutés seulement quand les paramètres sont posés ; console vérifiée |
| Disque saturé (rétro S15) | `df -h .` au démarrage, `bin/clean-test-caches` avant chaque lot, seuil jamais abaissé |
| Détail exploitable dans un document public (US-145) | Conclusions seulement, relecture dédiée en revue |
| Marge de 7 points non utilisée | Tampon de capacité ; aucune story ajoutée sans décision de Thibaut |

## Contraintes

Aucune action externe sans GO : pas de push, de PR, de merge ni de tag sans confirmation explicite. Aucune commande Railway par Claude (T-145-08 et la pose des variables sont à Thibaut). Pas de lecture de `.env` ni de secrets. Aucun commit sans demande. `git add` par chemins explicites. Chaque worker rend le bloc de rapport de fin (≤ 100 mots, [tasks/README.md](./tasks/README.md)).

## Definition of Done (rappel)

- [ ] Critères d'acceptation automatisés et verts
- [ ] ECS (`src tests`) et PHPStan verts
- [ ] Libellés FR et EN
- [ ] Rendu réel des pages touchées vérifié (pas seulement la configuration), `bin/measure-mobile`, pa11y WCAG2AAA
- [ ] Aucun secret ni détail exploitable dans le dépôt
- [ ] Documentation à jour
- [ ] CI verte (« Quality and smoke tests », « Docker image »), run terminé avant le merge

## Checklist pré-sprint

- [ ] Sprint 15 : statut `done`, revue et rétrospective tenues (`sprint-review.md`, `sprint-retro.md`)
- [ ] Branche `feature/sprint-16-demo-credible` active ; `git fetch` et branche vérifiés
- [ ] Base de test : `bin/prepare-test-db` (voir `docs/tests-locaux.md`) ; Docker PHP 8.4
- [ ] Puppeteer et Chromium disponibles pour `bin/measure-mobile` (variables d'environnement documentées par T-141-04)
- [ ] `df -h .` : au moins 5 Go libres ; `bin/clean-test-caches` lancé
- [ ] D1 à D4 lues par l'équipe ; ordre de sacrifice accepté
- [ ] Actions de Thibaut identifiées : T-145-08, T-142-09, T-143-08, T-092-10

## Cérémonies

| Cérémonie | Date | Durée |
|-----------|------|-------|
| Sprint Planning Part 1 (Quoi) | 2027-01-25 | 1 h |
| Sprint Planning Part 2 (Comment) | 2027-01-25 | 1 h |
| Daily Scrum | Chaque jour ouvré | 15 min |
| Point d'étape (sacrifices) | 2027-01-29 | 30 min |
| Affinage Backlog (sprint 17) | 2027-02-01 | 1 h |
| Sprint Review | 2027-02-05 (matin) | 1 h |
| Rétrospective | 2027-02-05 (après-midi) | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
