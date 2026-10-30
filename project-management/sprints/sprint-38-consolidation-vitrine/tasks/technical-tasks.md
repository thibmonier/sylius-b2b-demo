# Tâches transverses : Sprint 038 (actions de la rétro du sprint 37)

> Actions de la rétrospective du sprint 37 (lignes ~163-209 de `sprint-37-copilote-et-passeport/sprint-retro.md`). Ces tâches sont hors points mais comptent dans la capacité (environ 6 h).

## Vue d'ensemble

| ID | Type | Tâche | Estimation | Dépend de | Statut |
|----|------|-------|------------|-----------|--------|
| T-TECH-38-01 | [DOC] | Gabarit de test fonctionnel partagé (locale, firewall, canal) | 1 h | - | 🔲 |
| T-TECH-38-02 | [DOC] | Règle 46 : tests en local obligatoires avant tout push, cités dans le rapport | 0,5 h | - | 🔲 |
| T-TECH-38-03 | [OPS] | TaskStop automatique de chaque worker dès sa passation confirmée | 1 h | - | 🔲 |
| T-TECH-38-04 | [TEST] | Suite complète ou pré-contrôle avant PR de fin de sprint (règle) | 0,75 h | - | 🔲 |
| T-TECH-38-05 | [DOC] | Gabarit de test d'installation vérifiant kernel.bundles | 1 h | - | 🔲 |

**Total estimé** : 4,25 h

---

## Détail des tâches

### T-TECH-38-01 : Gabarit de test fonctionnel partagé
- **Type** : [DOC]
- **Estimation** : 1 h
- **Dépend de** : -

**Description** :
Créer `docs/test-functional-template.md` avec un gabarit de test fonctionnel réutilisable : (1) création de produit avec locale (évite "No translation found for product") ; (2) client/admin connecté sur le bon firewall (`admin` ou `shop`, jamais `main`) ; (3) canal complet configuré (devise, locale, pays, zones). Le gabarit est copié dans chaque nouveau plugin dès le premier test fonctionnel. Ce gabarit évite les rouges invalides de setup constatés au sprint 37.

**Fichiers** :
- `docs/test-functional-template.md` (nouveau gabarit)

**Critères** :
- [ ] Gabarit créé dans `docs/test-functional-template.md`
- [ ] Section « Création de produit avec locale »
- [ ] Section « Client/admin connecté sur le bon firewall »
- [ ] Section « Canal complet configuré »
- [ ] Exemples de code PHP pour chaque section
- [ ] Au sprint 38, 0 rouge invalide sur locale/firewall/canal

---

### T-TECH-38-02 : Règle 46 — tests en local avant tout push
- **Type** : [DOC]
- **Estimation** : 0,5 h
- **Dépend de** : -

**Description** :
Nouvelle règle 46 dans `consigne-worker.md` : dès qu'une base de test est disponible (après le premier test d'installation ou le premier test fonctionnel), le worker lance `bin/run-tests` en local et cite la ligne "Tests: N" dans son rapport **avant le premier push**. Si les tests échouent en local, le worker corrige avant de pousser. Cette règle évite les rouges invalides de setup (multiples runs de 5 à 10 minutes perdus au sprint 37).

**Fichiers** :
- `project-management/sprints/sprint-38-consolidation-vitrine/consigne-worker.md` (règle 46)

**Critères** :
- [ ] Règle 46 écrite dans `consigne-worker.md`
- [ ] Obligation de lancer `bin/run-tests` en local avant le premier push
- [ ] Obligation de citer "Tests: N" dans le rapport
- [ ] Au sprint 38, 0 push sans tests en local cités dans le rapport
- [ ] Au sprint 38, 0 rouge invalide sur setup

---

### T-TECH-38-03 : TaskStop automatique après passation
- **Type** : [OPS]
- **Estimation** : 1 h
- **Dépend de** : -

