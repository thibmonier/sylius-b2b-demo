# Audit de sécurité : synthèse

- Date : 2026-10-18 · Commande : `/team:security --scope=full` · Base : `main` @ `3927587`
- Trois revues en parallèle, en lecture seule : code de la démo (OWASP Top 10:2025), dépendances et chaîne d'approvisionnement, infrastructure et configuration (Docker, Caddy, Railway, CI, sondes sur l'instance locale).
- Le dépôt étant public, ce fichier ne donne ni fichier, ni ligne, ni mode d'exploitation des constats non corrigés. Le détail est conservé hors du dépôt et sera versé ici une fois les correctifs mergés.

**Niveau de risque global : moyen.** 0 critique, 2 hauts (conditionnels, à vérifier), 8 moyens, 18 bas. Les risques déjà acceptés dans `docs/durcissement-demo.md` (dont l'accès public en lecture au back-office avec le compte de démo, décision du 2026-10-17) ne sont pas comptés.

## Avant l'ouverture publique

| Gravité | Constat (résumé) | Action |
|---|---|---|
| Haute, à vérifier | Accès public possible à des services internes Railway | vérifier dans Railway qu'aucun service interne n'est exposé ; ajouter le contrôle à la checklist de passage en public |
| Haute, à vérifier | Fichiers de configuration versionnés dans le dépôt public | relire leur contenu (valeurs factices seulement) ; changer et purger tout vrai secret |
| Moyenne, confirmée | Des mails déclenchés par des visiteurs peuvent apparaître dans la boîte publique de la démo | ne publier que les mails générés par les fixtures |
| Moyenne | Branche `main` non protégée alors qu'un push déploie | PR obligatoire et CI verte requise |
| Moyenne, conditionnelle | Adresse IP client prise dans un en-tête transmis | vérifier le comportement du proxy Railway (US-052) |

## À planifier

- Licence propriétaire d'une dépendance embarquée dans le dépôt public : relire ses conditions.
- Chaîne d'approvisionnement : Dependabot ou Renovate, scan d'image et SBOM en CI, images de base et actions GitHub figées par empreinte, audit des dépendances en CI.
- braces (CVE-2026-93687, haute) : outil de build seulement, absent de l'image finale ; risque réel bas, exception à documenter.
- Durcissements mineurs : en-têtes cross-origin, fichiers statiques inutiles servis, outillage dans l'image finale, motif `TRUSTED_HOSTS`, jeton CSRF sur un formulaire du simulateur, durée des cookies « se souvenir de moi ».
- Correctifs disponibles : `api-platform/*` 5.0.4, `doctrine/orm` 3.7.5 ; paquets abandonnés `behat/transliterator`, `php-http/message-factory`.

## Vérifié et conforme

- `composer audit` : 0 alerte ; licences : ni GPL ni AGPL (LGPL et MPL en bibliothèques).
- Image multi-étapes exécutée en `www-data`, `APP_DEBUG=0`, pas de Xdebug, dépendances de développement absentes.
- `/_profiler`, `/.env`, `/.git/HEAD`, `/composer.json`, `/api/v2/*` répondent 404.
- CSP, X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy présents ; HSTS sous HTTPS.
- Code : comparaisons en temps constant, aucun `|raw`, client HTTP sortant sur liste blanche (pas de SSRF), XML protégé contre XXE, pas de désérialisation, pas d'open redirect, contrôle d'accès des listes et des mails par identifiants stricts.
- CI en `contents: read`, sans `pull_request_target` ni secret.
