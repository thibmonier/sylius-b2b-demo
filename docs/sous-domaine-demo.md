# Sous-domaine de la démo (US-066)

Préparation du remplacement de l'URL provisoire Railway par un sous-domaine du site de l'utilisateur, sans coupure. Le préfixe est `sylius-b2b` (décision du 2026-10-17) : `sylius-b2b.<domaine du site>`. Le domaine du site n'étant pas connu, **aucune valeur réelle n'est dans le dépôt** : le code lit la variable `DEMO_PUBLIC_HOST` et les docs écrivent `<sous-domaine>`. Contexte : [deploiement-railway.md](deploiement-railway.md), [ouverture-publique.md](ouverture-publique.md), [durcissement-demo.md](durcissement-demo.md).

## Où l'hôte compte

| Endroit | Comportement | Action |
|---------|--------------|--------|
| `TRUSTED_HOSTS` (hôtes de confiance) | Un hôte hors liste répond `400`. Liste : `TRUSTED_HOSTS` (domaine Railway d'origine, constante `RAILWAY_SERVICE_DOMAIN` de `.railway/railway.ts`), `DEMO_PUBLIC_HOST` (sous-domaine, échappé et ancré par `PublicHostPatternProcessor`), `healthcheck.railway.app`, `127.0.0.1` | `DEMO_PUBLIC_HOST` à poser dans Railway. Absent, vide ou invalide (motif, URL, chemin) : seul le domaine Railway est accepté |
| Cookies et sessions | Aucun `cookie_domain` : cookies liés à l'hôte. Une session ouverte sur le domaine Railway n'existe pas sur le sous-domaine | Aucune (pas de `cookie_domain` ni de session figée). Les visiteurs se reconnectent une fois (porte privée comprise) |
| Page MCP, simulateur PunchOut (URL de retour) | URL absolues construites depuis l'hôte de la requête | Aucune (vérifié : guide, métadonnées OAuth et outils du plugin MCP utilisent l'hôte de la requête, test `TrustedProxiesTest`). Le sous-domaine est ajouté aux domaines de retour de l'acheteur PunchOut (fixtures) : le simulateur fonctionne sur les deux hôtes |
| E-mails, URL hors requête | Construites avec `DEFAULT_URI` | Interrupteur `CANONICAL_IS_PUBLIC_HOST` de `.railway/railway.ts` (étape 4) |
| `robots.txt`, `X-Robots-Tag` | Aucun hôte écrit ; mêmes règles sur les deux hôtes | Aucune |
| Page Plugins, guide MCP | Aucun hôte provisoire figé dans les gabarits (liens vers GitHub et Sylius uniquement) | Aucune |

## Étapes de l'utilisateur

1. **Domaine personnalisé Railway** : service `web`, onglet Settings, Networking, Custom Domain, saisir `sylius-b2b.<domaine du site>`. Railway affiche la cible du CNAME.
2. **CNAME** chez le registrar du site : nom `sylius-b2b`, cible donnée par Railway. Si le DNS est proxyfé (Cloudflare), le passer en « DNS seul » le temps de l'émission du certificat.
3. **Variable** : `railway variables --service web --set "DEMO_PUBLIC_HOST=sylius-b2b.<domaine du site>"` (nom d'hôte seul, sans schéma ni chemin). Déployer. Transition : les deux hôtes répondent.
   Les domaines de retour de l'acheteur PunchOut (simulateur) ne sont écrits qu'au chargement des fixtures : lancer un reset, toujours en `www-data` (le reset nocturne le fait aussi) :
   `railway ssh --service web -- su -s /bin/sh www-data -c 'php -d memory_limit=1G bin/console app:demo:reset --no-interaction'`
4. **Bascule** (après vérification du certificat) : dans `.railway/railway.ts`, passer `CANONICAL_IS_PUBLIC_HOST` à `true` (précondition : `DEMO_PUBLIC_HOST` posée dans Railway **et** exportée dans le shell de `railway config plan` / `apply`, sinon le script s'arrête avec un message ; e-mails et fixtures pointent vers le sous-domaine), `railway config plan` (seule `DEFAULT_URI` change), puis `apply`. Ajouter l'URL en ligne aux README (US-068).

## Vérifier le certificat (lecture seule)

```bash
HOST=sylius-b2b.<domaine du site>
dig +short CNAME "$HOST"                                   # la cible donnée par Railway
curl -sS -o /dev/null -w '%{http_code} %{ssl_verify_result}\n' "https://$HOST/healthz"   # 200 0
echo | openssl s_client -connect "$HOST:443" -servername "$HOST" 2>/dev/null | openssl x509 -noout -issuer -subject -dates
```

Écarts fréquents : pas de réponse DNS (CNAME absent ou mal saisi), certificat absent (émission en cours, jusqu'à quelques dizaines de minutes ; Railway affiche l'état du domaine), `400` (variable `DEMO_PUBLIC_HOST` absente ou fautive : hôte refusé par `TRUSTED_HOSTS`), `401` (normal tant que la démo est privée).

## Piège : `RAILWAY_PUBLIC_DOMAIN` change avec le domaine personnalisé

Incident du 2026-10-18 : `TRUSTED_HOSTS` était construit depuis `${{RAILWAY_PUBLIC_DOMAIN}}`. L'ajout du domaine personnalisé a changé cette variable ; le domaine Railway d'origine a alors répondu `400` (hôte hors liste). Correction (US-140) : `.railway/railway.ts` écrit le domaine d'origine dans la constante `RAILWAY_SERVICE_DOMAIN` (nom public, non secret), l'échappe et l'ancre ; le sous-domaine personnalisé reste accepté par `DEMO_PUBLIC_HOST`. Ne jamais reconstruire `TRUSTED_HOSTS` depuis `RAILWAY_PUBLIC_DOMAIN`. Si le service est recréé (nouveau domaine d'origine), mettre à jour la constante. Le domaine Railway d'origine figure dans l'IaC (il n'est pas secret) : la protection repose sur la porte privée, pas sur l'obscurité. Un test (`tests/Unit/Railway/RailwayTrustedHostsTest.php`) garde la règle. Après `railway config apply`, vérifier que les deux domaines répondent (`/healthz`).

## Retour arrière

Remettre `CANONICAL_IS_PUBLIC_HOST` à `false` puis `apply`. Pour retirer le sous-domaine : `railway variables --service web --delete DEMO_PUBLIC_HOST` (le domaine Railway reste accepté) puis supprimer le domaine personnalisé et le CNAME. Rien d'autre à défaire.
