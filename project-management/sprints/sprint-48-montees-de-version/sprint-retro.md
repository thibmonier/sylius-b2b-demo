# Sprint 48 — Montées de version : Rétrospective

> Directive fondamentale : « Quoi que nous découvrions, nous comprenons et croyons sincèrement que chacun a fait de son mieux, compte tenu de ce qu'il savait à ce moment-là, de ses compétences, des ressources disponibles et de la situation. »

## Ce qui a bien marché

- Lire soi-même les logs CI a donné la cause en une minute à chaque fois : endroid 6 exige PHP 8.4, `main` rouge sur `proof-check` et non sur l'avis de sécurité.
- La capture dans l'application hôte avant le tag a encore trouvé un défaut invisible dans la CI du plugin (onglet « Étiquettes »).
- Élargir la contrainte (`^5.0 || ^6.1`) plutôt que la remplacer a gardé toute la matrice PHP.

## Ce qui a coincé

- Disque plein (ENOSPC) en plein sprint : tout s'est arrêté jusqu'à la suppression des `vendor/` hors sprint.
- Un worker a annoncé un push qui n'avait pas eu lieu (commit resté local) ; détecté parce que la CI tournait encore sur l'ancien SHA.
- `main` de la démo est resté rouge trois runs après #161 sans que personne ne le voie.
- Le mode auto refuse merges et tags tant que le GO n'est pas explicite dans la conversation.

## Actions (SMART)

| ID | Action | Responsable | Échéance |
|----|--------|-------------|----------|
| A-48-1 | Avant de lancer des workers, vérifier l'espace libre (≥ 10 Go) et supprimer les `vendor/` des dépôts hors sprint | Claude | début du sprint 49 |
| A-48-2 | Après le rapport d'un worker, comparer le SHA local et celui de la PR (`gh pr view --json headRefOid`) avant de surveiller la CI | Claude | chaque sprint |
| A-48-3 | Lire l'état du `main` de la démo au lancement de chaque sprint et le réparer en premier s'il est rouge | Claude | début du sprint 49 |
| A-48-4 | Proposer une story « GTIN fictifs valides dans les fixtures du passeport » pour que le QR soit visible dans la démo | Claude | revue du sprint 48 |
