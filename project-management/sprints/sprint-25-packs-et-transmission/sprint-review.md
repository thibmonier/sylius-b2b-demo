# Sprint Review - Sprint 25 (Packs et transmission)

> Préparée le 2026-10-24, le jour du sprint : périmètre vérifié, y compris en production.

## Informations

| Attribut | Valeur |
|----------|--------|
| Sprint | 25, `sprint-25-packs-et-transmission` |
| Période | 2026-10-24 (lancé et terminé le même jour ; fin au plus tard prévue le 2026-11-07) |
| Points engagés | 17 (US-176 5, US-178 2, US-177 5, US-180 5) |
| Points en done | **17** |
| PR de la démo | #78 (lancement), #79 (US-178 et résultats des packs), #80 (débordement de la page des packs) |
| Autres dépôts | packs : PR #1 (US-176) et #2 (US-177) de chacun ; nouveau dépôt `thibmonier/sylius-einvoice-transmission-plugin`, release v0.1.0 ; `symfony/recipes-contrib#2094` |
| Mode | Orchestrateur (Claude) et workers : wP (US-176, US-177), wD (US-178), wE (US-180) ; correctif #80 par l'orchestrateur |

## Sprint Goal

> Prouver que les packs s'installent réellement, les rendre installables sans geste manuel, les présenter dans la démo, et poser la première tranche de la transmission des factures électroniques.

**Atteint.** Les deux packs s'installent en CI dans un projet Sylius vierge (2.2 et 2.3), avec et sans recette Flex ; la page « Installer un pack » est en production ; le plugin de transmission est publié en v0.1.0.

## User stories livrées

| Story | Points | Livré | Commit rouge (TDD) | Preuve principale |
|-------|--------|-------|--------------------|-------------------|
| US-176 Installation réelle d'un pack en CI | 5 | Gestes du README appliqués par script (source unique), enveloppe d'étape qui nomme plugin et étape, job d'installation en matrice Sylius-Standard 2.2.4 et 2.3.0, job « deux packs ensemble » | 7 commits par pack (tests seuls) | Runs 40036530846 (IA) et 40036324215 (Conformité) ; « OK 711 routes, no conflict » ; preuves négatives sur branches jetables (`FAIL plugin=… step=…`) |
| US-177 Recettes Flex des packs | 5 | Recette de chaque pack, essai d'aller-retour `bin/flex-trial`, job `recipe` sans geste manuel ; résultat de l'essai dans l'ADR 0015 ; PR publique sur `recipes-contrib` sur GO | 2 commits par pack | Runs 40038113308 (IA) et 40038119600 (Conformité) ; symfony/recipes-contrib#2094 |
| US-178 Page « Installer un pack » | 2 | Page `/{locale}/packs` FR et EN, données lues dans les dépôts des packs (`bin/packs-refresh`), retard contrôlé par `bin/proof-check`, liens depuis Intégrer et Architecture | `bf29699` ; `416afba` pour le correctif #80 | Production : pa11y WCAG2AAA sans problème FR et EN, aucune largeur de page au-delà de l'écran à 360 et 1280 px (après #80) |
| US-180 Transmission des factures électroniques, tranche A | 5 | Nouveau plugin : port `InvoiceTransmitterInterface`, journal d'une ligne par facture, écouteur idempotent de `tmonier_facturx.invoice_generated`, adaptateur nul ; README FR et EN | `d817be6` (15 tests, 14 erreurs, 1 échec) | CI verte sur 10 combinaisons (Sylius 2.2 et 2.3, PHP 8.2 à 8.5, MySQL, MariaDB, PostgreSQL) ; release v0.1.0 |

20 scénarios, chacun relié à un test ou à une preuve dans le fichier de tâches de sa story.

## Décisions de Thibaut (2026-10-24)

- Lancement : ADR 0016 limité à la story A (US-180), ADR 0017 rien ce sprint, US-177 avec PR publique sur son GO.
- Dépôts des packs : PR fusionnées dès qu'elles sont vertes, sans tag.
- US-180 : création du dépôt `thibmonier/sylius-einvoice-transmission-plugin` et push, puis GO pour le tag v0.1.0. Soumission à Packagist par Thibaut.
- GO pour la PR sur `symfony/recipes-contrib`, puis pour le commentaire qui explique l'échec du job automatique.

## Éléments non terminés

Aucun dans le périmètre. Reste une action de Thibaut : soumettre `sylius-einvoice-transmission-plugin` à Packagist (T-180-13).

