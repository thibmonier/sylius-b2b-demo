# Dossier de GO : ouverture publique de la démo (US-085)

Document de décision pour Thibaut. Dépôt public : ce dossier ne donne que des **conclusions**, jamais de valeur, de chemin ni de mode d'exploitation. Le détail des constats reste dans le rapport de revue privé. La décision d'ouvrir est celle de Thibaut seul ; la bascule elle-même est l'US-052 (commandes Railway lancées par Thibaut), voir [ouverture-publique.md](ouverture-publique.md).

> **État de rédaction : 2026-10-18, fin de développement du sprint 15.** Les états des stories du sprint 15 sont ceux des fiches de tâches (revues restantes) ; à remettre à jour avec les revues indépendantes et la CI avant la relecture de Thibaut (T-085-04).

## Recommandation

**NO-GO tant que les bloquants ci-dessous ne sont pas levés.** Aucun bloquant de code n'est connu ouvert ; restent des vérifications et des actions de Thibaut (domaine, certificat, validations d'hébergement) et la confirmation des stories du sprint 15. Dès que le tableau « Bloquants » ne contient plus que des lignes « conforme » ou « risque accepté », la recommandation devient **GO**.

## Bloquants

| # | Prérequis | État | Recommandation | Reste à faire / qui |
|---|-----------|------|----------------|---------------------|
| 1 | Accessibilité AAA, mobile sans débordement, repères de page (US-071 à US-073, US-077) | Livré au sprint 14 (pa11y WCAG2AAA sans erreur sur les 5 pages de l'audit, aucun débordement à 375 et 320 px) | GO | Rejouer `pa11y` et `bin/measure-mobile` sur la version finale (T-085-02) |
| 2 | Signaux « prototype » retirés, pied de page de la démo, page Plugins neutre (US-074, US-087, US-104) | Livré au sprint 14 | GO | Relecture visuelle FR et EN |
| 3 | Boîte de courriels limitée aux mails des fixtures (US-080) | Livré au sprint 14 | GO | Aucune |
| 4 | Vérifications de sécurité de Thibaut (US-079 : exposition des services, configuration versionnée, clé de chiffrement des paiements) | Protection de `main` appliquée et contrôlée en lecture (pull request obligatoire, contrôles « Quality and smoke tests » et « Docker image » requis, ni force-push ni suppression). Les trois autres vérifications sont des actions sur l'hébergement | **NO-GO tant qu'elles ne sont pas cochées** | Thibaut : cocher les trois vérifications de la section US-079 de [ouverture-publique.md](ouverture-publique.md), en y notant « conforme » ou « action corrective » (T-085-05) |
| 5 | Durcissement applicatif : en-têtes, fichiers servis, formulaire du simulateur, correctifs de dépendances (US-082) | Codé, 7 tâches sur 9 terminées au 2026-10-18 (revue restante) | GO une fois la revue close | Rapport de fin ; sinon risque résiduel ci-dessous |
| 6 | Chaîne d'approvisionnement : mises à jour automatiques, scan d'image, inventaire des composants, empreintes figées (US-081) | Codé, 7 tâches sur 8 terminées (revue restante) | Risque acceptable si non livrée, avec date de revue | Idem |
| 7 | Licence du plugin MCP tiers (US-083) | Relecture faite (4 tâches sur 4) ; décision de Thibaut en attente | NO-GO sur la partie MCP tant que Thibaut n'a pas tranché | Thibaut : lire les conditions de la licence et décider (T-083-04) |
| 8 | Plugins à jour dans la démo (US-139) | Codé, 4 tâches sur 5 terminées | GO une fois mergée | Version publiée des plugins en dépendance |
| 9 | Hôtes de confiance indépendants du domaine Railway (US-140) | Code terminé (5 tâches sur 6) ; application par Thibaut (la sixième) | NO-GO tant que les deux domaines ne répondent pas | Thibaut : appliquer et vérifier le domaine Railway **et** `sylius-b2b.thibaut-monier.fr` (T-140-06) |
| 10 | Domaine `sylius-b2b.thibaut-monier.fr` et son certificat (D3) | CNAME et variable d'hôte posés le 2026-10-18, propagation DNS en cours | NO-GO tant que le certificat n'est pas valide | Thibaut : vérifier la propagation et le certificat (commandes de lecture dans [sous-domaine-demo.md](sous-domaine-demo.md)) |
| 11 | Bascule `CANONICAL_IS_PUBLIC_HOST` | Non faite, à faire **après** le point 10 | Après le certificat, avant l'annonce du lien | Thibaut : passer l'interrupteur à `true` dans la configuration Railway, relire le plan (seule l'adresse par défaut change), appliquer |
| 12 | Prix négociés et paliers visibles (US-089 à US-091) | Codés, revue restante | Must : GO une fois livrées | Rapports de fin |
| 13 | Porte privée et inscriptions : `OPEN_TO_PUBLIC` (porte ouverte, `DEMO_BASIC_AUTH=0` et `DEMO_REGISTRATION_OPEN=0`) | Mode privé aujourd'hui ; la bascule est l'US-052 | Sur GO explicite seulement | Thibaut, tâches T-052-03 à T-052-07 : moniteur externe sur `/healthz`, bascule, vérification de l'adresse IP vue par les limiteurs avec deux connexions, parcours MCP avec un vrai assistant, relevé de coût |
| 14 | Persistance des images produit | **Bloquant constaté le 2026-10-18** : les images sont créées par la remise à zéro dans le système de fichiers du conteneur, sans volume dédié ; tout redéploiement non suivi d'une remise à zéro (même un simple changement de variable) laisse les images cassées, constaté après deux redéploiements | **NO-GO tant que non traité** | Décision de Thibaut : volume de l'hébergeur pour les médias, ou remise à zéro automatique au démarrage du conteneur. Après chaque redéploiement, en attendant : lancer une remise à zéro et vérifier une fiche produit |

Prérequis de communication du sprint 16, **non bloquants pour le GO technique** : US-144 (bandeau newsletter retiré, fait), US-142 (appel à l'action contact et prise de rendez-vous), US-143 (mesure d'audience Umami).

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
| Story du sprint 15 non livrée (US-078, US-081, US-082, US-083, US-141) | À qualifier | Ordre de sacrifice (D1) | À accepter ou NO-GO | À fixer par Thibaut |

## Actions de Thibaut, dans l'ordre

1. Valider les trois vérifications d'hébergement de l'US-079 (point 4).
2. Vérifier la propagation DNS et le certificat du sous-domaine, et que les deux domaines répondent (points 9 et 10).
3. Décider de la licence du plugin MCP (point 7).
4. Relire ce dossier à jour, accepter ou refuser les risques résiduels.
5. Répondre **GO** ou **NO-GO** par écrit. Sans GO explicite, la démo reste privée (401 pour un anonyme, `/healthz` en 200) et aucune commande Railway n'est lancée par Claude.
6. Sur GO : créer le moniteur externe, passer `CANONICAL_IS_PUBLIC_HOST` puis `OPEN_TO_PUBLIC` à `true`, appliquer, exécuter `bin/verify-public-mode`, vérifier l'IP vue par les limiteurs et le parcours MCP ([ouverture-publique.md](ouverture-publique.md)). Retour arrière en un geste décrit dans le même document.

Rappel de politique : démo publique mais **non promue**, lien partagé à la main uniquement.

## Journal des vérifications exécutées (T-085-02)

Le 2026-10-18, sur la démo locale :

- `bin/verify-public-mode` (mode public) : toutes les vérifications OK.
- Protection de `main` (lecture) : conforme.
- `composer audit` : aucune alerte de sécurité ; deux paquets abandonnés signalés (risque bas, déjà connu).
- Lighthouse mobile des trois pages principales : performance 97 à 99, LCP 2,1 à 2,2 s ([seo-et-vitesse.md](seo-et-vitesse.md)).
- Restent à rejouer sur la version finale : pa11y WCAG2AAA, `bin/measure-mobile`, CI (« Quality and smoke tests », « Docker image »).
