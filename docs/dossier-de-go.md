# Dossier de GO : ouverture publique de la démo (US-085)

Document de décision pour Thibaut. Dépôt public : ce dossier ne donne que des **conclusions**, jamais de valeur, de chemin ni de mode d'exploitation. Le détail des constats reste dans le rapport de revue privé. La décision d'ouvrir est celle de Thibaut seul ; la bascule elle-même est l'US-052 (commandes Railway lancées par Thibaut), voir [ouverture-publique.md](ouverture-publique.md).

> **État de rédaction : 2026-10-19.** Sprints 14, 15 et 16 mergés et déployés (`main` 5b93a2b), CI verte à chaque fois. Toutes les stories de ces sprints sont livrées.

## Recommandation

**GO donné par Thibaut et démo ouverte au public le 2026-10-19** sur `https://sylius-b2b.thibaut-monier.fr` (PR #32 et #33, `railway config apply`). Vérifications après bascule : 1 à 12 du runbook [ouverture-publique.md](ouverture-publique.md) toutes conformes, dont la 11 (limiteurs par visiteur) vérifiée depuis une seconde connexion. Exposition des services internes vérifiée par Thibaut, sonde externe posée (Sentry), hôte canonique basculé (`DEFAULT_URI` sur le sous-domaine), images persistantes (volume). Retour arrière : section « Refermer la porte » du runbook.

## Bloquants

| # | Prérequis | État | Recommandation | Reste à faire / qui |
|---|-----------|------|----------------|---------------------|
| 1 | Accessibilité AAA, mobile sans débordement, repères de page (US-071 à US-073, US-077) | Livré au sprint 14 (pa11y WCAG2AAA sans erreur sur les 5 pages de l'audit, aucun débordement à 375 et 320 px) | GO | Rejouer `pa11y` et `bin/measure-mobile` sur la version finale (T-085-02) |
| 2 | Signaux « prototype » retirés, pied de page de la démo, page Plugins neutre (US-074, US-087, US-104) | Livré au sprint 14 | GO | Relecture visuelle FR et EN |
| 3 | Boîte de courriels limitée aux mails des fixtures (US-080) | Livré au sprint 14 | GO | Aucune |
| 4 | Vérifications de sécurité de Thibaut (US-079 : exposition des services, configuration versionnée, clé de chiffrement des paiements) | Protection de `main` appliquée. Relecture de la configuration versionnée : « ok à priori » selon Thibaut. Vérification de l'exposition des services internes dans l'hébergeur : en cours (aucun domaine public ni accès TCP public sur les services internes) | **NO-GO tant que la vérification d'exposition n'est pas faite** | Thibaut : terminer la vérification d'exposition et noter « conforme » ou « action corrective » dans la section US-079 de [ouverture-publique.md](ouverture-publique.md) (T-085-05) |
| 5 | Durcissement applicatif : en-têtes, fichiers servis, formulaire du simulateur, correctifs de dépendances (US-082) | Livré au sprint 15, CI verte | GO | Aucune |
| 6 | Chaîne d'approvisionnement : mises à jour automatiques, scan d'image, inventaire des composants, empreintes figées (US-081) | Livré au sprint 15, CI verte | GO | Aucune |
| 7 | Licence du plugin MCP tiers (US-083) | Licence MIT du plugin retenue par Thibaut (2026-10-18) | GO | Aucune |
| 8 | Plugins à jour dans la démo (US-139) | Livré au sprint 15 | GO | Aucune |
| 9 | Hôtes de confiance indépendants du domaine Railway (US-140) | Livré et appliqué (l'application du 2026-10-19 n'a montré aucun changement des hôtes de confiance) ; les deux domaines répondent | GO | Aucune |
| 10 | Domaine `sylius-b2b.thibaut-monier.fr` et son certificat (D3) | DNS propagé, vérification de l'hébergeur faite, certificat valide jusqu'au 2027-01-16, `/healthz` en 200, porte privée vérifiée sur les deux hôtes | GO | Renouvellement du certificat à surveiller |
| 11 | Bascule `CANONICAL_IS_PUBLIC_HOST` | Non faite, à faire **après** le point 10 (levé) | Avant l'annonce du lien | Thibaut : passer l'interrupteur à `true` dans la configuration Railway, relire le plan (seule l'adresse par défaut change), appliquer |
| 12 | Prix négociés et paliers visibles (US-089 à US-091) | Livrés au sprint 15 | GO | Aucune |
| 13 | Porte privée et inscriptions : `OPEN_TO_PUBLIC` (porte ouverte, `DEMO_BASIC_AUTH=0` et `DEMO_REGISTRATION_OPEN=0`) | Mode privé aujourd'hui ; la bascule est l'US-052 | Sur GO explicite seulement | Thibaut, tâches T-052-03 à T-052-07 : sonde externe sur `/healthz` (posée par Thibaut sur Sentry, tolérance de 3 échecs consécutifs car la remise à zéro nocturne rend `/healthz` indisponible 30 à 60 s), bascule, vérification de l'adresse IP vue par les limiteurs avec deux connexions, parcours MCP avec un vrai assistant, relevé de coût (surveillé par Thibaut) |
| 14 | Persistance des images produit (US-145) | Volume de l'hébergeur monté sur le dossier des médias, appliqué le 2026-10-19 ; deux redéploiements successifs sans remise à zéro ont conservé les 210 images (image témoin servie en 200, même taille) | GO | Aucune |

Prérequis de communication du sprint 16, **non bloquants pour le GO technique** : US-144 (bandeau newsletter retiré, fait), US-142 (appel à l'action : prise de rendez-vous posée le 2026-10-19 ; « Écrire un message » attend l'adresse de la page contact du site, en construction), US-143 (mesure d'audience Umami posée le 2026-10-19 ; correctif en cours : la politique de sécurité de contenu n'autorisait pas le domaine de collecte, aucune donnée ne remontait encore).

Vitesse et référencement (US-078, Should, hors bloquant) : meta description et aperçu de lien ajoutés, polices et images allégées ; démo toujours `noindex`. Voir [seo-et-vitesse.md](seo-et-vitesse.md) pour les mesures avant et après.

## Revue de sécurité de réouverture (conclusions)

Relecture du code et de la configuration de la démo, à partir de la synthèse d'audit du 2026-10-18 et de [durcissement-demo.md](durcissement-demo.md).

- **Exposition** : seul le service web a vocation à être public ; l'API REST est désactivée en production ; les chemins d'outillage et de fichiers cachés répondent 404 (constaté à l'audit). À reconfirmer par Thibaut côté hébergement (point 4).
- **Accès et données** : la démo ne contient que des données fictives, remises à zéro chaque nuit. L'accès en écriture au back-office avec le compte de démonstration est un **risque accepté** par Thibaut (décision du 2026-10-17, voir [securite-admin-demo.md](securite-admin-demo.md)). Les comptes clients partagés ne peuvent changer ni leur mot de passe ni leur e-mail.
- **Mode public** : inscriptions, commande invité, devis invité et avis anonyme fermés par un seul interrupteur ; le démarrage du conteneur refuse une configuration publique dangereuse (contrôle de pré-vol). Limiteurs de débit par visiteur actifs ; leur justesse dépend de l'adresse IP transmise par l'hébergeur, à vérifier à la bascule (point 13).
- **Code** : aucune alerte de dépendance connue à l'audit ; pas de rendu non échappé dans les gabarits ajoutés au sprint 15 (les métadonnées de partage sont échappées une seule fois) ; appels sortants limités à une liste blanche.
- **Intégration continue** : droits en lecture seule, aucun secret exposé aux contributions externes.
- **Réserve** : cette revue est une relecture, pas un test d'intrusion. Elle ne remplace pas la vérification des points 4, 9 et 10 par Thibaut ni la vérification après bascule (`bin/verify-public-mode`).

## Risques résiduels

À compléter avec la date de revue et l'acceptation de Thibaut pour chaque ligne conservée (une story sacrifiée devient une ligne ici).

| Risque | Gravité | Origine | Acceptation | Revue |
|--------|---------|---------|-------------|-------|
| Défiguration visible jusqu'à la remise à zéro de 03:00 (écriture admin) | Faible | Décision du 2026-10-17 | Acceptée | Au prochain changement de politique |
| Limiteurs de débit tenus en mémoire du conteneur (remis à zéro à chaque déploiement) | Faible | [durcissement-demo.md](durcissement-demo.md) | Acceptée | Au passage à plusieurs instances |
| Prix de groupe appliqués aux requêtes de boutique sans session (réglage `group_prices_for_guests` du plugin de paliers), nécessaire au PunchOut ; les comptes de démo étant publics, aucun prix confidentiel n'est exposé | Faible | Choix de démo | Acceptée ; correctif à prévoir dans le plugin | 2027-02-22 (sprint 17) |
| Blocage du paiement sur compte en cas de pièce échue désactivé dans la démo (sinon la pièce échue du client ACME empêcherait de payer sur compte, simulateur PunchOut compris) | Faible | Choix de démo | Acceptée par Thibaut le 2026-10-19 | Au prochain changement de politique |

## Actions de Thibaut, dans l'ordre

1. Terminer la vérification d'exposition des services internes dans l'hébergeur (US-079, point 4).
2. Poser la sonde externe sur `/healthz` (fait côté Sentry par Thibaut, à confirmer) ; fournir l'adresse de la page contact quand elle sera prête (non bloquant).
3. Relire ce dossier à jour, accepter ou refuser les risques résiduels.
4. Répondre **GO** ou **NO-GO** par écrit. Sans GO explicite, la démo reste privée (401 pour un anonyme, `/healthz` en 200) et aucune commande Railway n'est lancée par Claude.
5. Sur GO : passer `CANONICAL_IS_PUBLIC_HOST` puis `OPEN_TO_PUBLIC` à `true`, appliquer, exécuter `bin/verify-public-mode`, vérifier l'IP vue par les limiteurs et le parcours MCP ([ouverture-publique.md](ouverture-publique.md)). Retour arrière en un geste décrit dans le même document.

Rappel de politique : démo publique mais **non promue**, lien partagé à la main uniquement.

## Journal des vérifications exécutées (T-085-02)

Le 2026-10-18, sur la démo locale :

- `bin/verify-public-mode` (mode public) : toutes les vérifications OK.
- Protection de `main` (lecture) : conforme.
- `composer audit` : aucune alerte de sécurité ; deux paquets abandonnés signalés (risque bas, déjà connu).
- Lighthouse mobile des trois pages principales : performance 97 à 99, LCP 2,1 à 2,2 s ([seo-et-vitesse.md](seo-et-vitesse.md)).
- Restent à rejouer sur la version finale : pa11y WCAG2AAA, `bin/measure-mobile`.

Le 2026-10-19, sur la version déployée (`main` 5b93a2b) :

- CI verte (« Quality and smoke tests », « Docker image ») à la fin de chacun des sprints 14, 15 et 16.
- Domaine `sylius-b2b.thibaut-monier.fr` : certificat valide jusqu'au 2027-01-16, `/healthz` en 200, `bin/verify-public-mode --private` conforme sur les deux hôtes.
- Application de la configuration d'hébergement : aucun changement des hôtes de confiance.
- Persistance des images : 210 images conservées après deux redéploiements sans remise à zéro.
