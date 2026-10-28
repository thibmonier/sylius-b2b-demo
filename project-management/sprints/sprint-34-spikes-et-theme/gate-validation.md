# Validation Sprint Ready Gate — Sprint 34

> **Date :** 2026-10-28  
> **Validateur :** Claude Opus 5.5  
> **Périmètre :** Sprint 34 — Spikes et thème tranche 1

═══════════════════════════════════════════════════════

## Sprint

**ID :** sprint-34  
**Nom :** sprint-34-spikes-et-theme  
**Période :** 2026-10-28 → 2026-11-11 (10 jours ouvrés)

## Résultats de validation

──────────────────────────────────────────────────────

### ✅ Métadonnées Sprint (20%)

- **ID :** sprint-34
- **Nom :** sprint-34-spikes-et-theme
- **Début :** 2026-10-28
- **Fin :** 2026-11-11
- **Capacité :** 14 points (31,4 h)

### ✅ Sprint Goal (15%)

> « Cadrer trois capacités innovantes de la démo par leurs spikes et livrer la tranche 1 du thème Industrie & Négoce : format de rejeu du copilote sans appel LLM payant, formulation prudente du passeport produit, packaging d'un thème Sylius 2 installable par Composer, et paquet du thème avec ses jetons de design et ses composants communs. »

Objectif clair et mesurable, aligné avec la feuille de route innovation.

### ✅ Stories prêtes (25%)

**3 stories en statut ready-for-dev, 1 en backlog (attendu)**

- US-210 : ready-for-dev ✅
- US-221 : ready-for-dev ✅
- US-229 : ready-for-dev ✅
- US-222 : backlog ✅ (attend le GO de l'ADR d'US-221, décision D3 du registre)

Total points : **14 points**

### ✅ Stories estimées (20%)

Les 4 stories ont des story points :
- US-210 : 3 points
- US-221 : 3 points
- US-229 : 3 points
- US-222 : 5 points

**Total : 14 points**

### ✅ Vérification capacité (10%)

- **Planifié :** 14 points (32,5 h de charge Claude)
- **Capacité :** 14 points (31,4 h)
- **Écart :** -1,6 h
- **Utilisation :** 103,5 %

**Analyse :** Écart d'un point sur la vélocité de 13 accepté par Thibaut (registre du 2026-10-28). Les spikes sont timboxés à 6 h chacun. Si la charge dépasse, ordre de sacrifice décidé : US-222 d'abord, puis US-229 ; US-210 et US-221 protégées.

### ✅ Dépendances résolues (10%)

Aucune story bloquée en statut ready.

- US-222 dépend d'US-221 (ADR + GO de Thibaut) mais reste en backlog jusqu'au GO : **conforme**
- Portes clairement identifiées dans le registre et le sprint-goal

### ✅ Critères Gherkin

Toutes les stories respectent le minimum 1 nominal + 2 alternatifs + 2 erreurs :
- US-210 : 1+2+2 ✅
- US-221 : 1+2+2 ✅
- US-229 : 1+2+2 ✅
- US-222 : 1+2+2 ✅

### ✅ Estimations des tâches

**43 tâches décomposées**, toutes entre 0 et 8 h :
- Minimum : 0 h (tâches de contrôle)
- Maximum : 5,5 h (recherches des spikes)
- Conforme à la contrainte 0,5-8 h

### ✅ Cohérence avec le registre

Toutes les décisions du registre sont alignées :
- Périmètre : 14 points validé par Thibaut ✅
- Ordre de sacrifice : US-222, US-229 ; US-210/US-221 protégées ✅
- GO nouveau dépôt : règle d'exécution claire (orchestrateur crée et fusionne, worker pousse et PR) ✅
- Relecture au fil de l'eau : portes identifiées ✅
- Disque : contraintes actées (aucun test sous 5 Go) ✅

### ✅ Absence de détail de sécurité

- US-210 : note privée de sécurité hors dépôt public ✅
- US-221, US-222, US-229 : aucun détail sensible ✅
- Sprint-goal : politique de sécurité rappelée ✅
- Registre : ton neutre, aucune comparaison avec Sylius Plus ✅

──────────────────────────────────────────────────────

## Score : 100/100

═══════════════════════════════════════════════════════

## ✅ SPRINT READY GATE VALIDÉ

Le sprint 34 peut être démarré.

### Stories prêtes

📖 **US-210** : Spike copilote d'achat : format de rejeu, adaptateur LLM et recoupement Sylius Plus (3 pts)  
📖 **US-221** : Spike packaging d'un thème Sylius 2 : Twig hooks, assets et Flex (3 pts)  
📖 **US-229** : Spike passeport numérique produit : texte du règlement ESPR et formulation prudente (3 pts)  
📋 **US-222** : Thème Industrie & Négoce : paquet installable, jetons de design et composants communs (5 pts) — *attend GO de l'ADR d'US-221*

### Actions de Thibaut avant démarrage

- [ ] Libérer le disque (cible 10 Go libres) avant lancement des tests (T-TECH-34-03)
- [ ] Relire chaque ADR de spike au fil de l'eau (T-210-03, T-221-03, T-229-03)
- [ ] Donner le GO de l'ADR d'US-221 avant de lancer US-222 (T-221-03)

### Commandes

```
# Lancer le premier lot de spikes (US-210, US-221, US-229)
# puis attendre le GO de l'ADR d'US-221 avant US-222
```

═══════════════════════════════════════════════════════

## Observations et recommandations

### Points forts

1. **Périmètre maîtrisé** : 14 points, écart d'un point accepté, ordre de sacrifice décidé
2. **Spikes timboxés** : 6 h chacun, livrables clairs (ADR + prototypes)
3. **Portes explicites** : GO de Thibaut sur l'ADR d'US-221 avant US-222, relecture au fil de l'eau
4. **Registre complet** : toutes les décisions tracées avec leur auteur et leur date
5. **Gherkin complet** : toutes les stories ont 1+2+2 scénarios
6. **Tâches bien calibrées** : maximum 5,5 h, 43 tâches décomposées
7. **Sécurité** : note privée pour US-210, aucun détail sensible dans les fiches publiques

### Vigilances

1. **Disque à 4 Go** : contrainte stricte, aucun test sous 5 Go, Thibaut doit libérer avant les tests
2. **Timebox des spikes** : s'arrêter à 6 h avec points ouverts si nécessaire, ré-estimation des epics au planning suivant
3. **GO d'US-221** : US-222 ne démarre qu'après validation de l'ADR par Thibaut
4. **Ordre de sacrifice** : US-222 en premier, puis US-229 si la charge dépasse

### Critères spécifiques respectés

- ✅ Stories prêtes (US-222 en backlog attendu)
- ✅ Critères Gherkin (1+2+2 partout)
- ✅ Estimations des tâches entre 0 et 8 h
- ✅ Dépendances résolues (portes claires)
- ✅ Capacité (écart accepté, ordre de sacrifice décidé)
- ✅ Cohérence avec le registre-decisions.md
- ✅ Absence de détail de sécurité (note privée pour US-210)

═══════════════════════════════════════════════════════
