# Sprint Review - Sprint 22 (Finalisation)

> Préparée le 2026-10-23 à partir des PR #59, #61 à #66 de la démo, de la PR #5 et de la release v0.1.1 du plugin customer-asset-management, de la PR #7 de company-insights, de `git log`, de `.bmad/sprint-status.yaml` et de [verification-reviews.md](./verification-reviews.md). Aucun détail de sécurité n'est consigné dans ce dépôt public.

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2026-11-06 (matin) |
| Durée | 1 h |
| Branche / PR | `feature/sprint-22-finalisation` / PR #59 (mergée, 2d50ee4), puis PR de suivi #61 à #66 (toutes mergées) |
| PR de plugins | customer-asset-management #5 (confirmation de suppression, release v0.1.1) ; company-insights #7 (README) |
| Animateur | Scrum Master (Claude) |

## Participants

- [ ] Product Owner (Thibaut)
- [ ] Scrum Master
- [ ] Équipe dev (développeurs Claude)

## Sprint Goal

> « Finaliser la démo comme preuve vérifiable : vérifier en production et clore les sprints 18 à 21, solder les points de sécurité et de qualité des audits du 2026-10-22, puis livrer les dernières finitions de lisibilité. »

**Atteint : ✅ OUI.** Les 9 stories engagées après sacrifice (23 points) sont en done. Les reviews des sprints 17 à 21 sont closes après vérification : plus aucune story n'est en review. Une réserve : le scénario d'erreur « Preuve en retard » d'US-084 n'est pas livré (voir les éléments non terminés).

Le sprint a été lancé, développé et vérifié le 2026-10-23, avant la date prévue de son planning (2026-10-26). Les 10 jours ouvrés prévus restent donc disponibles : voir le feedback à collecter.

## User stories livrées

| ID | Titre | Points | Démo | Statut |
|----|-------|--------|------|--------|
| US-163 | Clôture des deux points hauts de l'audit et checklist US-079 | 2 | - | ✅ Done (PR #57, avant le sprint) |
| US-164 | Bornes du parc d'équipements des comptes partagés et limitation de débit des routes récentes | 3 | ✅ | ✅ Done (#59, correctif #63) |
| US-162 | Anomalies mineures relevées en production | 1 | ✅ | ✅ Done, vérifiée en production |
| US-161 | Actions de la rétrospective du sprint 17 (solde) | 2 | - | ✅ Done (registre validé par Thibaut) |
| US-084 | Qualité : Rector en CI, seuil de couverture, `bin/proof-refresh` | 5 | ✅ | ✅ Done, avec une réserve (scénario « Preuve en retard ») |
| US-165 | Dettes qualité de l'audit | 3 | - | ✅ Done |
| US-167 | Performance à faible effort | 2 | ✅ | ✅ Done, vérifiée en production |
| US-088 | Journal d'audit lisible : reset masqué et événements métier | 3 | ✅ | ✅ Done |
| US-098 | Jauge de remplissage condensée sur la fiche produit | 2 | ✅ | ✅ Done |
| US-154 | Company-insights : conversion des devis et économies | 3 | - | ↪️ Sacrifiée (n°2), retour au backlog |
| US-062 | Mineurs du script d'images | 1 | - | ↪️ Sacrifiée (n°1), retour au backlog |

**Livré : 23 points en done sur 27 planifiés.** Les 4 points restants (US-062 et US-154) ont été sacrifiés dans l'ordre prévu au plan, car la décomposition dépassait la capacité de 14 h.

### Clôture des reviews des sprints 17 à 21 (T-TECH-22-01)

23 stories des sprints précédents passent de review à done après vérification, avec le verdict par story dans [verification-reviews.md](./verification-reviews.md) :

- **Vérifiées sur la démo locale et en production (lecture seule) :** US-105, US-106, US-107, US-117, US-158, US-160, US-111, US-068, US-155, US-056, US-057, US-058, US-157, US-060 et US-148 (autowiring des limiteurs vérifié).
- **Relues et validées par Thibaut :** les stories de communication US-116, US-120, US-122, US-123, US-124, US-127, US-129 et US-159.

## Défauts trouvés par la vérification, puis corrigés

