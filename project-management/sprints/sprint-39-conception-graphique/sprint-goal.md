# Sprint 39 : Conception graphique — maquettes du copilote et du réassort, principe écrit et réponse cohérente

> **Planifié le 2026-10-31**, après la clôture du sprint 38 (PR #144). Périmètre décidé par Thibaut le 2026-10-31 : **8 points** ([registre des décisions](./registre-decisions.md)). La décomposition en tâches sera faite au planning. Source de vérité des statuts : `.bmad/sprint-status.yaml` (`sprint-39`).

## Informations

| Attribut | Valeur |
|----------|--------|
| Numéro | 39 |
| Début | 2026-10-31 (planning) |
| Fin | **2026-11-14** (10 jours ouvrés) au plus tard ; clôture dès que le périmètre est vérifié |
| Capacité | **8 points** ; tâches à décomposer |
| Vélocité de référence | S33 13, S34 9, S35 8, S36 11, S37 13, S38 10 |
| Branche | `feature/sprint-39-conception-graphique` (arbre principal, déjà créée ; pas de checkout dans l'arbre partagé) |

Le périmètre retient 8 points : conception graphique du widget du copilote dans Claude Design (US-248, 2 pts), conception graphique du bloc de réassort dans Claude Design (US-250, 2 pts), principe de conception écrit et maquettes montrées dans la démo (US-249, 3 pts), réponse enregistrée du copilote cohérente avec le panier de Claire (US-247, 1 pt). Les actions de rétro du sprint 38 (T-TECH-39-01 à 05) seront intégrées comme tâches transverses. Contraintes machine : 8 Go RAM, disque 18 Go libres le 2026-10-31 ; tests ciblés seulement ; suite complète de la démo par la CI.

## Sprint Goal

> « Répondre aux retours de la revue du sprint 38 sur la conception graphique : maquetter dans Claude Design le widget du copilote et le bloc de réassort, écrire le principe de conception et le montrer dans la démo, et aligner la réponse enregistrée du copilote sur le panier de Claire. »

## Périmètre engagé

Gherkin : au minimum 1 nominal, 2 alternatifs et 2 erreurs par story (US-248 : 1+2+2, US-250 : 1+2+2, US-249 : 1+2+2, US-247 : 1+1+1). « Fiche » = fichier `backlog/user-stories/US-XXX.md`.

| ID | Titre | Pts | MoSCoW | Statut | Notes |
|----|-------|-----|--------|--------|-------|
| [US-248](../../backlog/user-stories/US-248.md) | Copilote d'achat : conception graphique du widget dans Claude Design | 2 | Should | ready-for-dev | Dépend d'US-244 ; bloque US-246 |
| [US-250](../../backlog/user-stories/US-250.md) | Commerce prédictif : conception graphique du bloc de réassort dans Claude Design | 2 | Should | ready-for-dev | Dépend d'US-240 ; bloque US-251 ; cohérence avec US-248 |
| [US-249](../../backlog/user-stories/US-249.md) | Conception graphique dans Claude Design : principe écrit et maquettes montrées dans la démo | 3 | Should | ready-for-dev | Utilise les maquettes existantes ; US-248 ajoutera celle du copilote |
| [US-247](../../backlog/user-stories/US-247.md) | Copilote d'achat : réponse enregistrée cohérente avec le panier de Claire | 1 | Should | ready-for-dev | Dépend d'US-244 ; correctif rapide |

Total engagé : **8 points**.

- **US-248** crée la page « Copilote d'achat » dans le canevas Claude Design unique (`G2StRQigRKD6SqNRoyHpkW`) avec le tableau de bord B2B de Claire et le widget, en desktop et mobile (360 px), tous les états (vide, question, réponse en cours, réponse, repli, erreur), contrastes AAA mesurés, et la note de passation pour US-246 distinguant les styles du plugin et ceux du thème.
- **US-250** crée la page « Réassort » dans le même canevas avec le tableau de bord B2B et le bloc d'alertes, en desktop et mobile, tous les états (aucune alerte, une alerte, plusieurs dont une en retard), cohérente avec la maquette du copilote (jetons, espacements, en-têtes), contrastes AAA mesurés (retard non signalé par la seule couleur), et la note de passation pour US-251.
- **US-249** écrit le principe de conception graphique dans `docs/conception-graphique.md` (canevas unique, validation avant Twig, export des images), ajoute la ligne à la Definition of Done des stories d'écran, crée la section « De la maquette à l'écran » sur la page Architecture (images optimisées avec textes alternatifs, lien public si possible), test fonctionnel rouge prouvé, audit pa11y AAA sans erreur.
- **US-247** aligne la réponse enregistrée du copilote (« état de mon panier ») sur les fixtures du panier de Claire (47 articles, 1 017,35 €), en FR et EN, avec un test qui compare la réponse au panier réel et échoue si les deux divergent.
- **Tâches techniques** (actions de rétro du sprint 38) : test en application hôte avant chaque tag de plugin (T-TECH-39-01), règle 49 d'un worker par dépôt (T-TECH-39-02), variables de test du parc en `<server>` (T-TECH-39-03), règle 50 de vérification avant création de fichier (T-TECH-39-04), `RunTestsLockTest` déterministe (T-TECH-39-05).

**Hors sprint** : US-246 (mise en forme du widget, 2 pts) et US-251 (mise en forme du bloc, 2 pts) exclues volontairement car elles nécessitent la validation de Thibaut des maquettes puis son GO pour les releases, deux portes humaines qu'un run autonome ne peut pas franchir. Elles passeront au sprint 40 dès les maquettes validées. **Réserve** : US-252 (couverture de code, 5 pts) à tirer seulement si le périmètre est fini en avance.

## Décisions de Thibaut (2026-10-31, registre)

1. Périmètre : US-248 (2), US-250 (2), US-249 (3), US-247 (1), soit 8 points, plus les actions de rétro T-TECH-39-01 à 05.
2. Contraintes machine : 8 Go RAM, disque 18 Go libres le 2026-10-31 ; tests ciblés seulement ; suite complète de la démo par la CI.
3. US-246 et US-251 exclues volontairement (portes humaines).
4. Réserve : US-252 (5 points) si fini en avance.
5. US-248 et US-250 par le même worker, dans l'ordre US-248 puis US-250, pour partager les jetons de design (critère d'acceptation d'US-250).
6. Toutes les maquettes dans le canevas unique `G2StRQigRKD6SqNRoyHpkW` (pas de nouveau projet).

## Estimation du sprint

À compléter après décomposition en tâches.

## Politique Sylius Plus

Ton neutre partout. Aucun recoupement connu des plugins copilote, commerce prédictif et parc avec Sylius Plus. En cas de recoupement avéré pendant le sprint, le plugin reste public mais n'est pas promu (politique du 2026-10-14). Aucun texte, titre, description, message de commit ou description de PR ne compare avec l'offre de l'éditeur Sylius ni ne la mentionne. Aucun détail de sécurité (garde-fous, seuils, routes d'admin) dans les dépôts publics (démo et plugins). Les descriptions de PR restent factuelles et neutres.

## Checklist de démarrage (2026-10-31)

### Avant le Sprint Planning

- [x] Sprint précédent terminé : code du sprint 38 fusionné (PR #144), US-242, US-243, US-244, US-245 en done
- [x] Actions de la rétrospective du sprint 38 reprises :
  - action 1 : T-TECH-39-01 (test en application hôte avant chaque tag de plugin) ;
  - action 2 : T-TECH-39-02 (règle 49 : un worker par dépôt pour stories multi-dépôts) ;
  - action 3 : T-TECH-39-03 (variables de test du parc en `<server>`) ;
  - action 4 : T-TECH-39-04 (règle 50 : vérifier existence avant création de fichier) ;
  - action 5 : T-TECH-39-05 (rendre `RunTestsLockTest` déterministe).
- [x] Backlog priorisé : périmètre décidé par Thibaut le 2026-10-31
- [x] Stories estimées et « Ready » : voir la Definition of Ready ci-dessous
- [ ] Capacité calculée : à calculer après décomposition
- [x] Fiches US-248, US-250, US-249, US-247 à jour (en-tête « Sprint 39 »)
- [x] Branche `feature/sprint-39-conception-graphique` créée (arbre principal)
- [ ] Disque et mémoire : `df -h .`, `bin/check-disk`, mémoire libre et swap relevés avant le premier lot

### Après le Sprint Planning

- [ ] Sprint backlog visible : [task-board.md](./task-board.md), [tasks/README.md](./tasks/README.md)
- [ ] Dépendances : US-248 → US-250 (même worker), US-249 utilise les maquettes existantes
- [ ] Consigne de worker et checklist du sprint : [consigne-worker.md](./consigne-worker.md)
- [ ] Tags demandés quand les plugins sont fusionnés (copilote v0.1.2 après US-247)

## Definition of Ready (contrôle du 2026-10-31)

| US | Fiche | Gherkin | Prérequis | État |
|----|-------|---------|-----------|------|
| US-248 | oui | oui (1+2+2) | US-244 fusionnée, copilote installé dans la démo | prête (dépendance US-244) |
| US-250 | oui | oui (1+2+2) | US-240 fusionnée, alertes de réassort dans la démo | prête (dépendance US-240) |
| US-249 | oui | oui (1+2+2) | maquettes existantes dans le canevas `G2StRQigRKD6SqNRoyHpkW` | prête |
| US-247 | oui | oui (1+1+1) | US-244 fusionnée, copilote installé dans la démo | prête (dépendance US-244) |

## Definition of Done (rappel)

- [ ] Chaque scénario Gherkin relié à un test ou à une preuve dans le fichier de tâches
- [ ] Stories de code (US-247) : commit de tests rouges cité dans la PR, **avec l'échec observé de chaque test cité dans le message du commit** (règle 27)
- [ ] US-248 : page « Copilote d'achat » ajoutée au canevas `G2StRQigRKD6SqNRoyHpkW`, desktop et mobile, tous les états ; maquette validée par Thibaut ; note de passation pour US-246 ; critères d'acceptation d'US-246 mis à jour
- [ ] US-250 : page « Réassort » ajoutée au canevas `G2StRQigRKD6SqNRoyHpkW`, desktop et mobile, tous les états ; cohérence avec US-248 (jetons, espacements) ; maquette validée par Thibaut ; note de passation pour US-251
- [ ] US-249 : `docs/conception-graphique.md` écrit ; ligne ajoutée au gabarit de DoD ; section « De la maquette à l'écran » sur la page Architecture (FR et EN) ; test fonctionnel rouge prouvé ; pa11y AAA sans erreur ; page Preuve à jour
- [ ] US-247 : réponse enregistrée alignée sur les fixtures (FR et EN) ; test comparant la réponse au panier, rouge prouvé avec l'ancienne réponse ; capture du README refaite si la réponse y figure ; CI verte
- [ ] **Tout état écrit hors base** (règle 16) : aucune variable d'environnement nouvelle dans la démo ce sprint

## Cérémonies

| Cérémonie | Date | Heure | Durée | Participants |
|-----------|------|-------|-------|--------------|
| Sprint Planning Part 1 (QUOI) | 2026-10-31 | 09:00 | 2h | PO, Scrum Master, équipe |
| Sprint Planning Part 2 (COMMENT) | 2026-10-31 | 14:00 | 2h | Équipe |
| Daily Scrum | Chaque jour | 09:30 | 15min | Équipe |
| Affinage du backlog | 2026-11-07 | 14:00 | 1h | PO, Scrum Master, équipe |
| Sprint Review | 2026-11-14 | 14:00 | 2h | PO, Scrum Master, équipe, parties prenantes |
| Rétrospective | 2026-11-14 | 16:30 | 1h30 | Scrum Master, équipe |

## Directive Fondamentale de la Rétrospective

> « Quelles que soient les choses que nous découvrirons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. »
> — Norman Kerth
