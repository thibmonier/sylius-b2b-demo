# Sprint Review - Sprint 43

## Informations

| Attribut | Valeur |
|----------|--------|
| Date | 2026-11-01 |
| Sprint Goal | Compléter le copilote d'achat avec les garde-fous de sécurité et l'adaptateur LLM réel, et ajouter le lien passeport numérique dans les factures Factur-X |
| Points engagés | 13 |
| Points livrés | 13 (100 %) |
| Vélocité | 13 pts |

## Stories livrées

### US-214 — Copilote d'achat : garde-fous et journal d'audit (5 pts)

- Garde-fous complets câblés dans le plugin copilot : scope guard, whitelist d'outils, audit logger, rate limiter
- Cassettes de refus ajoutées pour les intentions commande / paiement (FR et EN)
- Le contrôleur transmet désormais le contexte `member_id` et `member_scopes`

### US-216 — Copilote d'achat : adaptateur LLM réel pour les intégrateurs (5 pts)

- Adaptateur Symfony AI (`SymfonyAiCopilotAdapter`) intégrant `PlatformInterface::invoke()`
- Chaîne de fallback : l'adaptateur réel est tenté en premier, le replay prend le relais en cas d'erreur
- Limiteur de coût quotidien via Symfony Cache
- Configuration DI conditionnelle : `ai.enabled: true` active la chaîne réelle ; `false` reste en replay

### US-234 — Lien DPP dans la facture Factur-X (3 pts)

- Plugin Factur-X v1.2.0 : nouveau port `PassportUrlResolverInterface` et document de référence additionnel BT-128 (TypeCode 916)
- Pont DPP → Factur-X dans la démo (`PassportUrlResolver`) : résout l'URL publique du passeport numérique par code variante
- Activation via `dpp_link_in_invoice: true`

## Releases plugins

| Plugin | Version | Notes |
|--------|---------|-------|
| `sylius-b2b-sales-copilot-plugin` | v1.3.0 → v1.3.1 | v1.3.0 : garde-fous + adaptateur LLM ; v1.3.1 : correctif config.yaml (référence paramètre non résolu) |
| `sylius-facturx-plugin` | v1.2.0 | Port passeport, document de référence BT-128 |

## PR démo

- PR [#150](https://github.com/thibmonier/sylius-b2b-demo/pull/150) (draft, CI en cours)
- 3 commits : planning sprint, bump copilot ^1.3.1, bridge DPP → Factur-X

## Feedback

- Le bug `config/config.yaml` (référence à un paramètre non résolu lors du chargement de l'extension framework) a été détecté et corrigé immédiatement (v1.3.1). Ce type de régression est lié au fait que la démo importe explicitement les `config/config.yaml` des plugins — les méthodes `prepend()` rendent cette importation redondante pour les configs framework.
- T-TECH-43-04 (correctif montant maquette copilote) reste à traiter.

## Décisions

- Le `config/config.yaml` du plugin copilot a été vidé de son contenu framework (rate_limiter, monolog) car l'Extension les gère via `prepend()`. Les applications hôtes n'ont plus besoin d'importer ce fichier pour ces deux aspects.
