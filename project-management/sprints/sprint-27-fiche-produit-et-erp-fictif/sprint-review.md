# Sprint Review - Sprint 27 (Fiche produit et ERP fictif)

> Préparée le 2026-10-24, le jour du sprint. Les contrôles en production non consignés dans le dépôt sont indiqués tels quels dans « Éléments non terminés ou à confirmer ».

## Informations

| Attribut | Valeur |
|----------|--------|
| Sprint | 27, `sprint-27-fiche-produit-et-erp-fictif` |
| Période | 2026-10-24 (lancé et terminé le même jour ; fin au plus tard prévue le 2026-11-21) |
| Points engagés | 12 au planning (US-189 5, US-188 5, US-193 2), **13** avec US-194 (1), ajoutée en cours de sprint sur demande de Thibaut |
| Points en done | **13** |
| PR de la démo | #94 (planning), #95 (US-194), #96 (backlog US-195 et US-196), #97 (US-193), #98 (US-188), #99 (US-189), #100 (correctif de l'échange ERP), #101 (tâches techniques) |
| Autres dépôts | aucun |
| Mode | Orchestrateur (Claude) et workers : w189 (US-189), w193 (US-193), w188 (US-188), w185 (US-194) ; maquette de la fiche par l'orchestrateur |

## Sprint Goal

> « Donner à la fiche produit la même chaleur que la home (maquette validée avant le code), faire lire à la page d'échange de fichier un vrai extrait produit par le plugin d'export, et montrer le va-et-vient des commandes avec un ERP fictif, journal des échanges compris, sans aucun système réel. »

**Atteint.** La fiche produit a été refaite d'après une maquette validée par Thibaut avant le premier gabarit, puis resserrée sur sa demande après des captures avec les vraies données. `/echange-fichier` lit le dernier fichier réellement produit. Un ERP fictif accuse réception des commandes exportées et son journal est visible au back-office. Le contrôle en production a trouvé un défaut (l'échange ERP disparaissait à chaque déploiement), corrigé le jour même.

## User stories livrées

