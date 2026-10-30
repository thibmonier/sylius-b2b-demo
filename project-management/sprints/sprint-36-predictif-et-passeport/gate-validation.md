# Gate Validation : Sprint 036 (prédictif et passeport)

> Validé le 2026-10-29 selon `.claude/commands/gate/validate-sprint.md`

═══════════════════════════════════════════════════════
           Validation Sprint Ready Gate
═══════════════════════════════════════════════════════

Sprint : sprint-036 - Prédictif et Passeport
Période : 2026-10-29 → 2026-11-13 (10 jours ouvrés)

Résultats de validation :
──────────────────────────────────────────────────────
✅ Métadonnées Sprint (20%)
   ID : sprint-36
   Nom : sprint-36-predictif-et-passeport
   Début : 2026-10-29
   Fin : 2026-11-13
   Goal : « Garantir une démo prédictive vivante à toute date (alertes garanties), lancer le passeport numérique produit avec sa page publique, et automatiser la veille hebdomadaire des versions du socle »

✅ Sprint Goal (15%)
   Objectif clair et mesurable défini (> 10 caractères)

✅ Stories prêtes (25%)
   4 stories en statut ready-for-dev
   Total points : 14

✅ Stories estimées (20%)
   Les 4 stories ont des story points
   - US-240 : 3 points (8 tâches, 6,5 h)
   - US-230 : 5 points (13 tâches, 11 h)
   - US-231 : 3 points (8 tâches, 6,5 h)
   - US-239 : 3 points (9 tâches, 6,5 h)

✅ Vérification capacité (10%)
   Planifié : 14 points (33 h, dont 32 h Claude)
   Capacité : 14 points (37,4 h)
   Utilisation : 100 %
   Marge : 5,4 h (14,4 %)
   Note : Écart d'un point sur la vélocité de 13 accepté par Thibaut

✅ Dépendances résolues (10%)
   Aucune story bloquée en statut ready
   - US-240 : US-219 partielle livrée, plugin v0.1.0 installé ✓
   - US-230 : GO création dépôt donné par Thibaut au registre des décisions ✓
   - US-231 : dépend d'US-230 (même sprint, ordre d'exécution planifié) ✓
   - US-239 : aucune dépendance bloquante ✓

Score : 100/100
──────────────────────────────────────────────────────

✅ SPRINT READY GATE VALIDÉ

Le sprint peut être démarré.

Stories prêtes :
  📖 US-240 : Commerce prédictif — alertes garanties (3 pts)
  📖 US-230 : Passeport numérique produit — squelette (5 pts)
  📖 US-231 : Passeport numérique produit — page publique (3 pts)
  📖 US-239 : Veille hebdomadaire des versions (3 pts)

Tâches transverses :
  🔧 T-TECH-36-01 à 05 : Actions de rétro S35 (2,5 h)

Commandes :
  /sprint:dev US-XXX        Développer une story
  /sprint:status            Métriques du sprint
═══════════════════════════════════════════════════════

## Détails de validation

### Gherkin (minimum 1+2+2)

| US | Nominal | Alternatifs | Erreurs | Conforme |
|----|---------|-------------|---------|----------|
| US-240 | 1 | 2 | 2 | ✅ |
| US-230 | 1 | 2 | 2 | ✅ |
| US-231 | 1 | 2 | 2 | ✅ |
| US-239 | 1 | 2 | 2 | ✅ |

### Estimations des tâches (0,5-8 h)

Toutes les tâches décomposées respectent la limite :
- US-240 : 8 tâches de 0,5 h à 1,5 h ✅
- US-230 : 13 tâches de 0,25 h à 1,5 h ✅
- US-231 : 8 tâches (voir fichier de décomposition) ✅
- US-239 : 9 tâches (voir fichier de décomposition) ✅
- Transverses : 5 tâches de 0,5 h ✅

**Total : 43 tâches, 33 h (32 h Claude + 1 h Thibaut)**

### Definition of Done

Chaque fiche contient :
- [ ] Critères d'acceptation Gherkin 1+2+2 ✅
- [ ] DoD détaillée ✅
- [ ] Estimations en points ✅
- [ ] Validation INVEST ✅

### Ordre d'exécution planifié

1. T-TECH-36-01 à 05 (parallèles) : actions de rétro
2. US-240 ∥ US-239 (parallèles) : alertes et veille
3. US-230 (après GO dépôt de Thibaut) : squelette passeport
4. US-231 (après US-230) : page publique

### Portes et actions de Thibaut

- [ ] GO création dépôt passeport (donné au registre des décisions) ✅
- [ ] GO tags quand les plugins sont fusionnés (en attente)
- [ ] Soumission Packagist (en attente)

### Notes

- Périmètre décidé par Thibaut le 2026-10-29
- Contraintes machine : 8 Go RAM, OrbStack 2 Go
- `docker compose up -d mysql mailpit` seulement
- Branche `feature/sprint-36-predictif-et-passeport` créée
- US-232 et US-233 reportées au sprint 37

═══════════════════════════════════════════════════════
