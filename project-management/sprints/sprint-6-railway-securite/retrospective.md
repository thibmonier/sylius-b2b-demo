# Rétrospective — sprint 6 (Railway, démo privée et durcie)

Date : 2026-10-16. Format : Start / Stop / Continue + directive fondamentale.

> **Directive fondamentale** : quels qu'aient été les constats, nous croyons que chacun a fait de son mieux avec ce qu'il savait, ses compétences, les ressources et la situation du moment.

## Sprint Goal — atteint ?

Mettre la démo en ligne sur Railway, privée et durcie : déploiement, remise à zéro nocturne supervisée, budget, admin restreint, durcissement et revue de sécurité.

**Atteint** : 6/6 stories en review, 29 pts (ré-estimés après décomposition : 127,5 h pour les 21 pts initiaux). La démo tourne sur Railway, privée (auth HTTP basique), et la revue de sécurité recommande l'ouverture. Restent à l'utilisateur : la vérification des notifications Railway (T-033-09), le moniteur externe sur `/healthz` et le GO d'ouverture (T-037-07). Le relevé de coût à J+7 (T-034-02) est calendaire : sprint 7.

## Métriques

- Stories : 6/6, 29 pts ; 54 tâches actives (107,5 h), 11 glissées au sprint 7 (20 h).
- Tests : 489 (CI verte, 0 échec), contre 299 à la fin du sprint 5 ; ~190 nouveaux tests, dont ~40 tests de rendu du durcissement et ~40 pour l'admin restreint et le reset.
- Revue US-037 : 1 majeur (V1, trouvé en dynamique, corrigé et revérifié), 8 mineurs (3 corrigés, 5 au sprint 7), 0 critique.
- Déploiement : 3 tentatives avant le premier succès (Dockerfile), puis reset réel en 25 s.

## Ce qui a bien marché (Continue)

- **Vérifier sur la vraie plateforme** : V1 (contournement de toutes les limites par `X-Forwarded-For`) était invisible en tests et en revue de code ; seule une mesure sur Railway (avec et sans en-tête forgé) l'a révélé. Même constat qu'aux sprints 4 et 5 : le rendu réel trouve ce que les tests ne voient pas.
- **Lire la doc de la plateforme avant de promettre** : `railway.json` fermé aux nouveaux services (IaC adoptée), e-mails Railway inexistants pour un cron en échec (alerte repensée via `/healthz`). Deux décisions auraient sinon échoué en production.
- **Décisions utilisateur rapides et tracées** (périmètre, ouverture privée, limite de dépense workspace, alerte) au lieu d'hypothèses.
- **Échec fermé partout** : preflight, porte, garde sortant comme erreur réseau, IP inconnue → clé partagée plutôt que forgée.
- **Workers parallèles avec base, cache et frontières de fichiers distincts** : deux blocages croisés (env() dans `login_throttling`, garde sortant qui cassait l'admin) remontés et corrigés en quelques minutes.

## Ce qu'il faut arrêter / corriger (Stop)

- **Proposer une solution sans vérifier la plateforme** : j'ai recommandé « faire planter le conteneur » pour l'alerte avant de lire que Railway n'envoie l'e-mail qu'après 10 redémarrages ; il a fallu revenir vers l'utilisateur.
- **Une CI verte grâce au cache** : l'image construisait en CI sur une couche Composer ancienne, Railway (sans cache) a échoué. Épingler les images de build.
- **Caches compilés périmés** (test en `APP_DEBUG=0`, prod du test de télémétrie) : plusieurs faux échecs chez les workers et chez moi. Vider le cache après tout changement de service, systématiquement.
- **Tests qui comparent l'avant et l'après sur une base partagée** : le test du reset et l'historique de commandes comptaient les commandes laissées par d'autres tests. Comparer à l'état initial connu.
- **TDD sauté** sur deux correctifs (M1 côté worker, V1 côté orchestrateur : tests écrits avec le code).

## À tenter (Start)

- Un script de vérification post-déploiement versionné (401, en-têtes, IP forgée, Mailpit, `/healthz`), rejoué à chaque déploiement plutôt qu'à la main.
- Épingler par digest les images Mailpit et `composer`, et les actions CI par SHA (Renovate).
- Couper la journalisation des dépréciations en production.

## Actions

1. GO de merge de la PR #6, puis `railway.ts` repointé sur `main` et appliqué.
2. Utilisateur : moniteur externe sur `/healthz`, notifications Railway vérifiées, puis GO d'ouverture (`DEMO_BASIC_AUTH=0`).
3. Sprint 7 : M2, M4, M5, M6, M8 ; S2-R3…R10, S4-R3 ; US-006, US-029, US-030 ; relevé de coût J+7 ; correctifs punchout-cxml (commande non invitée, puis retrait du contournement) et container-fill ; US-019/US-020.
