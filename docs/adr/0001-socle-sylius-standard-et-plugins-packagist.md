# ADR-0001 — Socle Sylius-Standard 2.3 et plugins installés depuis Packagist

- **Statut** : Accepté (2026-10-15)
- **Contexte projet** : sylius-b2b-demo, phase Conception

## Contexte

La démo doit montrer l'installation réelle des plugins, comme le ferait un intégrateur, et rester à jour des versions publiées.

## Décision

Le projet part de `sylius/sylius-standard` ~2.3. Les 21 plugins (et les nouveaux) y sont requis en **versions taguées depuis Packagist**. Les plugins de paiement en ligne de Sylius-Standard (Stripe, Adyen, Mollie, PayPal) sont retirés ; refund-plugin est conservé sous réserve de compatibilité.

## Alternatives considérées

Réutiliser une TestApplication de plugin : couplée aux tests, non représentative. Chemins locaux (`path` repositories) : pratiques en dev, mais ne prouvent pas l'installation publique.

## Conséquences

La démo valide l'installation publique de chaque version. Une correction de plugin passe par une release avant d'arriver dans la démo. agentic-feeds doit d'abord être publié sur Packagist.
