# Sprint 13 : Vitrine des plugins (README bilingues, captures, charte Sylius Plus, sous-domaine)

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 13 |
| Début | 2026-12-11 |
| Fin | 2026-12-25 |
| Durée | 10 jours ouvrés (le 2026-12-25 est férié : revue et rétrospective ont lieu le 2026-12-24) |
| Capacité | 15 points, environ 30-35 h (37,5 h décomposées, dont 2,5 h calendaires ; 2,5 h sacrifiables, plus une coupe franche de 2 h) |
| Branche | `feature/sprint-13-vitrine-des-plugins` |

## Sprint Goal

> « Préparer les 24 plugins à l'ouverture publique : les classer par rapport à Sylius Plus et fixer un ton neutre, les doter d'un README français, d'une référence à la démo B2B et de vraies captures d'écran, et préparer le sous-domaine de la démo, sans publier le site ni les liens de contact. »

## Sprint Backlog

| ID | Titre | Points | Heures | Statut |
|----|-------|--------|--------|--------|
| US-063 | Classement Sylius Plus des 24 plugins et charte de ton | 2 | 6,5 h | ready-for-dev |
| US-064 | Bloc standard, outil de contrôle et README FR du lot A (12 plugins) | 4 | 9,5 h | ready-for-dev |
| US-067 | README FR du lot B (12 plugins) et livraison des 24 dépôts | 3 | 8 h (dont 1,5 h calendaire) | ready-for-dev |
| US-065 | Captures d'écran manquantes et vérification des existantes | 4 | 8,5 h | ready-for-dev |
| US-066 | Préparation du sous-domaine de la démo | 2 | 5 h (dont 1 h calendaire) | ready-for-dev |

Report : US-052 (ouverture publique) reste `in-progress`, `carried-over` vers ce sprint ; ses tâches [OPS] (T-052-03 à T-052-07) restent celles de l'utilisateur et ne comptent pas dans la capacité.

Engagement : 15 points, 34 tâches, 37,5 h décomposées (35 h de charge hors calendaires) pour ≈ 30-35 h de capacité. Le sprint tient à 30,5 h après les sacrifices n° 1 à 4 de [tasks/README.md](./tasks/README.md).

## Décisions de l'utilisateur (2026-10-17)

- **D1** : on publie sans le site. Première passe (ce sprint) : README FR, images, référence à la démo. Les liens vers le site et la page contact sont une seconde passe (US-068, backlog), quand l'URL est connue. La page Plugins de la démo renvoie vers le site plus tard (US-069, backlog).
- **D2** : Sylius Plus. Tout est listé, ton neutre. Les plugins qui recoupent Plus sont présentés sobrement : pas de comparaison avec Plus, pas de « alternative gratuite ». Rien n'est publié sur les canaux Sylius. Il faut d'abord classer chaque plugin (concurrent, complémentaire, hors périmètre) à partir de la documentation officielle et des pages publiques de Plus (US-063).
- **D3** : images = vraies captures (boutique et admin) prises depuis la démo locale (`docker compose`), placées dans `docs/images/` de chaque dépôt ; depuis l'application de test pour un plugin absent de la démo. Plugins purement techniques sans image, liste justifiée.
- **D4** : sous-domaine. L'utilisateur crée le CNAME et le domaine personnalisé dans Railway ; Claude prépare `TRUSTED_HOSTS`, le runbook et les références, puis vérifie le certificat. Préfixe du sous-domaine : `sylius-b2b` (voir D5).
- **Lien vers la démo tant qu'elle est privée** : dépôt public `https://github.com/thibmonier/sylius-b2b-demo`. L'URL en ligne est ajoutée à la bascule.

- **D5** : sous-domaine de la démo. Le préfixe est `sylius-b2b` (`sylius-b2b.<domaine du site>`). Le domaine du site est encore inconnu : l'URL complète arrivera avec lui, en story de backlog US-068. US-066 prépare tout avec ce préfixe.
- **D6** : livraison des dépôts. Une PR par dépôt de plugin (option A). Claude vérifie le rendu GitHub et la CI, puis présente le lot. Un GO unique de l'utilisateur merge les 24 PR.
- **D7** : `order-hold` est le seul plugin purement technique, sans image : c'est un noyau sans entité, route ni écran. C'est vérifié.
- **D8** : la revue et la rétrospective ont lieu le 2026-12-24, le 25 étant férié.

