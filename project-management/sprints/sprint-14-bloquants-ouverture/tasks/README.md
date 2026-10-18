# Tâches : Sprint 014 (bloquants d'ouverture)

> Sprint Planning Part 2 du 2026-12-28 (décomposition préparée le 2026-10-18). Source de vérité du statut : `.bmad/sprint-status.yaml` (`sprint-14`). Sprint 13 : [../../sprint-13-vitrine-des-plugins/tasks/README.md](../../sprint-13-vitrine-des-plugins/tasks/README.md). Objectif : [../sprint-goal.md](../sprint-goal.md). Tableau : [../task-board.md](../task-board.md). Dépendances : [../sprint-dependencies.md](../sprint-dependencies.md).

## Vue d'ensemble

| US | Titre | Points | Tâches | Heures | Statut |
|----|-------|--------|--------|--------|--------|
| [US-070](./US-070-tasks.md) | Punchout : titre et fil d'Ariane des acheteurs traduits | 1 | 4 | 2,5 h | 🔲 |
| [US-071](./US-071-tasks.md) | Mise en page mobile | 3 | 6 | 6 h | 🔲 |
| [US-072](./US-072-tasks.md) | Repères, lien d'évitement et annonces | 5 | 8 | 10 h | 🔲 |
| [US-073](./US-073-tasks.md) | Contrastes AAA et jetons uniques | 3 | 6 | 6 h | 🔲 |
| [US-074](./US-074-tasks.md) | Pied de page B2B | 3 | 6 | 6 h | 🔲 |
| [US-075](./US-075-tasks.md) | Connexion : comptes en premier | 2 | 4 | 4 h | 🔲 |
| [US-076](./US-076-tasks.md) | Barre de démo sur une ligne | 2 | 5 | 4 h | 🔲 |
| [US-077](./US-077-tasks.md) | Libellés accessibles en français | 2 | 4 | 4 h | 🔲 |
| [US-079](./US-079-tasks.md) | Sécurité avant ouverture | 3 | 8 | 7 h | 🔲 |
| [US-080](./US-080-tasks.md) | Boîte de mails limitée aux fixtures | 2 | 4 | 4 h | 🔲 |
| [US-086](./US-086-tasks.md) | Exemple de commande rapide | 2 | 6 | 4,5 h | 🔲 |
| [US-087](./US-087-tasks.md) | Signaux « prototype » | 3 | 7 | 6 h | 🔲 |
| [US-104](./US-104-tasks.md) | Retrait de l'encart Sylius Plus | 1 | 4 | 2 h | 🔲 |

**Total décomposé** : 72 tâches | **66 h** pour 32 points, dont 3 h calendaires (T-070-04, T-086-06 : PR et release de patch des plugins après GO ; T-079-05 et T-079-06 : vérifications de Thibaut), soit **63 h de charge**. Fonctions de Thibaut hors charge : T-052-03 à T-052-07 (US-052 reportée).

## Capacité

Périmètre complet voulu par Thibaut (D1) : 32 points, soit ≈ 2 h par point (66 h pour 32 points) alors que la vélocité récente est de 10-15 points et le ratio observé de 2,5 à 3,1 h par point (≈ 30-35 h par sprint). Sur 9 jours ouvrés, la charge de 63 h dépasse la capacité habituelle : risque assumé. Le Scrum Master suit la consommation au point d'étape du 2027-01-04 et applique l'ordre ci-dessous dès qu'il y a retard.

### Ordre de sacrifice (si le sprint dérive)

À appliquer dans cet ordre, en s'arrêtant dès que la charge restante tient dans la capacité.

