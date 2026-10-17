# Webhooks de la démo (US-029)

La démo montre le plugin d'outbox de webhooks (`tmonier/sylius-webhook-outbox-plugin`) **sans aucun appel sortant** : les webhooks signés sont livrés à un récepteur interne, dans le même conteneur, et listés dans le back-office.

## Fonctionnement

1. Une commande est passée (ou payée, expédiée, annulée) : le plugin écrit l'événement dans l'outbox, dans la même transaction.
2. `tmonier:webhook:dispatch` (supercronic, chaque minute, voir `docker/crontab`) signe la requête (Standard Webhooks : `webhook-id`, `webhook-timestamp`, `webhook-signature`) et l'envoie à `http://127.0.0.1:${PORT}/demo/webhooks/receive`.
3. Le récepteur (`WebhookReceiverController`) vérifie la signature avec le secret du point de terminaison de démo, puis stocke le webhook dans `app_received_webhook`.
4. Sophie le voit dans **Intégrations > Webhooks reçus** (`/admin/demo/webhooks`, liste puis détail, charge utile échappée).

Le délai entre la commande et l'apparition du webhook est d'au plus une à deux minutes (cron chaque minute).

## Point de terminaison de démo

- Créé par la fixture `demo_webhook_endpoint` (priorité -80, après l'historique de commandes pour que les fixtures ne remplissent pas l'outbox). Abonné à `order.placed`, `order.paid`, `order.shipped`, `order.cancelled`.
- Le secret de signature est **généré à chaque chargement** et lu en base par le récepteur : il n'est jamais versionné.
- Le récepteur et le worker tournent dans le même conteneur : garder au moins un thread FrankenPHP libre ([ADR-0013](adr/0013-mode-worker-frankenphp.md), threads bornés 8/16).

## Garde-fous

| Risque | Mesure |
|--------|--------|
| URL externe (SSRF) côté admin | `DemoEndpointUrl` (validation du formulaire du plugin, groupe `sylius`) : seule l'URL exacte du récepteur est acceptée en mode démo |
| URL externe côté émission | `ReceiverOnlyHttpClient` décore le transport des webhooks, derrière `OutboundGuardHttpClient` : tout autre URL est refusée avant connexion |
| Redirection | Aucune n'est suivie (`max_redirects = 0`) ; le récepteur répond 2xx directement |
| Appel anonyme du récepteur | Seul `POST /demo/webhooks/receive` (chemin exact) saute la barrière basic auth ; la signature tient lieu d'authentification. `/demo/webhooks/receive/`, `GET`, etc. restent derrière la barrière |
| Hôte `127.0.0.1` | Ajouté aux `trusted_hosts` (`config/packages/demo_framework.yaml`) |
| Signature invalide | 401, rien n'est stocké |
| Rejeu | Dédoublonné sur `webhook-id` (200 `duplicate`) |
| Abus | Limiteur propre (`DEMO_RATE_LIMIT_WEBHOOK_RECEIVER`, 60/min), corps limité à 256 Kio, charge utile conservée tronquée à 8 Kio |
| Rétention | 200 webhooks au plus ; table vidée par la remise à zéro nocturne |

## Réglages

| Variable | Défaut | Rôle |
|----------|--------|------|
| `PORT` | `8080` | Port du serveur web (Caddy), utilisé pour l'URL du récepteur |
| `DEMO_RATE_LIMIT_WEBHOOK_RECEIVER` | `60` | Webhooks acceptés par minute |

Le plugin refuse par défaut les URL `http://` et les réseaux privés : `config/packages/demo_webhooks.yaml` ouvre `allow_http_endpoints` et `127.0.0.0/8`, que la validation et le garde d'émission restreignent ensuite à l'unique URL du récepteur.

## Tests

`tests/Functional/Demo/WebhookReceiverTest.php` (récepteur, page, validation) et `WebhookDeliveryTest.php` (livraison réelle sur un serveur PHP local derrière la barrière, hôte externe refusé, redirection non suivie).
