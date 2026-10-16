# Tâches — US-006 : Spike : mode worker FrankenPHP

## Informations US

- **Epic** : EPIC-01
- **Persona** : Thibaut
- **Story Points** : 2
- **Sprint** : sprint-9-reports
- **Dépendances** : US-002

## Résumé

En tant que Thibaut, je veux savoir si le mode worker FrankenPHP est sûr avec Sylius et les plugins, afin de gagner en performance sans fuite d'état.

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Statut |
|----|------|-------|------------|-----------|--------|
| T-006-01 | [BE] | Activer le mode worker en local (Caddyfile, variables FrankenPHP) derrière un drapeau | 1.5h | — | 🔲 |
| T-006-02 | [TEST] | Test de fuite d'état en mode worker : requêtes alternées entre comptes, canaux, locales et badges | 2.5h | T-006-01 | 🔲 |
| T-006-03 | [BE] | Mesurer le gain : latence p50/p95 sur 5 pages types, classique contre worker | 1.5h | T-006-01 | 🔲 |
| T-006-04 | [DOC] | ADR-0013 : mode retenu (worker ou classique) et inventaire des services à état | 1h | T-006-02, T-006-03 | 🔲 |
| T-006-05 | [REV] | Revue du spike (méthode de mesure, couverture du test de fuite) | 0.5h | T-006-04 | 🔲 |

**Total estimé : 7h**

---

## Détail des tâches

### T-006-01 — Activer le mode worker en local (Caddyfile, variables FrankenPHP) derrière un drapeau

- **Type** : [BE]
- **Estimation** : 1.5h
- **Dépend de** : —

**Description** :
Profil `worker` (`FRANKENPHP_WORKER=1`) sans changer le mode par défaut. Aucun build d'image sur le poste (disque) : image existante ou CI.

**Fichiers (indicatifs)** :
- `docker/Caddyfile`
- `docker/entrypoint.sh`
- `compose.yaml`

**Critères de validation** :
- [ ] Le démarrage en mode worker est reproductible par une seule variable
- [ ] Le mode classique reste le défaut

---

### T-006-02 — Test de fuite d'état en mode worker : requêtes alternées entre comptes, canaux, locales et badges

- **Type** : [TEST]
- **Estimation** : 2.5h
- **Dépend de** : T-006-01

**Description** :
Script de fumée enchaînant sur le même worker : visiteur A (panier, canal, locale en_US, cookie `demo_badges`), visiteur B anonyme, compte B2B. Détecte toute donnée de A visible pour B (panier, canal, utilisateur, locale, badges, jeton CSRF).

**Fichiers (indicatifs)** :
- `tests/Smoke/WorkerStateLeakTest.php (ou script bin/)`

**Critères de validation** :
- [ ] Aucune donnée d'une requête visible dans la suivante
- [ ] Le test échoue si on injecte volontairement un service à état (témoin)

---

### T-006-03 — Mesurer le gain : latence p50/p95 sur 5 pages types, classique contre worker

- **Type** : [BE]
- **Estimation** : 1.5h
- **Dépend de** : T-006-01

**Description** :
Mesure reproductible (même machine, même jeu de données, échauffement exclu) sur accueil, liste, fiche, panier, compte.

**Fichiers (indicatifs)** :
- `bin/bench-worker.sh`
- `var/bench/ (non versionné)`

**Critères de validation** :
- [ ] Tableau p50/p95 classique/worker pour 5 pages
- [ ] Méthode documentée et rejouable

---

### T-006-04 — ADR-0013 : mode retenu (worker ou classique) et inventaire des services à état

- **Type** : [DOC]
- **Estimation** : 1h
- **Dépend de** : T-006-02, T-006-03

**Description** :
Décision argumentée par le gain mesuré et le résultat du test de fuite ; liste des services à état éventuels ; repli : mode classique.

**Fichiers (indicatifs)** :
- `docs/adr/0013-mode-worker-frankenphp.md`

**Critères de validation** :
- [ ] ADR au statut Accepté avec le mode retenu
- [ ] Gain chiffré cité

---

### T-006-05 — Revue du spike (méthode de mesure, couverture du test de fuite)

- **Type** : [REV]
- **Estimation** : 0.5h
- **Dépend de** : T-006-04

**Description** :
Relecture indépendante.

**Fichiers (indicatifs)** :
- `docs/adr/0013-mode-worker-frankenphp.md`

**Critères de validation** :
- [ ] Méthode de mesure jugée valide
- [ ] Cas de fuite manquants consignés

---

## Graphe de dépendances

```mermaid
graph TD
    T00601["T-006-01 [BE] 1.5h"]
    T00602["T-006-02 [TEST] 2.5h"]
    T00603["T-006-03 [BE] 1.5h"]
    T00604["T-006-04 [DOC] 1h"]
    T00605["T-006-05 [REV] 0.5h"]
    T00601 --> T00602
    T00601 --> T00603
    T00602 --> T00604
    T00603 --> T00604
    T00604 --> T00605
```

## Résumé

| Couche | Nb tâches | Heures |
|--------|-----------|--------|
| [BE] | 2 | 3h |
| [TEST] | 1 | 2.5h |
| [DOC] | 1 | 1h |
| [REV] | 1 | 0.5h |
| **TOTAL** | **5** | **7h** |