| # | Sacrifice | Heures | Charge restante | Conséquence |
|---|-----------|--------|-----------------|-------------|
| 0 | (charge décomposée, hors calendaires) | n/a | 63 h | n/a |
| 1 | US-070 (T-070-01 à T-070-03) | 2 h | 61 h | Clé brute `tmonier_punchout.ui.buyers` visible dans l'admin ; T-087-06 la tolère explicitement |
| 2 | US-077 | 4 h | 57 h | Libellés accessibles du cœur restent partiellement en anglais |
| 3 | US-076 | 4 h | 53 h | Barre de démo actuelle sur deux lignes |
| 4 | US-075 | 4 h | 49 h | Connexion inchangée (comptes sous le formulaire) |
| 5 | T-073-05 contraste élevé | 0,5 h | 48,5 h | Pas de réglage dédié `prefers-contrast` |
| 6 | T-071-05 contenu long à 320 px | 0,5 h | 48 h | Cas limite vérifié à la revue seulement |
| 7 | T-087-06 parcours de rendu des clés brutes | 0,5 h | 47,5 h | Seul le test de fumée automatisé reste |
| 8 | T-072-07 et T-074-05 documentation | 1 h | 46,5 h | Documentation reprise au sprint suivant |

**Non sacrifiables** : toutes les Must (US-071 à 074, 079, 080, 086, 087, 104) hors les tâches ⚠️ ci-dessus ; les tests rouges et les revues de chaque Must ; T-079-02 et T-079-03 (protection de branche) ; les vérifications de rendu réel (D5). Après le sacrifice n° 8, ≈ 46,5 h restent : si le point d'étape montre encore un déficit, la décision de couper une Must revient à Thibaut.

### Sacrifiables en un coup d'œil

⚠️ US-070, US-077, US-076, US-075 (14 h) puis T-073-05, T-071-05, T-087-06, T-072-07, T-074-05 (2,5 h).

## Chemin critique et attentes

- **Style** : T-073-01 → T-073-04 (jetons) avant US-071, US-075, US-076 ; un seul worker à la fois sur `assets/shop/styles`.
- **Layout** : T-072-01 → T-072-02 → T-074-03 (pied de page) ; T-074-02 (URL) → T-076-03.
- **Page Plugins** : T-104-03 avant T-087-03.
- **Plugins externes** : T-070-02 et T-086-03 dans leurs dépôts, puis T-070-04 et T-086-06 (PR, merge, release de patch après GO de Thibaut).
- **Sécurité** : T-079-01 → T-079-02 ; T-079-04 → T-079-05 et T-079-06 (Thibaut) → T-079-07.
- **Dernière vérification** : T-087-06 après T-070-02 ; rendu réel final de toutes les pages touchées avant la revue (T-071-06, T-073-06, T-072-08).

## Contrat de fin de worker (obligatoire)

**Chaque consigne de worker se termine par le bloc ci-dessous, collé tel quel, et le worker le place EN PREMIER dans sa réponse finale (≤ 120 mots).** Le Scrum Master refuse de clore une tâche sans lui.

```markdown
## Rapport de fin (en premier, ≤ 120 mots)

- **Tâches** : T-XXX-YY
- **État** : terminé | bloqué | partiel (cause, reste à faire)
- **Fichiers** : chemins créés ou modifiés (dépôts de plugins : branche et hash)
- **Vérifications** : commandes et résultat, ou « non exécutées » + raison
- **Rendu réel** : pages vues (URL, FR et EN) ou « sans objet »
- **Nettoyage** : `var/cache-*` du worker supprimé (`rm -rf var/cache-<nom>`), `df -h .` en Go libres
- **Commit** : fait (hash) | non fait (défaut : aucun commit sans demande)
- **Besoin de l'utilisateur** : GO, accès ou validation, sinon « aucun »
```

**Règles propres au sprint** : avant de toucher un dépôt de plugin, `git fetch` et vérification de la branche courante, partir de `main` à jour ; `git add` par chemins explicites ; aucun push, aucune PR, aucun merge sans GO (seule exception cadrée : protection de `main`, D3, après confirmation) ; aucun détail exploitable dans les documents d'US-079 ; tests via `TEST_DB_NAME=<db> WKHTMLTOPDF_BINARY="$PWD/tests/Fixtures/bin/fake-wkhtmltopdf" bin/run-tests …`, ECS sur `src tests` ; mobile mesuré avec Puppeteer `isMobile` à 375 et 320 px (Chrome headless seul impose ≈ 500 px).