## Incidents et écarts de processus

| Incident | Effet | Suite |
|----------|-------|-------|
| Page des packs plus large que l'écran en production (645 px à 360 px, 2105 px à 1280 px) : Bootstrap donne `word-wrap: break-word` aux `code`, ce qui annule le retour à la ligne hérité de la cellule | Défaut publié, trouvé par la vérification en production (T-178-07), invisible avec les données de test courtes | #80 : test rouge seul, CI rouge puis verte, nouvelle mesure en production ; action 2 |
| Dossier de recette `1.0` préparé par un worker, alors que la seule version publiée des packs est 0.1.0 : Flex n'aurait jamais appliqué la recette | Relevé avant publication | Dossier `0.1` dans la PR #2094 ; action 3 |
| Texte de la PR `recipes-contrib` qui disait les paquets « à soumettre » à Packagist, alors qu'ils y étaient | Relevé avant publication | Texte corrigé |
| Job « Run updated recipes » de `recipes-contrib` en échec : projet Symfony nu, sans le `default_bus` que fixe Sylius | Même échec sur les 5 dernières recettes Sylius fusionnées | Commentaire sur la PR, sur GO |
| Le hook du projet refuse l'écriture des fichiers `.env*` : wE bloqué au squelette du plugin | Non contourné ; application de test configurée par `phpunit.xml.dist` et l'environnement de la CI, écart écrit dans le README | Action 4 |
| wE a démarré son propre MySQL (port 3351) avant la consigne « un seul serveur lourd » | Deux MySQL en même temps pendant une partie du lot | Conteneur et volume supprimés à la fin ; action 4 |
| Espace libre tombé à 3,9 Go | `bin/run-tests` refuse sous 5 Go : le test rouge de #80 n'a tourné qu'en CI | Action 1 |
| Refus d'outil : `rm -rf` de `vendor/` (wE), du cache de lint (wD), d'essais Flex et `pkill` (wP), une lecture groupée (wE) | Non contournés ; restes listés à Thibaut | Action 1 |
| Deux défauts trouvés dans le plugin b2b-mcp par l'installation réelle : `symfony/lock` non requis, routes du serveur MCP importées deux fois si l'on suit le README | Le pack IA ajoute `symfony/lock` à ses gestes ; plugin non modifié | Story proposée au backlog |

## Métriques

| Métrique | Sprint 25 | Sprint 24 |
|----------|-----------|-----------|
| Points engagés / en done | 17 / 17 | 19 / 19 |
| Stories en done avec un scénario non livré | 0 | 0 |
| Stories de code avec commit rouge | 4 sur 4 | 5 sur 5 |
| Tâches à la clôture | 61 ✅, 1 📅 (Packagist, Thibaut), 0 🔲 | 69 ✅, 1 🚫 justifiée |
| Fichiers de tests de la démo (preuve publiée) | 227 | 219 |
| Plantage de la machine | 0 | 0 |
| Espace libre minimal | **3,9 Go** | 4,0 Go |
| Contournements de refus d'outil | 0 (5 refus) | 0 (3 refus) |
| Défauts trouvés en production après merge | 1 (débordement, corrigé le jour même) | 0 |

## Démonstration (ordre suggéré)

1. `/fr_FR/packs` sur téléphone : un pack, sa commande, les gestes restants, puis le lien vers l'installation un par un.
2. Les runs `install` et `recipe` d'un pack : le projet Sylius-Standard vierge, la recette appliquée sans geste manuel.
3. Un run rouge de preuve négative : `FAIL plugin=… step=…`.
4. Le plugin de transmission : README, port, scénario de l'événement rejoué.
5. symfony/recipes-contrib#2094.

## Impact sur le backlog

| Élément | Statut |
|---------|--------|
| Défauts de b2b-mcp (`symfony/lock`, routes importées deux fois) | nouvelle story à créer (1 point), cible sprint 26 |
| Tag v0.2.0 des deux packs (README de `main` qui décrit la recette) | à décider par Thibaut |
| ADR 0016, tranches suivantes ; ADR 0017 (ERP) | à trancher au planning du sprint 26 |
| Garde sur les `code` dans les tableaux des pages de la démo | action 2 de la rétro |

## Prochaines étapes

1. Thibaut valide la revue, la rétrospective et la clôture du sprint 25, et soumet le plugin de transmission à Packagist.
2. Planning du sprint 26.
