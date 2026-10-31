# Consigne de worker : Sprint 039

> Gabarit : [../../templates/consigne-worker.md](../../templates/consigne-worker.md). Les règles 1 à 48 des consignes des [sprint 35](../sprint-35-commerce-predictif/consigne-worker.md), [sprint 36](../sprint-36-predictif-et-passeport/consigne-worker.md), [sprint 37](../sprint-37-copilote-et-passeport/consigne-worker.md) et [sprint 38](../sprint-38-consolidation-vitrine/consigne-worker.md) restent applicables, sauf les adaptations du tableau « Sprint 39 ». Les règles 49 et 50 sont nouvelles (rétro du sprint 38). L'orchestrateur colle **tel quel** le bloc « Rapport de fin » de [../../templates/checklist-integration.md](../../templates/checklist-integration.md). Gabarits de tests : [docs/gabarits-tests.md](../../../docs/gabarits-tests.md) ; outils : [docs/tests-locaux.md](../../../docs/tests-locaux.md).
>
> **Règle 1 en tête : les tests de la démo se lancent seulement par `bin/run-tests`, jamais par `phpunit` en direct.**

## Identité et ressources

| Worker | Stories | `WORKER_ID` | Dépôt de travail | Base | Cache |
|--------|---------|-------------|------------------|------|-------|
| wtech | T-TECH-39-01 à 05 | `wtech` | démo (arbre principal), plugins (parc, copilote) | `b2b_wtech` | `cache-wtech` |
| wdesign | US-248, US-250 | `wdesign` | canevas Claude Design `G2StRQigRKD6SqNRoyHpkW` (aucun dépôt git) | aucune | aucun |
| w249 | US-249 | `w249` | démo (arbre principal, après wtech) | `b2b_w249` | `cache-w249` |
| w247 | US-247 | `w247` | démo (arbre principal, après w249) | `b2b_w247` | `cache-w247` |

**Important** : wdesign travaille dans le canevas Claude Design unique `G2StRQigRKD6SqNRoyHpkW` (pas de nouveau projet). Il produit deux pages : « Copilote d'achat » (US-248) puis « Réassort » (US-250), **dans cet ordre**, pour que la seconde partage les jetons de design de la première. wdesign ne fait **aucun commit git** ni aucune commande, seulement des maquettes dans Claude Design.

## Sprint 39 : adaptations des règles 1 à 48

| Règle | Sprint 39 |
|-------|-----------|
| 6 (branches) | Démo : branche `feature/sprint-39-conception-graphique` dans l'arbre principal, **un seul worker de la démo à la fois** (wtech, puis w249, puis w247 ; pas de worktree, faute de disque pour un second `vendor/`) ; pas de checkout. Plugins : `feature/<sujet>` depuis `origin/main`, un seul worker par dépôt. |
| 8 et 30 (GO) | Plugins : l'orchestrateur fusionne à CI verte ; aucun tag ni release sans GO de Thibaut. Démo : aucun push sans demande de l'orchestrateur (la branche du sprint part en une PR brouillon de fin de sprint ; un `proof/*` peut être demandé pour prouver un rouge en CI). |
| 10 (plugins) | US-247 : copilote déjà installé (US-244 au sprint 38) ; un nouveau plugin installé entre aussi dans `config/demo/plugins.yaml`, puis `bin/compat-refresh` et `bin/proof-refresh`. Canal B2B de la démo : code `B2B`. |
| 23 (captures) | Chromium sans interface, 1280 px, recadrage par ImageMagick (`magick in.png -crop 1280xH+0+0 +repage out.png`), pas par `sips` ; PNG relus avant tout commit ; pa11y WCAG2AAA sur la page rendue avec son CSS. |
| 24 (parallélisme) | Disque à 18 Go libres : tests ciblés seulement, jamais la suite complète en local ; au plus deux workers lancent `bin/run-tests` en même temps. |

## Règles 49 et 50 (rétro du sprint 38)

49. **Un worker par dépôt pour les stories multi-dépôts** (T-TECH-39-02). Une story qui touche plusieurs dépôts est découpée en un worker par dépôt, lancés dans l'ordre des dépendances (plugin, puis démo). Chaque worker cite dans son rapport la commande `grep` dans `vendor/` qui prouve l'existence de chaque méthode, route ou fonction Twig utilisée (règle 38).
50. **Vérifier l'existence d'un fichier avant de le créer** (T-TECH-39-04). Avant de créer un fichier, lancer `git ls-files <chemin>` et `ls <chemin>`. Si le fichier existe, le lire et le modifier, ou choisir un autre nom. Cette règle s'applique aussi à l'orchestrateur.

## Preuves attendues dans le rapport

Pour chacun de PHPStan (dépôt entier), ECS (`src tests`), lint Twig, lint YAML et la suite : la **commande exacte** et la **dernière ligne de sortie**. Pour les tests rouges : la ligne d'échec locale **et** l'URL ou l'ID du run CI rouge. SHA copiés de `git log --oneline`, ID de run copiés de `gh run list` (règle 37). Rapport final à l'orchestrateur en moins de 150 mots. Interdits rappelés : force-push, `--force-with-lease`, `--amend` ou `reset` d'un commit poussé ; `git add -A` ou `git add .` ; tout fichier `.env*` ; supprimer ou affaiblir un test ; infrastructure factice ; serveur local ; tag, release, fusion ; contourner un refus d'outil.
