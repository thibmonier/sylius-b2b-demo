# Sprint 12 : Ouverture et plugins (ouverture publique, simulateur OCI, parc d'équipements, dette)

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 12 |
| Début | 2026-11-27 |
| Fin | 2026-12-11 |
| Durée | 11 jours ouvrés (aucun férié) |
| Capacité | 14 points, environ 30-35 h (43,5 h décomposées, dont 4 h calendaires ; 4,5 h sacrifiables, plus une coupe franche de 6,5 h) |
| Branche | `feature/sprint-12-ouverture-et-plugins` |

## Sprint Goal

> « Ouvrir la démo au public sur GO de l'utilisateur et la vérifier de bout en bout, montrer le second protocole PunchOut (OCI) dans le simulateur, poser les fondations du plugin de parc d'équipements client (spec, ADR, verdict Sylius Plus, socle qualité) et solder la dette outillage des rétrospectives. »

## Sprint Backlog

| ID | Titre | Points | Heures | Statut |
|----|-------|--------|--------|--------|
| US-052 | Ouverture publique de la démo | 3 | 8,5 h (dont 3,5 h calendaires) | ready-for-dev |
| US-053 | Simulateur PunchOut OCI dans la démo | 4 | 12,5 h | ready-for-dev |
| US-054 | Plugin parc d'équipements client, tranche 1 | 4 | 14 h (dont 0,5 h calendaire) | ready-for-dev |
| US-055 | Actions de rétro et dette | 3 | 8,5 h | ready-for-dev |

Engagement : 14 points, 34 tâches, 43,5 h décomposées (39,5 h de charge hors calendaires) pour ≈ 30-35 h de capacité. La charge dépasse la fourchette de 4,5 h : le sprint tient à 35 h après les sacrifices n° 1 à 3 de [tasks/README.md](./tasks/README.md).

## Décisions de l'utilisateur (validation du périmètre)

- **US-052** : reprend les [OPS] restées ouvertes au sprint 11 (T-050-09, T-050-10, T-050-11, T-049-08) et la vérification de `X-Real-IP`. Les commandes Railway (redeploy, variables, `config apply`) sont lancées par l'utilisateur ; la bascule exige son **GO explicite**. Claude prépare, vérifie (curl, tests en mode public) et met à jour le runbook et les docs.
- **US-053** : le plugin `tmonier/sylius-punchout-cxml-plugin` gère déjà OCI ; seul le simulateur de la démo ne fait que du cXML (OCI laissé de côté au sprint 5, T-027-02). Garde-fous du cXML repris : limiteurs par IP et usage unique ; le plafond quotidien des commandes ne s'applique pas à OCI, aucune commande n'étant créée (décision D3).
- **US-054** : nouveau plugin OSS `tmonier/sylius-customer-asset-management-plugin`, dépôt séparé. Tranche 1 découpée à 4 points (spec, ADR, vérification du chevauchement avec Sylius Plus, socle qualité) ; le reste est en backlog (US-056 à US-060). Si Sylius Plus couvre le besoin, le plugin est publié mais pas promu (politique de communication). Création du dépôt GitHub public et tag : par Claude après confirmation explicite ; soumission à Packagist : par l'utilisateur (décision D1).
- **US-055** : actions 1 à 3 de la rétro du sprint 11, vérification de l'action 4 du sprint 10, déblocage d'US-031 (agentic-feeds est sur Packagist depuis le 2026-10-13, v1.0.0).

### Décisions du 2026-10-17 (planification)

- **D1 (US-054, publication)** : le plugin de parc d'équipements a un dépôt GitHub public et est publié sur Packagist, comme les autres plugins. Claude crée le dépôt et pose le tag après confirmation explicite ; l'utilisateur soumet lui-même le paquet à Packagist. Si Sylius Plus couvre déjà le besoin, le plugin est publié mais pas promu.
- **D2 (capacité)** : l'ordre de sacrifice de [tasks/README.md](./tasks/README.md) est accepté tel quel.
- **D3 (US-053, décision technique du Tech Lead)** : le retour OCI ne crée pas de commande, donc le plafond quotidien des commandes (`SimulatorOrderGate`) ne s'applique pas à OCI. Les limiteurs par IP et l'usage unique de la session s'appliquent.
- **D4 (US-052, GO)** : la date du GO de bascule n'est pas fixée ; le GO est donné par l'utilisateur pendant le sprint.

## Dépendances

- US-052 dépend de la PR #21 du sprint 11 mergée et déployée (modes privé et public, `/healthz` fiable, runbook).
- US-053 dépend d'US-027 (simulateur cXML, sprint 5), de la sécurité du simulateur (US-036) et de `punchout-cxml` ≥ 1.1 (OCI VALIDATE/DETAIL).
- US-054 est indépendante de la démo (dépôt séparé) ; l'intégration à la démo est en backlog (US-060).
- US-055 : aucune dépendance ; T-055-02 sert aux suites de tests des autres stories (à livrer en première semaine).
- Tâches [OPS] : GO, comptes et commandes Railway de l'utilisateur.

## Risques

| Risque | Mitigation |
|--------|------------|
| Pas de GO de bascule dans le sprint (la date n'est pas fixée, D4) | Tâches calendaires hors chemin critique, jamais closes sur une estimation ; la préparation (T-052-01, T-052-02) reste livrée |
| Une vérification post-bascule échoue (`X-Real-IP` non lu, 200 sur l'inscription) | Retour arrière en un geste (porte refermée), documenté dans le runbook ; correctif en story de suite |
| Le contrat OCI du plugin diffère de ce que le simulateur suppose (champs, authentification, `HOOK_URL`) | T-053-01 lit le plugin d'abord ; écart tracé, jamais contourné dans la démo |
| Sylius Plus couvre déjà le besoin (parc d'équipements) | Verdict documenté (T-054-03) ; dans ce cas le plugin est publié mais pas promu (décision D1) |
| Disque plein ou cache de test périmé (sprint 11) | US-055 en première semaine ; `df` avant chaque suite (seuil 5 Go) ; nettoyage `var/cache-*` en fin de worker |
| Charge supérieure à la capacité (39,5 h) | Ordre de sacrifice relu par l'utilisateur à la planification |

## Contraintes

Aucune action externe sans GO : pas de push, de merge, de création de dépôt ni de tag sans confirmation explicite, pas de soumission Packagist (faite par l'utilisateur), ni de commande Railway par Claude. Pas de lecture de `.env` ni de secrets. Aucun commit sans demande. Chaque worker rend le bloc de rapport de fin et respecte la règle d'isolation (`git add` par chemins explicites).

## Definition of Done (rappel)

- [ ] Critères d'acceptation automatisés et verts
- [ ] ECS et PHPStan verts
- [ ] Libellés FR et EN si interface
- [ ] Aucun secret dans le dépôt
- [ ] Documentation à jour
- [ ] CI verte

## Cérémonies

| Cérémonie | Date | Durée |
|-----------|------|-------|
| Sprint Planning Part 1 (Quoi) | 2026-11-27 | 1 h |
| Sprint Planning Part 2 (Comment, confirmation de la décomposition) | 2026-11-27 | 1 h |
| Daily Scrum | Chaque jour | 15 min |
| Affinage Backlog | 2026-12-04 | 1 h |
| Sprint Review | 2026-12-11 (matin) | 1 h |
| Rétrospective | 2026-12-11 (après-midi) | 1 h 30 |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
