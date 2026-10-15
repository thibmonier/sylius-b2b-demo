# ADR-0010 — Connexion de démo par formulaire pré-rempli

- **Statut** : Proposé (2026-10-15)
- **Contexte projet** : sylius-b2b-demo, phase Conception

## Contexte

Le visiteur doit pouvoir endosser chaque rôle en un clic, sans porte dérobée d'authentification.

## Décision

Le bandeau mène à `/login?demo=<persona>` (persona en liste blanche). Un contrôleur Stimulus pré-remplit le formulaire de connexion standard avec les identifiants publics de la démo, et l'utilisateur valide. L'authentification Sylius normale est conservée (limiteur, CSRF).

## Alternatives considérées

Route « se connecter en tant que » côté serveur : contourne l'authentification et devient une porte dérobée si elle est mal protégée.

## Conséquences

Un clic de plus (« Se connecter »), mais aucune surface d'authentification nouvelle.
