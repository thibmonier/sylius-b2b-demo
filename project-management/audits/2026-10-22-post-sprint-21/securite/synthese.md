# Audit de sécurité : synthèse

- Date : 2026-10-22 · Revue post-sprint 21 · Base : `main` @ `4a4dcfc`
- Revue en lecture seule du code (OWASP Top 10:2025), centrée sur les nouveautés des sprints 18 à 21.
- Deux passes : la première, interrompue, a couvert la gestion du parc d'équipements ; la seconde (complément du 2026-10-22) a couvert l'aperçu des approbations, le tableau de bord d'achats et son export, la fixture du parc, les commits depuis le 2026-10-18, les dépendances et les en-têtes de production (8 requêtes GET passives, aucune connexion).
- Le dépôt étant public, ce fichier ne donne ni fichier, ni ligne, ni mode d'exploitation des constats non corrigés. Le détail est conservé hors du dépôt et sera versé ici une fois les correctifs mergés.

**Niveau de risque global : moyen.** 0 critique, 2 hauts (conditionnels, repris du 2026-10-18, non clos), 1 moyen, 3 bas, 1 information, sur les deux passes. Les risques déjà acceptés dans `docs/durcissement-demo.md` ne sont pas comptés.

## Constats

| Gravité | Constat (résumé) | Action |
|---|---|---|
| Moyenne | Parc d'équipements des comptes de démo partagés : un visiteur peut y laisser du contenu visible par les autres visiteurs, ou le vider, jusqu'à la remise à zéro nocturne | borner ces actions pour les comptes partagés (débit et plafond) ; décision PO sur l'étendue |
| Basse | Les droits sur une machine ne dépendent pas du rôle dans la société | réserver modification et suppression au propriétaire et aux administrateurs de la société, ou documenter la règle métier |
| Basse | Le plafond de machines est contrôlé par client et sans verrou | plafond par flotte, contrôle atomique |
| Basse | Limitation de débit à compléter pour quelques routes (export des achats, parcours guidés) | ajouter ces routes aux seaux de limitation existants |
| Information | En-têtes `Strict-Transport-Security` et `Vary` envoyés en double en production ; pas de `preload` | dédoublonner ; décider du `preload` |
| Haute, à vérifier (reprise du 2026-10-18) | Exposition des services internes de l'hébergeur : la vérification est notée « en cours » dans le dossier de GO, sa case n'est pas cochée | consigner la conclusion (conforme ou action corrective) dans la section US-079 de `docs/ouverture-publique.md` |
| Haute, à vérifier (reprise du 2026-10-18) | Configuration versionnée et clé de chiffrement des paiements : « ok à priori » seulement, cases non cochées | consigner la conclusion ; aucun `.env*` lu dans cette revue |
| Risque accepté, repris | CSP compatible pour les scripts (déjà connue) | étudier des nonces pour réduire l'exception |

## Non vérifié (à reprendre)

- Exposition réelle des services de l'hébergeur (console de l'hébergeur, hors de portée) et contenu des fichiers de configuration versionnés.
- Comportement authentifié en production (rôles, isolation entre sociétés) : lecture de code seulement, aucune connexion.
- Audit JavaScript, scan d'image et SBOM : non relancés (couverts par la CI selon la documentation).
- Les plugins non listés dans ce périmètre (hors parc d'équipements, approbations, tableau de bord d'achats).

## Vérifié et conforme (gestion du parc d'équipements)

- Identifiants de machine non prédictibles (UUID) ; une machine d'un autre client répond comme une machine inexistante.
- Refus par défaut pour les visiteurs, les administrateurs et les autres clients.
- Suppression en POST avec jeton CSRF propre à chaque machine.
- Le propriétaire n'est pas un champ du formulaire (pas d'affectation de masse).
- Filtre catalogue `?equipment=` : valeurs sur liste blanche, requête paramétrée et bornée à la flotte du client, rien ne révèle l'existence d'une machine hors flotte.
- Aucune sortie non échappée dans les gabarits du plugin ; administration du plugin réservée à un rôle.
- Aperçu des approbations (purchase-approval 1.3) : réservé aux approbateurs actifs, limité à leur société, fragment échappé, script du navigateur sans source tierce.
- Tableau de bord d'achats et export CSV (company-insights 0.2) : réservés à l'administrateur de la société ; filtres par membre et par site validés contre la société connectée ; formules de tableur neutralisées ; export borné (24 mois, 50 000 lignes), non mis en cache, journalisé sans contenu ; hors mesure d'audience.
- Fixture du parc d'équipements : données fictives uniquement.
- `composer audit --locked` : aucun avis de sécurité ; deux paquets abandonnés déjà connus (`behat/transliterator`, `php-http/message-factory`).
- Production : `/_profiler`, `/.env`, `/.git/HEAD`, `/composer.json` et `/api/v2/*` répondent 404 ; CSP, HSTS, COOP, CORP, `X-Frame-Options`, `nosniff`, `Referrer-Policy`, `Permissions-Policy` et `noindex` présents sur l'accueil, la connexion et la page Plugins ; cookie de session `Secure`, `HttpOnly`, `SameSite=Lax` ; l'hôte de mesure d'audience n'est autorisé que sur les pages publiques mesurées.
- Commits depuis le 2026-10-18 : nouvelles pages publiques en lecture seule ; les deux POST des parcours guidés protégés par jeton CSRF ; aucun appel externe déclenché par une requête web ; image finale sans Composer.

## Évolution depuis le 2026-10-18

- Selon `docs/durcissement-demo.md` : chaîne d'approvisionnement outillée (Dependabot, références figées, audits, Trivy, SBOM), en-têtes cross-origin, fichiers cachés refusés, CSRF du simulateur, image finale sans Composer, `api-platform/*` 5.0.4, `main` protégée. En production, les en-têtes cross-origin, le refus des fichiers cachés et les 404 sur les chemins sensibles ont été constatés le 2026-10-22.
- `doctrine/orm` reste en 3.6.x (conflit déclaré par Sylius 2.3).
- Nouveau constat moyen lié à la fonction parc d'équipements ; deux bas et une information issus de la seconde passe.
- Les deux points « haute, à vérifier » du 2026-10-18 ne sont pas consignés comme clos dans le dépôt.
