# Tâches : US-184 : Statuts de transmission au back-office et relance d'un envoi en erreur

## Informations US

- **Epic** : EPIC-14
- **Persona** : Thibaut ; lead développeur d'une agence, puis l'administratrice de la boutique
- **Story Points** : 3 (Should, sacrifice : **n°2**)
- **Sprint** : sprint-26-vitrine-et-flux
- **Worker proposé** : w183 (après US-183)
- **Story** : [US-184](../../../backlog/user-stories/US-184.md)

## Résumé de la US

**En tant qu'**administratrice de la boutique  
**Je veux** voir le statut de transmission de chaque facture et relancer un envoi en erreur  
**Afin de** corriger un incident sans toucher à la base

## Règles et contraintes

- Deux dépôts : **plugin `sylius-einvoice-transmission-plugin` v0.2.0** (cas d'usage de relance, réouverture du journal) puis **démo** (écran d'administration) ; l'écran réutilisable dans le plugin est reporté (YAGNI).
- **Tag et release `v0.2.0` seulement sur GO de Thibaut** (T-184-06) ; concordance du CHANGELOG, du tag et de la contrainte `^0.2` de la démo contrôlée avant le tag.
- Relance du seul statut `error`, réservée aux administrateurs, avec jeton CSRF ; aucun second envoi pour une facture déjà déposée.
- Dépend d'US-183 (démo avec le plugin et la PA fictive) ; s'il faut sacrifier, US-184 part après US-185.
- **Budget disque** : `vendor/` du plugin d'environ 400 Mo et base d'essai (MySQL de la démo, règle 12), supprimés en fin de lot, espace libre relevé.
- Tests de la démo uniquement par `bin/run-tests` ; commits de tests rouges seuls (plugin et démo) ; règle 10.
- Règles du sprint, consigne de worker et actions de la rétro du sprint 25 : voir [README.md](./README.md).

## Lieu de vérification (action 4 de la rétro du sprint 23)

Pas de rendu public (administration). Preuves : tests du plugin et de la démo, CI du plugin sur sa matrice après push (si GO).

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- Dépôt `sylius-einvoice-transmission-plugin` : `src/Journal/`, `src/Transmission/`, `tests/`, `README*.md`, `CHANGELOG.md`
- `src/Demo/Einvoice/` ou `src/Demo/Admin/`, `templates/admin/`, `config/packages/` (hooks du menu et de l'écran), `translations/`
- `composer.json`, `composer.lock` (contrainte `^0.2`)
- `config/demo/proof.json`, `tests/` de la démo

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-184-01 | [DOC] | Cadrage : journal du plugin (`open`, `record`), règle de relance (statut `error` seul), écran du modèle « Webhooks reçus » (`src/Demo/Admin`, hooks), version 0.2.0 et contrainte `^0.2` | 0,5 h | T-183-10 | Claude | ✅ |
| T-184-02 | [TEST] | Tests rouges du plugin, commités seuls et cités dans la PR : réouverture d'une ligne en erreur, rappel de l'adaptateur une fois, refus d'un statut non relançable, aucune ligne en double | 0,75 h | T-184-01 | Claude | ✅ |
| T-184-03 | [BE] | Plugin : cas d'usage de relance et réouverture du journal ; README FR et EN, CHANGELOG ; ECS, PHPStan max, Rector dans Docker (budget disque : `vendor/` supprimé en fin de lot) | 1 h | T-184-02 | Claude | ✅ |
| T-184-04 | [TEST] | Tests rouges de la démo, commités seuls : liste des statuts, relance, bouton absent hors erreur, refus d'une relance non autorisée ou sans jeton, accès administrateur, FR et EN | 0,75 h | T-184-03 | Claude | ✅ |
| T-184-05 | [FE-WEB] | Démo : contrôleur et écran d'administration (liste, motif, dates, bandeau « simulés », bouton « Relancer » avec jeton), entrée de menu, scénario « erreur puis succès » dans les données | 1,25 h | T-184-04 | Claude | ✅ |
| T-184-06 | [OPS] | 📅 Thibaut donne son GO pour pousser, fusionner le plugin puis poser le tag et la release `v0.2.0` | 0,25 h | T-184-03 | Thibaut | ✅ |
| T-184-07 | [OPS] | Après GO : push, CI verte sur toute la matrice (run terminé), concordance (CHANGELOG, tag, contrainte), tag et release ; démo en `^0.2` ; Packagist mis à jour contrôlé | 0,75 h | T-184-06 | Claude | ✅ |
| T-184-08 | [TEST] | Suite ciblée de la démo (`WORKER_ID=w184 …`), ECS, PHPStan max, Rector ; revue et table « scénario → test ou preuve » remplie | 0,75 h | T-184-05, T-184-07 | Claude | ✅ |

**Total estimé : 6 h** (8 tâches, dont 0,25 h de Thibaut (1 tâche) ; 5,75 h pour Claude et l'orchestrateur).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, run de CI). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Liste des statuts (nominal) | `EinvoiceTransmissionsAdminTest::testTheListShowsEveryInvoiceWithItsStatusReasonAndDateAndTheSimulatedBanner` (FR et EN) ; `testTheMenuOfTheIntegrationsGroupLinksToTheScreen` |
| Relance d'un envoi en erreur (alternatif) | `EinvoiceTransmissionsAdminTest::testRelaunchingASendInErrorDepositsItOnTheSameLineAndConfirms` ; plugin : `RelaunchTransmissionHandlerTest::testASendInErrorIsRelaunchedOnTheSameLineAndTheAdapterIsCalledOnce`, `TransmissionReopenTest::testALineInErrorIsReopenedAsNotTransmittedWithoutANewLine` ; `FictitiousPlatformOutageTest` |
| Statut non relançable (alternatif) | `EinvoiceTransmissionsAdminTest::testOnlyTheRowsInErrorOfferTheRelaunchButton` (FR et EN) |
| Relance d'une facture non relançable (erreur) | `EinvoiceTransmissionsAdminTest::testRelaunchingAnInvoiceThatIsNotInErrorIsRefusedWithAClearMessage` ; plugin : `RelaunchTransmissionHandlerTest::testOnlyTheErrorStatusIsRelaunchedAndNothingIsSent`, `testASecondRelaunchAfterTheFirstOneSucceededSendsNothingMore` |
| Accès non autorisé ou jeton absent (erreur) | `EinvoiceTransmissionsAdminTest::testARelaunchWithoutAValidTokenIsRefusedAndNothingIsSent`, `testAVisitorWhoIsNotAnAdministratorCannotSeeOrRelaunch` ; `DemoAdminWritePolicyTest` (route autorisée au compte restreint, POST seulement) |
