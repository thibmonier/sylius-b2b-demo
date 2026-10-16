# Constats — sprint 8 (US-040)

Date : 2026-10-16. Clôture des constats mineurs M2, M4, M5, M6 et M8 de la revue de sécurité du sprint 6
([review-findings](../sprint-6-railway-securite/review-findings.md)). Les preuves sont des tests sur le comportement rendu
(HTTP, HTML, base). Contre-vérification indépendante (T-040-14) : faite, voir la section dédiée en fin de document.

| # | Constat | Statut | Preuve (code / test) |
|---|---------|--------|----------------------|
| M2 | Clients saisis par les visiteurs lisibles via la liste de Sophie | **Corrigé avec interrupteur ; risque accepté tant que la démo est privée** | `RegistrationGateListener`, `DEMO_REGISTRATION_OPEN` / `RegistrationClosedTest` (formulaire 403, aucun client créé, liste de Sophie sans donnée, API boutique 403, formulaire servi par défaut) |
| M4 | Images téléversées non purgées au reset | **Corrigé** | `OrphanImagePurger`, étape 4 de `DemoResetCommand` / `OrphanImagePurgerTest`, `ResetMediaTest` (orphelin purgé, fixtures conservées, échec journalisé et `reset failed`) |
| M5 | `scoped_clients` du MCP hors du garde sortant | **Corrigé** | `demo.outbound_guard.transport` (décore `http_client.transport`) / `McpOutboundGuardTest` (hôte externe refusé, redirection non suivie), `OutboundGuardHttpClientTest` |
| M6 | `/healthz` sans limite, SQL à chaque appel | **Corrigé** | limiteur `demo_health` (`RateLimitSubscriber`), verdict gardé 5 s (`HealthController`) / `HealthzTest` (rafale 429, fréquence normale jamais bloquée), `HealthControllerTest` (une requête SQL dans la fenêtre) |
| M8 | Retour du simulateur non authentifié | **Corrigé** | `ReturnAuthenticator` (journal du plugin, usage unique, expiration) / `PunchOutSimulatorReturnTest` (légitime, jamais envoyé, falsifié, rejoué, expiré), `PunchOutSimulatorSmokeTest` (message réel du plugin accepté) |

## Décisions et acceptations écrites

- **M2 — risque accepté tant que la démo est privée.** L'inscription fait partie de ce que la démo montre (validation par Sophie,
  US-020) : la fermer par défaut retirerait la démonstration. Les données saisies sont lisibles par les seuls porteurs du mot
  de passe de la porte, un encart prévient (« aucune donnée réelle »), le reset nocturne purge tous les comptes hors fixtures.
  **Avant l'ouverture publique : `DEMO_REGISTRATION_OPEN=0`** (à ajouter à la liste « Actions avant l'ouverture publique »).
- **M5 — les redirections ne sont plus suivies en mode démo** (le garde ne voit que la première URL). Les hôtes autorisés
  (API boutique, Mailpit, webhooks, icônes) ne redirigent pas.
- **M4 — les tests ont leur propre répertoire d'images** (`var/media-test`, `config/packages/test/demo_media.yaml`) : un
  passage de la suite ne supprime jamais les images d'une base de développement.
- **M8 — l'actualisation de la page de retour affiche « déjà reçu »** (usage unique du retour) : relancer une session.

## Limites connues

- Les fixtures de la suite `demo` ne portent aucune image aujourd'hui : « images de fixtures conservées » est démontré par
  le test unitaire du purgeur (chemin connu conservé), pas par une image réelle de fixture.
- L'échec de purge fait échouer le reset **après** le commit : les données sont intactes, `/healthz` répond 503 jusqu'au
  prochain reset réussi.

## Contre-vérification indépendante (T-040-14) et revue à froid

Deux agents ont relu le code sans le modifier : un auditeur de sécurité pour US-040 et un relecteur Symfony pour US-030,
US-041, US-042 et US-043. L'orchestrateur a revérifié chaque constat dans le code avant de le retenir.

| # | Constat | Verdict | Suite donnée |
|---|---------|---------|--------------|
| M2 | Inscription | Confirmé (interrupteur) | Ajouté à la liste d'ouverture publique (`docs/deploiement-railway.md`) |
| M4 | Purge des images | Confirmé : la liste des chemins est construite avant toute suppression ; `Finder` ne suit pas les liens | — |
| M5 | Garde sortant | Confirmé : IP décimales ou octales, `127.1` et IPv6 refusées ; les hôtes autorisés ne sont pas fournis par le visiteur | — |
| M6 | `/healthz` | Confirmé : même `VisitorIpResolver` que les autres limiteurs ; verdict en `cache.app` | — |
| M8 / N1 | Usage unique du retour et de la session du simulateur, plafond quotidien | **Course** : lecture `isHit()` puis `save()` sans verrou ; des POST parallèles passaient tous | **Corrigé** (`1f3888e`) : contrôle et écriture sous verrou Symfony Lock (store `flock`, une instance) ; test qui échoue sans le verrou |
| N2 | Création de clients par commande invité ou devis invité (non couverte par M2) | À vérifier | Liste d'ouverture publique (étape 3) |
| N3 | Jetons d'usage unique perdus quand un redéploiement vide `var/cache` | Bas, accepté | Un retour de moins d'une heure redevient rejouable après un déploiement ; démo privée |
| R1 | Routes de prix non restreintes (quick-order, devis) | **Faux positif** : même liste que l'ancienne passe ; les pages quick-order n'affichent aucun prix ; les devis sont protégés par leur jeton ou le compte client | — |
| R2 | `ApprovedRegistrationChecker` ne tient pas compte du canal de la connexion | Connu : le contrat du plugin ne transmet que le client | Évolution du plugin b2b-mcp si une règle par canal est voulue |
| R3 | README citant la classe supprimée `McpApprovalGuard` | Corrigé (`d554cb4`) | — |
| R4 | Bandeau « prochaine remise à zéro » | Confirmé : 03:00 Europe/Paris, identique à `docker/crontab` (`CRON_TZ`) | — |
