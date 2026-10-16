# Rétrospective — sprint 8 (démo et durcissement)

Date : 2026-10-16. PR #11. 25 points : US-030, US-040, US-041, US-042, US-043.

> Directive fondamentale : quoi que nous découvrions, nous comprenons et croyons sincèrement que chacun a fait du mieux
> qu'il pouvait, compte tenu de ce qu'il savait à ce moment-là, de ses compétences, des ressources disponibles et de la
> situation.

## Résultat

- 5 stories sur 5 réalisées. Restent hors du sprint : T-030-03, la validation avec un vrai assistant, qui suppose
  l'ouverture publique, et T-041-15, le relevé de coût à J+7, qui va au sprint 9.
- 3 publications de plugins : channel-access 0.2.0, registration-approval 0.1.2, b2b-mcp 1.2.1.
- Suite de 594 tests. ECS et PHPStan à 0. CI verte, Factur-X compris.

## Ce qui a bien marché

- Les évolutions des plugins (routes restreintes, statut d'utilisabilité) ont remplacé deux contournements de la démo.
  La passe de compilation et le garde MCP ont été supprimés.
- La contre-vérification indépendante a trouvé une vraie course : l'usage unique et le plafond du simulateur ne
  tenaient pas sous des requêtes parallèles. Elle est corrigée, avec un test qui échoue sans le verrou.

## Ce qui a coincé

- L'incident de tag sur b2b-mcp 1.2.0 : le tag a été posé sur l'ancien `main`, et Packagist a gardé cette référence.
  Il a fallu publier la 1.2.1.
- Les agents de revue se sont arrêtés au bout de 6 tours sans rendre de rapport, comme aux sprints précédents. Une
  relance a suffi chaque fois. L'un d'eux a de nouveau produit un faux positif (routes de prix).
- La CI de la PR ne s'est pas déclenchée : seul GitGuardian a tourné. Il a fallu la lancer à la main
  (`workflow_dispatch`).
- PHPUnit plante en local avec la limite mémoire de 128 Mo : il faut le lancer avec `-d memory_limit=-1`.

## Actions

| Action | Porteur | Échéance |
|--------|---------|----------|
| Toujours vérifier que le commit tagué est bien le bon avant de taguer (push, tête de PR, merge, contenu) | orchestrateur | chaque publication |
| Vérifier N2 (commande invité, devis invité) avant l'ouverture publique | sprint 9 / ouverture | avant le GO public |
| Si une PR n'a pas de run CI au bout de 2 minutes, lancer `gh workflow run ci.yaml --ref <branche>` | orchestrateur | continu |
