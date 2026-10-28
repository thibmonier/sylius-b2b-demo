# Captures d'écran des plugins (US-065)

Audit du 2026-10-17 (branches locales des dépôts, lecture seule) et protocole de capture.

## Audit des images existantes

| Plugin | Images | Boutique | Admin | Remarque |
|---|---|---|---|---|
| admin-order-creation | 5 | - | oui | admin seul par nature |
| agentic-feeds | 3 | - | oui | admin seul par nature |
| ai-catalog-copilot | 5 | - | oui | admin seul par nature |
| audit-log | 3 | - | oui | admin seul par nature |
| b2b-mcp | 4 | oui | oui | |
| bulk-actions | 6 | - | oui | admin seul par nature |
| command-palette | 1 puis 2 | - | oui | complément : recherche « claire » |
| company-accounts | 5 | oui | oui | |
| container-fill | 7 | oui | oui | |
| facturx | 2 | - | oui | admin seul |
| gpsr | 2 | oui | oui | |
| purchase-approval | 6 | oui | oui | |
| quick-order | 3 | oui | - | pas d'écran admin |
| quote-request | 6 | oui | oui | |
| tier-pricing | 4 | oui | oui | |
| timeline | 2 | - | oui | admin seul par nature |
| trade-credit | 6 | oui | oui | |
| webhook-outbox | 4 | - | oui | admin seul par nature |
| withdrawal | 4 | oui | oui | |
| channel-access, registration-approval, punchout-cxml | 0 | | | prises dans ce lot |
| order-hold | 0 | | | purement technique (D7) |
| customer-asset-management | 5 | | | captures dans le README du plugin (Mon matériel, filtre, état vide, mobile), sprint 21 |

Aucun manque de face (boutique ou admin) hors de ces cas : pas de capture de comblement (T-065-05). La fraîcheur n'a pas été comparée écran par écran : aucune image n'a été signalée comme obsolète.

## Protocole

- Démo locale : `docker compose build web && docker compose up -d web`, puis `docker compose exec web php -d memory_limit=1G bin/console app:demo:reset --no-interaction` (base de développement du compose). Porte privée désactivée par le compose.
- Navigateur piloté (Chrome), fenêtre d'environ 1330×900 en pixels de capture, badges de plugins masqués (`/demo/badges/off`).
- Langue : boutique en anglais (`/en_US/`) ; l'admin est en français (seul compte admin de démo, `sophie`, `fr_FR`).
- Comptes : connexion en un clic de la page de login (comptes `.example`). Ne pas capturer un formulaire de connexion (le gestionnaire de mots de passe du navigateur y préremplit un identifiant) ni un champ de formulaire (icône du gestionnaire).
- Format et poids : JPEG (convention des dépôts), moins de 300 Ko ; nommage `shop-<sujet>.jpg` et `admin-<sujet>.jpg`.
- Contrôle : aucun e-mail réel, secret ou mot de passe visible ; adresses `.example` ou `.demo` seulement.
- Zone temporaire : `var/vitrine-shots/<plugin>/` (ignorée par git) avec un `MANIFEST.md` par plugin (légendes EN et FR, page source, date).

## Limites locales constatées

- PunchOut : le simulateur est refusé en local (403, l'URL de retour doit être HTTPS et le domaine autorisé) ; `/punchout/cxml` est réservé au simulateur en mode démo. Pas de capture d'une session PunchOut ni d'un journal d'échanges réussis en local.
- PunchOut : le titre et le fil d'Ariane des pages d'administration des acheteurs affichent la clé brute `tmonier_punchout.ui.buyers` (traduction manquante du plugin).

## Pages de compte des plugins (US-203, sprint 31)

Devis (liste et détail), paiements à terme et assistants IA dans le cadre de « Mon compte » : `account-quotes-fr.png`, `account-quote-detail-fr.png`, `account-statement-fr.png`, `account-connections-fr.png` (1280 px) et leurs versions `-320-fr.png` (320 px), dans `docs/images/`. Rendu du noyau en ligne de commande (`bin/render-page`, `APP_ENV=test` avec `--login` : Claire pour les devis, Isabelle pour le relevé et les assistants IA), feuille de style compilée à part depuis `b2b.scss` (le `public/build` local était périmé). Aucune vignette de produit sur ces pages ; la page de consentement n'a pas de capture publiée.

Page « Plugins » (US-204) : `plugins-fr.png`, `plugins-en.png` (haut de page, 1280 px), `plugins-cards-fr.png`, `plugins-cards-en.png` (première famille de cartes, 1280 px) et leurs versions `-320-` (320 px), dans `docs/images/`. Prises le 2026-10-28 sur la production, page publique sans session (Chromium sans interface), car le rendu local était refusé faute de disque.

## Page « Architecture » (US-206, sprint 32)

Page « Architecture » refondue dans la charte de l'accueil : `architecture-fr-1280.png`, `architecture-en-1280.png` (page entière, 1280 px) et `architecture-fr-320.png`, `architecture-en-320.png` (page entière, 320 px), dans `docs/images/`. Rendues par `bin/render-page` (canal B2B, feuille de styles reconstruite par `yarn build:prod`), sans barre de débogage ; contrôle à 320, 375 et 1280 px : aucun défilement horizontal, cibles d'au moins 44 px ; pa11y WCAG2AAA en français et en anglais : « No issues found! ».

## Page « Preuve technique » (US-205, sprint 32)

Page « Preuve technique » refondue dans la charte de l'accueil : `proof-fr-1280.png`, `proof-en-1280.png` (page entière, 1280 px, tableau des plugins) et `proof-fr-320.png`, `proof-en-320.png` (page entière, 320 px, une fiche par plugin), dans `docs/images/`. Rendues par `bin/render-page` (canal B2B, feuille de styles reconstruite par `yarn build:prod`) sur le rapport de preuve versionné ; contrôle à 320, 375 et 1280 px : aucun défilement horizontal, liens d'au moins 44 px ; pa11y WCAG2AAA en français et en anglais, à 1280 et 320 px : « No issues found! ». Sur le canal B2C (sans la feuille B2B), la page reste lisible, sans défilement horizontal : le tableau s'affiche dès 992 px, les fiches en dessous.