| Story | Points | Livré | Preuve principale |
|-------|--------|-------|-------------------|
| US-189 Refonte de la fiche produit | 5 | En-tête (catégorie, titre en Fraunces, référence, prix HT), pastille de norme lue dans l'attribut `epi_norm`, boîte d'achat (paliers en tableau, devis compact, favoris en bouton icône, remplissage palette et camion replié), visiteur invité à se connecter, corps sans section vide, B2C inchangé (#99). Maquette : https://claude.ai/artifact/9egcEkMJDxkTPJTj9re8zf | Tests rouges seuls `57ce592`, `4d6942a`, `5d80a9d` ; 193 tests ciblés verts ; pa11y WCAG2AAA sans problème sur 3 fiches types FR et EN ; 36 rendus à 360, 768 et 1280 px sans débordement ; production : LCP mobile 1408 à 1444 ms (avant 1388 à 1776), CLS 0 |
| US-188 ERP fictif | 5 | Simulateur « ERP Démo (fictif) » qui lit le dernier `orders-*.csv`, valide l'en-tête et chaque ligne, dépose un accusé de réception de façon atomique (format propre à la démo, validé par Thibaut) ; journal du simulateur ; page « Échanges ERP (fictif) » au back-office (lecture seule) ; documentation `docs/erp-demo.md` ; sans US-187 (#98) | Tests rouges seuls `e373811` (dans la PR) ; 39 tests ciblés verts, 444 sur un périmètre plus large ; pa11y : 0 erreur dans le contenu de la page, 28 dans la coque du back-office Sylius (commune à tous les écrans d'administration) |
| US-193 Extrait réel sur `/echange-fichier` | 2 | Fixture `demo_erp_export` (commandes terminées devenues événements `order.placed`, export au chargement) ; `ExchangeFileSample` lit le dernier fichier : en-tête et 3 lignes du canal du visiteur, e-mails masqués ; états neutres FR et EN (#97) | Tests rouges seuls `ea6df34` (rebasé : `d3c71cd` dans la PR) ; 39 tests ciblés verts puis 301 sur un périmètre plus large ; production : 3 lignes affichées, e-mails masqués (après #100) |
| US-194 Logo de l'auteur dans le pied de page | 1 | Logo WebP 112×112 (2,4 Ko) à gauche de « Réalisé par… », décoratif (`alt=""`), dimensions déclarées (#95). Ajoutée en cours de sprint sur demande de Thibaut | `FooterAuthorLogoTest` rouge seul `1e990ca` ; `DemoFooterTest` ; pa11y local sans problème FR et EN |

Hors points : **correctif #100** (`c6b9b6e`) : l'échange ERP est reconstruit au démarrage du conteneur (voir « Incidents »). Planning #94, backlog US-195 et US-196 #96, tâches techniques #101.

## Tâches techniques (retro du sprint 26)

| Action | Résultat | PR |
|--------|----------|----|
| 2 : compteurs en dur | 11 assertions lisent `DemoCatalogs::pluginCount()` ; garde `HardcodedPluginCountGuard` | #101 |
| 5 : tables larges | garde `WideTableGuard` (cartes ou défilement interne sous 768 px) ; `/demo/mails` et le simulateur PunchOut corrigés | #101 |
| 4 : 503 en local | cause : `variables_order = GPCS` du `php.ini` du poste ; `bin/local-server` force `EGPCS` ; `bin/render-page` rend une page par le noyau ; procédure dans `docs/tests-locaux.md` | #101 |
| 3 : seuil disque | `bin/check-disk --full` et `bin/run-tests` sans chemin ni filtre refusent une suite complète sous 6 Go ; mémoire et swap affichés | #101 |
| 5 : mesures consignées | LCP de la home et des fiches mesurés en production, consignés dans les fichiers de tâches | #101 |

## Décisions de Thibaut (2026-10-24)

- Périmètre de 13 points validé au planning ; ordre de sacrifice US-188 puis US-193, jamais appliqué ; US-187 reste au backlog.
- Maquette de la fiche produit validée avant le code, puis fiche resserrée après les captures avec les vraies données.
- Accusé de réception simulé propre à la démo (à la place du CSV de prix et de stock d'US-187).
- Ajout d'US-194 (logo de l'auteur dans le pied de page).
- Backlog : US-195 (77 noms de produits à marque réelle) et US-196 (photos du catalogue incohérentes) au sprint 28.

## Éléments non terminés ou à confirmer

- **Contrôles en production du 2026-10-24** (lecture seule) : fiches OUTI-001 FR et EN, EPI-VEST-001, FIXA-002, home, `/echange-fichier` FR et EN : aucun défilement horizontal à 360 et 1280 px, pa11y WCAG2AAA « No issues found! ».
- **LCP mobile** (protocole de w186) : fiches 1408 à 1444 ms (avant 1388 à 1776 ms) ; **home 1576 ms contre 1324 ms** au sprint 26 (+19 %), à surveiller (action 5 de la rétro). CLS 0.
- **Page « Échanges ERP (fictif) » du back-office** : vérifiée en local seulement (pas de connexion en production) ; non revérifiée sur l'image reconstruite après #100 (T-188-10).
- **US-194** : contrôle de production fait le 2026-10-24 (logo servi dans le pied de page, home FR et EN sans défilement à 360 et 1280 px, pa11y AAA sans problème).
- **T-TECH-27-08** (processus disque) : faite avec écart, voir la rétrospective.
- Rien dans le périmètre n'a été sacrifié. `proof.json` rafraîchi dans chaque PR.

## Incidents et écarts de processus

| Incident | Effet | Suite |
|----------|-------|-------|
| `/echange-fichier` affichait « L'extrait n'est pas disponible pour le moment » et l'écran « Échanges ERP » était vide après chaque déploiement : `var/erp-exchange` est dans le système de fichiers du conteneur, neuf à chaque déploiement, alors que la base survit et que l'entrypoint ne recharge pas les fixtures | Défaut visible en production entre le déploiement de #97 et #98 et celui de #100, trouvé au contrôle de production | #100 : `ErpDemoExchange::refreshIfEmpty()` appelé par l'entrypoint ; extrait revérifié en production (3 lignes, e-mails masqués) ; action 3 de la rétro |
| Espace libre tombé à 3-4 Go : 12 caches de rendu de 150 Mo d'un worker, puis des applications ouvertes ; les `rm` des workers sont refusés, Thibaut nettoie | Lots différés | Action 1 de la rétro |
| Un worker a continué à générer des rendus malgré le seuil disque | Consommation de disque après le seuil | Action 1 de la rétro |
| Conflits de `proof.json` entre PR parallèles | Rebase et régénération à chaque PR | PR fusionnées en série, rebase et régénération scriptés ; action 2 de la rétro |
| Commits de #100 libellés « US-195 » par erreur sur la branche partagée (la PR porte le bon titre) | Historique trompeur (`88b26a3`, `881be80`) | Action 4 de la rétro |

## Métriques

| Métrique | Sprint 27 | Sprint 26 |
|----------|-----------|-----------|
| Points engagés / en done | 13 (dont 1 ajouté en cours de sprint) / 13 | 29 (dont 6 ajoutés) / 29 |
| Stories de code avec commit rouge | 4 sur 4 (US-189, US-188, US-193, US-194) ; #100 aussi (`fb1b346`) | 7 sur 9 |
| Tâches décomposées à la clôture | 42 ✅, 1 🟡, 2 ⊘ sans objet, 0 🔲 | 64 ✅, 6 🔲 |
| Plugins au catalogue de la démo | 27 | 27 |
| Espace libre minimal | 3 à 4 Go (cible 5 Go) | 2 à 3 Go |
| CI rouges après tests ciblés verts | 0 sur les runs de la période (1 run annulé sur la branche d'US-189) | 3 |
| Défauts trouvés en production après merge | 1 (échange ERP après déploiement, #100) | 4 |
| Page à défilement horizontal à 360 px | 0 sur les pages contrôlées | 1 (`/demo/accounts`) |
| Publication sans GO | 0 | 0 |

## Démonstration (ordre suggéré)

1. Une fiche produit sur téléphone puis sur grand écran : OUTI-001 (paliers), EPI-VEST-001 (nom long), FIXA-002 (sans palier), puis la vue visiteur.
2. `/echange-fichier` : l'extrait réel du dernier fichier.
3. Le back-office, Intégrations › Échanges ERP (fictif) : lot sortant et accusé de réception simulé.
4. Le pied de page avec le logo de l'auteur.
5. `docs/erp-demo.md` : ce qui est simulé, ce qui reste à US-187.

## Impact sur le backlog

| Élément | Statut |
|---------|--------|
| [US-195](../../backlog/user-stories/US-195.md) Noms de produits sans marque réelle (2) | Candidate au sprint 28 |
| [US-196](../../backlog/user-stories/US-196.md) Photos du catalogue cohérentes et sans marque (3) | Candidate au sprint 28 |
| [US-187](../../backlog/user-stories/US-187.md) Import CSV de prix et de stock (8) | Backlog, non engagée |
| LCP de la home (+19 %) | À surveiller, action 5 de la rétro |

## Prochaines étapes

1. Validation de la revue, de la rétrospective et de la clôture du sprint 27 par Thibaut.
2. Planning du sprint 28 (US-195, US-196 et actions de la rétro).
