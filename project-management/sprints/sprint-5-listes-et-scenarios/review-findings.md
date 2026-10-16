# Revue de code — sprint 5 (listes et scénarios)

Date : 2026-10-16. Revue indépendante en deux volets : Symfony (correction, Doctrine, tests) et sécurité (US-028 puis US-027, US-024, US-025). Périmètre : `git diff main...HEAD` de la PR #5.

## Verdict

**GO pour le merge** : aucun défaut critique ni élevé ; deux correctifs appliqués ; quatre points reportés avant toute ouverture publique (sprint 6). La démo reste privée jusque-là.

## Défaut trouvé par le test de bout en bout (et non par la revue)

- **Commande PunchOut créée comme « invitée »** (plugin punchout-cxml) : `PunchoutCartFactory` rattache le client par `setCustomer()`, qui laisse `createdByGuest = true` ; le plugin trade-credit refuse alors le paiement sur compte de tout OrderRequest (`no_account`, HTTP 500). Aucune commande PunchOut ne pouvait aboutir. Contournement démo `App\PunchOut\AuthorizedPunchOutOrderFactory` (décision utilisateur), correctif du plugin au sprint 7.

## Corrigé dans ce sprint

| # | Constat | Correctif |
|---|---------|-----------|
| C1 | `/demo/mails` lisait jusqu'à 50 e-mails l'un après l'autre (4 s max chacun) : un Mailpit lent bloquait la page plusieurs minutes | `MailpitClient::findMany()` envoie toutes les requêtes avant d'en lire une : page bornée à ~4 s (`a1a5cd5`, test dédié) |
| C2 | La fixture d'historique supprimait `<dossier>/*.pdf` sans vérifier le dossier : un chemin vide visait `/*.pdf` | Exception si le dossier des factures est vide (`a1a5cd5`) |

## Non-défauts vérifiés

- « `BuyerCredentialsProvider.php` absent » (revue Symfony) : faux, le fichier est suivi par git et présent dans HEAD.
- Rejeu de l'état signé du simulateur avec le **même** n° de commande : dédupliqué par le plugin (`knownOrderNumber`).
- Boîte de démo : jetons Sylius (64 caractères) et liens `/verify/…` masqués, refus par identifiant, pas de SSRF par l'id, HTML jamais rendu, Mailpit lié à 127.0.0.1.
- Simulateur : secret jamais rendu, état signé HMAC (`hash_equals`, expiration), pas d'open redirect, pas de SSRF (cible loopback fixe), CSRF sur `/start`, XML sûr, garde 404 hors démo.
- Listes partagées et carnet d'adresses : pas d'IDOR entre entreprises, droits admin/créateur/membre respectés, CSRF sur tous les POST, « Livrer ici » limité au panier de la session.
- `AuthorizedPunchOutOrderFactory` n'est injectée que dans `PunchoutCartFactory` : le checkout invité de la boutique n'y passe pas.

## Reporté — à faire AVANT toute ouverture publique (sprint 6)

| # | Gravité (démo publique) | Constat | Piste |
|---|-------------------------|---------|-------|
| R1 | Moyenne | Simulateur public : commandes Acme anonymes à volonté (état signé rejouable 1 h avec un autre n° ; le secret de démo permet aussi de poster sur `/punchout/cxml`), consommation du crédit d'Acme | Accès restreint à `/demo/*` ou nonce à usage unique + rate limiter par IP ; reset régulier d'Acme |
| R2 | Basse | Pas de `framework.trusted_hosts` ; `TRUSTED_PROXIES` = `REMOTE_ADDR` | Hôte public + plages du proxy Railway |
| R3 | Basse | Le limiteur de setup du plugin voit le simulateur en 127.0.0.1 : un visiteur peut bloquer les autres (429) | Limiteur par IP visiteur côté simulateur |
| R4 | Basse → moyenne | La boîte de démo montre tous les e-mails, quel que soit le destinataire (RGPD si inscription réelle) | N'afficher que les destinataires des comptes de démo (liste blanche) |

## Hors périmètre

- `doctrine:schema:validate` (sans `--skip-sync`) : 4 écarts préexistants sur des tables Sylius (refund, payment request) ; la CI ne valide que le mapping.
- Le README annonçait Marc approbateur chez BuildCo, contre `config/demo/accounts.yaml` (un client = une entreprise) — corrigé dans le README.
- Fonction Twig `demo_mailpit_url()` utilisée par aucun template.
