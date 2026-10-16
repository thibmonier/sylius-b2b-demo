# Revue de code — sprint 2 (données B2B)

Date : 2026-10-16. Branche `feature/sprint-2-donnees-b2b`, PR #2.
Méthode : 2 revues indépendantes (Symfony/code, sécurité OWASP Top 10:2025) + vérification locale extensive (repro conteneur : comptes en base, `app:demo:reset`, ecs/phpstan niveau max/lint:container).

CI au moment de la revue : **verte** (Quality & smoke tests ✅, Docker image ✅, GitGuardian ✅).

## Verdict global

**Aucun risque bloquant.** La sécurité audite 4 points sensibles et les juge corrects par conception pour une démo jetable à données 100 % fictives, réinitialisée chaque nuit :
- règle `access_control` `^/([a-z]{2}_[A-Z]{2}/)?demo(/|$)` bien bornée (le `(/|$)` empêche tout débordement type `/democracy`) et bien ordonnée (avant la règle shop `/account`) ;
- `/demo/accounts` sans XSS (auto-échappement Twig, aucun `|raw`), aucune PII réelle (emails `.example`) ;
- secret cXML PunchOut factice et **stocké haché** ;
- `app:demo:reset` est une commande **CLI/cron uniquement** (aucune route HTTP) ;
- mots de passe de démo via le hasher Sylius (`auto` → Argon2id en prod).

Données vérifiées en base (repro locale) : 210 produits, 420 prix, 5 entreprises, 11 membres, 24 règles tier, 5 GPSR safety, 2 devis, 3 comptes crédit ; `app:demo:reset` → exit 0 en ~21 s.

## Quick wins appliqués dans ce sprint

| # | Constat | Fichier | Source |
|---|---------|---------|--------|
| Q1 | `localhost` retiré des return domains PunchOut (réduit une forme SSRF/open-redirect) | `PunchoutDemoFixture.php` | Sécu #2 |
| Q2 | Code mort `$mpn` supprimé (bloc `if` vide + commentaire trompeur) | `ProductCsvFixture.php` | Symfony #9 |
| Q3 | Numérotation des étapes/logs harmonisée (1 fixtures, 2 media, 3 cache) | `DemoResetCommand.php` | Symfony #12 |
| Q4 | Commentaire obsolète « secret affiché sur /demo/accounts » corrigé (il est haché) | `PunchoutDemoFixture.php` | Sécu #4 |

## Faux positif / non-défaut

- **`app:demo:reset` « exposé »** : NON — c'est une `#[AsCommand]` sans aucune `#[Route]`, déclenchée seulement par supercronic (crontab `0 3 * * *`). Non atteignable via HTTP.
- **Numérotation d'étapes** (Symfony #12) : déjà partiellement corrigée au fix savepoint, finalisée en Q3.

## Reporté au sprint 6 (durcissement pré-ouverture — US-033/035/036/037)

Le déploiement public Railway est US-032 (sprint 6) ; la démo n'est pas encore exposée. Ces durcissements y ont leur place :

| # | Constat | Sévérité | Source | Story cible |
|---|---------|----------|--------|-------------|
| R1 | Compte admin de démo (`sophie`) exposé sur `/demo/accounts` : garantir least-privilege strict, mailer de prod en puits (null/catch-all), rate-limiting login + `/demo/accounts` | Medium (contexte) | Symfony #1 + Sécu #1 | US-035, US-036 |
| R2 | `app:demo:reset` sans garde d'environnement **démo-aware** (une garde `APP_ENV=prod` naïve casserait le reset nocturne voulu) ni atomicité (purge puis reload non transactionnel) | Majeur/Low | Symfony #2,#3 + Sécu #3 | US-033 |
| R3 | `CsvProductReader` ignore silencieusement les lignes dont le nb de colonnes ≠ en-tête (perte de données muette) → journaliser/compter, voire rejeter | Majeur (latent) | Symfony #4 | US-036 |
| R4 | Doublon de SKU **inter-fichiers** écrase silencieusement (validation seulement intra-fichier) → accumuler les SKU sur les 3 CSV | Majeur (latent) | Symfony #5 | US-036 |
| R5 | `attributes_json` invalide ignoré en silence ; valeur JSON non-chaîne → TypeError potentielle ; code d'attribut inconnu ignoré | Majeur (latent) | Symfony #6 | US-036 |
| R6 | BOM UTF-8 / en-tête non trimé / encodage non validé dans le CSV reader | Mineur | Symfony #7 | US-036 |
| R7 | `channelRepository->findAll()` + lookups taxon/attribut par produit (≈ N+1, ~420 requêtes au reset) | Mineur (perf fixtures) | Symfony #10 | US-036 |
| R8 | `DemoAccountsController` recopie en dur les comptes des fixtures (dérive possible) → générer depuis une source partagée | Mineur | Symfony #13 | US-036 |
| R9 | Fixtures partageant la même priorité (-15, -40, -50) : ordre indéfini entre elles (vérifier qu'aucune n'en dépend d'une autre) | Mineur (à vérifier) | Symfony #14 | US-036 |
| R10 | Valeurs d'attribut créées pour fr_FR et en_US même si l'attribut n'est pas traduisible | Mineur (à vérifier) | Symfony #11 | US-036 |

Note : R3/R4/R5/R6 sont **latents** — la repro locale charge exactement 210 produits sans perte, les 3 CSV ayant des préfixes disjoints et des `attributes_json` valides. Ce sont des durcissements de robustesse, pas des bugs actifs.

## Classement par confiance

- **Vérifié en base (repro locale)** : tous les comptes de données corrects ; `app:demo:reset` fonctionnel ; sécurité des 4 points sensibles.
- **Haute confiance** : règle `/demo` bien bornée (analyse regex + test comportemental 200), secret cXML haché.
- **À confirmer au sprint 6** : R9 (ordre entre priorités égales), R10 (traduisibilité des attributs).
