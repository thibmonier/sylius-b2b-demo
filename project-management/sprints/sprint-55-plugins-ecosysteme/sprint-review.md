# Revue du sprint 55 — Trois plugins qui comblent des manques de l'écosystème Sylius

**Date** : 2026-11-12 (sprint lancé et livré le même jour, fin prévue le 2026-11-26)
**Objectif** : Un marchand B2B peut vérifier l'identité d'une entreprise à l'inscription, transmettre ses factures Factur-X à Pennylane et publier le passeport public d'une batterie : trois manques de l'écosystème Sylius 2 comblés, sans recoupement avec Sylius Plus.

**Objectif atteint côté plugins, sous réserve des fusions par Thibaut.** 15 points sur 15, CI verte sur les trois PR. Aucun tag : il attend le GO de Thibaut (T-TECH-55-02). L'installation dans la démo (T-TECH-55-03) attend les tags et Packagist.

## Stories

| Story | Points | PR | Rouge prouvé en CI | Vert | Résultat |
|-------|--------|----|--------------------|------|----------|
| US-282 company-verification | 5 | [company-verification #1](https://github.com/thibmonier/sylius-company-verification-plugin/pull/1) | `6f11b56` : 17 échecs d'assertion sur 43 tests unitaires, 10 jobs | `bfc731c` puis `7643f6d` (délai 15 s) ; tête `495ec0d`, 24 checks verts | 43 tests unitaires + 30 non unitaires (1 sauté : badge de registration-approval, Sylius 2.3 seulement) |
| US-283 einvoice-pennylane | 5 | [einvoice-pennylane #1](https://github.com/thibmonier/sylius-einvoice-pennylane-plugin/pull/1) | `2318d27` : 24 échecs d'assertion sur 25 tests unitaires, 10 jobs | `4aaf115` ; tête `15e2ff9`, 24 checks verts | 25 tests unitaires + 4 non unitaires |
| US-284 profil batterie du passeport | 5 | [passeport #20](https://github.com/thibmonier/sylius-digital-product-passport-plugin/pull/20) | `644cb77` : 12 échecs d'assertion sur 57 tests, 9 jobs | `682ac16` ; tête `1d69eb0`, 21 checks verts | 57 tests sur MySQL, MariaDB, PostgreSQL |

## Démonstration (projet Sylius 2.3 vierge local, API réelles)

- **US-282** : entreprise « La Poste » (SIRET `35600000000048`, TVA `FR39356000000`) → « SIREN/SIRET active » (API Recherche d'entreprises) et « VAT valid (VIES) », avec dénomination et adresse de chaque source, date et bouton « Verify again ». Capture dans le README.
- **US-284** : passeport d'une batterie industrielle LFP de chariot élévateur, section « Battery » avec les unités. Capture dans le README.
- **US-283** : pas d'écran (ni ce plugin ni `einvoice-transmission`) ; le README montre une ligne du journal. Le test contre le bac à sable Pennylane attend un jeton de Thibaut.

## Découvert pendant le sprint

- **VIES pour la France** : réponses en 7 à 8 s ou `MS_MAX_CONCURRENT_REQ`. Avec 5 s, toutes les vérifications de TVA française finissaient « non vérifiées » : délai par défaut porté à 15 s (`7643f6d`). Le message `VerifyCompany` part sur `sylius.command_bus`, synchrone tant qu'il n'est pas routé : la démo devra le router vers un transport asynchrone (T-TECH-55-03).
- **Défaut de `facturx`** : le SIRET réel du siège de La Poste est refusé par `FrenchIdentifiers` (règle de la somme par 5 seule) → [US-285](../../backlog/user-stories/US-285.md), 1 point.
- **US-283, écart avec la story** : `InvoiceDocument` ne porte pas le code client Sylius. La référence Pennylane est `siren-<SIREN>` pour une entreprise, `buyer-<empreinte>` du nom, du code postal et du pays pour un particulier : un particulier qui change d'adresse devient un nouveau client Pennylane. Limite écrite dans le README ; à reprendre si `einvoice-transmission` ajoute le code client.
- **US-283, points d'API à confirmer** sur le bac à sable : `invoice_options` envoyé en champ JSON du multipart ; 400/404/422 classés `rejected`.
- **Statut de Pennylane sur la liste de la DGFiP** (T-283-00) : la liste officielle n'a pas pu être lue (fichiers en téléchargement trop lent). Le README n'affirme rien et renvoie à la liste.
- **Squelette copié de company-insights** : il manquait les points d'entrée des assets (build de l'application de test en échec) et les `.env` de l'application de test portaient le nom de company-insights ; corrigés par Thibaut, le hook interdisant de les modifier.

## Tâches techniques

- T-TECH-55-01 : fait.
- T-TECH-55-02 : tags v0.1.0 (company-verification, einvoice-pennylane) et v0.4.0 (passeport) sur GO de Thibaut, puis soumission Packagist des deux nouveaux par Thibaut.
- T-TECH-55-03 : à faire après Packagist.
- T-TECH-55-04 : deux dépôts ajoutés à `config/demo/plugin-repositories.json`. `bin/plugin-supply-chain apply` appliqué (Dependabot) ; le rapport lit `main`, qui n'a encore que le commit initial : à relancer après les fusions.

## Reste à faire

1. Thibaut : relire et fusionner les trois PR, puis GO des tags.
2. Thibaut : soumettre les deux nouveaux paquets à Packagist ; jeton du bac à sable Pennylane pour le test manuel.
3. Démo : T-TECH-55-03 et `bin/plugin-supply-chain report`.
