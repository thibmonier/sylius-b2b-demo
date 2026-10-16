# Revue de code — sprint 4 (thème B2B)

Date : 2026-10-16. Branche `feature/sprint-4-theme-b2b`, PR #4.
Méthode : 2 revues indépendantes (Symfony/code, sécurité OWASP Top 10:2025) + vérification réelle en conteneur (rendu des pages, connexion en un clic, badges, redirections).

## Verdict

Rien de critique ni d'élevé. La connexion en un clic est conforme (formulaire standard, jeton CSRF réel, `form_login` + user_checker ; refus vérifiés sans jeton et pour le compte désactivé). Aucun `|raw`, pas de XSS. Les points ouverts relèvent du durcissement avant ouverture publique (sprint 6).

## Bugs trouvés par la vérification réelle (et non par les tests des workers)

| # | Bug | Cause | Correctif |
|---|-----|-------|-----------|
| B1 | **Pages catégorie du shop vides** depuis le sprint 2 | La fixture CSV ne posait que le taxon principal ; les listes Sylius lisent `product_taxon` | `ProductTaxon` ajouté (idempotent) + smoke test sur une vraie page catégorie. `product_taxon` 0 → 210 |
| B2 | **Aucun badge de plugin rendu** (shop et admin) | Le template lisait `hookable_configuration`, variable que les twig hooks Sylius n'exposent pas (`hookable_metadata`) ; les tests ne vérifiaient que l'enregistrement des hooks | Lecture de `hookable_metadata.configuration` + smoke test sur le rendu (badges présents, masqués avec le cookie `off`) |
| B3 | Smoke tests en échec CI | `$crawler->filter()` (sélecteurs CSS) exige `symfony/css-selector`, absent | Ajout en dev (v7.4.18, aucune alerte) |

## Corrigé dans ce sprint

| # | Constat | Source | Correctif |
|---|---------|--------|-----------|
| C1 | `DEMO_MODE` actif par défaut (un dépôt réutilisé exposerait les identifiants) | Sécu #2, Symfony #1 | Défaut `0` ; `DEMO_MODE=1` explicite dans `compose.yaml`, `phpunit.xml.dist` et la CI. Test du mode désactivé (pas de bandeau ni de comptes, `/demo/accounts` 404) |
| C2 | Redirection ouverte résiduelle du toggle des badges (`//evil.tld`, `/\evil.tld`) | Sécu #3, Symfony #11 | Chemins `//…` et `\` renvoyés à l'accueil ; tests. Vérifié en conteneur |
| C3 | URL Mailpit codée en dur | Sécu #4 | `env(DEMO_MAILPIT_URL)`, vide par défaut, lien masqué si vide ; `compose.yaml` la définit |
| C4 | Routes de démo du catalogue jamais vérifiées (risque de 500 sur la page Plugins) | Symfony #4 | Test kernel : chaque `demoRoute` existe dans le router |
| C5 | Pas de contrôle d'unicité des e-mails de démo | Symfony #8 | Exception nommant l'e-mail en double ; tests |

## Non-défauts vérifiés

- **Requête de conditionnement** (Symfony #7) : `PackagingUnitProvider` mémorise le résultat → une seule requête par page, pas par carte.
- **Compte conseillé inconnu → 500** (Symfony #3) : le test unitaire charge les vrais `plugins.yaml` et `accounts.yaml` ; une entrée invalide fait échouer la CI.
- **agentic-feeds listé malgré `published: false`** (Symfony #5) : voulu, la page affiche « Non publié » (vérifié sur Packagist : seul plugin en 404).
- **Mot de passe de démo dans le HTML** (Symfony #2) : exigence explicite de l'utilisateur (comptes écrits sur la page de login), conditionnée au mode démo.

## Reporté — à faire AVANT toute ouverture publique (sprint 6)

| # | Constat | Story cible |
|---|---------|-------------|
| R1 | Compte admin `sophie` public avec accès complet au back-office (Sylius CE n'a pas de droits fins) : blocage de la démo, défiguration | US-035 (admin de démo restreint), US-033 (remise à zéro supervisée) |
| R2 | Inscription et checkout publics : un visiteur peut saisir des données personnelles réelles, visibles via l'admin public (RGPD) | US-036 (avertissement « aucune donnée réelle » ou inscription désactivée) |
| R3 | Prix « dès » calculé produit par produit sur une liste (coût CPU) | US-036 (batch) |
| R4 | `csrf_token()` dans le bandeau ouvre une session pour chaque visiteur anonyme (empêche le cache HTTP) ; `Vary: Cookie` si un cache est ajouté | US-036 |
| R5 | Locales des pages légales non restreintes aux locales du canal | US-036 |
| R6 | Cookie `demo_badges` sans `Secure` forcé (dépend de `trusted_proxies` derrière Railway) | US-032 (déploiement) |

## Hors périmètre

- Bloc « documents » produit (US-013) : aucun plugin ne fournit de documents ; la fiche GPSR est rendue par son plugin.
- « Prochaine remise à zéro » dans le bandeau (US-015) : dépend du reset supervisé (US-033).
