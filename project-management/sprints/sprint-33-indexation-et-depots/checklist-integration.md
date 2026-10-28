# Checklist d'intégration : Sprint 033

> Complète [../../templates/checklist-integration.md](../../templates/checklist-integration.md) (gabarit de rapport de fin et checklist générale). L'orchestrateur applique cette liste à chaque rapport de worker avant de déclarer « vert ». Nouveautés du sprint 33 par rapport au sprint 32 : rouge prouvé (règle 27), table des invariants de test (règle 28), dépôts de plugins (règle 29).

## Ouverture du sprint

- [ ] `df -h .` et `bin/check-disk` relevés ; mémoire libre et swap relevés (T-TECH-33-04, T-TECH-33-11) ; **sous 5 Go, aucun test ni rendu local** ; **sous 6 Go, aucune suite complète** ; au-delà de 2 Go de swap ou sous 7 Go de disque, au plus 2 workers lancent des tests (règle 24)
- [ ] **T-TECH-33-04** (runs autorisés annoncés, file d'attente sur le verrou) fait avant le lancement des workers qui testent ; disque libéré par Thibaut (T-TECH-33-03), ou travail limité à l'écriture de tests et de code
- [ ] **T-TECH-33-05 et 08** (rendu sans barre de débogage, avertissement de build périmé) faits **avant la première capture** ; `public/build` frais
- [ ] Branche `feature/sprint-33-indexation-et-depots` à jour ; aucune configuration non commitée dans l'arbre partagé
- [ ] **Table « invariants de test » d'US-207 confirmée et confrontée au périmètre par l'orchestrateur avant le premier test rouge** (règle 28, T-207-01) ; tout conflit noté au registre avec ses options
- [ ] État « avant » relevé avant tout code : tests existants de la table verts, ou « non exécuté » avec la raison du disque (T-207-02)
- [ ] US-209 : liste des 29 dépôts versionnée ; copies locales présentes sous `/Users/tmonier/Projects/sylius_plugins/` ; relevé de départ refait sur les branches par défaut **distantes** (T-209-01)

## À chaque rapport de worker

- [ ] Chaque ligne du tableau de preuves porte la commande **et** la dernière ligne de sortie ; un rapport sans preuve est refusé
- [ ] **PHPStan rejoué sur tout le dépôt** (`src` et `tests`), ECS (`src tests`) et Rector rejoués par l'orchestrateur sur l'arbre intégré
- [ ] **Tests lancés dans l'arbre partagé**, jamais depuis un worktree au `vendor` en lien symbolique (règle 22) ; **`vendor/` de l'arbre partagé non modifié par un worker** (règle 25)
- [ ] Aucun compteur écrit en dur : valeur déduite du catalogue, de la configuration ou du rapport de preuve
- [ ] **Commit de tests rouges seul, avec l'échec observé de chaque test cité dans son message** (règle 27) ; sinon « non exécuté, story non comptée rouge prouvé » ; **aucune correction des données d'un test rouge dans le commit du code**
- [ ] **Tests existants modifiés = ceux de la table, chacun avec sa ligne de registre écrite avant** (règle 28) ; `git diff --stat` des autres tests existants vide
- [ ] `git show --stat` et `git log -1` de chaque commit : seuls les fichiers de la tâche, **bon numéro de story**, aucun `git commit -a` ; **fichiers communs conformes à la matrice** (règle 21)
- [ ] **Aucune commande `cp -R`, `rm -rf` ni `mv` sur un chemin issu d'une variable ou d'un glob sans la garde de la règle 18** ; commandes de nettoyage destinées à Thibaut en chemins absolus, précédées d'un `du -sh`
- [ ] **Aucun plugin modifié hors US-209** ; pour US-209, `composer.lock`, code et dépendances des plugins inchangés (`git diff --stat` d'un dépôt : `.github/` et `composer.json` section `config` seulement) ; `git diff --stat -- vendor composer.lock` de la démo vide
- [ ] **Aucun détail de sécurité** dans les fichiers, les messages de commit et les PR de la démo comme des plugins ; ton neutre, aucune comparaison avec Sylius Plus ; **plugins en recoupement et import ERP absents des données structurées et des descriptions**
- [ ] Suite en échec rejouée seule avant toute enquête
- [ ] Preuve publiée par `bin/proof-rebase` si des fichiers de tests changent ; PR de la démo fusionnées en série
- [ ] **Séquence exacte de la CI rejouée en local** (règle 19) avant le push, quand le disque le permet ; CI verte, run terminé, avant le merge
- [ ] Réponse de l'orchestrateur au worker : **identifiant de la décision cité** (règle 25) ; le worker a relu le registre avant d'attendre (règle 26)
- [ ] Tâche cochée dans le commit qui la livre

## Dépôts de plugins (US-209, à chaque lot)

- [ ] **Règle 29 : `git fetch origin` et branche courante contrôlés avant tout commit**, dans chaque dépôt ; branche `chore/supply-chain-hardening` créée depuis `origin/<branche par défaut>` ; le script a refusé un chemin hors de `/Users/tmonier/Projects/sylius_plugins/` et un dépôt non Git
- [ ] `bin/plugin-supply-chain check` : les 9 contrôles passent (ou « sans objet » pour la revue du verrou d'un dépôt sans `composer.lock`) avant le commit, puis sur la branche par défaut après la fusion
- [ ] Une PR par dépôt ; message Conventional Commits neutre ; **CI verte, run terminé, avant la fusion** ; **aucun tag, aucune release** (`git tag --list` et les releases du dépôt inchangés)
- [ ] Dépôt à CI rouge préexistante, sans CI ou à branche protégée : **PR laissée ouverte**, ligne dans T-209-11, décision de Thibaut au registre avant d'agir ; aucune protection contournée
- [ ] Le fork de la wishlist n'est pas touché (exclu) ; aucun dépôt créé

## Portes de Thibaut

- [ ] **Liste blanche et activation de l'indexation confirmées par Thibaut (T-207-03), GO noté au registre avant d'activer le réglage en production** ; le code et les tests peuvent être prêts avant, réglage désactivé par défaut
- [ ] Disque libéré par Thibaut (T-TECH-33-03), relevé par `df`
- [ ] Sitemap déclaré dans Google Search Console **par Thibaut** après le déploiement (T-207-14) ; aucun compte de moteur de recherche touché par Claude
- [ ] Exceptions d'US-209 décidées par Thibaut et notées au registre avant d'agir (T-209-11)
- [ ] **Aucune PR fusionnée avec une question à Thibaut ouverte** (commentaire ou description relu avant l'enchaînement automatique)
- [ ] Toute phrase « accepté par Thibaut » retrouvée dans le [registre des décisions](./registre-decisions.md) ; **toute PR hors sprint a sa ligne au registre avant son ouverture** (règle 26)

## Avant la PR

- [ ] **Suite complète locale** (T-TECH-33-13) lancée avant le push ; sinon attente explicite dite dans la PR et espace demandé à Thibaut (aucune CI rouge découverte après des contrôles locaux verts)
- [ ] **Capture à 1280 px relue par l'orchestrateur** (règle 23, T-TECH-33-14) : home connectée et page vitrine listée ; aucune vignette vide, aucune capture simulée ; captures de production, dites dans la PR, si le rendu local est impossible
- [ ] Relevé de départ des dépôts joint à la PR de la démo, sans détail sensible
- [ ] Aucun changement de dépendances de la démo : `composer.lock` et `vendor/` inchangés

## Avant le passage en done

- [ ] Table « scénario → test ou preuve » complète dans le fichier de tâches de la story (noms de tests réels)
- [ ] US-207 : **mesures de production consignées le jour du déploiement** : `robots.txt` n'est plus `Disallow: /` partout ; `X-Robots-Tag: noindex` présent hors liste blanche et absent des pages listées ; `sitemap.xml` limité à la liste ; `bin/verify-public-mode` (contrôles 1 à 12) ; validateur public des données structurées (résultat au registre) ; pa11y WCAG2AAA FR et EN, 320, 375 et 1280 px
- [ ] US-207 : mode privé toujours fermé (`noindex` et `Disallow: /` partout) ; une route ajoutée sans être listée répond `noindex` (test)
- [ ] **Que devient cet état après un déploiement ?** Réglage d'activation déclaré dans l'infrastructure avec `preserve()` (une variable posée à la main est supprimée par `config apply` sinon) ; sitemap servi par l'application ; vérifié dans un conteneur neuf avant le merge
- [ ] US-209 : rapport final « 29 dépôts conformes » ou écarts justifiés dépôt par dépôt ; mémoire du socle qualité des plugins mise à jour
- [ ] Texte public relu contre sa source ; parité FR et EN ; aucune comparaison avec Sylius Plus
- [ ] Reliquats du sprint 32 : T-TECH-33-07 fait (US-203 en done, noté au registre) et capture du panier (T-TECH-33-09), ou reportés avec la raison du disque