**Description** :
Chaque worker est arrêté par TaskStop dès sa passation confirmée par l'orchestrateur. La passation inclut : (1) rapport de fin du worker ; (2) confirmation de l'orchestrateur ; (3) TaskStop du worker sortant ; (4) lancement du worker entrant. Aucun worker ne reste actif après sa passation. Cette pratique évite les commits dans l'arbre d'un worker par le worker précédent (w211d au sprint 37).

**Fichiers** :
- `project-management/sprints/sprint-38-consolidation-vitrine/consigne-worker.md` (protocole de passation)

**Critères** :
- [ ] Protocole de passation écrit dans `consigne-worker.md`
- [ ] Section « TaskStop automatique après passation »
- [ ] Au sprint 38, 0 worker actif après passation
- [ ] Chaque passation inclut un TaskStop du worker sortant avant le lancement du worker entrant

---

### T-TECH-38-04 : Suite complète ou pré-contrôle avant PR de fin de sprint
- **Type** : [TEST]
- **Estimation** : 0,75 h
- **Dépend de** : -

**Description** :
Avant d'ouvrir la PR de fin de sprint, l'orchestrateur lance soit la suite complète de la démo (`bin/run-tests --testsuite=all`), soit le pré-contrôle du catalogue (`bin/pre-commit-check` ou équivalent). Les erreurs trouvées sont corrigées avant d'ouvrir la PR. Cette règle évite les régressions trouvées par la CI de la PR (4 régressions de la démo au sprint 37 : catalogue vitrine, page "À propos", fixtures, port MySQL de `bin/render-page`).

**Fichiers** :
- `project-management/sprints/sprint-38-consolidation-vitrine/consigne-worker.md` (règle avant ouverture PR)

**Critères** :
- [ ] Règle écrite dans `consigne-worker.md`
- [ ] Section « Suite complète ou pré-contrôle avant PR de fin de sprint »
- [ ] Au sprint 38, PR de fin de sprint ouverte seulement après contrôle complet
- [ ] Au sprint 38, 0 régression trouvée par la CI de la PR

---

### T-TECH-38-05 : Gabarit de test d'installation vérifiant kernel.bundles
- **Type** : [DOC]
- **Estimation** : 1 h
- **Dépend de** : -

**Description** :
Au premier commit d'un nouveau dépôt de plugin, un test d'installation vérifie que le bundle du plugin est chargé dans `kernel.bundles`. Assertion : `self::assertContains(PluginBundle::class, self::getContainer()->getParameter('kernel.bundles'))`. **Attention** : `Kernel::getBundles()` est indexé par le nom du bundle, pas par sa classe (erreur rencontrée sur le copilote au sprint 37). Ce test est lancé en local et prouvé rouge (bundle non enregistré) puis vert (bundle enregistré dans `bundles.php`). Gabarit dans `docs/test-installation-template.md`.

**Fichiers** :
- `docs/test-installation-template.md` (nouveau gabarit)

**Critères** :
- [ ] Gabarit créé dans `docs/test-installation-template.md`
- [ ] Assertion `kernel.bundles` avec explication
- [ ] Explication de l'indexation par nom de bundle (pas par classe)
- [ ] Exemple de test rouge puis vert
- [ ] Au sprint 38, 0 plugin dont le bundle n'est pas chargé dans les tests

---

## Dépendances

Les tâches transverses sont **parallèles** et peuvent être lancées en même temps.

```mermaid
graph TD
    T-TECH-38-01[T-TECH-38-01: Gabarit test fonctionnel]
    T-TECH-38-02[T-TECH-38-02: Règle 46 tests locaux]
    T-TECH-38-03[T-TECH-38-03: TaskStop passation]
    T-TECH-38-04[T-TECH-38-04: Suite complète avant PR]
    T-TECH-38-05[T-TECH-38-05: Gabarit test installation]

    style T-TECH-38-01 fill:#e1f5ff
    style T-TECH-38-02 fill:#e1f5ff
    style T-TECH-38-03 fill:#e1f5ff
    style T-TECH-38-04 fill:#e1f5ff
    style T-TECH-38-05 fill:#e1f5ff
```
