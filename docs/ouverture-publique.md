# Ouverture publique de la démo

Application de l'US-050. Pas-à-pas pour passer la démo de **privée** (porte HTTP basique) à **publique**, la vérifier, et revenir en arrière en un geste. La bascule n'est jamais faite sans **GO explicite** de l'utilisateur. Contexte : [durcissement-demo.md](durcissement-demo.md), [deploiement-railway.md](deploiement-railway.md), [mcp.md](mcp.md), [ADR-0008](adr/0008-securite-de-la-demo-publique.md).

Politique : la démo est **publique mais non promue** (aucune annonce sur les canaux Sylius, aucune indexation par les moteurs de recherche) et ne contient **aucune donnée réelle**.

## Les deux modes

| | Privé (aujourd'hui) | Public |
|---|---|---|
| `DEMO_BASIC_AUTH` | `1` | `0` |
| `DEMO_REGISTRATION_OPEN` | `1` | `0` |
| `DEMO_PUNCHOUT_PUBLIC_ENDPOINT` | `0` | `0` |
| Visiteur anonyme | `401` (sauf `/health`, `/healthz`) | `200` |
| Guide MCP `/{locale}/mcp` | avertissement « démo privée » | sans avertissement, un assistant hébergé peut se connecter |
| Inscription, `POST /api/v2/shop/customers` | ouvertes | `403` |
| Commande invité (étape adresse du checkout) | ouverte (canal Particuliers) | `403` pour un visiteur non connecté |
| Demande de devis invité | ouverte | redirigée vers la connexion |
| `robots.txt` | derrière la porte (`Disallow: /` pour qui a le mot de passe) | `User-agent: *` / `Disallow: /` |
| En-tête `X-Robots-Tag` | `noindex, nofollow` | `noindex, nofollow` |
| `app:demo:preflight` | exige `DEMO_BASIC_AUTH_USER` et `DEMO_BASIC_AUTH_PASSWORD_HASH` valides | n'exige pas la porte, refuse de démarrer si les inscriptions ou l'endpoint PunchOut public sont ouverts |

### Règle inscriptions et invités (décision de l'US-050)

Une seule variable commande tout ce par quoi un visiteur laisserait des données personnelles dans la liste des clients de Sophie : **`DEMO_REGISTRATION_OPEN`**. À `0` :

- le formulaire d'inscription et l'API d'inscription répondent `403` (`RegistrationGateListener`) ;
- l'étape adresse du checkout répond `403` à un visiteur non connecté (`GuestCheckoutGateListener`) : c'est là qu'un invité saisit nom, e-mail et adresse. Un client connecté (comptes de démo) commande normalement ;
- la demande de devis invité est désactivée : `allow_guests` du plugin suit `DEMO_REGISTRATION_OPEN` (`config/packages/demo_hardening.yaml`). Un invité est renvoyé vers la connexion, un client connecté demande son devis normalement.

Le **preflight** (`app:demo:preflight`, lancé par l'entrypoint du conteneur) **refuse de démarrer** quand la porte est ouverte et que `DEMO_REGISTRATION_OPEN` n'est pas `0` ou que `DEMO_PUNCHOUT_PUBLIC_ENDPOINT` vaut `1`. C'est un refus au démarrage, pas à chaque requête : un déploiement oublié échoue, Railway garde la version précédente en service. Le lancement local (`compose.yaml`) a la porte ouverte : il ferme donc aussi les inscriptions par défaut (`DEMO_REGISTRATION_OPEN=1` possible avec `DEMO_BASIC_AUTH=1` et des identifiants).

Le même interrupteur ferme l'**avis produit anonyme** (`sylius_shop_product_review_create` : un visiteur non connecté y saisit e-mail et nom, ce qui crée un client invité) : `403` comme pour la commande invité, et le POST est limité par `DEMO_RATE_LIMIT_FORMS`.

Les visiteurs sans compte peuvent donc toujours parcourir le catalogue, se connecter avec les comptes de démo (`/demo/accounts`), commander avec eux et essayer le simulateur PunchOut ; ils ne peuvent pas créer de client.

## Comptes de démo partagés et risques acceptés

- **Comptes clients de démo** (ceux de `/demo/accounts`, mot de passe public) : changement de mot de passe et d'e-mail refusés (flash « Compte de démo partagé »), `SharedDemoAccountGuardListener`. Adresses, membres et crédit restent modifiables, la remise à zéro de 03:00 les restaure.
- **Défiguration par Sophie** : décision de l'utilisateur du 2026-10-17, Sophie garde ses droits d'écriture en mode public ; le risque de défiguration visible jusqu'au reset de 03:00 est **accepté** (démo non promue, données fictives). Détail : [securite-admin-demo.md](securite-admin-demo.md).
- **Limiteur global** : `DEMO_RATE_LIMIT_SHOP` (300 requêtes PHP par minute et par IP, défaut) s'ajoute aux limiteurs par formulaire ; `/health`, `/healthz`, le récepteur de webhooks signés et le simulateur interne en sont exemptés.
- **`DEMO_MODE` obligatoire sur Railway** : le preflight échoue si `RAILWAY_ENVIRONMENT` est défini et `DEMO_MODE` ne vaut pas `1` (sinon limiteurs, hôtes de confiance et garde sortant seraient désactivés sans bruit).
- **Tous les limiteurs dépendent de `X-Real-IP`** posé par l'edge Railway (`RailwayClientIpListener`) : voir la vérification 11 ci-dessous.

## Prérequis

- [ ] Revue de sécurité de l'US-050 (T-050-01/02) sans constat critique ou majeur ouvert.
- [ ] Moniteur externe créé sur `https://<domaine>/healthz` (UptimeRobot ou Better Stack, alerte vers la messagerie de l'utilisateur) : voir [Moniteur externe](#moniteur-externe).
- [ ] Reset nocturne et `/healthz` verts (US-049, [remise-a-zero-demo.md](remise-a-zero-demo.md)).
- [ ] **GO explicite** de l'utilisateur.

## Bascule

1. Dans `.railway/railway.ts`, passer `OPEN_TO_PUBLIC` à `true` (une seule ligne). Elle positionne `DEMO_BASIC_AUTH=0` et `DEMO_REGISTRATION_OPEN=0` ; `DEMO_PUNCHOUT_PUBLIC_ENDPOINT` reste `0`.
2. Relire le diff (`git diff .railway/railway.ts`), commit `chore(ops): open the demo to the public`.
3. Appliquer : `railway config apply` (déploiement du service `web`). Le preflight du conteneur vérifie les variables ; le déploiement échoue sans effet si l'une manque.
4. Vérifier (section suivante).

Rien d'autre à changer : `TRUSTED_HOSTS`, les limiteurs, le mailer en puits et les secrets restent ceux du mode privé. Les identifiants de la porte (`DEMO_BASIC_AUTH_USER`, `DEMO_BASIC_AUTH_PASSWORD_HASH`) peuvent rester en place, inutilisés : c'est ce qui permet le retour arrière.

## Vérifications après bascule

`URL` est l'adresse publique de la démo. Chaque commande doit donner le résultat indiqué.

```bash
URL=https://<domaine>

# 1. Anonyme : 200 (et non 401)
/usr/bin/curl -s -o /dev/null -w '%{http_code}\n' "$URL/fr_FR/"                         # 200

# 2. Santé : 200, ok
/usr/bin/curl -s -w ' %{http_code}\n' "$URL/healthz"

# 3. Inscription refusée
/usr/bin/curl -s -o /dev/null -w '%{http_code}\n' "$URL/fr_FR/register"                 # 403

# 4. Commande invité refusée (canal Particuliers, visiteur non connecté)
/usr/bin/curl -s -o /dev/null -w '%{http_code}\n' -b 'demo_channel=B2C' "$URL/fr_FR/checkout/address"   # 403

# 5. Devis invité : renvoyé vers la connexion
/usr/bin/curl -s -o /dev/null -w '%{http_code} %{redirect_url}\n' -b 'demo_channel=B2C' "$URL/fr_FR/quote-request"   # 302 .../login

# 6. Moteurs de recherche tenus à l'écart
/usr/bin/curl -s "$URL/robots.txt"                                                      # User-agent: * / Disallow: /
/usr/bin/curl -sI "$URL/fr_FR/" | /usr/bin/grep -i x-robots-tag                         # noindex, nofollow

# 7. Guide MCP : sans avertissement « démo privée »
/usr/bin/curl -s "$URL/fr_FR/mcp" | /usr/bin/grep -c 'data-mcp-private-note'            # 0

# 8. Métadonnées OAuth du serveur MCP publiées
/usr/bin/curl -s -o /dev/null -w '%{http_code}\n' "$URL/.well-known/oauth-authorization-server"   # 200

# 9. API REST toujours désactivée en production
/usr/bin/curl -s -o /dev/null -w '%{http_code}\n' "$URL/api/v2/shop/products"           # 404

# 10. Plafonds de débit actifs : au-delà de DEMO_RATE_LIMIT_PUBLIC par minute, 429
for i in $(seq 1 130); do /usr/bin/curl -s -o /dev/null -w '%{http_code}\n' "$URL/demo/accounts"; done | /usr/bin/sort | /usr/bin/uniq -c   # des 429 apparaissent

# 11. Les limiteurs voient l'IP du visiteur et non celle du proxy : deux IP différentes ne partagent pas le compteur.
#     Depuis une machine, 130 appels (vérification 10) donnent des 429 ; depuis une seconde connexion (autre IP, 4G par exemple), la page répond encore 200.
#     Si les deux connexions sont limitées ensemble, X-Real-IP n'est pas lu : ne pas laisser ouvert, refermer la porte.

# 12. Avis produit invité refusé (canal Particuliers, slug d'un produit du catalogue)
/usr/bin/curl -s -o /dev/null -w '%{http_code}\n' -b 'demo_channel=B2C' "$URL/fr_FR/products/<slug>/reviews/new"   # 403
```

À la main (navigateur) :

- [ ] Connecté avec un compte client de démo, le changement de mot de passe et d'e-mail est refusé (flash « Compte de démo partagé »).
- [ ] L'admin `/admin` se connecte avec le compte de Sophie et reste en lecture seule hors des zones autorisées ([durcissement-demo.md](durcissement-demo.md)).
- [ ] `/demo/mails` ne liste que les e-mails des comptes de démo.
- [ ] Mailpit n'a **aucun domaine public** (tableau de bord Railway, service `mailpit` : seul le domaine privé existe) ; son port 8025 est injoignable depuis l'extérieur.
- [ ] Le moniteur externe reçoit des `200` sur `/healthz`.
- [ ] Connexion d'un assistant hébergé au serveur MCP (T-050-11, résultat consigné dans [mcp.md](mcp.md)).

## Retour arrière en un geste : refermer la porte

1. Dans `.railway/railway.ts`, remettre `OPEN_TO_PUBLIC` à `false`, commit, `railway config apply`.
2. Vérifier : `curl -s -o /dev/null -w '%{http_code}\n' "$URL/fr_FR/"` doit donner `401`, `/healthz` reste `200`.

Les identifiants de la porte étant restés en place, la démo est de nouveau privée dès le déploiement terminé. Le preflight échouerait si les identifiants avaient été supprimés : les remettre d'abord ([deploiement-railway.md](deploiement-railway.md), section secrets).

En urgence, sans passer par le dépôt : `railway variables --service web --set DEMO_BASIC_AUTH=1` (puis redéploiement), à reporter ensuite dans `railway.ts` pour que le dépôt reste la référence.

## Moniteur externe

- Service : UptimeRobot ou Better Stack, compte de l'utilisateur (T-050-09).
- URL : `https://<domaine>/healthz`, méthode GET, attendu `200`, intervalle 1 à 5 minutes, alerte par e-mail vers l'utilisateur.
- `/healthz` est exempté de la porte en mode privé : le moniteur peut être créé **avant** la bascule et ne change pas avec elle.
- Le limiteur `DEMO_RATE_LIMIT_HEALTH` (30 appels par minute et par IP) est très au-dessus d'un moniteur à 1 appel par minute.
- `503` sur `/healthz` : base indisponible, migration en attente, configuration dangereuse ou dernière remise à zéro en échec (corps `reset failed`, voir [remise-a-zero-demo.md](remise-a-zero-demo.md)).

## robots.txt et noindex

La démo n'est pas promue : `GET /robots.txt` répond `Disallow: /` et chaque réponse porte `X-Robots-Tag: noindex, nofollow`, dans les deux modes (`RobotsController`, `NoIndexSubscriber`). `public/robots.txt` n'existe plus : le serveur web servirait le fichier avant l'application, qui ne pourrait plus répondre selon le mode.

Limite connue : `Disallow: /` empêche l'exploration, donc un moteur peut ne pas lire l'en-tête `noindex` ; une adresse partagée par un tiers peut alors apparaître sans contenu. Pour désindexer une adresse déjà connue, il faudrait autoriser l'exploration (retirer le `Disallow`) le temps que le `noindex` soit lu. Non fait : la démo n'est pas promue, personne ne la publie.

## Rappel de non-promotion

Aucune annonce sur Slack, GitHub Discussions, réseaux sociaux ou les canaux Sylius ; pas de lien depuis la documentation des plugins qui chevauchent Sylius Plus. La démo reste un lien partagé à la main.

## Problèmes courants

| Symptôme | Cause | Action |
|----------|-------|--------|
| Déploiement en échec après `railway config apply`, journal « DEMO_REGISTRATION_OPEN is not 0 » | porte ouverte avec inscriptions ouvertes | passer `OPEN_TO_PUBLIC` à `true` (les deux variables), pas seulement `DEMO_BASIC_AUTH` |
| `503` sur toutes les pages | porte active sans identifiants valides, ou `TRUSTED_HOSTS` / mailer invalides | `app:demo:preflight` dans le conteneur nomme la cause |
| Le guide MCP affiche encore « démo privée » | déploiement pas terminé, ou `DEMO_BASIC_AUTH` encore à `1` | `railway variables --service web`, attendre le déploiement |
