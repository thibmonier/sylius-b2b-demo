# ADR-0018 — Copilote d'achat : format de rejeu et adaptateur LLM

- **Statut** : Accepté (2026-10-28, GO de Thibaut avec amendements, voir « Décision de Thibaut »)
- **Contexte projet** : sylius-b2b-demo, sprint 34, spike US-210 (3 points, timebox 6 h)

## Contexte

Un acheteur B2B attend un assistant conversationnel dans son espace client qui prépare son panier à partir d'une demande en langage courant (« comme le mois dernier », « ajoute 50 unités de la référence XYZ »). Le plugin `sylius-b2b-sales-copilot-plugin` (EPIC-16) fournira cette capacité en s'appuyant sur les outils existants du plugin `b2b-mcp` et les prix négociés (`tier-pricing`).

**Décision de Thibaut du 2026-10-28** : la démo publique fonctionne en **mode rejoué** (réponses enregistrées, aucun appel LLM payant). Le plugin garde un adaptateur LLM réel pour les intégrateurs qui le déploient chez eux.

Ce spike répond à : quel format de rejeu, quelle architecture d'adaptateur, quels outils exposer, et y a-t-il un recoupement avec Sylius Plus.

## Recherche documentée

Date de consultation : 2026-10-28. Timebox respecté : 6 h.

### Format de rejeu (pratiques 2026)

Plusieurs bibliothèques open source utilisent un format « cassette » pour enregistrer et rejouer des interactions LLM sans appel réseau :

- **Formats observés** : YAML (llmvcr), JSON pretty-printed (llm_replay_eval), JSON Lines append-only (Bernstein, LLMTivo avec compression zstd)
- **Clés de correspondance** : combinaisons de modèle, prompt, provider, température, max_tokens ; certaines bibliothèques simplifient en ignorant la température
- **Ordre des réponses** : FIFO par clé pour les appels répétés (Bernstein)
- **Comportement sur miss** : mode strict (erreur), mode permissif (message de repli), **consensus : jamais d'appel LLM en mode rejeu**