| Défaut | Story | Correctif |
|--------|-------|-----------|
| Écran de sortie du parcours sans lien vers le profil | US-106, US-158 | PR #62 |
| Refus d'écriture sur le parc des comptes partagés : page 405 au lieu d'un retour à la liste | US-164 | PR #63 |
| Suppression d'une machine sans confirmation | US-057 | Plugin v0.1.1 (page de confirmation sans JavaScript, PR #5 du plugin), montée dans la démo (PR #65) |
| README de company-insights sans lien de langue ni bloc démo | US-068 | PR #7 de company-insights |

## Éléments non terminés

| Élément | Avancement | Raison | Action |
|---------|-----------|--------|--------|
| US-084, scénario « Preuve en retard » (T-084-02, T-084-04) | Non livré : `bin/proof-refresh` rafraîchit `proof.json`, mais aucun contrôle ne signale un écart avec la suite réelle | Story passée en done sans confronter chaque scénario Gherkin au code | À décider : rouvrir US-084 ou créer une story de 1 point |
| T-088-05, T-098-04 : pa11y WCAG2AAA | Rendu réel vérifié le 2026-10-23 ; aucune trace d'un passage de pa11y | Non tracé dans les tâches | À rejouer avant la revue |
| Déploiement de la v0.1.1 du parc d'équipements | Mergée dans `main` (f8ddeea) | Déploiement Railway à la main de Thibaut | Prochain déploiement |
| Dependabot #54 (PHPUnit 13) | Ouverte | Incompatible avec la version de PHPUnit utilisée par Sylius | Story d'évaluation à proposer |

## Détail par lot

### Sécurité et stabilité de la démo publique (US-164, US-162, US-163)
- Parc d'équipements en lecture seule pour les comptes de démo partagés ; limitation de débit dédiée aux routes récentes (sans détail ici).
- Liens « Créer un compte » masqués quand l'inscription est fermée ; en-tête HSTS envoyé une seule fois (vérifié en production).

### Qualité (US-084, US-165)
- Rector en CI (mode vérification) après un passage complet sur `src` et `tests`.
- Seuil de couverture bloquant à 90 % (92,7 % mesurés sur `main`), taux publié dans le résumé du job.
- `proof.json` rafraîchi.
- Écriture des listes de l'entreprise déplacée dans un service testé ; codes acheteurs PunchOut à source unique ; code mort de la vitrine supprimé.

### Performance (US-167)
- Premières vignettes du catalogue chargées immédiatement ; `/build/*` en cache d'un an `immutable`.
- LCP mobile du catalogue : 1 976 ms le 2026-10-22, puis 1 448 ms le 2026-10-23.

### Lisibilité (US-088, US-098)
- Journal d'audit sans ligne de remise à zéro, avec 6 événements métier datés et attribués.
- Jauge de remplissage condensée : seuls les contenants pertinents sont affichés, les autres sont repliés dans un `<details>`.

