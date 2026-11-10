# Rétrospective du sprint 51 — Valeur visible dans la démo

**Date** : 2026-11-10

> « Quoi que nous découvrions, nous comprenons et croyons sincèrement que chacun a fait du mieux qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences et capacités, des ressources disponibles et de la situation. » — Directive fondamentale

## Suivi des actions du sprint 50

| Action | Résultat |
|--------|----------|
| A-50-1 branches de worktree `--no-track`, push `HEAD:<branche>`, `headRefOid` vérifié | Appliqué : aucun push annoncé sans avoir eu lieu au sprint 51 |
| A-50-2 test avec une application hôte avant la première version d'un thème ou plugin d'interface | Fait pour le thème avec US-273 (projet Sylius réel, canal avec et sans thème) |
| A-50-3 `composer install` dans le worktree, jamais de `vendor/` copié | Fait dans les worktrees ; raté dans le checkout principal de la démo (voir plus bas) |
| A-50-4 `php bin/proof-check` et `proof.json` dans la PR qui ajoute des tests | Fait : #170 et #171 ont chacune mis `proof.json` à jour, `main` est resté vert |
| A-50-5 VM Docker à 4 Go | Fait |

## Ce qui a bien marché

- Les trois stories étaient dans trois dépôts différents : aucun conflit, US-272 et US-273 menées en parallèle de la version du plugin predictive.
- Le job « projet vierge » a trouvé un vrai défaut du README (l'import des styles manquait) dès sa première exécution : c'est le scénario que les tests du thème, sans application hôte, ne pouvaient pas voir.
- Les dates d'IndusPro relatives au jour du reset rendent la démo stable dans le temps, et le test le prouve sur 5 dates.
- Les échecs du job affichent la page et la cause (titre de l'exception rejouée en debug, lignes d'erreur du log) : chaque itération a été diagnostiquée sur le log de la CI, sans reproduire en local.

## Ce qui a coincé

- Le job « projet vierge » a demandé six itérations, chacune sur un écart d'environnement de Sylius-Standard : script post-création sans `vendor/`, `FOSRestBundle` absent sur 2.2, npm qui plante sur 2.3, serveur PHP intégré qui ne voit pas l'environnement du job, `curl` qui interrompait le script sans nommer la page.
- Le `vendor/` du checkout principal de la démo était resté sur l'ancienne version du plugin predictive : le hook pre-push (PHPStan) a refusé le push.
- Les PR de dépendances sont devenues rouges pour des raisons extérieures au sprint (avis de sécurité sur halite, Rector 2.7 incompatible avec PHPStan 2.3.4) et ont demandé deux PR de correction.
- Plusieurs runs ont échoué sur des incidents d'infrastructure (installation de symfony-cli, téléchargement de wkhtmltopdf en 504, image Dependabot introuvable) ; relancés sans changement.
- Le canari « Symfony AI next minor » d'ai-catalog-copilot est resté rouge pour une liste de paquets incomplète : il n'a pas testé la 0.15 la semaine de sa sortie.

## Actions

| ID | Action | Responsable | Échéance |
|----|--------|-------------|----------|
| A-51-1 | Pour un job qui monte une application de zéro : lancer d'abord le job avec un `--list` ou une seule étape pour valider l'environnement, avant les assertions | Claude | prochaine story de CI |
| A-51-2 | Après la fusion d'une PR qui change `composer.lock`, `composer install` dans le checkout principal de la démo avant le prochain push | Claude | sprint 52 |
| A-51-3 | Dans un canari de version, monter tous les paquets d'une même famille (`symfony/ai-*`), `require-dev` compris | Claude | fait (branche `ci/symfony-ai-next-all-packages`) |
| A-51-4 | Veille du lundi : distinguer un rouge d'infrastructure (relance) d'un rouge réel avant de le lister dans « `main` rouge » | Claude | veille de la semaine 47 |
