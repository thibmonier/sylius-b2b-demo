# Rétrospective du sprint 50 — La démo sur le thème publié

**Date** : 2026-11-05

> « Quoi que nous découvrions, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. » — Directive fondamentale

## Suivi des actions du sprint 49

| Action | Résultat |
|--------|----------|
| A-49-1 scénario « canal sans le thème » dans les stories de thème | Fait : tests avec et sans thème dans la PR #20 du thème |
| A-49-2 lire la PR d'un worker avant d'imposer un schéma | Fait : les options de la liste dense ont été soumises à Thibaut avant tout code |
| A-49-3 captures et audit sur la démo, pas sur l'application de test | Fait pour les captures avant/après, ce qui a révélé les défauts du thème ; audit abandonné avec la bascule |
| A-49-4 un push puis attendre la fin du run | Fait par les workers ; l'orchestrateur surveille la CI et relaie le résultat |

## Ce qui a bien marché

- Les captures avant/après demandées par Thibaut ont montré le défaut de l'en-tête en quelques minutes : aucune suite de tests du thème ne l'avait vu, car elle tournait sans application hôte.
- La décision « corriger le thème d'abord » a évité de fusionner une démo dégradée ; les tests de maquettes de la démo ont servi de filet.
- Les deux stories de couverture ont dépassé le seuil avec de vrais tests de comportement (92,61 % et 98,07 %).
- Une seule règle (`TmonierPackages`) remplace quatre copies de « tout paquet tmonier/* est un plugin ».

## Ce qui a coincé

- Le thème avait été conçu et testé sans application hôte réelle : ses surcharges supprimaient les hookables d'une application et ses hooks écrasaient ses réglages. Le défaut n'est apparu qu'à l'intégration, au sprint suivant sa livraison.
- J'ai créé les branches des worktrees en suivant `origin/main` : le `git push` d'un worker n'a rien envoyé, il a annoncé un push qui n'avait pas eu lieu, et j'ai attendu une CI sur l'ancien commit.
- Un worker a mesuré la couverture avec un `vendor/` copié d'un autre checkout (Symfony AI 0.14 au lieu de 0.15) : une classe de test absente de la CI a fait rougir 12 jobs.
- La simulation d'application hôte utilisait une clé de configuration de Twig Hooks propre à Sylius 2.3 : rouge sur 2.2, que le worker n'avait pas en local.
- Le contrôle de la preuve publiée n'est bloquant que sur `main` : `main` de la démo est passé au rouge après la fusion de #167.
- La VM Docker n'a que 2 Go : les mesures de couverture locales n'ont pas pu inclure les suites fonctionnelles.

## Actions

| ID | Action | Responsable | Échéance |
|----|--------|-------------|----------|
| A-50-1 | Créer les branches de worktree avec `--no-track` et pousser explicitement `HEAD:<branche>` ; vérifier `headRefOid` avant d'attendre une CI | Claude | sprint 51 |
| A-50-2 | Pour un thème ou un plugin d'interface : un test fonctionnel avec une application hôte qui ajoute ses propres hookables et réglages, avant la première version | Claude | US-226, US-227 |
| A-50-3 | Brief des workers : `composer install` dans le worktree, jamais de `vendor/` copié | Claude | sprint 51 |
| A-50-4 | Avant de fusionner une PR démo qui ajoute des fichiers de tests : lancer `php bin/proof-check` et committer `proof.json` dans la même PR | Claude | sprint 51 |
| A-50-5 | Proposer à Thibaut d'augmenter la mémoire de la VM Docker (4 Go) | Claude | revue du sprint 50 |
