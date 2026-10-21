# Tâches : Sprint 017 (conversion)

> Sprint Planning Part 2 du 2027-02-08 (décomposition préparée le 2026-10-20). Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-17`). Sprint 16 : [../../sprint-16-demo-credible/tasks/README.md](../../sprint-16-demo-credible/tasks/README.md). Objectif : [../sprint-goal.md](../sprint-goal.md). Tableau : [../task-board.md](../task-board.md). Dépendances : [../sprint-dependencies.md](../sprint-dependencies.md). Actions de la rétro du sprint 16 : [technical-tasks.md](./technical-tasks.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Statut |
|----|-------|--------|--------|--------|--------|
| [US-116](./US-116-tasks.md) | Charte éditoriale LinkedIn et messages clés | 2 | 6 | 5,5 h | 🔲 |
| [US-129](./US-129-tasks.md) | Profil LinkedIn : texte, bannière et section « À la une » | 2 | 7 | 5,5 h | 🔲 |
| [US-122](./US-122-tasks.md) | Calendrier éditorial LinkedIn sur huit semaines | 3 | 5 | 5 h | 🔲 |
| [US-123](./US-123-tasks.md) | Posts LinkedIn rédigés, lot 1 : trois posts de suite à l'annonce | 3 | 10 | 9,5 h | 🔲 |
| [US-127](./US-127-tasks.md) | Suivi de l'annonce d'ouverture, de J+1 à J+30 | 2 | 7 | 7 h | 🔲 |
| [US-117](./US-117-tasks.md) | Mesure de la communication : visites par campagne sans cookie | 3 | 7 | 6,5 h | 🔲 |
| [US-120](./US-120-tasks.md) | Kit média (1/2) : GIF courts de PunchOut, devis et commande rapide | 5 | 9 | 10,5 h | 🔲 |
| [US-105](./US-105-tasks.md) | Parcours guidés : moteur de visite par étapes | 8 | 12 | 18,5 h | 🔲 |
| [US-106](./US-106-tasks.md) | Parcours guidés : contenu par persona et écran de sortie vers le contact | 5 | 7 | 11 h | 🔲 |
| [US-107](./US-107-tasks.md) | Page « Intégrer ces plugins dans votre projet » | 5 | 9 | 11,5 h | 🔲 |
| [Transverse](./technical-tasks.md) | Actions de la rétro du sprint 16 (sans points) | 0 | 8 | 8 h | 🔲 |

**Total décomposé** : 87 tâches | **98,5 h** pour 38 points (+ 4 actions sans points), dont 8,5 h calendaires (actions de Thibaut), soit **90 h de charge pour Claude**.

## Capacité

Capacité retenue : 38 points × 2,1 h ≈ **80 h** pour les stories (ratio du sprint 16 : 57 h pour 27 points) + 8 h pour les quatre actions de la rétro, soit **88 h**. La charge de Claude est de **90 h** : dépassement de **2 h** à absorber par l'ordre de sacrifice si le point d'étape du 2027-02-12 montre un retard. Deux pistes de développement qui se partagent le thème (US-105/106 et US-107) et une piste rédactionnelle (US-116, 129, 122, 123, 127, 117, 120) qui ne partage aucun fichier avec elles ; les actions de la rétro (verrou, tests) passent en premier et seules les tâches rédactionnelles ont des échéances calendaires.

### Ordre de sacrifice (si les heures dépassent la capacité)

À appliquer dans cet ordre, en s'arrêtant dès que la charge restante tient dans 88 h. **Non sacrifiables** : US-116, US-129, post 1 d'US-123, US-127 et la mesure native d'Umami (échéance du 2026-10-26), les quatre actions de la rétro, les tests d'accessibilité et de rendu réel (US-105, US-107), les revues. Toute tâche sacrifiée est consignée dans la revue comme dette datée.

| # | Sacrifice | Heures | Charge restante | Conséquence |
|---|-----------|--------|-----------------|-------------|
| 0 | (charge de Claude décomposée) | n/a | 90 h | n/a |
| 1 | Documentation de finition (`docs/parcours-guides.md`, `docs/theme-b2b.md`) (T-105-11, T-107-08) | 1 h | 89 h | Dette de doc datée, soldée au sprint 18 |
| 2 | US-117 réduite à la mesure native d'Umami (UTM de la première page) : pas de propriété `campaign` sur les événements (T-117-02, T-117-03, T-117-06) | 3,5 h | 85,5 h | Les parcours lancés ne sont pas ventilés par campagne ; visites par campagne toujours mesurées (Should) |
| 3 | Séquence « commande rapide » du kit média (deux séquences sur trois) (T-120-05) | 1 h | 84,5 h | Critère « trois fichiers » d'US-120 non atteint ; troisième séquence reportée au kit 2/2 (sprint 18) |
| 4 | Post 3 du lot 1 (T-123-06) | 1,5 h | 83 h | Deux posts de suite au lieu de trois ; le troisième est repris dans le calendrier (US-122) |
| 5 | Parcours « directeur financier » et « directeur e-commerce » (≈ 40 % des tâches 03, 05 et 06 : 2,2 h) (T-106-03, T-106-05, T-106-06) | 2,2 h | 80,8 h | Trois parcours sur cinq livrés ; les deux autres suivent au sprint 18 |

Si la charge dépasse encore 88 h après le sacrifice n° 5, la décision de couper une Must revient à Thibaut.

## Chemin critique et attentes

- **Échéance du 2026-10-26 (en premier)** : T-116-01 à T-116-05 (charte, UTM, contrôle) → T-129-02 à T-129-07 (profil à jour) → T-123-01 à T-123-04 (post 1) ; en parallèle T-117-01 et T-117-05 (lien de test dans Umami) → T-127-04 (chiffres J+7) → T-127-05 (bilan). Les échéances calendaires (2026-10-26 et 2026-11-18 pour J+30) précèdent le début calendaire du sprint (2027-02-08) : démarrage effectif des stories de communication dès la décision de Thibaut.
- **Actions de la rétro** : T-TECH-01 et T-TECH-02 (verrou) avant tout run de tests ; T-TECH-03 à T-TECH-05 avant la première story qui touche des données de démo (US-105).
- **Piste parcours** : US-105 (T-105-01 à T-105-11) → US-106 (T-106-01 à T-106-07) ; T-106-04 attend la route de la page « Intégrer » (T-107-04) ; T-117-03 attend T-105-06.
- **Piste « Intégrer »** : US-107 en parallèle ; un seul worker à la fois sur le pied de page, la page Plugins et les traductions communes.
- **Kit média** : US-120 (T-120-01 à T-120-09) avant les posts 2 et 3 (T-123-05, T-123-06).
- **Rendu réel final** de toutes les pages touchées (T-105-10, T-106-06, T-107-07, T-117-06) avant la revue ; T-TECH-08 (reconstruction de l'image) avant la revue.

## Actions de Thibaut (tâches qui ne sont pas réalisables par Claude seul)

| ID | Action | Durée |
|----|--------|-------|
| T-116-06 | Thibaut relit et valide cibles, piliers, ton et interdits | 0,5 h |
| T-129-01 | Thibaut fournit le texte actuel du profil (titre, À propos, bannière, À la une) pour partir de l'existant | 0,5 h |
| T-129-06 | Thibaut relit textes, bannière et liste | 0,5 h |
| T-129-07 | Thibaut met à jour son profil (texte, bannière, À la une) et épingle le commentaire | 0,5 h |
| T-122-05 | Thibaut choisit la cadence et valide le calendrier | 0,5 h |
| T-123-03 | Thibaut relit et ajuste le post 1 (relecture seule, sans charte) | 0,5 h |
| T-123-04 | Thibaut publie le post 1 au plus tard le 2026-10-26 et épingle le commentaire avec le lien de la démo | 0,5 h |
| T-123-09 | Thibaut relit les posts 2 et 3 | 0,5 h |
| T-123-10 | Thibaut publie les posts 2 et 3 aux dates du calendrier | 0,5 h |
| T-127-03 | Thibaut relit le plan de suivi | 0,5 h |
| T-127-04 | Thibaut relève les chiffres LinkedIn et Umami à J+7 (2026-10-26) | 0,5 h |
| T-127-06 | Thibaut relève les chiffres LinkedIn et Umami à J+30 (2026-11-18) | 0,5 h |
| T-117-05 | Thibaut ouvre un lien de test `?utm_source=linkedin&utm_campaign=ouverture-demo` et vérifie la visite dans Uma… | 0,5 h |
| T-120-09 | Thibaut valide les trois séquences (sinon séquence refaite) | 0,5 h |
| T-106-07 | Thibaut relit les textes (ton, exactitude) ; revue de code | 1 h |
| T-107-02 | Thibaut valide formules, délais et mention du support sous contrat | 0,5 h |

Sont aussi en partie de Thibaut : le choix de la cadence (T-122-05), la validation des délais et formules (T-107-02) et la relecture des textes de parcours (T-106-07). Claude ne publie rien sur LinkedIn, ne modifie pas le profil et ne lit pas les statistiques LinkedIn ni Umami ; la captation d'écran de secours (T-120-03 à T-120-05) est un repli de Thibaut si le rendu de Claude n'est pas net.

## Contrat de fin de worker (obligatoire)

**Chaque consigne de worker se termine par le bloc ci-dessous, collé tel quel, et le worker le place EN PREMIER dans sa réponse finale (≤ 100 mots au total).** Le Scrum Master refuse de clore une tâche sans lui.

```markdown
## Rapport de fin (en premier, ≤ 100 mots)

