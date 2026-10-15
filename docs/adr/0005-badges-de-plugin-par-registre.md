# ADR-0005 — Badges de plugin générés à partir d'un registre

- **Statut** : Accepté (2026-10-15)
- **Contexte projet** : sylius-b2b-demo, phase Conception

## Contexte

Chaque fonctionnalité doit indiquer le plugin qui l'apporte (demande de l'utilisateur), sans modifier les plugins et sans imiter le marquage commercial de Sylius Plus.

## Décision

Un registre `config/demo/badges.yaml` associe un hook, un plugin et une priorité. Une extension DI de la démo le lit et prepend la configuration `sylius_twig_hooks` (un hookable par entrée), rendu par le composant `DemoPluginBadge` : icône neutre, infobulle, lien vers la page Plugins et GitHub, accessible au clavier, masquable par cookie.

## Alternatives considérées

Badges dans les plugins : pollue les plugins. Surcharges de templates : fragiles.

## Conséquences

Ajouter un badge revient à éditer une ligne de YAML. Les badges suivent les hooks réels des plugins (testés en fumée).
