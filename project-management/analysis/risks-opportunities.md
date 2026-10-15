# Risques et opportunités — démo B2B Sylius

## Risques

| # | Risque | Impact | Probabilité | Atténuation |
|---|--------|--------|-------------|-------------|
| R1 | Coût Railway au-delà du crédit Hobby (web et base allumés en permanence) | Moyen | Élevée | Un seul conteneur web (supercronic intégré), MySQL managé, limite d'usage Railway, mesure après une semaine ; mise en veille en option |
| R2 | Interactions entre plugins non testées ensemble (I1 à I10) : checkout bloqué, comportement d'OrderRequest | Élevé | Moyenne | Tests fonctionnels de la démo par scénario ; corrections dans les plugins concernés (patch releases) |
| R3 | Comptes publics détournés : spam par e-mail, webhooks vers des URLs externes (SSRF), contenus injurieux | Élevé | Moyenne | Mailpit sans relais, webhooks limités à un récepteur interne, admin de démo avec des droits restreints si possible, remise à zéro nocturne, rate limiting |
| R4 | Remise à zéro qui échoue (fixtures longues, mémoire) et laisse la démo vide ou cassée | Élevé | Moyenne | Script idempotent avec healthcheck, échec visible (journal), dernière image saine, durée mesurée |
| R5 | Mémoire PHP : compilation du conteneur de plus de 1 Go avec plus de 30 bundles | Moyen | Élevée | Cache préchauffé au build, `memory_limit` adapté, taille du service calibrée |
| R6 | MCP et iframe PunchOut exigent HTTPS, CORS et SameSite None | Moyen | Moyenne | Domaine Railway HTTPS, trusted proxies, scénario PunchOut sans iframe par défaut |
| R7 | Nouveaux plugins (catalogue fermé, PO, listes d'achat) qui allongent le délai | Moyen | Moyenne | MVP strict par plugin, publiés comme les autres (tests, CI, revue sécurité) |
| R8 | Thème qui casse les hooks des plugins à chaque mise à jour Sylius | Moyen | Faible | Ajouts seulement (TC-7), test de rendu de chaque page clé en CI |
| R9 | Démo vue comme une copie de Sylius Plus | Moyen | Faible | Aucune promotion sur les canaux Sylius, badges neutres, pas de référence à Plus |
| R10 | Images produits sous licence douteuse | Moyen | Moyenne | Visuels générés (SVG ou illustrations maison) ou CC0 vérifiés, sources listées |

## Opportunités
- **O1** La démo devient un banc d'intégration des plugins : chaque interaction corrigée améliore les plugins publiés.
- **O2** Les nouveaux plugins (catalogue fermé, référence PO, listes d'achat) comblent des manques réels de l'écosystème Sylius 2, qui n'a pas d'équivalent communautaire.
- **O3** Un simulateur d'outil d'achat (PunchOut cXML/OCI) intégré à la démo montre le PunchOut sans compte Ariba ou SAP.
- **O4** Une boîte Mailpit publique montre les e-mails envoyés (validation, devis, relances de crédit) sans relais réel.
- **O5** MCP : le visiteur connecte Claude à la démo et passe une commande par conversation, une vitrine forte.
- **O6** Les données B2B réutilisables (CSV versionné) servent aussi de fixtures de démo aux plugins.
- **O7** Le Dockerfile et la stack Railway forment un modèle d'hébergement de démo pour des clients.
