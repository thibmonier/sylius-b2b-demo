# Rétrospective du sprint 59

**Directive fondamentale** : « Quoi que nous découvrions, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »

Entrée principale : [sprint-review.md](./sprint-review.md). Bilan du sprint :
- 13 points sur 13 « done » en deux jours (2026-11-26 → 2026-11-27) ;
- quatre stories réparties sur 33 dépôts : santé, prédictif, démo et 30 plugins pour US-314 ;
- deux releases (santé v0.5.0, prédictif v0.2.1), posées après la documentation ;
- « Dérive du schéma » vue `ok` en production, ce qui était le point de sortie d'US-318.

Format : ce qui a marché, ce qui a coûté, actions SMART (comme aux sprints 56 à 58).

## Suivi des actions du sprint 58

| ID | Appliquée ? | Preuve |
|----|-------------|--------|
| A-58-1 (CI de `main` suivie après chaque fusion) | Oui | Suivie après #234 à #240. Seul écart : un run annulé après #237, remplacé par celui de #238, qui teste le même `main` et qui est vert. Sur les 30 dépôts d'US-314, la CI de `main` a été vérifiée, et le rouge de bulk-actions a été signalé à Thibaut puis inscrit (US-319) |
| A-58-2 (`proof-check` en CI allégée, `proof.json` livré avec chaque test ajouté) | Oui | #235 ajoute le job ; son premier passage réel, sur #239, est vert. #238 livre son `proof.json` (364 fichiers). Aucun écart du nombre de fichiers de tests sur `main` |
| A-58-3 (procédure Docker des tests de la démo) | Oui | #236, mesurée sur une copie neuve ; deux pièges trouvés et écrits (`serverVersion`, `icu-data-full`). Elle a servi pour #238 : le rouge était lisible du premier coup |
| A-58-4 (cas aux bornes pour les seuils, contradictions tranchées avant le premier test) | Oui | Les codes de sortie d'US-307 (`unknown` → 1, option invalide → 3) ont été tranchés à la planification ; aucun écart découvert à l'implémentation |
| A-58-5 (règle 26 pour les tâches de la démo) | Oui | Les rouges de la démo ont été vus en CI : #234 (runs 45866427853 et 45868284675) et #238 (run 45996438034) |
| A-57-4 (relevé Railway) | En attente | Échéance au 2026-12-01, à la charge de Thibaut |

## Ce qui a bien marché

- **La DoD d'US-318 exigeait la production, pas seulement une CI verte.** Avec une CI verte et les releases publiées, la story aurait pu s'arrêter là. Le passage en production a révélé les 5 instructions de `messenger_messages`, corrigées le jour même (#238).
- **Le test de la démo lit la sonde elle-même**, pas une copie de sa logique. Il a trouvé que la sonde du plugin de santé ne pouvait jamais être `ok` (table des migrations), ce qui a été corrigé en v0.5.0 (#22).
- **La matrice de 10 combinaisons** a encore attrapé ce que le poste ne voit pas : `getSchemaAssetsFilter()` peut renvoyer `null` sous DBAL 3 (Sylius 2.2).
- **Décisions soumises à Thibaut et tranchées vite** :
  - clés étrangères en `ON DELETE CASCADE` ;
  - migration plutôt que filtre, deux fois ;
  - job `proof` non bloquant ;
  - option A pour `unknown`, avec US-320 au backlog.
- **US-314 en lot** : 30 PR `ci:` remises sous la forme d'une liste unique et fusionnées par Thibaut en une passe, avec la CI de `main` vérifiée partout.
- **La nouvelle commande a servi le jour même** : `tmonier:health:check --format=json`, lancée en lecture seule dans la console de Railway, a fourni la preuve de clôture du sprint.

## Ce qui a coûté

- **Un angle mort créé par le test lui-même** : le `schema_filter` de `when@test` cache `messenger_messages`. Ce choix est juste pour la comparaison, mais aucun autre test ne vérifiait la forme de la table. Cinq pourquoi :
  1. Pourquoi la production dérivait-elle encore ? La table avait encore son ancienne forme.
  2. Pourquoi ? `Version20261113100000` n'a rien fait en production.
  3. Pourquoi ? `CREATE TABLE IF NOT EXISTS` passe sans erreur sur une table existante, quelle que soit sa forme.
  4. Pourquoi la CI ne l'a-t-elle pas vu ? Sa base est construite à neuf par les migrations, et la table est exclue de la comparaison en test.
  5. **Cause racine** : l'état historique de la production n'est comparé à rien avant le déploiement.
- **Un faux avertissement de CI** sur `composer.lock` : Composer 2.11 dans l'image, 2.10.3 en CI. Il a fallu un commit pour recalculer le lock.
- **Une copie neuve de la démo dans Docker a donné deux faux rouges** (base inconnue, prix en `€35.76`) avant le premier vrai rouge. Les deux sont désormais documentés.
- **Un test instable dans bulk-actions** : il a fallu une relance pour lire la CI de `main` d'US-314 (US-319, sprint 60).
- **Frictions d'outillage** :
  - guillemets de `railway ssh` ;
  - `set --` sous zsh ;
  - un verrou git passager ;
  - le téléchargement inutile d'une image `mysql:8.4`, arrêté.
  Aucune n'a eu d'effet, mais chacune a coûté un aller-retour.

## Actions (SMART)

| ID | Action | Responsable | Échéance | Mesure |
|----|--------|-------------|----------|--------|
| A-59-1 | À la planification d'une story qui touche au schéma de la démo, relever en lecture seule `doctrine:schema:update --dump-sql` en production (en `www-data`) et l'écrire dans la fiche avant le premier test. Le test de la story couvre aussi l'état de production, pas seulement une base neuve | orchestrateur | planification du sprint 60 | aucune instruction de dérive propre à la production découverte après une release |
| A-59-2 | Une migration de la démo n'utilise `IF NOT EXISTS` que si un test couvre la table déjà présente sous une autre forme ; sinon, elle lit la forme réelle (comme `Version20261127090000`) | orchestrateur | consigne du sprint 60 | aucune migration du sprint 60 sans effet en production |
| A-59-3 | Après chaque déploiement de la démo, `tmonier:health:check --format=json` en lecture seule dans Railway ; le résultat est noté dans la PR de suivi, et tout statut autre que `ok` est signalé à Thibaut avant la PR suivante | orchestrateur | dès le sprint 60 | chaque déploiement du sprint 60 a son relevé |

## ROTI

**5/5** (Thibaut, 2026-11-27). Actions A-59-1 à A-59-3 approuvées le même jour ; à planifier au sprint 60.