## Choix du Tech Lead

- **Découpe** : l'ex-US-064 (README FR de 24 dépôts, plus de 8 points) est scindée en US-064 (bloc, outil, lot A) et US-067 (lot B, livraison).
- **Livraison des dépôts** : option A retenue (D6), une PR par dépôt (règle 09) et un seul GO de merge pour le lot ; l'option B (commit direct sur `main`) est écartée. La CI de beaucoup de plugins ignore les `*.md` : un contrôle exigé par la protection de branche resterait « en attente » (voir US-064).
- **Plugin purement technique** : `order-hold` seul, vérifié (D7) (noyau sans entité, route ni écran). `punchout-cxml`, `channel-access` et `registration-approval` ont un écran (admin, boutique, simulateur) et sont photographiés.

## Dépendances

- US-064 et US-065 dépendent d'US-063 (charte de ton, liste des plugins sans image).
- US-067 dépend d'US-064 (bloc, outil) ; l'insertion des images (T-065-06) travaille sur les branches d'US-067.
- US-066 est indépendante des plugins ; ses tâches [OPS] attendent le domaine du site (préfixe `sylius-b2b` fixé, D5).
- US-052 (reportée) : GO et comptes de l'utilisateur ; la bascule doit précéder l'ajout de l'URL en ligne aux README.

## Risques

| Risque | Mitigation |
|--------|------------|
| Une page Plus ne tranche pas | Marqué « à confirmer », traité comme concurrent pour le ton, question à l'utilisateur |
| Un dépôt est sur une branche de travail non mergée (par ex. `chore/quality-tooling`) | `git fetch` et vérification de la branche avant tout commit ; branche `docs/readme-fr-demo` partie de `main` |
| La CI ignore les `*.md` et la protection de branche exige un contrôle | Relevé avant le GO (US-067), décision de l'utilisateur, aucun contournement |
| La démo locale ne démarre pas ou les données ont changé | `docker compose` et remise à zéro dès T-065-03 ; repli : application de test du plugin |
| Capture avec donnée réelle ou secret | Protocole de capture (T-065-02) et contrôle T-065-07 |
| Traduction française incohérente entre 24 dépôts | Outil `--check` (structure, blocs de code, termes interdits) et glossaire FR dans la charte |
| Charge supérieure à la capacité (35 h) | Ordre de sacrifice de [tasks/README.md](./tasks/README.md) |

## Contraintes

Aucune action externe sans GO : pas de push, de PR, de merge ni de tag sans confirmation explicite, rien sur GitHub, Railway ni les canaux Sylius, aucune commande Railway par Claude. Pas de lecture de `.env` ni de secrets. Les dépôts de plugins ne sont modifiés que sur une branche locale, après `git fetch` et vérification de la branche courante. Aucun commit sans demande hors du planning. Chaque worker rend le bloc de rapport de fin et respecte la règle d'isolation (`git add` par chemins explicites).

## Definition of Done (rappel)

- [ ] Critères d'acceptation automatisés et verts
- [ ] ECS et PHPStan verts (outil de la démo)
- [ ] Libellés FR et EN si interface
- [ ] Aucun secret dans le dépôt
- [ ] Documentation à jour
- [ ] CI verte

## Cérémonies

| Cérémonie | Date | Durée |
|-----------|------|-------|
| Sprint Planning Part 1 (Quoi) | 2026-12-11 | 1 h |
| Sprint Planning Part 2 (Comment, confirmation de la décomposition) | 2026-12-11 | 1 h |
| Daily Scrum | Chaque jour | 15 min |
| Affinage Backlog | 2026-12-18 | 1 h |
| Sprint Review | 2026-12-24 (matin ; le 25 est férié) | 1 h |
| Rétrospective | 2026-12-24 (après-midi ; le 25 est férié) | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
