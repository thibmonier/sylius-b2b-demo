# Task Board : Sprint 015 (Dossier de GO)

## Légende
🔲 À faire | 🔄 En cours | 👀 En review | ✅ Terminé | 🚫 Bloqué | 📅 Calendaire | ⚠️ Sacrifiable

## 🔲 À Faire

| ID | US | Type | Tâche | Estimation | Assigné |
|----|-----|------|-------|------------|---------|
| T-078-01 | US-078 | [DOC] | ⚠️ Mesure Lighthouse avant (mobile : LCP fiche et catégorie, CLS, SEO) consignée | 1h | |
| T-078-02 | US-078 | [TEST] | ⚠️ Tests rouges : meta description et métadonnées de partage, `noindex` conservé, dimensio… | 1,5h | |
| T-078-03 | US-078 | [FE-WEB] | ⚠️ Meta description et métadonnées de partage (titre, description, image), `noindex` conse… | 1,5h | |
| T-078-04 | US-078 | [BE] | ⚠️ Images produit adaptées : tailles générées, `srcset`, format léger, sans appel externe… | 2h | |
| T-078-05 | US-078 | [FE-WEB] | ⚠️ Dimensions explicites, chargement différé hors écran, première image prioritaire (LCP,… | 1,5h | |
| T-078-06 | US-078 | [FE-WEB] | ⚠️ Visuel de repli quand l'image est absente, sans erreur | 0,5h | |
| T-078-07 | US-078 | [TEST] | ⚠️ Mesure Lighthouse après : LCP ≤ 2,5 s fiche et catégorie, CLS < 0,1 ; consignée | 1h | |
| T-078-08 | US-078 | [DOC] | ⚠️ Documentation (`docs/images-produits.md`) et avant/après | 0,5h | |
| T-078-09 | US-078 | [REV] | ⚠️ Revue (rendu réel FR et EN, aperçu de lien) | 0,5h | |
| T-081-01 | US-081 | [DOC] | ⚠️ Choisir l'outil de mises à jour par écosystème (Dependabot ou Renovate), consigner dans… | 1h | |
| T-081-02 | US-081 | [TEST] | ⚠️ Contrôle rouge `bin/check-pinned-refs` : échoue si une action ou une image de base est… | 1,5h | |
| T-081-03 | US-081 | [BE] | ⚠️ Figer les actions GitHub par empreinte de commit et les images du `Dockerfile` par cond… | 1,5h | |
| T-081-04 | US-081 | [BE] | ⚠️ CI : scan d'image publié en rapport, échec sur critique non justifiée, fichier d'except… | 2h | |
| T-081-05 | US-081 | [BE] | ⚠️ CI : SBOM (CycloneDX ou SPDX) publié en artefact à chaque build | 1,5h | |
| T-081-06 | US-081 | [BE] | ⚠️ Composer retiré de l'image finale (étage de production) ; test de contenu de l'image | 1,5h | |
| T-081-07 | US-081 | [DOC] | ⚠️ Documentation (`docs/durcissement-demo.md` ou ADR), sans détail exploitable | 0,5h | |
| T-081-08 | US-081 | [REV] | ⚠️ Revue (CI verte : « Quality and smoke tests » et « Docker image ») | 0,5h | |
| T-082-01 | US-082 | [DOC] | ⚠️ Relire l'audit et inventorier les éléments à traiter, sans détail exploitable ; état de… | 1h | |
| T-082-02 | US-082 | [TEST] | ⚠️ Tests rouges : en-têtes Cross-Origin attendus sur une page publique ; fichier de config… | 1,5h | |
| T-082-03 | US-082 | [BE] | ⚠️ En-têtes Cross-Origin de réponse (serveur ou Symfony), vérification des images, scripts… | 1,5h | |
| T-082-04 | US-082 | [BE] | ⚠️ Fichiers cachés non servis (404) côté serveur | 1h | |
| T-082-05 | US-082 | [BE] | ⚠️ Protection CSRF du formulaire de commande du simulateur PunchOut, message d'erreur géné… | 1,5h | |
| T-082-06 | US-082 | [BE] | ⚠️ Montée api-platform 5.0.4 et doctrine/orm 3.7.5 (`composer.lock`), `composer audit` | 1h | |
| T-082-07 | US-082 | [TEST] | ⚠️ Suite de fumée complète verte et rendu réel des pages clés après les en-têtes (aucune r… | 1,5h | |
| T-082-08 | US-082 | [DOC] | ⚠️ `docs/durcissement-demo.md` à jour (conclusions seulement) | 0,5h | |
| T-082-09 | US-082 | [REV] | ⚠️ Revue (relecture « aucun détail exploitable ») | 0,5h | |
| T-083-01 | US-083 | [DOC] | ⚠️ Lire la licence du paquet installé (fichier de licence, `composer.json`) ; relever les… | 1h | |
| T-083-02 | US-083 | [DOC] | ⚠️ Note de décision dans `docs/licence-plugin-mcp.md` : permis, interdit, incertain, avec… | 0,5h | |
| T-083-03 | US-083 | [REV] | ⚠️ Revue (citations exactes, aucun avis juridique présenté comme définitif) | 0,5h | |
| T-085-01 | US-085 | [DOC] | Collecter l'état de chaque bloquant : rapports des sprints 14 et 15 (US-079, 080, 081,… | 1h | |
| T-085-02 | US-085 | [TEST] | Revue de réouverture exécutée : `bin/verify-public-mode`, `bin/check-branch-protection`… | 1h | |
| T-085-03 | US-085 | [DOC] | Rédiger `docs/dossier-de-go.md` : bloquants, état, GO ou NO-GO, risques résiduels datés… | 1h | |
| T-085-04 | US-085 | [REV] | Revue indépendante (relecture « aucun détail exploitable » de tous les documents du dos… | 0,5h | |
| T-089-01 | US-089 | [DOC] | Analyser l'accueil actuel (blocs existants, historique de commandes, liste Atelier) et… | 1h | |
| T-089-02 | US-089 | [TEST] | Tests rouges : trois blocs sans doublon, anonyme, compte sans historique, produit retir… | 2h | |
| T-089-03 | US-089 | [BE] | Fournisseur des références habituelles (historique de commandes et liste Atelier), repl… | 2h | |
| T-089-04 | US-089 | [BE] | Réassort rapide et promotions négociées (prix de la société d'US-090) | 1,5h | |
| T-089-05 | US-089 | [FE-WEB] | Gabarits des trois blocs à la place des deux anciens, FR et EN | 1,5h | |
| T-089-06 | US-089 | [TEST] | Rendu réel FR et EN : Claire avec historique, compte sans commande, visiteur anonyme, m… | 1h | |
| T-089-07 | US-089 | [DOC] | Documentation (`docs/theme-b2b.md`) | 0,5h | |
| T-089-08 | US-089 | [REV] | Revue | 0,5h | |
| T-090-01 | US-090 | [DOC] | Spike D2 : ce que permet tier-pricing (canal, groupe, produit, variante, taxon) et pour… | 1,5h | |
| T-090-02 | US-090 | [TEST] | Tests rouges : tableau d'au moins 3 paliers avec palier atteignable en évidence ; prix… | 1,5h | |
| T-090-03 | US-090 | [BE] | Données : groupe de clients de la société de démo, règle de prix propre à ce groupe, pa… | 1,5h | |
| T-090-04 | US-090 | [FE-WEB] | Tableau des paliers sur la fiche (hook du plugin ou surcharge), palier atteignable en é… | 2h | |
| T-090-05 | US-090 | [FE-WEB] | Ligne « Votre prix négocié » de la société et écart avec le prix catalogue, FR et EN | 1,5h | |
| T-090-06 | US-090 | [TEST] | Rendu réel FR et EN (fiche, quantité saisie, mobile 375 px), pa11y du tableau | 1h | |
| T-090-07 | US-090 | [DOC] | Documentation : D2, société = groupe de clients, données de démonstration | 0,5h | |
| T-090-08 | US-090 | [REV] | Revue | 0,5h | |
| T-091-01 | US-091 | [TEST] | Tests rouges : panier détaillé (prix unitaire, palier appliqué, total) ; carte de liste… | 1,5h | |
| T-091-02 | US-091 | [FE-WEB] | Cartes de la liste : prix de la société, mention « dès » conditionnée à l'écart | 1,5h | |
| T-091-03 | US-091 | [FE-WEB] | Panier et devis : palier appliqué et ligne de remise, FR et EN | 1,5h | |
| T-091-04 | US-091 | [TEST] | Rendu réel FR et EN, mobile 375 et 320 px via `bin/measure-mobile` | 1h | |
| T-091-05 | US-091 | [REV] | Revue | 0,5h | |
| T-139-01 | US-139 | [DOC] | Relever les versions publiées (v1.2.0 quick-order, v1.2.1 punchout) et les changements… | 0,5h | |
| T-139-02 | US-139 | [TEST] | Tests rouges : l'exemple de commande rapide vient de l'option `example_lines` (aperçu à… | 1h | |
| T-139-03 | US-139 | [BE] | Monter les deux plugins (`composer.json`, `composer.lock`), supprimer la surcharge du g… | 1,5h | |
| T-139-04 | US-139 | [TEST] | Réactiver `/admin/punchout-buyers/` dans `RawTranslationKeysSmokeTest` ; lancer la suit… | 0,5h | |
| T-139-05 | US-139 | [REV] | Revue (rendu réel de la commande rapide et de l'admin PunchOut, FR et EN) | 0,5h | |
| T-140-01 | US-140 | [DOC] | Relire `.railway/railway.ts`, `docs/sous-domaine-demo.md`, `docs/deploiement-railway.md… | 0,5h | |
| T-140-02 | US-140 | [TEST] | Test rouge (structurel) : `TRUSTED_HOSTS` ne lit plus `RAILWAY_PUBLIC_DOMAIN`, points é… | 0,5h | |
| T-140-03 | US-140 | [BE] | Corriger `.railway/railway.ts` : domaine d'origine explicite ou variable dédiée, `DEMO_… | 1h | |
| T-140-04 | US-140 | [DOC] | Piège documenté dans `docs/sous-domaine-demo.md` ; `docs/deploiement-railway.md` mis à… | 0,5h | |
| T-140-05 | US-140 | [REV] | Revue (aucune valeur secrète, expression relue) | 0,5h | |
| T-141-01 | US-141 | [TEST] | ⚠️ Test du script en `--dry-run` : rien n'est supprimé, base active conservée et signalée,… | 0,5h | |
| T-141-02 | US-141 | [BE] | ⚠️ `bin/clean-test-caches` : caches, bases MySQL périmées, journaux binaires, `df -h .`, `… | 1,5h | |
| T-141-03 | US-141 | [DOC] | ⚠️ `docs/tests-locaux.md` : procédure disque (contrôle au démarrage, seuil jamais abaissé)… | 0,5h | |
| T-141-04 | US-141 | [DOC] | Documenter `PUPPETEER_MODULE` et `PUPPETEER_EXECUTABLE_PATH` dans l'en-tête de `bin/mea… | 0,5h | |
| T-141-05 | US-141 | [REV] | ⚠️ Revue (script relu, lancement à froid de `bin/measure-mobile` selon la doc) | 0,5h | |

## 📅 Calendaire (GO ou action de Thibaut, hors chemin critique)

| ID | US | Tâche | Estimation | Dépend de |
|----|-----|-------|------------|-----------|
| T-083-04 | US-083 | [OPS] Thibaut : trancher et consigner la décision dans la story | 0,5h | T-083-03 |
| T-085-05 | US-085 | [OPS] Thibaut : relire le dossier, accepter les risques résiduels, décider GO ou NO-GO | 0,5h | T-085-04 |
| T-140-06 | US-140 | [OPS] Thibaut : appliquer la configuration, vérifier que les deux domaines répondent | 0,5h | T-140-05 |
| T-052-03 à T-052-07 | US-052 | [OPS] Moniteur, bascule, `X-Real-IP`, MCP, coût Railway (hors capacité) | n/a | GO de Thibaut après US-085 |

## 🔄 En Cours
| ID | US | Tâche | Démarré | Assigné |
|----|-----|-------|---------|---------|

## 👀 En Review
| ID | US | Tâche | Reviewer |
|----|-----|-------|----------|

## ✅ Terminé
| ID | US | Tâche | Réel | Terminé |
|----|-----|-------|------|---------|

## 🚫 Bloqué
| ID | US | Raison | Action |
|----|-----|--------|--------|

## Métriques
- **Tâches** : 72 total | 0 terminées (0 %)
- **Heures** : 73,5 estimées (dont 1,5 calendaires) | 0 h consommées | 73,5 restantes (ordre de sacrifice dans [tasks/README.md](./tasks/README.md))
