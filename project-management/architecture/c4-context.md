# C4 niveau 1 — Contexte

```mermaid
flowchart LR
    hugo([Hugo / Nadia<br/>visiteurs]) -->|HTTPS| demo[[Démo B2B Sylius<br/>Railway]]
    personas([Comptes de démo<br/>Claire, Paul, Isabelle, Sophie, Léa]) -->|HTTPS| demo
    claude([Claude / assistant IA]) -->|MCP + OAuth 2.1| demo
    demo -->|SMTP interne| mailpit[(Mailpit<br/>boîte de démo)]
    hugo -->|lecture seule| mailpit
    thibaut([Thibaut<br/>mainteneur]) -->|git push| github[[GitHub<br/>CI + dépôt]]
    github -->|déploiement| demo
    demo -.->|alerte d'échec de remise à zéro<br/>seule sortie autorisée| ntfy[(Service de notification)]
    packagist[[Packagist<br/>plugins tmonier]] -->|composer au build| github
```

- L'outil d'achat (Ariba ou SAP) est **simulé à l'intérieur** de la démo : aucun système d'e-procurement réel n'est appelé.
- Aucun e-mail réel, aucun webhook vers l'extérieur, aucune API IA (générateur `demo`).
