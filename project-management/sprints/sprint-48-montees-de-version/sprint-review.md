# Sprint 48 — Montées de version : Revue

**Date** : 2026-11-04
**Sprint Goal** : Lancer les montées de version en attente (endroid/qr-code 6, Symfony AI 0.15), finir la couverture publiée des 30 plugins et redonner au backlog des statuts fiables.

**Goal atteint : oui.**

## Résultat

| Métrique | Valeur |
|----------|--------|
| Points engagés | 8 |
| Points livrés | 8 |
| Vélocité | 100 % |
| Tâches techniques | 3/3 |

## Stories livrées

| ID | Livraison | Preuve |
|----|-----------|--------|
| US-268 | Passeport : `endroid/qr-code ^5.0 \|\| ^6.1` (6.1+ exige PHP 8.4 : contrainte élargie, minimum PHP inchangé) | PR #18 du passeport (b2e301a) ; CI : endroid 5.1.0 sur PHP 8.2/8.3, 6.1.3 sur PHP 8.4, 39 tests. Capture dans la démo avec endroid 6.2.0 |
| US-269 | Copilote : `symfony/ai-platform ^0.15`, test de contrat de l'adaptateur, test d'architecture (Symfony AI confiné à `src/Infrastructure/SymfonyAi/`), README FR/EN | PR #14 du copilote (1980ab5) ; 76 tests en CI. Dependabot #7 fermée |
| US-235 | Démo : 5ᵉ passeport (OUTI-026, pas de batterie au catalogue), `PassportSheet` nomme le champ obligatoire manquant, test « aucune promesse de conformité » (FR/EN, fiche et page publique), 5 passeports vérifiés après `app:demo:reset` | Branche feature/us-235-passport-fixtures, intégrée à la PR du sprint |

## Tâches techniques

- T-TECH-48-01 : sprint 47 clos, `current_sprint: 48`.
- T-TECH-48-02 : couverture publiée par predictive-analytics (PR #12, 2dc7add) et sales-copilot (PR #13, 5033247) ; `proof.json` à 30/30. Taux : 58,90 % et 78,36 % → US-270 et US-271 au backlog (validées par Thibaut).
- T-TECH-48-03 : 44 fiches et entrées de `sprint-status.yaml` remises en accord avec les revues des sprints 28 à 43.

## Hors périmètre, traité pendant le sprint

- **Onglet « Étiquettes » du passeport** rendu comme une puce hors de la navigation latérale dans l'admin (défaut en production depuis US-232), vu pendant la capture d'US-268. Corrigé en TDD (rouge prouvé en CI : `'nav-link' … contains "list-group-item"`), PR #19 (b381cde), vérifié dans la démo.
- **`main` de la démo rouge depuis #161** sur `bin/proof-check` (fichiers de test publiés 340, réels 342) : preuve technique rafraîchie (346).
- **Correctifs de sécurité Symfony du 2026-11-04** (21 avis sur 12 paquets, dont XXE de `symfony/config`) : patchs 8.1.x, `composer audit` propre.

## Constats

- Le QR code n'apparaît jamais dans la démo : les fixtures mettent le code de variante dans `productIdentifier`, pas un GTIN. La capture a été faite avec le GTIN d'exemple GS1 sur une base jetable. Proposition : story pour des GTIN fictifs valides dans les fixtures.
- Release v0.3.1 du passeport : tag à créer par Thibaut (refusé au mode auto), puis passage de la démo à `^0.3.1`.
