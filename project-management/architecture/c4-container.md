# C4 niveau 2 — Conteneurs (Railway)

```mermaid
flowchart TB
    subgraph railway[Projet Railway « sylius-b2b-demo »]
        subgraph web[Service web — image FrankenPHP]
            caddy[Caddy / FrankenPHP<br/>HTTP, compression, en-têtes]
            app[Application Symfony<br/>Sylius 2.3 + 25 plugins + src/Demo]
            cron[supercronic<br/>tâches des plugins, webhooks, remise à zéro 03:00]
            fs[(FS éphémère<br/>public/media, private/invoices, var/cache)]
            caddy --> app
            cron --> app
            app --> fs
        end
        mysql[(MySQL 8.4<br/>service managé)]
        mailpit[Mailpit<br/>SMTP 1025, UI 8025]
        app -->|réseau privé| mysql
        app -->|SMTP réseau privé| mailpit
        caddy -->|/mails/* GET seulement| mailpit
    end
    internet((Internet)) -->|HTTPS, TLS Railway| caddy
```

| Conteneur | Technologie | Responsabilités | Ressources |
|-----------|-------------|-----------------|------------|
| web | FrankenPHP 1 (PHP 8.4, Caddy), supercronic | Shop, admin, API, MCP, PunchOut, simulateur, tâches, remise à zéro | 1 Go RAM, CPU partagé |
| MySQL | MySQL 8.4 (Railway) | Données Sylius et plugins | volume Railway |
| Mailpit | axllent/mailpit | Réception et consultation des e-mails | minimal |

Le cache applicatif, les verrous (flock) et les limiteurs (cache filesystem) restent locaux : une seule instance, pas de Redis (TH-5).