- **Tâches** : T-XXX-YY
- **État** : terminé | bloqué | partiel (cause, reste à faire)
- **Fichiers** : chemins créés ou modifiés (dépôts de plugins : branche et hash)
- **Vérifications** : commandes et résultat, ou « non exécutées » + raison
- **Rendu réel** : pages vues (URL, FR et EN) ou « sans objet »
- **Nettoyage** : `var/cache-<nom>` du worker supprimé (`bin/clean-test-caches`), `df -h .` en Go libres
- **Commit** : fait (hash) | non fait (défaut : aucun commit sans demande)
- **Besoin de l'utilisateur** : GO, accès ou validation, sinon « aucun »
```

**Règle disque et gel** : `df -h .` au démarrage de chaque worker et `bin/clean-test-caches` avant chaque lot de tests ; seul `bin/run-tests` est autorisé (jamais `phpunit` en direct, jamais de seuil `MIN_FREE_GB` abaissé). Depuis T-TECH-02, un second run est refusé par le verrou : attendre la fin par le verrou, jamais par `pgrep`.

**Tâches de fond (action 3 de la rétro S16)** : arrêter explicitement (TaskStop) toute tâche de fond avant d'en relancer une et vérifier l'arrêt.

**Comptes de démo partagés (action 2 de la rétro S16)** : tout test qui modifie un compte partagé (panier de Claire, etc.) l'isole (transaction ou copie) ou annule ses effets. Règle détaillée et trait `IsolatesSharedAccounts` : [docs/tests-locaux.md](../../../../docs/tests-locaux.md) (section « Comptes de démo partagés »). Les assertions sur les données vivantes restent strictes.

**Échec sur un test partagé** : mettre à jour la branche et relancer ce test avant de le signaler.

**Règles propres au sprint** : tests via `TEST_DB_NAME=<db> bin/run-tests …`, ECS sur `src tests`, PHPStan ; mobile mesuré avec `bin/measure-mobile`, pa11y WCAG2AAA ; libellés FR et EN ; seul l'orchestrateur reconstruit l'image Docker (et la valide avant la clôture d'une revue, action 4) ; `git add` par chemins explicites ; aucun push, PR, merge ni tag sans GO ; `main` protégée (attendre la fin du run CI) ; aucune commande Railway par Claude ; pas de lecture de `.env` ni de secrets ; valeurs Umami et Calendly vides par défaut, valeurs factices dans les tests ; livrables rédactionnels dans `../communication/` (hors dépôt public), aucun nom ni contenu de conversation de prospect dans le dépôt.
