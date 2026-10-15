# B — Audit des plugins, lot 2 (rapport brut, agent Explore, 2026-10-15)

Plugins audités : order-hold, punchout-cxml, purchase-approval, quick-order, quote-request, tier-pricing, timeline, trade-credit, webhook-outbox, withdrawal.

Les 10 sont compatibles avec PHP 8.4, Symfony 7.4 et Sylius 2.3 (`^2.2(.9) || ^2.3`). Aucun n'a d'assets JS à builder, de handler Messenger ni de règle security. Aucun ne modifie une entité du cœur.

## Par plugin

**order-hold** — Noyau d'extension, sans UI. Décore les moyens de paiement, la mise à jour des commandes impayées et la page de paiement. Ajoute un listener `kernel.request` qui bloque le paiement. Les autres plugins s'y branchent par tags. Fait échouer le build si les graphes de commande tournent sur winzou (Symfony Workflow obligatoire).

**punchout** :
- Requiert ext-dom, lock, rate-limiter et Argon2id.
- Routes `/punchout/*`, 6 migrations.
- Variables d'environnement `PUNCHOUT_SUPPLIER_DOMAIN` et `PUNCHOUT_SUPPLIER_IDENTITY` (le README parle de `PUNCHOUT_SUPPLIER_ID` : **à vérifier**).
- Cron : `tmonier:punchout:purge`.
- Fixture `tmonier_punchout_demo`.
- Pont company-accounts.
- Hooks panier : bloc de session, remplacement de `checkout`, offcanvas.
- L'iframe exige `SameSite=None; Secure` sur toute l'application.

**purchase-approval** :
- Requiert company-accounts et order-hold.
- Routes `/account/company/approvals` et `/account/company/limits`.
- Cron : `approval:expire`.
- Fixture `tmonier_purchase_approval` (`company` = ACME_INDUSTRIES, `buyer_order_limit` = 50.00), à charger après company-accounts.
- Décore `sylius.email_sender`.
- Hooks : en-tête de la commande dans le compte, remplacement de `pay` ; côté admin, `order.show#right` et une colonne dans la grille des commandes.
- Prepends : workflow.

**quick-order** :
- Pas de table.
- Routes `/quick-order`, `/account/orders/{number}/reorder` (réassort déjà couvert), matrice de variantes.
- Fixture `tmonier_quick_order_demo_catalog`.
- Hooks : surcharge `add_to_cart` sur la fiche produit, lien dans le panier, bouton réassort.
- Ponts tier-pricing et quote.

**quote-request** :
- PDF via knp-snappy et wkhtmltopdf, en option.
- 4 migrations.
- Cron : `quote-request:expire`.
- Fixture `tmonier_quote_request`.
- Processeur de commande en priorité 45 et garde sur `complete`.
- Hooks : fiche produit (50) et panier (150/50).

**tier-pricing** :
- 5 migrations.
- Import et export CSV.
- Fixture `tmonier_tier_pricing` (12 premiers produits de chaque canal).
- Processeur en priorité 44.
- Hooks : `add_to_cart` et remplacement de `item` dans le panier.
- Pool de cache.

**timeline** :
- 1 migration, pas de fixture.
- Commande `tmonier:timeline:backfill` à lancer une fois.
- Hooks admin : fiche commande `#left`, fiche client, colonne de notes.

**trade-credit** :
- Requiert order-hold et symfony/clock.
- Route `/account/trade-credit`.
- Crons : `send-reminders` et `recalculate --apply`.
- Fixture `tmonier_trade_credit` (b2b@example.com, « Acme Industries » portée seulement par l'adresse).
- Réservation sur la transition `complete`.
- Côté shop : seulement le menu du compte.

**webhook-outbox** :
- Requiert un `server_version` de base réel (SKIP LOCKED) et `framework.router.default_uri`.
- Worker `tmonier:webhook:dispatch --loop`, ou cron chaque minute.
- Commande `purge`.
- Admin uniquement.

**withdrawal** :
- Rétractation, B2C.
- Route `/withdrawal`, 1 migration.
- Exige un e-mail de contact sur le canal.
- Hooks : pied de page et boutons de la commande. **Hors sujet sur une démo B2B** : à désactiver ou limiter.

## Conflits et points d'attention
1. Aucune double décoration de service. Les points sensibles du paiement passent par les tags d'order-hold.
2. Processeurs de commande : quote (45) puis tier (44), ordre voulu.
3. Pendant une session punchout, `/quotes/{token}/checkout` n'est pas bloquée et quick-order peut ajouter des lignes au panier punchout : **à tester**.
4. Un OrderRequest cXML déclenche la réservation trade-credit, l'approbation et la garde quote : **à tester**.
5. Hooks empilés sur le panier, la fiche produit et les boutons de commande (quick-order et withdrawal tous deux à -100). Une surcharge applicative de ces hookables casserait les plugins : **le thème doit ajouter, pas remplacer**.
6. Grille des commandes admin : colonnes timeline et approval, sans conflit.
7. Prepends `framework.lock` (flock) partagés, sans conflit sur un seul serveur.
8. `SameSite=None` exigé par punchout pour toute l'application.
9. Données incohérentes entre plugins :
   - trade-credit est par client (b2b@example.com), mais avec company-accounts le propriétaire du compte est la société ;
   - les paliers tier-pricing modifient les seuils d'approbation ;
   - les priorités de fixtures sont toutes à -100.
   **→ Un jeu de données B2B unifié est nécessaire.**
10. Le mailer asynchrone n'est pas recommandé (PDF des devis) : mailer synchrone.

## Pré-requis globaux
- PHP 8.4 (intl, Argon2id), Symfony Workflow, MySQL 8.0.16+ ou PostgreSQL 10+ avec un `server_version` réel.
- Importer `config.yaml` et `routes.yaml` de chaque plugin (sauf order-hold), puis les migrations.
- Variables d'environnement : `DATABASE_URL`, `MAILER_DSN`, `PUNCHOUT_SUPPLIER_*`.
- `framework` : `router.default_uri`, `trusted_proxies`, `trusted_hosts`.
- Crons quotidiens : approval:expire, quote-request:expire, trade-credit:send-reminders et recalculate, punchout:purge, webhook:purge, sylius:cancel-unpaid-orders, sylius:remove-expired-carts.
- Worker : webhook:dispatch, ou cron chaque minute.
- Emplacements pour les badges :
  - shop : fiche produit (summary, add_to_cart), panier (content, items), commande dans le compte (header et boutons), pages dédiées (company, trade-credit, quotes, quick-order), pied de page ;
  - admin : fiche commande (#left, #right), fiche client, formulaire produit, menus.
