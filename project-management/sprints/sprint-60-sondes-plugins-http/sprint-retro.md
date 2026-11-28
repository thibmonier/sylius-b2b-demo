# Rétrospective du sprint 60

**Directive fondamentale** : « Quoi que nous découvrions, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »

Entrée principale : [sprint-review.md](./sprint-review.md). Bilan du sprint :
- 14 points sur 14 « done » en deux jours (2026-11-27 → 2026-11-28) ;
- quatre stories réparties sur six dépôts : santé, webhook-outbox, purchase-approval, trade-credit, bulk-actions et la démo ;
- quatre releases (santé v0.6.0, webhook-outbox v1.1.0, purchase-approval v1.5.0, trade-credit v1.2.0), posées après la documentation ;
- 23 sondes relevées en production, sans aucune `critical`.

Format : ce qui a marché, ce qui a coûté, actions SMART (comme aux sprints 56 à 59).

## Suivi des actions du sprint 59

| ID | Appliquée ? | Preuve |
|----|-------------|--------|
| A-59-1 (`--dump-sql` de production relevé avant une story de schéma) | Sans objet | Aucune story du sprint ne touchait au schéma de la démo ; les trois plugins d'US-304 n'apportent aucune migration |
| A-59-2 (`IF NOT EXISTS` seulement avec un test de la table existante) | Sans objet | Aucune migration écrite au sprint 60 |
| A-59-3 (relevé `tmonier:health:check` après chaque déploiement) | Oui | Un relevé en lecture seule avant la fusion de #245, puis un après chaque déploiement (#245 : 18 sondes ; #246 : 23 sondes), notés dans #246 et dans cette revue ; les deux avertissements ont été signalés à Thibaut |
| A-57-4 (relevé Railway) | En attente | Échéance au 2026-12-01, à la charge de Thibaut |

## Ce qui a bien marché

- **Le rouge a été vu en CI pour chaque story**, dans les six dépôts (règle 26). Pour US-319, il a été vu sur les 8 combinaisons concernées, ce qui a montré que le test instable touchait trois combinaisons et non une.
- **L'enquête bornée a tenu son budget** : US-319 a trouvé sa cause (le tirage aléatoire de la zone des méthodes de livraison par les fixtures) en environ 2 h 30 sur 4 h, et le correctif n'utilise aucune relance.
- **Le worker d'US-321 a déclaré les deux jetons dans `preserve()`** : sans cela, `railway config apply` aurait supprimé les variables que Thibaut venait de créer.
- **Le test de la démo lit la page réelle** : il a donné un rouge propre pour T-TECH-60-04 (une seule assertion, la catégorie « Plugins » absente), puis le vert avec les 23 sondes.
- **Décisions soumises à Thibaut et tranchées vite** :
  - niveau détail sans le résumé des sondes ;
  - les trois tests ignorés de bulk-actions deviennent une story (US-322) ;
  - la durée de la CI de la démo devient une story (US-323).
- **Le relevé de production est devenu un réflexe** (A-59-3) : il a confirmé que chaque déploiement contenait bien le changement attendu (sondes `demo.*` après #245, catégorie « Plugins » après #246).

## Ce qui a coûté

- **Des runs de CI annulés par la concurrence** : le rouge d'US-319, puis deux runs du vert de #246, d'environ 20 minutes chacun. Cinq pourquoi :
  1. Pourquoi des runs ont-ils été annulés ? Un nouveau commit a été poussé pendant qu'ils tournaient.
  2. Pourquoi ? Les commits de suivi (preuve rafraîchie, suivi du sprint, nouvelle story) ont été poussés un par un, dès qu'ils étaient prêts.
  3. Pourquoi est-ce coûteux ? La CI calcule son périmètre sur tout le diff de la PR : un commit de documentation relance aussi les 20 minutes de tests.
  4. Pourquoi 20 minutes ? PHPUnit exécute les 4 155 tests en un seul processus (17 min).
  5. **Cause racine** : les commits d'une PR ne sont pas regroupés, et la CI est assez longue pour que chaque push en trop coûte cher.
- **Un test sans rouge** : `RailwayPreservedVariablesTest` est arrivé avec le vert d'US-321 (règle 26).
- **Docker local peu fiable** :
  - OrbStack bloqué pendant US-319 ;
  - erreur de segmentation de `sylius-php:8.5-alpine` pendant les fixtures pour #246, si bien que le vert n'a été vu qu'en CI.
- **Une erreur de manipulation du suivi** : la guillemet fermante des notes du sprint a été supprimée dans `sprint-status.yaml`. `bin/check-sprint-status` l'a attrapée avant le commit.
- **Frictions d'outillage, comme au sprint 59** : `set --` sous zsh, `sleep` en chaîne bloqué, un verrou git passager. Aucune n'a eu d'effet, mais chacune a coûté un aller-retour.

## Actions (SMART)

| ID | Action | Responsable | Échéance | Mesure |
|----|--------|-------------|----------|--------|
| A-60-1 | Sur une PR, ne rien pousser pendant que le run du rouge tourne ; après le rouge, regrouper le vert et les commits de suivi (preuve, suivi du sprint, stories) en un seul push quand c'est possible | orchestrateur | dès le sprint 61 | aucun run rouge annulé au sprint 61, et au plus un run vert annulé par PR |
| A-60-2 | Un test ajouté avec le vert (garde de configuration, non-régression) a son propre commit rouge, poussé avant le correctif, comme les autres tests (règle 26) | orchestrateur | consigne du sprint 61 | aucun test du sprint 61 sans rouge prouvé en CI |
| A-60-3 | Enquête bornée à 1 h sur l'erreur de segmentation de `sylius-php:8.5-alpine` pendant les fixtures de la démo : reproduire, contourner ou documenter dans `docs/tests-locaux.md` | orchestrateur | sprint 61 | la procédure Docker de la démo exécute de nouveau `PlatformHealthDemoTest` en local, ou la limite est écrite avec sa cause |

## ROTI

**5/5** (Thibaut, 2026-11-28). Actions A-60-1 à A-60-3 approuvées le même jour ; à planifier au sprint 61.