**Sources** :
- [llmvcr (PyPI)](https://pypi.org/project/llmvcr/)
- [llm_replay_eval (Pub.dev)](https://pub.dev/packages/llm_replay_eval)
- [Bernstein deterministic replay](https://bernstein.readthedocs.io/en/latest/operations/deterministic-replay/)

### Adaptateur LLM (pattern standard 2026)

Le pattern adapter est devenu le standard pour abstraire les différences entre providers LLM :

- **Port** : interface commune avec contrat générique (objet chat request)
- **Adaptateurs** : un par provider (Anthropic, OpenAI, Ollama, mock)
- **Normalisation** : streams, events, tokens dans un format unifié
- **Failover** : circuit breaker, rotation entre providers en cas d'échec
- **Bibliothèques de référence** : llm-seam (Python, 3 méthodes + fallback), aisuite (Andrew Ng, base Provider class), FreeLLMAPI (BaseProvider abstrait), ractogateway (BaseLLMAdapter)

**Sources** :
- [LLM Provider Abstraction in 2026](https://tkmxai.it.com/llm-provider-abstraction-in-2026)
- [LLM adapter pattern (Flavio Copes)](https://flaviocopes.com/llm-adapter-pattern/)
- [llm-seam (PyPI)](https://pypi.org/project/llm-seam/)

### Outils b2b-mcp disponibles

Le plugin `tmonier/sylius-b2b-mcp-plugin` (v1.1) expose via MCP :

**Lecture** (scope `b2b:read`) :
- `list_my_orders`, `get_order` : commandes passées du client
- `get_checkout_link` : panier et lien vers le checkout
- `list_my_quotes`, `get_trade_credit_statement`, `list_pending_approvals` : avec plugins optionnels

**Modification panier** (scope `b2b:cart`, idempotent) :
- `reorder` : articles d'une commande passée dans le panier
- `quick_order_add` : ajout par SKU avec validation
- `request_quote` : demande de devis depuis le panier

**Principes b2b-mcp** : aucun outil ne paie, ne passe commande, ni ne modifie le compte. Chaque appel est audité et rate-limité par connexion OAuth.

**Source** : [sylius-b2b-mcp-plugin README](https://github.com/thibmonier/sylius-b2b-mcp-plugin)

### Recoupement Sylius Plus

La vérification du non-recoupement avec l'offre Sylius Plus a été effectuée par consultation de la documentation publique. Le détail de l'analyse est consigné dans la note privée de sécurité.

## Décision proposée

### 1. Format de rejeu

**JSON Lines** (`.jsonl`), un enregistrement par ligne, append-only :

```jsonl
{"version":"1.0","intent":"reorder","normalized":{"action":"reorder","order_number":"000042"},"timestamp":"2026-10-15T10:30:00Z","tools":[{"name":"reorder","args":{"order_number":"000042"},"result":"success"}],"response":"J'ai ajouté les articles de la commande 42 à votre panier. Voulez-vous passer au checkout ?"}
```

**Clé de correspondance** : hash SHA-256 de `{"intent":normalisé,"action":normalisée}`, tronqué à 16 caractères pour le nom de fichier.

**Comportement sur miss** : message de repli générique (« Scénario non enregistré »), **jamais d'appel LLM**.

**Validation** : JSON Schema strict au chargement, rejet silencieux des cassettes invalides avec log.

### 2. Architecture d'adaptateur

**Port** (interface) :
```php
interface SalesCopilotAdapterInterface
{
    public function chat(string $userMessage, array $context): CopilotResponse;
}
```

**Adaptateurs** :
- `ReplayAdapter` : charge la cassette correspondante, retourne la réponse enregistrée ou un message de repli
- `AnthropicAdapter` : appel réel à l'API Anthropic Claude (pour les intégrateurs, US-216 sprint 42)
- `OpenAIAdapter` : appel réel à l'API OpenAI (optionnel, si demandé)

**Normalisation** : extraction de l'intention structurée (action + paramètres) depuis le message utilisateur, indépendamment du provider.

**Configuration** : un réglage choisit l'adaptateur (rejoué par défaut pour la démo, réel pour les intégrateurs).

### 3. Outils exposés au copilote

**Liste blanche** (réutilisables de b2b-mcp) :
- Lecture : `list_my_orders`, `get_order`, `get_checkout_link`, `list_my_quotes`, `get_trade_credit_statement`
- Panier : `reorder`, `quick_order_add`, `request_quote`

**Liste noire** (jamais exposés) : voir note privée de sécurité (référence ci-dessous).

**Validation** : chaque outil vérifie le scope OAuth (`b2b:read` ou `b2b:cart`), génère une clé d'idempotence pour les modifications de panier, et logue l'appel via `ToolCallLogger`.

### 4. Conclusion Sylius Plus

La vérification du non-recoupement avec l'offre Sylius Plus a été effectuée (détail dans la note privée).

## Note privée de sécurité

Les détails de sécurité (liste exhaustive des outils interdits, garde-fous, seuils, scénarios d'attaque) sont consignés dans une **note privée**, hors dépôt public.

**Référence** : remise à Thibaut au rapport de fin de US-210 (chemin du scratchpad w210).

## Questions ouvertes

1. **Choix de cassette en mode rejoué** : sans LLM pour normaliser une phrase libre en intention, comment la démo choisit-elle la cassette ? Scénarios proposés en boutons/suggestions ? Correspondance exacte de phrases enregistrées ? Autre mécanisme ?
2. **Nombre maximum de cassettes** : 50 scénarios enregistrés suffisent-ils pour la démo ?
3. **Provider pour l'adaptateur réel** (US-216, sprint 42) : Anthropic Claude uniquement, ou aussi OpenAI ?
4. **UI du widget** : confirmation utilisateur avant chaque action de panier, ou validation globale du panier préparé ?
5. **Audit** : faut-il un journal séparé pour le copilote, ou l'audit b2b-mcp existant suffit-il ?
6. **Promotion du plugin** : Thibaut souhaite-t-il promouvoir le plugin sur son profil LinkedIn, ou rester discret ?

## Conséquences

- **Positives** : démo sans coût d'API, architecture extensible (ajout d'un nouveau provider = un nouvel adaptateur), réutilisation de b2b-mcp (outils, OAuth, audit, rate limiting), vérification du non-recoupement Sylius Plus effectuée.
- **Négatives** : cassettes à maintenir manuellement, mode rejoué ne montre pas l'adaptabilité réelle d'un LLM, adaptateur réel planifié au sprint 42 (US-216), question ouverte sur le mécanisme de choix de cassette.

## Décision

**Décision de Thibaut (2026-10-28)** : approche acceptée, avec ces choix.

- En mode rejoué, la démo propose des **scénarios en boutons** ; la saisie libre affiche un message qui explique le mode démo.
- L'acheteur **confirme chaque action** de panier préparée par le copilote.
- L'adaptateur réel d'US-216 passe par **Symfony AI** (couche multi-fournisseurs) plutôt que par des adaptateurs maison par fournisseur ; ce choix remplace les adaptateurs Anthropic et OpenAI décrits plus haut.

Restent ouverts : le nombre de scénarios (planning d'US-212), le journal d'audit (réutilisation de l'audit b2b-mcp proposée) et la promotion du plugin.

---

**Sources citées** :
- [llmvcr (PyPI)](https://pypi.org/project/llmvcr/)
- [llm_replay_eval (Pub.dev)](https://pub.dev/packages/llm_replay_eval)
- [Bernstein deterministic replay](https://bernstein.readthedocs.io/en/latest/operations/deterministic-replay/)
- [LLM Provider Abstraction in 2026](https://tkmxai.it.com/llm-provider-abstraction-in-2026)
- [LLM adapter pattern (Flavio Copes)](https://flaviocopes.com/llm-adapter-pattern/)
- [llm-seam (PyPI)](https://pypi.org/project/llm-seam/)
- [sylius-b2b-mcp-plugin README](https://github.com/thibmonier/sylius-b2b-mcp-plugin)