### Dépendances
- MySQL reste sur la 8.4 LTS (PR #61), sur décision de Thibaut (Dependabot #53 refusée).
- Plugin parc d'équipements en ^0.1.1 (PR #65).

## Vérification finale (Scrum Master)

- Production en lecture seule : les 12 vérifications de `bin/verify-public-mode` sont OK, et les quatre pages clés restent dans la zone « bon » du LCP mobile.
- CI de `main` verte après chaque merge (« Quality and smoke tests », « Docker image »).
- Suite complète non lancée en local (disque sous 5 Go) : la CI fait foi.

## Incidents et écarts de processus

- La machine a planté par manque de ressources pendant le sprint ; le travail a repris sans perte. Le disque est descendu jusqu'à environ 2,6 Go libres.
- Un test du verrou de `bin/run-tests` est instable (`RunTestsLockTest`) : échec sur la PR #64, vert au run suivant.
- US-084 est passée en done avec un scénario d'erreur non livré.
- Les tâches livrées ont gardé le statut 🔲 jusqu'à la clôture : 9 dans `technical-tasks.md` (environ 50 au sprint 21).
- La base de test du plugin pointait vers une autre base ; ce prérequis a été découvert en cours de sprint.
- 4 refus d'outil ont été rencontrés, aucun n'a été contourné. Rien n'a été poussé, tagué ou publié sans le GO de Thibaut.

## Métriques

| Métrique | Valeur | Tendance |
|----------|--------|----------|
| Points planifiés | 27 (23 après les sacrifices) | sprint 21 : 17 engagés |
| Points en done | 23 | sprint 21 : 0 en done, 17 en review |
| Stories des sprints précédents closes | 23 | - |
| Stories en review à la clôture | 0 | sprint 21 : 4 |
| Tâches du périmètre engagé | 66 ✅ et 4 🔲 sur 70 (statuts mis à jour à la clôture) | sprint 21 : 41 ✅ sur 61 |
| Défauts trouvés par la vérification | 4, tous corrigés | - |
| Couverture de lignes | 92,7 % (seuil bloquant 90 %) | nouvelle mesure |
| LCP mobile du catalogue | 1 448 ms | 1 976 ms la veille |
| Releases de plugins | 1 (customer-asset-management v0.1.1) | sprint 21 : 0 |

Vélocité de référence : 23 points en done. Historique des sprints 18 à 22 : 25, 25, 22, 17 et 23.

## Démonstration (ordre suggéré)

1. **Clôture des reviews** (~5 min) : [verification-reviews.md](./verification-reviews.md), 0 story en review.
2. **US-057 et la v0.1.1** (~5 min) : suppression d'une machine avec sa page de confirmation.
3. **US-088 et US-098** (~5 min) : journal d'audit lisible, jauge condensée.
4. **US-084 et US-167** (~5 min) : résumé de CI (Rector, couverture), mesure LCP.
5. **US-164 et US-162** (~3 min) : compte de démo partagé en lecture seule, plus de lien d'inscription.

```gherkin
Given un client avec une machine dans son parc
When il clique sur « Supprimer »
Then une page lui demande de confirmer, sans afficher la plaque
And la machine n'est supprimée qu'après sa confirmation

Given un compte de démo partagé
When un visiteur tente de modifier le parc
Then il revient à la liste du parc avec un message, sans page d'erreur
```

## Feedback à collecter

1. Le sprint 22 est terminé avant son planning du 2026-10-26. Faut-il le clore maintenant et avancer le sprint 23, ou garder le calendrier et tirer des stories du sprint 23 ?
2. Pour le scénario « Preuve en retard » d'US-084 : rouvrir la story, créer une story de 1 point, ou accepter l'écart ?
3. PHPUnit 13 (#54) : faut-il une story d'évaluation au sprint 23 ?
4. Faut-il reprendre au sprint 23 les sujets laissés hors périmètre : les faux positifs du contrôleur `bin/vitrine-readme` (commentaires traduits, captures propres à chaque langue) et la bannière demo-bar ?
5. US-154 et US-062 sont sacrifiées : faut-il les replanifier ou les abandonner ?

## Notes de session

Feedback reçu :
- {à remplir}

Nouvelles demandes :
- {à remplir}

Décisions prises :
- Rester sur MySQL 8.4 LTS (Thibaut, 2026-10-23).
- Ajouter une confirmation à la suppression d'une machine (Thibaut, 2026-10-23), livrée en v0.1.1.
- Clôture anticipée du sprint 22 le 2026-10-23 et passage au sprint 23 (Thibaut).
- Scénario « Preuve en retard » d'US-084 repris par une story de 1 point : [US-168](../../backlog/user-stories/US-168.md) (Thibaut).
- Évaluation de PHPUnit 13 au sprint 23 : spike [US-169](../../backlog/user-stories/US-169.md) (Thibaut).

## Impact sur le backlog

| Action | Élément | Description |
|--------|---------|-------------|
| Retour au backlog | US-154, US-062 | Sacrifiées dans l'ordre du plan |
| À décider | US-084 (scénario d'erreur) | Contrôle d'écart entre `proof.json` et la suite réelle |
| À proposer | PHPUnit 13 | Évaluation de compatibilité avec Sylius |
| Sprint 23 (esquisse) | US-128, US-103, US-121, US-166, US-132, US-130 | US-103 et US-121 restent conditionnées au transcript T-103-03 |

## Prochaines étapes

1. Thibaut déploie `main` (v0.1.1) sur Railway ; le Scrum Master vérifie la confirmation de suppression en production.
2. Rejouer pa11y sur le journal d'audit et la fiche produit (T-088-05, T-098-04).
3. Décider du calendrier : clôture anticipée ou maintien au 2026-11-06.
4. Tenir la rétrospective : [sprint-retro.md](./sprint-retro.md).
