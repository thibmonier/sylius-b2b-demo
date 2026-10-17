# Rétrospective - Sprint 11

## Informations

| Attribut | Valeur |
|----------|--------|
| Date prévue | 2026-11-27 (après-midi) ; préparée le 2026-10-17 |
| Format | Starfish |
| Facilitateur | Scrum Master (Claude) |
| Participants | PO (Thibaut), workers Claude |

## Directive Fondamentale

> « Peu importe ce que nous découvrons, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles, et de la situation. »
> — Norman Kerth

## Rappel du sprint

Sprint Goal atteint côté code et documentation : 3 stories (10 pts, dont US-051 ajoutée en cours de sprint) en review, 808 tests et 8 386 assertions sans échec sur base neuve (2 passages), ECS et PHPStan OK. Cinq éléments [OPS] restent à l'utilisateur. Le sprint a surtout été ralenti par l'environnement : disque plein et caches de test.

## Observations (Starfish)

### Continuer
- Vérifier sur base neuve avec deux passages de la suite complète et contrôler l'absence de marqueur de reset laissé.
- La revue de sécurité indépendante avant l'ouverture, dont les constats ont été appliqués.
- Écrire le test avant le correctif (variante sans dimensions ajoutée à la revue d'US-051).
- Accepter un défaut signalé par l'utilisateur en cours de sprint (US-051) et publier le total honnêtement (10 pts pour 8 planifiés).

### Commencer
- Contrôler `df` avant chaque suite complète et avant un lot de travaux lourds.
- Isoler le cache de test par exécution et le vider avant chaque suite.
- Donner aux relecteurs assez de tours pour rendre un rapport.
- Relire les commandes collées sur plusieurs lignes avant de les lancer.

### Arrêter
- Laisser s'accumuler les caches `var/cache-*` des workers après leur fin.
- Réutiliser un `var/cache/test` compilé avant un changement de constructeur.
- Lancer les relecteurs spécialisés avec la limite de 6 tours par défaut.

### Plus de
- Tests qui n'écrivent jamais dans le vrai `var/` (chemin du marqueur surchargé en config de test).
- Documentation des limites actées (`demo_orders`) plutôt que du code.

### Moins de
- Faux échecs dus à l'état de la machine et non au code.
- Relances de relecteurs.

## Thèmes et analyse

### Thème 1 : espace disque (priorité haute)
Faits : disque plein à 100 % à plusieurs reprises (caches de test par worker, OrbStack, VM de l'app Claude) ; MySQL arrêté, vérification bloquée.

5 Pourquoi :
1. Pourquoi MySQL s'est-il arrêté ? → le disque était plein.
2. Pourquoi plein ? → chaque worker crée son `var/cache-*` et rien ne le supprime.
3. Pourquoi rien ne le supprime ? → aucune étape de fin de worker ne nettoie.
4. Pourquoi le problème est-il découvert tard ? → `df` n'est pas contrôlé avant une suite ; la checklist de pré-sprint ne dit que « disque local ~3 Go ».
Cause racine : ni nettoyage en fin de worker ni contrôle d'espace avant la suite.

### Thème 2 : caches de test partagés ou périmés (priorité haute)
Faits : `var/cache/test` compilé avant un changement de constructeur (10 erreurs Badge) ; marqueur de reset écrit dans le vrai `var/` car `services.yaml` l'emportait sur la config de test (corrigé dans 0e8dbda) ; `bin/prepare-test-db` en échec sur un dossier de cache neuf (DoctrineMetadataCacheWarmer), corrigé par `--no-warmup` (4bf5dbf).
Cause racine : le cache de test est partagé entre exécutions et n'est pas vidé avant la suite.

### Thème 3 : relecteurs spécialisés (priorité moyenne)
Faits : security-auditor et symfony-reviewer ont atteint la limite de 6 tours sans rendre de rapport ; relancés en general-purpose.
Cause racine : la limite de tours est insuffisante pour une revue de plusieurs dizaines de fichiers.

### Thème 4 : commandes collées (priorité basse)
Fait : une commande de mesure collée par l'utilisateur et coupée sur deux lignes a laissé une boucle active.
Cause racine : commande longue et multi-lignes, sans garde-fou de durée.

## Actions (SMART)

### Action 1 : Gérer l'espace disque
| Attribut | Valeur |
|----------|--------|
| Description | Supprimer le dossier `var/cache-*` d'un worker dans son bloc de fin, et contrôler `df` avant chaque suite complète (arrêt si moins de 5 Go libres). |
| Responsable | Scrum Master (Claude) |
| Deadline | Planning du sprint 12 |
| DoD | Étape de nettoyage ajoutée au contrat de fin de worker (`tasks/README.md`) et ligne `df` dans `pre-sprint-checklist.md` ; 0 arrêt de MySQL dû au disque pendant le sprint 12. |
| Priorité | Haute |

### Action 2 : Cache de test isolé et vidé avant chaque suite
| Attribut | Valeur |
|----------|--------|
| Description | Faire créer un cache de test dédié par exécution (ou par worker) et le vider avant chaque suite, via `bin/prepare-test-db` ou un wrapper. |
| Responsable | Worker devops, relu par le Scrum Master |
| Deadline | Fin de la première semaine du sprint 12 |
| DoD | La suite complète passe 2 fois de suite sans erreur liée au cache, y compris après un changement de constructeur ; aucun fichier de test écrit dans le vrai `var/`. |
| Priorité | Haute |

### Action 3 : Relecteurs avec assez de tours
| Attribut | Valeur |
|----------|--------|
| Description | Porter la limite de tours des relecteurs (security-auditor, symfony-reviewer) à 12 au minimum, ou lancer les revues larges en general-purpose, et exiger le rapport dans les dernières consignes. |
| Responsable | Scrum Master (Claude) |
| Deadline | Planning du sprint 12 |
| DoD | 100 % des revues du sprint 12 rendent un rapport au premier lancement ; 0 relance pour limite de tours atteinte. |
| Priorité | Moyenne |

## Suivi des actions de la rétrospective du sprint 10

| Action (S10) | Responsable | Statut | Constat |
|--------------|-------------|--------|---------|
| 1. Isoler les workers parallèles | SM | ✅ Fait | Règle d'isolation (chemins explicites) dans `tasks/README.md` ; aucun mélange de commits constaté dans le journal |
| 2. Rapport de fin court et placé en premier | SM | ✅ Fait | Contrat en version courte, bloc en première section |
| 3. Solder les relevés Railway | Thibaut + SM | ⚠️ En cours | Relevé de coût repris en T-049-08 (après le 2026-10-23) |
| 4. Reporter les mineurs au backlog | SM | À confirmer | Vérifier en séance |
| 5. Chiffrer disque et quotas à la planification | SM | ❌ Insuffisant | La ligne de checklist (~3 Go) n'a pas évité les disques pleins ; repris en action 1 |

## Check-out

ROTI moyen : {à collecter en séance}/5

Verbatims :
- {à collecter}
