# Tâches — US-054 : Plugin parc d'équipements client, tranche 1

## Informations US

- **Epic** : EPIC-07
- **Persona** : Thibaut
- **Story Points** : 4 (tranche 1 ; reste en backlog US-056 à US-060)
- **Sprint** : sprint-12-ouverture-et-plugins
- **Dépendances** : aucune (dépôt séparé `/Users/tmonier/Projects/sylius_plugins/sylius-customer-asset-management-plugin`)

## Résumé

En tant que Thibaut, je veux cadrer le plugin OSS `tmonier/sylius-customer-asset-management-plugin` et en poser le socle qualité, afin de décider en connaissance de cause de le construire, de le publier et de le promouvoir (ou non) au regard de Sylius Plus.

## Règles

- Plugin en dépôt séparé ([ADR-0006](../../../../docs/adr/0006-nouveaux-plugins-publies-separement.md)) ; ses commits n'entrent pas dans le dépôt de la démo.
- Base : skeleton SyliusLabs, à l'image de `/Users/tmonier/Projects/sylius_plugins/sylius-channel-access-plugin` (plugin le plus récent) : ECS, PHPStan max, Rector, PHPUnit, CI multi-versions.
- **Politique de communication** : si Sylius Plus couvre déjà le besoin, le plugin n'est pas promu (aucune annonce sur les canaux Sylius).
- **Actions réservées à l'utilisateur** : création et publication du dépôt GitHub public, tag, soumission Packagist. Aucune n'est faite par Claude.
- YAGNI : aucun code fonctionnel (entités, admin, espace client, filtre) dans cette tranche ; il est en backlog.
- Tâches **sacrifiables** ⚠️ : T-054-07, T-054-08 ; coupe franche T-054-05, T-054-06, T-054-09 ; ordre dans [README.md](./README.md).
- Contrat de fin de worker et isolation par chemins explicites : voir [README.md](./README.md).

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Statut |
|----|------|-------|------------|-----------|--------|
| T-054-01 | [DOC] | Mini-spec `SPEC.md` : cibles (industrie, bâtiment), vocabulaire, modèle d'équipement ↔ variantes compatibles, parc du client, filtre, hors périmètre | 2h | — | 🔲 |
| T-054-02 | [DOC] | ADR-0001 : modèle de données (modèle d'équipement, équipement du client), compatibilité par variante, stratégie de filtre du catalogue | 1.5h | T-054-01 | 🔲 |
| T-054-03 | [REV] | Vérification de chevauchement avec Sylius Plus (documentation et notes de version Plus) ; verdict et sources dans `docs/plus-overlap.md` | 1.5h | T-054-01 | 🔲 |
| T-054-04 | [OPS] | 📅 Création et publication du dépôt GitHub public (sujets : ecommerce, sylius, sylius-plugin, symfony, b2b), par l'utilisateur, confirmation explicite | 0.5h | T-054-03, T-054-06 | 📅 |
| T-054-05 | [BE] | Skeleton SyliusLabs du plugin dans le dépôt local : `composer.json`, bundle, extension DI, application de test, `Makefile`, licence MIT | 3h | T-054-02 | 🔲 |
| T-054-06 | [BE] | Socle qualité : `ecs.php`, `phpstan.neon` (max), `rector.php`, `phpunit.xml.dist`, CI multi-versions (`.github/workflows/build.yaml`) | 2.5h | T-054-05 | 🔲 |
| T-054-07 | [TEST] | ⚠️ Test de fumée : le noyau démarre avec le bundle et le conteneur compile | 1h | T-054-06 | 🔲 |
| T-054-08 | [DOC] | ⚠️ `README.md`, `CHANGELOG.md`, `CLAUDE.md` du plugin | 1h | T-054-06 | 🔲 |
| T-054-09 | [REV] | Revue | 1h | T-054-07, T-054-08 | 🔲 |

**Total estimé : 14h** (9 tâches), dont 0.5h calendaire (T-054-04) et 2h sacrifiables (T-054-07, T-054-08) ; coupe franche de 6.5h.

---

## Détail des tâches

### T-054-01 — Mini-spec

- **Type** : [DOC]
- **Estimation** : 2h
- **Fichiers (indicatifs)** : `sylius-customer-asset-management-plugin/SPEC.md` (format de `sylius-channel-access-plugin/SPEC.md`)

**Description** : besoin B2B (industrie, bâtiment) : un client déclare son parc dans son espace client (« Camion Modèle X, immatriculation AB-123-CD ») ; le catalogue peut être filtré pour n'afficher que les pièces compatibles avec ses équipements. Définir : modèles d'équipement liés aux variantes compatibles (admin), équipement du client (modèle, libellé, immatriculation ou numéro de série), CRUD côté espace client, filtre « pièces compatibles » (état conservé, repli si parc vide), droits (un client ne voit que son parc), hors périmètre (gestion des interventions, import en masse). Découpe de la suite en US-056 à US-060.

### T-054-02 — ADR-0001

- **Type** : [DOC]
- **Estimation** : 1.5h
- **Fichiers (indicatifs)** : `sylius-customer-asset-management-plugin/docs/adr/0001-modele-de-donnees-et-filtre.md`

**Description** : décision, alternatives, conséquences (format `docs/adr/` de la démo) : table de compatibilité modèle ↔ variante (many-to-many), propriété par client, filtre appliqué à la liste de produits du catalogue (extension de la requête de listing plutôt que moteur de recherche), identifiants non prédictibles (UUID), validation côté serveur, RGPD (immatriculation = donnée personnelle potentielle).

### T-054-03 — Chevauchement avec Sylius Plus

- **Type** : [REV]
- **Estimation** : 1.5h
- **Fichiers (indicatifs)** : `sylius-customer-asset-management-plugin/docs/plus-overlap.md`

**Description** : comparer la spec aux fonctionnalités de Sylius Plus (documentation et notes de version publiques), avec les sources et les dates. Verdict : « non couvert », « partiellement couvert » ou « couvert ». Si Plus couvre déjà le besoin : le plugin n'est pas promu (mémoire `sylius-plus-communication-policy`), la publication est une décision de l'utilisateur. L'encart Plus de la démo n'est pas modifié ici. Un verdict incertain est dit incertain.

### T-054-04 — Dépôt GitHub public

- **Type** : [OPS] 📅
- **Estimation** : 0.5h
- **Condition** : confirmation explicite de l'utilisateur, après T-054-03

**Description** : Claude prépare la commande exacte (`gh repo create tmonier/sylius-customer-asset-management-plugin --public ...`) et les sujets du dépôt (`ecommerce`, `sylius`, `sylius-plugin`, `symfony`, `b2b`) ; l'utilisateur l'exécute. Pas de tag ni de Packagist dans ce sprint (US-059). Si le verdict est « couvert », la création est à reconsidérer (dépôt privé ou pas de dépôt).

### T-054-05 — Skeleton

- **Type** : [BE]
- **Estimation** : 3h
- **Fichiers (indicatifs)** : racine du dépôt local du plugin (`composer.json`, `src/`, `config/`, `tests/`, `Makefile`)

**Description** : initialiser le dépôt git local (sans remote), copier la structure de `sylius-channel-access-plugin` (nom de paquet `tmonier/sylius-customer-asset-management-plugin`, espace de noms `Tmonier\SyliusCustomerAssetManagementPlugin\`, `sylius/sylius ^2.3`, application de test `sylius/test-application`). Aucune entité.

### T-054-06 — Socle qualité

- **Type** : [BE]
- **Estimation** : 2.5h
- **Fichiers (indicatifs)** : `ecs.php`, `phpstan.neon`, `rector.php`, `phpunit.xml.dist`, `.github/workflows/build.yaml`

**Description** : ECS (coding standard SyliusLabs), PHPStan niveau max avec extensions Doctrine et Webmozart, Rector, PHPUnit, CI multi-versions (matrice PHP et Symfony comme le plugin de référence). Exécution dans Docker (PHP 8.4). Vérifier que Rector et la CI sont d'accord sur la version PHP cible (écueil connu du socle qualité).

### T-054-07 — Test de fumée ⚠️

- **Type** : [TEST]
- **Estimation** : 1h

**Description** : test fonctionnel : le noyau de l'application de test démarre avec le bundle chargé et le conteneur compile ; rouge d'abord (bundle absent), puis vert.

### T-054-08 — README et CHANGELOG ⚠️

- **Type** : [DOC]
- **Estimation** : 1h

**Description** : `README.md` (état « en cours de conception », périmètre, installation prévue), `CHANGELOG.md` (Keep a Changelog, `Unreleased`), `CLAUDE.md` du plugin. Ne rien promettre de non implémenté ; ne mentionner Sylius Plus que selon le verdict de T-054-03.

### T-054-09 — Revue

- **Type** : [REV]
- **Estimation** : 1h

**Description** : relecture de la spec, de l'ADR, du verdict Plus et du socle ; cohérence avec le plugin de référence ; aucun secret ; aucune promotion si le verdict l'interdit.
