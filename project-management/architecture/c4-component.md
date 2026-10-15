# C4 niveau 3 — Composants de l'application

```mermaid
flowchart LR
    subgraph sylius[Sylius 2.3 + plugins]
        core[Sylius Core / Shop / Admin / API]
        plugins[21 plugins tmonier]
        newp[Nouveaux plugins<br/>b2b-access, order-reference,<br/>shopping-list, company-accounts 1.2]
        community[invoicing, refund,<br/>mcp-server, ai-bundle]
    end
    subgraph demo[src/Demo]
        badge[Badge<br/>registre + hooks générés + composant]
        banner[Banner<br/>comptes, Mailpit, prochaine remise à zéro]
        pages[Controller<br/>plugins, about, legal, healthz, mcp]
        sim[PunchoutSimulator]
        wh[Webhook receiver]
        reset[Reset<br/>app:demo:reset + maintenance]
        guard[Security<br/>DemoAdminGuard, OutboundGuard]
        fx[Fixture<br/>suite demo depuis CSV]
    end
    theme[Thème B2B<br/>hooks ajoutés + SCSS]
    badge --> core
    theme --> core
    sim -->|sous-requête| plugins
    wh <-->|Standard Webhooks| plugins
    reset --> fx
    fx --> plugins
    fx --> newp
    guard --> core
```

| Composant | Rôle | Stories |
|-----------|------|---------|
| Badge | Registre `config/demo/badges.yaml`, prepend `sylius_twig_hooks`, composant `DemoPluginBadge` | US-016, US-017 |
| Banner | Bandeau, liens de connexion pré-remplie, interrupteur des badges | US-015 |
| Controller | `/healthz`, `/{_locale}/plugins`, `/about-demo`, `/legal`, `/demo/mcp` | US-014, US-018, US-030 |
| PunchoutSimulator | Setup cXML et OCI, réception du panier, OrderRequest | US-027 |
| Webhook receiver | Réception signée et consultation | US-029 |
| Reset | Commande de remise à zéro, mode maintenance, journal | US-012, US-033 |
| Security | Garde de l'admin de démo, garde des sorties HTTP | US-035, US-036 |
| Fixture | Suite `demo` (CSV → entités Sylius et plugins) | US-007 à US-011 |
