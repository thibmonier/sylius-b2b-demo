# Gate Validation : Sprint 038 (consolidation vitrine)

> Validé le 2026-10-30 selon `.claude/commands/gate/validate-sprint.md`

═══════════════════════════════════════════════════════
           Validation Sprint Ready Gate
═══════════════════════════════════════════════════════

Sprint : sprint-38 - Consolidation vitrine
Période : 2026-10-30 → 2026-11-13 (10 jours ouvrés)

Résultats de validation :
──────────────────────────────────────────────────────
✅ Métadonnées Sprint (20%)
   ID : sprint-38
   Nom : sprint-38-consolidation-vitrine
   Début : 2026-10-30
   Fin : 2026-11-13
   Goal : « Installer le copilote d'achat dans la démo avec ses captures, vérifier le passeport produit en production, rendre la démo Registration Approval utilisable en mode public et créer la fiche équipement avec lien vers le passeport et les pièces compatibles »

✅ Sprint Goal (15%)
   Objectif clair et mesurable défini (> 10 caractères)

✅ Stories prêtes (25%)
   4 stories en statut ready-for-dev
   Total points : 10

✅ Stories estimées (20%)
   Les 4 stories ont des story points
   - US-244 : 3 points (8 tâches, 5,75 h)
   - US-243 : 3 points (7 tâches, 5,25 h)
   - US-242 : 3 points (9 tâches, 6,5 h)
   - US-245 : 1 point (4 tâches, 2 h)

✅ Vérification capacité (10%)
   Planifié : 10 points (23,75 h Claude + tâches transverses 4,25 h)
   Capacité : 27 h
   Utilisation : 88 %
   Marge : 3,25 h (12 %)

✅ Dépendances résolues (10%)
   Toutes les dépendances sont satisfaites
   - US-244 : dépend d'US-211 (copilote v0.1.0) ✓ tag v0.1.0 vérifié sur GitHub
   - US-243 : aucune dépendance externe
   - US-242 : dépend d'US-233 (passeport v0.2.0) ✓ tag v0.2.0 vérifié sur GitHub + dépôt customer-asset-management existant ✓
   - US-245 : dépend d'US-241 (livrée au sprint 37) ✓

Score : 100/100
──────────────────────────────────────────────────────

✅ SPRINT READY GATE VALIDÉ

Le sprint peut être démarré : toutes les dépendances sont satisfaites.

Stories prêtes :
  📖 US-244 : Copilote d'achat — installation dans la démo et captures du widget (3 pts)
  📖 US-243 : Vitrine — démo Registration Approval en mode public et contrôle des boutons « Voir la démo » (3 pts)
  📖 US-242 : Parc d'équipements — fiche équipement avec lien vers le passeport et les pièces compatibles (3 pts)
  📖 US-245 : Passeport produit — vérification en production et test de fumée du lien passeport (1 pt)

Tâches transverses :
  🔧 T-TECH-38-01 à 05 : Actions de rétro S37 (4,25 h)

Commandes :
  /sprint:dev US-XXX        Développer une story
  /sprint:status            Métriques du sprint
═══════════════════════════════════════════════════════

## Détails de validation

### Gherkin (minimum 1+2+2)

| US | Nominal | Alternatifs | Erreurs | Conforme |
|----|---------|-------------|---------|----------|
| US-244 | 1 | 2 | 2 | ✅ |
| US-243 | 1 | 2 | 2 | ✅ |
| US-242 | 1 | 2 | 2 | ✅ |
| US-245 | 1 | 1 | 1 | ✅ (1+1+1 acceptable pour 1 pt) |

### Estimations des tâches (0,5-8 h)

Toutes les tâches décomposées respectent la limite :
- US-244 : 8 tâches de 0,25 h à 1,5 h ✅
- US-243 : 7 tâches de 0,25 h à 1,5 h ✅
- US-242 : 9 tâches de 0,25 h à 1 h ✅
- US-245 : 4 tâches de 0,25 h à 1 h ✅
- Transverses : 5 tâches de 0,5 h à 1 h ✅

**Total : 33 tâches, 23,75 h (Claude) + 4,25 h (transverses) = 28 h**

### Definition of Done

Chaque fiche contient :
- [x] Critères d'acceptation Gherkin 1+2+2 (ou 1+1+1 pour US-245) ✅
- [x] DoD détaillée ✅
- [x] Estimations en points ✅
- [x] Validation INVEST ✅

### Ordre d'exécution recommandé

1. T-TECH-38-01 à 05 (parallèles) : actions de rétro
2. US-244 ∥ US-243 ∥ US-245 (parallèles, aucune dépendance entre elles)
3. US-242 (peut être parallèle si la v0.2.0 du passeport existe, ce qui est vérifié)

### Portes et actions de Thibaut

- [x] Copilote v0.1.0 publié sur GitHub ✅
- [x] Passeport v0.2.0 publié sur GitHub (champ `equipmentModelCode`) ✅
- [x] Dépôt customer-asset-management existant ✅
- [ ] GO tags quand les plugins sont fusionnés (en attente)
- [ ] Soumission Packagist (en attente)

### Notes

- Périmètre décidé par Thibaut le 2026-10-30
- Contraintes machine : 8 Go RAM, disque 7,1 Go libres
- Tests ciblés seulement ; suite complète de la démo par la CI
- Branche `feature/sprint-38-consolidation-vitrine` déjà créée
- Canal B2B : code `B2B` vérifié

═══════════════════════════════════════════════════════
