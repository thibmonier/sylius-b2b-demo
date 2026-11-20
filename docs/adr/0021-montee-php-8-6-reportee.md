# ADR-0021 — Montée vers PHP 8.6 : reportée, veille automatisée

- **Statut** : Accepté (2026-11-20)
- **Contexte projet** : sylius-b2b-demo

## Contexte

PHP 8.6.0 est sorti le 19 novembre 2026, le jour même de la publication de sécurité qui a fait passer la démo de 8.5.12 à 8.5.13 (CVE-2026-129474, exécution de code à distance propre à la branche 8.5). La question posée : peut-on monter en 8.6 **en conservant l'architecture actuelle** — FrankenPHP et supercronic dans un seul conteneur (ADR-0002), mode classique avec threads bornés (ADR-0013) — et sans casser la compatibilité des composants utilisés ?

## Décision

**La démo reste sur PHP 8.5.** La montée est reportée, et la question n'est plus instruite à la main : elle est réévaluée chaque semaine par le rapport de veille (US-239).

Les deux conditions de réouverture sont explicites et vérifiables par machine :

1. `dunglas/frankenphp` publie au moins un tag `*php8.6*` ;
2. aucun paquet verrouillé n'exclut PHP 8.6.

`App\Demo\Stack\PhpUpgradeWatchSection` les contrôle à chaque exécution hebdomadaire et écrit le verdict dans l'issue de veille. Tant qu'un verrou reste fermé, la section dit « toujours bloquée » ; quand les deux s'ouvrent, elle demande explicitement de rouvrir cet ADR.

## Constats (mesurés le 2026-11-20)

### Ce qui bloque

1. **Aucune image FrankenPHP pour PHP 8.6.** Zéro tag `php8.6` sur Docker Hub, contre 60 pour la branche 8.5. Le `docker/Dockerfile` épingle `dunglas/frankenphp:1-php8.5-bookworm` par digest : il n'existe rien à mettre à la place.

2. **Le ZTS a disparu des images PHP 8.6.** 342 tags `8.6` chez `library/php`, **aucune variante `zts`** ; la branche 8.5 en compte huit. La documentation de FrankenPHP indique que le ZTS est *requis*. C'est le modèle de threads derrière `FRANKENPHP_NUM_THREADS=8` / `FRANKENPHP_MAX_THREADS=16` et les mesures de l'ADR-0013. FrankenPHP 1.13.6 annonce compiler aussi contre du non-ZTS, mais tant que ce n'est ni publié ni documenté, monter en 8.6 changerait le modèle de concurrence — c'est-à-dire ne conserverait pas l'architecture actuelle, qui était la contrainte de départ.

3. **`league/oauth2-server` exclut 8.6.** Contrainte `~8.2.0 || ~8.3.0 || ~8.4.0 || ~8.5.0`, et 9.4.1 est la dernière version publiée. Le paquet arrive par `tmonier/sylius-b2b-mcp-plugin` (`^9.2`) : la contrainte est en amont, elle ne se corrige pas dans nos dépôts.

### Ce qui ne bloque pas

Sur **45 contraintes `php` distinctes** dans `composer.lock`, **une seule** exclut 8.6. Les cinq autres à borne supérieure l'autorisent explicitement : `laminas/laminas-stdlib`, `lcobucci/jwt`, `sabberworm/php-css-parser`, `setasign/fpdi`, `dasprid/enum`. La contrainte racine `php: ^8.5` accepte déjà 8.6 sans modification.

Autrement dit : **l'écosystème Sylius/Symfony est prêt, l'infrastructure ne l'est pas.**

### À vérifier le jour de la montée

- **Sessions.** PHP 8.6 change trois défauts : `session.use_strict_mode=1`, `session.cookie_httponly=1`, `session.cookie_samesite="Lax"`. `config/packages/framework.yaml` ne déclare pas `cookie_samesite`, la démo hérite donc du défaut, qui passe de « aucun attribut » à `Lax`. À éprouver sur les échanges PunchOut cXML, qui impliquent des retours cross-site.
- **DOM.** Plusieurs propriétés passent en visibilité asymétrique, et la sérialisation XML change de comportement. La démo sérialise du XML pour cXML (PunchOut) et Factur-X.
- **Intl.** Plusieurs `TypeError` nouveaux sur les fuseaux, les offsets et `MessageFormatter::parse()`. Sylius en dépend fortement.
- **Sans objet ici** : les CVE et changements SOAP (`ext-soap` n'est pas installée) et PHP-FPM / PHP-CGI (FrankenPHP n'utilise ni l'un ni l'autre).

## Conséquences

### Positives

- La démo reste sur une branche supportée et fraîchement corrigée (8.5.13), sans dette de sécurité.
- L'architecture de l'ADR-0002 et les mesures de l'ADR-0013 restent valides : rien n'est à remesurer.
- La question ne se reposera pas à vide : le rapport hebdomadaire y répond, avec les deux verrous nommés et leur état.

### Négatives

- La démo n'affiche pas la dernière version majeure de PHP, ce qui est un léger manque pour une vitrine technique.
- La réouverture dépend d'un tiers (FrankenPHP) et d'un paquet amont (`league/oauth2-server`) : aucune des deux dates n'est sous notre contrôle.

## Alternatives écartées

- **Compiler FrankenPHP soi-même contre PHP 8.6.** Abandonne l'image officielle épinglée par digest (US-081) et la chaîne d'approvisionnement associée, pour une version que FrankenPHP ne publie pas encore. Le coût de maintenance dépasse le bénéfice d'une vitrine.
- **Quitter FrankenPHP pour PHP-FPM en 8.6.** Contredit frontalement la contrainte posée (« en conservant l'architecture actuelle »), abandonne l'ADR-0002 et rouvrirait l'exposition aux trois CVE PHP-FPM du 19 novembre.
- **Retirer `tmonier/sylius-b2b-mcp-plugin` de la démo** pour lever le verrou `league/oauth2-server`. Le plugin est une des fonctionnalités que la démo existe pour montrer ; et cela ne lèverait pas les deux autres verrous.
