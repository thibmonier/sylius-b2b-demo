# Tâches — US-053 : Simulateur PunchOut OCI dans la démo

## Informations US

- **Epic** : EPIC-07
- **Persona** : Thibaut
- **Story Points** : 4
- **Sprint** : sprint-12-ouverture-et-plugins
- **Dépendances** : US-027 (simulateur cXML), US-036 (durcissement) ; `tmonier/sylius-punchout-cxml-plugin` ≥ 1.1

## Résumé

En tant que Thibaut, je veux que le simulateur PunchOut de la démo sache aussi jouer un acheteur SAP OCI, afin de montrer les deux protocoles du plugin avec les mêmes garde-fous que le cXML.

## Règles

- Chaque correctif commence par un test qui échoue avant correction.
- Le plugin n'est pas modifié : un écart de contrat est tracé (backlog), pas contourné dans la démo.
- Mêmes garde-fous que le cXML : limiteurs par IP, usage unique, plafond quotidien (`SimulatorOrderGate`).
- Le simulateur reste interne : il ne fait aucun appel sortant vers un hôte arbitraire (`HOOK_URL` restreinte aux domaines de retour autorisés).
- Contrat de fin de worker et isolation par chemins explicites : voir [README.md](./README.md).

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Statut |
|----|------|-------|------------|-----------|--------|
| T-053-01 | [DOC] | Analyse : contrat OCI du plugin (entrée `POST /punchout/oci/<code>`, champs, `HOOK_URL`, `oci_allow_get`, `VALIDATE`/`DETAIL`) et simulateur actuel ; plan et décision sur le plafond quotidien | 1h | — | 🔲 |
| T-053-02 | [TEST] | Tests rouges : constructeur de l'entrée OCI, lecture des champs `NEW_ITEM-*`, garde-fous | 1.5h | T-053-01 | 🔲 |
| T-053-03 | [BE] | Formulaire `HOOK_URL` et entrée OCI (`POST /punchout/oci/<code>`) depuis le simulateur | 2.5h | T-053-02 | 🔲 |
| T-053-04 | [BE] | Retour OCI : réception du `POST` `NEW_ITEM-*` du plugin, lecture et affichage des lignes reçues | 2h | T-053-03 | 🔲 |
| T-053-05 | [BE] | Garde-fous OCI : limiteurs, usage unique du retour, plafond quotidien | 1.5h | T-053-04 | 🔲 |
| T-053-06 | [TEST] | Tests fonctionnels du parcours OCI (nominal, 2 alternatifs, 2 erreurs) | 2h | T-053-05 | 🔲 |
| T-053-07 | [DOC] | Documentation du simulateur : parcours OCI, garde-fous ; la mention « OCI non simulé » retirée | 1h | T-053-06 | 🔲 |
| T-053-08 | [REV] | Revue | 1h | T-053-07 | 🔲 |

**Total estimé : 12.5h** (8 tâches).

---

## Détail des tâches

### T-053-01 — Analyse du contrat OCI

- **Type** : [DOC]
- **Estimation** : 1h
- **Fichiers lus (indicatifs)** : `../sylius-punchout-cxml-plugin/README.md` (section SAP OCI), `src/Demo/Controller/PunchOutSimulatorController.php`, `src/Demo/PunchOutSimulator/`, `src/Demo/PunchOutSimulator/SimulatorOrderGate.php`, `config/packages/demo_hardening.yaml`, `project-management/sprints/sprint-5-*/handoff.md` (T-027-02)

**Description** : relever l'authentification OCI (identifiants de l'acheteur OCI, nom d'utilisateur), les champs de l'entrée, l'effet de `HOOK_URL` (doit appartenir aux domaines de retour de l'acheteur) et les champs `NEW_ITEM-*` renvoyés. Décider avec l'utilisateur si le plafond quotidien s'applique aux entrées OCI (aucune commande n'est créée par le simulateur après un retour OCI) ; par défaut : un plafond quotidien d'entrées OCI séparé du plafond de commandes. Vérifier que la fixture `PunchoutDemoFixture` contient un acheteur OCI ; sinon, l'ajouter dans la tâche T-053-03.

### T-053-02 — Tests rouges

- **Type** : [TEST]
- **Estimation** : 1.5h
- **Fichiers (indicatifs)** : `tests/Unit/Demo/PunchOutSimulator/`

**Description** : un constructeur d'entrée OCI (champs `username`, `password`, `HOOK_URL`), un lecteur des champs `NEW_ITEM-DESCRIPTION[n]`, `NEW_ITEM-QUANTITY[n]`, `NEW_ITEM-PRICE[n]`, `NEW_ITEM-VENDORMAT[n]`, un cas d'usage unique et un cas de plafond. Rouge avant T-053-03.

### T-053-03 — Formulaire `HOOK_URL` et entrée OCI

- **Type** : [BE]
- **Estimation** : 2.5h
- **Fichiers (indicatifs)** : `src/Demo/Controller/PunchOutSimulatorController.php`, `src/Demo/PunchOutSimulator/`, `templates/demo/punchout_simulator/`, `translations/`

**Description** : le simulateur propose un choix de protocole (cXML ou OCI). En OCI, un formulaire pré-rempli avec la `HOOK_URL` du simulateur lui-même (domaine de retour autorisé) ; l'entrée est un `POST` serveur à serveur vers `/punchout/oci/<code>` avec les identifiants de la fixture, sans exposer le mot de passe dans une URL (`oci_allow_get` reste à `false`). Libellés FR et EN.

### T-053-04 — Retour OCI

- **Type** : [BE]
- **Estimation** : 2h

**Description** : route de retour qui reçoit le `POST` des champs `NEW_ITEM-*` du plugin, les convertit en `ReceivedCart` et affiche les lignes comme pour le cXML (aucun nouveau modèle). Un panier vide est affiché sans erreur.

### T-053-05 — Garde-fous OCI

- **Type** : [BE]
- **Estimation** : 1.5h
- **Fichiers (indicatifs)** : `config/packages/demo_hardening.yaml`, `src/Demo/PunchOutSimulator/SimulatorOrderGate.php`

**Description** : limiteurs par IP pour l'entrée et le retour OCI (même variables d'environnement que le cXML, ou `DEMO_RATE_LIMIT_PUNCHOUT_OCI` si la décision de T-053-01 l'impose), retour à usage unique (état signé et nonce comme `SignedState`), plafond quotidien appliqué selon la décision de T-053-01. Préflight et gardes inchangés (`DEMO_PUNCHOUT_PUBLIC_ENDPOINT` reste à `0`).

### T-053-06 — Tests fonctionnels

- **Type** : [TEST]
- **Estimation** : 2h
- **Fichiers (indicatifs)** : `tests/Smoke/PunchOutSimulatorSmokeTest.php` (ou nouveau test OCI), `tests/Unit/Demo/PunchOutSimulator/`

**Description** : les 5 scénarios Gherkin d'US-053, HTML servi ; non-régression du parcours cXML.

### T-053-07 — Documentation

- **Type** : [DOC]
- **Estimation** : 1h

**Description** : décrire le parcours OCI, les garde-fous et les variables dans la documentation du simulateur (`docs/`, section PunchOut) ; retirer la limite « OCI non simulé » du handoff du sprint 5 (renvoi) et du catalogue des plugins si elle y figure.

### T-053-08 — Revue

- **Type** : [REV]
- **Estimation** : 1h

**Description** : relecture indépendante (SSRF sur `HOOK_URL`, secrets absents, rejeu, plafond, non-régression cXML) ; 12 tours minimum pour le relecteur, rapport exigé.
