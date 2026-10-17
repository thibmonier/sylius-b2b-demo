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
- Garde-fous : limiteurs par IP et usage unique, comme le cXML. Décision du Tech Lead (2026-10-17) : le retour OCI ne crée pas de commande, donc le plafond quotidien des commandes (`SimulatorOrderGate`) ne s'applique pas à OCI.
- Le simulateur reste interne : il ne fait aucun appel sortant vers un hôte arbitraire (`HOOK_URL` restreinte aux domaines de retour autorisés).
- Contrat de fin de worker et isolation par chemins explicites : voir [README.md](./README.md).

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Statut |
|----|------|-------|------------|-----------|--------|
| T-053-01 | [DOC] | Analyse : contrat OCI du plugin (entrée `POST /punchout/oci/<code>`, champs, `HOOK_URL`, `oci_allow_get`, `VALIDATE`/`DETAIL`) et simulateur actuel ; plan (plafond quotidien non applicable à OCI, décision actée) | 1h | — | ✅ |
| T-053-02 | [TEST] | Tests rouges : constructeur de l'entrée OCI, lecture des champs `NEW_ITEM-*`, garde-fous | 1.5h | T-053-01 | ✅ |
| T-053-03 | [BE] | Formulaire `HOOK_URL` et entrée OCI (`POST /punchout/oci/<code>`) depuis le simulateur | 2.5h | T-053-02 | ✅ |
| T-053-04 | [BE] | Retour OCI : réception du `POST` `NEW_ITEM-*` du plugin, lecture et affichage des lignes reçues | 2h | T-053-03 | ✅ |
| T-053-05 | [BE] | Garde-fous OCI : limiteurs, usage unique du retour | 1.5h | T-053-04 | ✅ |
| T-053-06 | [TEST] | Tests fonctionnels du parcours OCI (nominal, 2 alternatifs, 2 erreurs) | 2h | T-053-05 | ✅ |
| T-053-07 | [DOC] | Documentation du simulateur : parcours OCI, garde-fous ; la mention « OCI non simulé » retirée | 1h | T-053-06 | ✅ |
| T-053-08 | [REV] | Revue | 1h | T-053-07 | 🔲 |

**Total estimé : 12.5h** (8 tâches).

---

## Détail des tâches

### T-053-01 — Analyse du contrat OCI

- **Type** : [DOC]
- **Estimation** : 1h
- **Fichiers lus (indicatifs)** : `../sylius-punchout-cxml-plugin/README.md` (section SAP OCI), `src/Demo/Controller/PunchOutSimulatorController.php`, `src/Demo/PunchOutSimulator/`, `src/Demo/PunchOutSimulator/SimulatorOrderGate.php`, `config/packages/demo_hardening.yaml`, `project-management/sprints/sprint-5-*/handoff.md` (T-027-02)

**Description** : relever l'authentification OCI (identifiants de l'acheteur OCI, nom d'utilisateur), les champs de l'entrée, l'effet de `HOOK_URL` (doit appartenir aux domaines de retour de l'acheteur) et les champs `NEW_ITEM-*` renvoyés. Décision actée (Tech Lead, 2026-10-17) : aucune commande n'est créée après un retour OCI, le plafond quotidien des commandes ne s'applique donc pas à OCI ; les limiteurs par IP et l'usage unique de la session s'appliquent. Vérifier que la fixture `PunchoutDemoFixture` contient un acheteur OCI ; sinon, l'ajouter dans la tâche T-053-03.

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

**Description** : limiteurs par IP pour l'entrée et le retour OCI (même variables d'environnement que le cXML, ou `DEMO_RATE_LIMIT_PUNCHOUT_OCI` si la décision de T-053-01 l'impose), retour à usage unique (état signé et nonce comme `SignedState`), pas de plafond quotidien (le retour OCI ne crée pas de commande, décision du Tech Lead). Préflight et gardes inchangés (`DEMO_PUNCHOUT_PUBLIC_ENDPOINT` reste à `0`).

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

---

## Notes de réalisation (worker w12-oci)

- **Contrat relevé (T-053-01)** : l'entrée OCI pose la session dans le navigateur (cookie + redirection 303), donc l'appel serveur à serveur du simulateur relaie le cookie et le `Location` (chemin local seulement) au visiteur. Le buyer OCI est un buyer à part (`acme-punchout-oci`, protocole `oci`, `USERNAME` `acme-oci-demo`, secret de démo publié) : un buyer OCI ne s'authentifie jamais sur cXML. `POST /punchout/oci/<code>` est ajouté au garde `PunchOutEndpointGuard` (sinon le secret publié ouvrirait une session publique). `HOOK_URL` : mêmes règles que le plugin (`ReturnUrlValidator`), vérifiées avant tout appel ; l'état signé (nonce + `oci`) voyage dans sa requête, le plugin la conserve et y poste le panier.
- **Garde-fous (T-053-05)** : limiteur de l'entrée = `DEMO_RATE_LIMIT_PUNCHOUT_START` ; retour = limiteur `demo_punchout_oci_return` (même variable, seau séparé : pas de nouvelle variable) ; retour à usage unique (`OciReturnGate`, nonce) ; pas de plafond quotidien (décision du Tech Lead).
- **Choix assumé** : contrairement au cXML (`ReturnAuthenticator`, journal du plugin), l'authenticité du contenu du retour OCI n'est pas prouvée octet par octet : le plugin ne journalise pas le corps posté de façon rejouable, et le retour OCI ne crée rien (affichage seul, échappé par Twig, champs bornés et typés). Le nonce signé prouve que le retour répond à une entrée du simulateur, une seule fois.
- **Écart de contrat** : aucun, le plugin n'est pas modifié.
