# Tâches Techniques Transverses : Sprint 036

Actions de la rétrospective du sprint 35.

## T-TECH-36-01 : Hook pre-push refusant le force-push + ECS/PHPStan

- **Type** : [OPS]
- **Estimation** : 0,5 h
- **Réalisé par** : Orchestrateur

**Description** :
Hook Git `pre-push` qui refuse tout force-push et relance ECS + PHPStan avant le push.

**Fichiers** :
- `.git/hooks/pre-push`
- `bin/pre-push-check` (nouveau)

**Critères** :
- [ ] Hook `pre-push` créé et testé
- [ ] ECS + PHPStan relancés avant chaque push
- [ ] Force-push refusé avec message explicite

---

## T-TECH-36-02 : Script wait-for-ci

- **Type** : [OPS]
- **Estimation** : 0,5 h
- **Réalisé par** : Orchestrateur

**Description** :
Script qui attend la fin d'un run CI avec polling régulier.

**Fichiers** :
- `bin/wait-for-ci` (nouveau)
- `docs/tests-locaux.md` (documenté)

**Critères** :
- [ ] Script fonctionnel avec `gh run watch`
- [ ] Polling configurable
- [ ] Exit code reflète le statut CI

---

## T-TECH-36-03 : Consigne worker durcie

- **Type** : [DOC]
- **Estimation** : 0,5 h
- **Réalisé par** : Orchestrateur

**Description** :
Renforcer la consigne de worker avec les leçons du sprint 35.

**Fichiers** :
- `project-management/sprints/sprint-36-predictif-et-passeport/consigne-worker.md`

**Critères** :
- [ ] Règle sur la vérification de la branche avant commit
- [ ] Règle sur les tests rouges prouvés en CI
- [ ] Règle sur la vérification du vendor

---

## T-TECH-36-04 : README bilingue + captures dans la DoD plugin

- **Type** : [DOC]
- **Estimation** : 0,5 h
- **Réalisé par** : Orchestrateur

**Description** :
Ajouter à la Definition of Done des plugins : README anglais + `README.fr.md` français + captures dans `docs/`.

**Fichiers** :
- `project-management/templates/dod-plugin.md` (mise à jour)

**Critères** :
- [ ] DoD mise à jour avec README bilingue
- [ ] DoD exige captures dans docs/
- [ ] Exemples de liens corrects donnés

---

## T-TECH-36-05 : Script check-resources

- **Type** : [OPS]
- **Estimation** : 0,5 h
- **Réalisé par** : Orchestrateur

**Description** :
Script qui vérifie disque, mémoire, swap et autorise ou non les tests/rendus.

**Fichiers** :
- `bin/check-resources` (nouveau)
- Extension de `bin/run-tests` pour l'utiliser

**Critères** :
- [ ] Script vérifie disque ≥ 5 Go
- [ ] Vérifie mémoire et swap
- [ ] Message clair si ressources insuffisantes
- [ ] `bin/run-tests` refuse de tourner si ressources insuffisantes
