# Audit de sécurité : synthèse

- Date : 2026-10-30 · Revue post-sprint 37 · Base : `main` @ `b1ad1c6`
- Revue en lecture seule du code (OWASP Top 10:2025), centrée sur les changements depuis le 2026-10-22 et les trois nouveaux plugins (copilote d'achat, passeport produit numérique, commerce prédictif).
- Périmètre : contrôleurs et routes nouvelles dans la démo, endpoint de chat du plugin copilote, page publique du plugin passeport, contrôleurs du plugin prédictif, dépendances (composer audit), en-têtes de production (8 requêtes GET passives, aucune connexion).
- Le dépôt étant public, ce fichier ne donne ni fichier, ni ligne, ni mode d'exploitation des constats non corrigés. Le détail est conservé hors du dépôt.

**Niveau de risque global : faible.** 0 critique, 0 haut, 1 moyen (repris du 2026-10-22), 4 bas (dont 1 nouveau), 2 information (reprises du 2026-10-22). Les risques déjà acceptés dans `docs/durcissement-demo.md` ne sont pas comptés.

## Constats

| Gravité | Constat (résumé) | État | Action |
|---|---|---|---|
| Moyenne (reprise) | Parc d'équipements des comptes de démo partagés : un visiteur peut y laisser du contenu visible par les autres visiteurs, ou le vider, jusqu'à la remise à zéro nocturne | **Traité** (US-164) | Parc en lecture seule pour les comptes de démo partagés depuis l'US-164 (sprint 28). La visite guidée n'écrit pas dans le parc. Décision : **clôturé** |
| Basse (reprise) | Les droits sur une machine ne dépendent pas du rôle dans la société | **Ouvert** | Réserver modification et suppression au propriétaire et aux administrateurs de la société, ou documenter la règle métier (décision PO) |
| Basse (reprise) | Le plafond de machines est contrôlé par client et sans verrou | **Ouvert** | Plafond par flotte, contrôle atomique |
| Basse (reprise) | Limitation de débit à compléter pour quelques routes (export des achats, parcours guidés) | **Traité** (US-164) | Routes du parc, de l'export CSV et des visites guidées rattachées à des seaux dédiés (US-164, sprint 28). Décision : **clôturé** |
| Basse (nouveau) | Énumération d'identifiants : la page publique d'un plugin utilise des identifiants numériques séquentiels dans l'URL | **Ouvert** | Mitigation : utiliser des UUID, un slug non prédictible, ou accepter le risque (données publiques, garde de publication en place) |
| Information (reprise) | En-têtes `Vary` envoyés en double en production ; pas de `preload` dans `Strict-Transport-Security` | **Confirmé** | Dédoublonner `Vary` (trouver quelle couche ajoute le second en-tête) ; décider du `preload` (inscription hstspreload.org) |
| Information (nouveau) | Fonction Twig d'un plugin marquée 'is_safe' pour du SVG généré par une bibliothèque tierce | **Ouvert** | Audit de la bibliothèque tierce ou retrait de 'is_safe' (détail hors dépôt) |
| Haute, à vérifier (reprise du 2026-10-22) | Exposition des services internes de l'hébergeur | **Fermé** | Conclusion consignée dans `docs/ouverture-publique.md` (US-079, 2026-10-23) : conforme |
| Haute, à vérifier (reprise du 2026-10-22) | Configuration versionnée et clé de chiffrement des paiements | **Fermé** | Conclusion consignée dans `docs/ouverture-publique.md` (US-079, 2026-10-23) : conforme |
| Risque accepté (reprise) | CSP compatible pour les scripts (déjà connue) | **Inchangé** | Étudier des nonces pour réduire l'exception (si ROI positif) |

## Évolution depuis le 2026-10-22

- **Sprints 22 à 37** : copilote d'achat (US-211), passeport produit numérique (US-230 à US-233, US-241), commerce prédictif (US-217 à US-219, US-240), indexation des pages vitrine (US-207), chaîne d'approvisionnement PHP (US-208), pages vitrine refondues (US-205, US-206).
- **Nouveau plugin copilote** : endpoint de chat réservé aux clients connectés, validation JSON stricte, widget JavaScript sans XSS (textContent), CSRF implicite via session.
- **Nouveau plugin passeport** : page publique `/product-passport/{id}` avec garde de publication (`isPublished()`), IDs numériques permettant l'énumération (nouveau constat bas).
- **Nouveau plugin prédictif** : contrôleurs avec CSRF explicite, authentification et autorisation par société, isolation des données.
- **Dépendances** : aucun avis de sécurité (`composer audit --locked` sur la démo et le plugin prédictif). Les deux paquets abandonnés (`behat/transliterator`, `php-http/message-factory`) sont inchangés depuis le 2026-10-22.
- **Fixture passeport** : données fictives uniquement (origine, recyclabilité, réparabilité de 3 casques EPI).
- **En-têtes de production** : conformes (CSP, HSTS, COOP, CORP, X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy), sauf `Vary` en double (confirmé, constat information repris). API REST toujours désactivée (404). `robots.txt` et `sitemap.xml` conformes à l'US-207 (indexation des pages vitrine).
- **Constats du 2026-10-22 fermés** : Exposition des services internes et configuration versionnée (US-079, conclusions dans `docs/ouverture-publique.md`). Parc d'équipements partagés et limitation de débit des routes récentes (US-164, sprint 28).

## Vérifié et conforme

- **Plugin copilote** : authentification (client connecté), autorisation (canal activé), validation JSON (structure + type), widget JavaScript sans XSS (textContent), CSRF implicite (session SameSite=Lax).
- **Plugin prédictif** : CSRF explicite (jetons vérifiés), authentification (ShopUserInterface), autorisation (membre de société), isolation (prédictions et masquages filtrés par société).
- **Dépendances** : aucun avis de sécurité dans la démo ni les plugins audités.
- **Fixture passeport** : données fictives uniquement (EPI-CASK-001/002/003, origines européennes inventées, indices de recyclabilité et réparabilité fictifs).
- **En-têtes de production** : CSP, HSTS (31536000s, includeSubDomains), COOP, CORP (same-origin), X-Frame-Options (DENY), X-Content-Type-Options (nosniff), Referrer-Policy (strict-origin-when-cross-origin), Permissions-Policy (camera/micro/geolocation refusés).
- **Chemins sensibles** : `/.env`, `/.git/HEAD`, `/api/v2/*` répondent 404.
- **robots.txt et sitemap** : conformes à l'US-207 (zones exclues, sitemap publié, pages vitrine indexables avec DEMO_INDEXING=1).
- **Scripts bin/** : trois nouveaux scripts depuis le 2026-10-22 (check-lock-diff, render-page, verify-worker-report), non audités en détail dans cette passe (recommandation : audit dédié si appelés depuis CI/CD).

## Non vérifié (à reprendre)

- **Bibliothèque tierce** de génération de QR code SVG (audit de la sûreté de l'échappement, ou retrait de 'is_safe').
- **Comportement authentifié en production** (rôles, isolation entre sociétés) : lecture de code seulement, aucune connexion.
- **Audit JavaScript, scan d'image et SBOM** : non relancés (couverts par la CI selon la documentation).
- **Scripts bin/** ajoutés depuis le 2026-10-22 : audit dédié recommandé si ces scripts sont appelés depuis des workflows CI/CD ou des hooks.

## Axes d'amélioration

Les axes ci-dessous sont classés par priorité. Le détail technique (fichier, ligne, scénario d'exploitation) est conservé **hors du dépôt** (dépôts publics).

| Priorité | Axe | Gravité | Estimation | Cible |
|---|---|---|---|---|
| 1 | Mitigation de l'énumération d'identifiants : utiliser des UUID, un slug non prédictible, ou accepter le risque (détail hors dépôt) | Basse | 5 points | Issue du dépôt du plugin concerné (nommé hors dépôt) |
| 2 | Audit de la bibliothèque tierce de génération SVG ou retrait de 'is_safe' (détail hors dépôt) | Information | 3 points | Issue du dépôt du plugin concerné (nommé hors dépôt) |
| 3 | Dédoublonner l'en-tête `Vary` (trouver quelle couche ajoute le second en-tête) | Information | 2 points | US-XXX (démo ou infrastructure) |
| 4 | Décider d'ajouter `preload` à `Strict-Transport-Security` (inscription hstspreload.org requise) | Information | 1 point | Décision de l'opérateur |
| 5 | Documenter ou corriger les droits sur les machines du parc (dépendance du rôle dans la société) | Basse | 8 points | US-XXX (démo ou plugin fleet-management) |
| 6 | Rendre atomique le contrôle du plafond de machines par flotte | Basse | 5 points | US-XXX (plugin fleet-management) |

---

**Conclusion :** Évolution maîtrisée depuis le 2026-10-22. Les trois nouveaux plugins (copilote, passeport, prédictif) ne présentent aucun constat critique ou haut. Un nouveau constat bas (énumération d'identifiants) est apparu et deux constats information (fonction Twig 'is_safe', en-têtes en double) complètent le tableau. Les constats haute gravité « à vérifier » du 2026-10-22 sont fermés (US-079). La démo reste ouverte au public avec un niveau de risque **faible**.

---

**Dernière mise à jour :** 2026-10-30  
**Prochaine revue :** Post-sprint 38 ou à la demande
