# Licence du plugin MCP tiers (US-083)

Note de décision du 2026-10-18. Ce n'est **pas un avis juridique** : elle rapporte ce que disent les fichiers et les pages publiques, et pose la question à trancher par Thibaut.

Paquet concerné : `sylius/mcp-server-plugin` v0.2.0 (éditeur : Sylius Sp. z o.o.), exigé par `composer.json` (`^0.2`), installé depuis `github.com/Sylius/McpServerPlugin`.

## Ce que disent les sources

| Source | Contenu |
|--------|---------|
| `composer.json` du paquet (et métadonnées Packagist) | `"license": "proprietary"` |
| Fichier `LICENSE` du paquet (ajouté au dépôt le 2026-02-23, commit « Add License ») | Texte de la licence MIT : « Copyright (c) 2018-present Sylius Sp. z o.o. — Permission is hereby granted, free of charge, to any person obtaining a copy of this software […] to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software […] The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software. » |
| `README.md` du paquet | « This plugin's source code is completely free and released under the terms of the MIT license. » |
| Dépôt GitHub public | Dépôt public, non archivé ; GitHub détecte la licence MIT à partir du fichier `LICENSE` |
| `LICENSE_OF_TRADEMARK_AND_LOGO` du paquet | Politique de marque : l'usage du nom « Sylius » pour un produit destiné à Sylius est admis tant qu'il n'implique ni approbation ni affiliation |

## Lecture

- **Incohérence côté éditeur** : le champ de métadonnées `proprietary` contredit le fichier `LICENSE` et le README, tous deux MIT. Les métadonnées n'ont pas été alignées depuis l'ajout du fichier de licence (le dernier changement de `composer.json` date de septembre 2026 et garde `proprietary`).
- **Si la licence applicable est la MIT** (fichier et README) : usage dans une démo publique, présence dans le dépôt et dans l'image, captures et vidéos sont permis ; la seule condition est de conserver l'avis de copyright et de licence dans les copies. Ici, le plugin n'est pas copié dans le dépôt (il est installé par Composer) et l'image qui l'embarque contient son `LICENSE` dans `vendor/`.
- **Si la licence applicable est « proprietary »** (métadonnées) : aucun texte de licence propriétaire n'est publié avec le paquet ; les droits de redistribution, d'usage public et de communication ne sont alors pas établis.
- Le dépôt de la démo ne contient pas le code du plugin (`vendor/` est ignoré). L'image n'est pas publiée dans un registre public : elle est construite et exécutée sur Railway. La redistribution du code n'a donc pas lieu aujourd'hui ; ce qui est public, c'est le service en marche et les références Composer.

| Usage | Statut |
|-------|--------|
| Démo publique qui exécute le plugin | Permis selon `LICENSE` / README (MIT) ; incertain selon `proprietary` |
| Dépôt public qui le référence par Composer, sans copier son code | Pas de redistribution ; aucun obstacle identifié |
| Image publiée dans un registre public | Non fait ; serait de la redistribution (MIT : avis conservé) |
| Captures et vidéos de la page MCP | Permis selon la MIT ; la politique de marque demande de ne pas suggérer d'approbation par Sylius |

## Question à trancher par Thibaut

La MIT du fichier `LICENSE` et du README suffit-elle comme base d'usage, malgré la mention `proprietary` du `composer.json` ? Actions possibles :

1. **Demander confirmation à l'éditeur** (Sylius, p. ex. via Przemek ou une issue sur le dépôt) en citant l'incohérence ; en attendant sa réponse, ne pas publier d'image du plugin dans un registre public. Recommandée, coût faible.
2. **S'appuyer sur la MIT** du fichier `LICENSE` et consigner cette décision ici, avec la date de la lecture.
3. **Retirer le plugin de la démo publique** (page MCP comprise) tant que le doute subsiste.

## Décision (2026-10-18)

Thibaut retient la licence MIT du fichier `LICENSE` comme applicable. Motif : `LICENSE` et README en MIT, métadonnées Composer et Packagist (`proprietary`) incohérentes avec eux, plugin non redistribué dans le dépôt (installé par Composer). Aucun contact avec Sylius pour l'instant. Condition à respecter : conserver l'avis de copyright et de licence dans les copies (le `LICENSE` reste dans `vendor/`).
