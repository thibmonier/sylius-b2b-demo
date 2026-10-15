# Contraintes — démo B2B Sylius

## Techniques
- **TC-1** Sylius 2.3, PHP 8.4, Symfony 7.4 LTS. La résolution Composer est validée.
- **TC-2** Tous les plugins sont installés depuis Packagist en versions taguées, comme le ferait un intégrateur. agentic-feeds doit d'abord être publié sur Packagist.
- **TC-3** Symfony Workflow obligatoire pour les graphes de commande (order-hold).
- **TC-4** MySQL 8.0.16+ ou PostgreSQL 10+ avec un `server_version` réel.
- **TC-5** Image avec intl, gd, dom, zip, fileinfo, pdo, Argon2id et wkhtmltopdf. Build des assets Node en multi-stage, sans Node dans l'image finale.
- **TC-6** HTTPS public et `trusted_proxies` (MCP, cookies secure, iframe PunchOut).
- **TC-7** Le thème **ajoute** des hookables et ne remplace jamais ceux des plugins (I7).
- **TC-8** Tâches planifiées : environ 12 commandes quotidiennes, plus les webhooks chaque minute ou en boucle, plus la remise à zéro nocturne.
- **TC-9** Médias écrits dans le conteneur qui les sert : le volume Railway n'est pas partageable.
- **TC-10** Environnement Docker identique en local (docker compose) et sur Railway, à partir de la même image.

## Hébergement (Railway Hobby)
- **TH-1** 5 $/mois avec 5 $ d'usage inclus ; au-delà, facturation à l'usage. **Un budget mensuel est à fixer avec l'utilisateur** (estimation : 10 à 25 $/mois sans mise en veille).
- **TH-2** Cron Railway en UTC, toutes les 5 minutes au minimum, horaire approximatif, processus qui doit se terminer.
- **TH-3** Pas de SMTP sortant.
- **TH-4** Mise en veille possible, mais avec 502 au réveil, et une base active empêche la veille.
- **TH-5** Le nombre de services et de domaines n'est pas documenté : viser au plus 3 services.

## Produit et réglementation
- **TP-1** Démo publique **non promue sur les canaux Sylius** (politique du 2026-10-14). Badges propres, sans imiter Sylius Plus.
- **TP-2** Aucune donnée personnelle réelle : comptes et entreprises fictifs, domaines `.example`.
- **TP-3** Aucun secret réel dans le dépôt (GitGuardian). Secrets Railway dans les variables du service. Clé IA facultative (générateur `demo` par défaut).
- **TP-4** Comptes de démo publics (mots de passe affichés). Ils doivent être sans privilège dangereux : pas de modification de la configuration critique, pas d'envoi d'e-mails réels, pas d'appels sortants arbitraires (webhooks vers des URLs saisies).
- **TP-5** Mentions légales minimales (éditeur, hébergeur) et RGPD : pas de collecte réelle, données remises à zéro chaque nuit.
- **TP-6** Bilingue français / anglais (les plugins sont traduits en 7 langues).

## Ressources
- **TR-1** Un seul développeur, assisté par l'agent ; méthode claude-craft (validation à chaque porte).
- **TR-2** Pas d'Ops dédiée : déploiement par GitHub vers Railway, CI GitHub Actions.
