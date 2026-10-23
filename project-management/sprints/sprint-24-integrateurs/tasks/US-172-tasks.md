# Tâches : US-172 : Guide de surcharge des plugins

## Informations US

- **Epic** : EPIC-12
- **Persona** : lead développeur d'une agence
- **Story Points** : 3 (Should, non sacrifiable)
- **Sprint** : sprint-24-integrateurs
- **Story** : [US-172](../../../backlog/user-stories/US-172.md)

## Résumé de la US

**En tant que** développeur d'une agence
**Je veux** savoir comment adapter un plugin sans le modifier
**Afin de** l'intégrer à un projet client et de garder les mises à jour possibles

## Règles et contraintes

- Quatre cas, chacun avec **un exemple réel pris dans la démo** (fichier et lignes) et une commande de vérification : template (Twig hooks de Sylius 2, surcharge de gabarit de bundle), formulaire (extension de type), entité (modèle Sylius surchargé, `resolve_target_entities`), service (décoration, liaisons typées comme les limiteurs de débit d'US-148).
- Exemples candidats relevés au 2026-10-23 : hooks dans `config/packages/tmonier_plugins_overrides.yaml`, gabarits dans `templates/bundles/`, `src/Form/Extension/OrderPoReferenceTypeExtension.php`, modèles dans `src/Entity/`, décoration de `tmonier_facturx.xml.writer` dans `config/services.yaml`, limiteurs dans `config/packages/demo_hardening.yaml`. Le cadrage confirme chacun et fixe les lignes.
- Un test vérifie que tout fichier cité existe encore ; l'échec nomme le chemin manquant.
- Une copie de gabarit entier est présentée comme **dernier recours** : le guide propose d'abord un hook ou une extension.
- Page de la démo ou fichier de `docs/` : décidé au cadrage (T-172-01). Libellés FR et EN exigés par la fiche : la page est la forme attendue.
- Les README de plugins renvoient au guide : une PR par dépôt, sans tag, **après GO de Thibaut** (T-172-08). Aucun push avant.
- Ton neutre (`PublicTextCharter`), aucune comparaison avec une offre commerciale, aucun détail de sécurité.
- Règles du sprint, consigne de worker et actions de la rétro du sprint 23 : voir [README.md](./README.md).

## Lieu de vérification du rendu (action 4 de la rétro du sprint 23)

**Production, après déploiement, en lecture seule** (environ 6 Go libres, image non construite en local). La story reste en review jusqu'à la preuve : pa11y WCAG2AAA en FR et en EN, 360 px, extraits de code sans défilement horizontal de la page. Le déploiement est l'action de Thibaut T-TECH-24-09.

## Fichiers principaux touchés

À croiser avec la matrice des chevauchements de [README.md](./README.md).

- `src/Demo/Controller/OverrideGuideController.php` (nouveau), `templates/demo/override_guide.html.twig`, `translations/override_guide.fr.yaml` et `.en.yaml`, `config/packages/demo_override_guide.yaml` (registre des exemples)
- `templates/demo/integrate.html.twig` (lien depuis la page Intégrer)
- `tests/Functional/Demo/OverrideGuidePageTest.php`, `tests/Unit/Demo/OverrideGuideExamplesTest.php` (nouveaux)
- README des plugins cités par les exemples (liste fixée au cadrage, six dépôts au plus), en lecture puis PR après GO
- `config/demo/proof.json` (rafraîchi : nouveaux fichiers de tests)

## Vue d'ensemble des tâches

La colonne « Réalisé par » dit qui fait la tâche : **Claude**, **Orchestrateur** (Claude en rôle d'orchestrateur) ou **Thibaut**. Ordre TDD : les tâches [TEST] rouges précèdent le code et sont commitées seules.

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-172-01 | [DOC] | Cadrage : les quatre exemples réels confirmés (fichier, lignes, commande de vérification essayée : `debug:container`, `debug:form`, `debug:config`, …), page ou `docs/`, registre unique des exemples (source de la page et du test), liste des README de plugins à renvoyer | 1 h | - | Claude | 🔲 |
| T-172-02 | [TEST] | Tests rouges, commités seuls et cités dans la PR : page FR et EN avec quatre cas (exemple et commande chacun), tout chemin cité existe (message avec le chemin manquant), copie de gabarit signalée comme dernier recours après le hook et l'extension, ton neutre, aucun détail de sécurité | 0,75 h | T-172-01 | Claude | 🔲 |
| T-172-03 | [BE] | Registre des exemples en configuration, route localisée et contrôleur | 0,5 h | T-172-02 | Claude | 🔲 |
| T-172-04 | [FE-WEB] | Gabarit et textes FR et EN : quatre cas, extrait, lien vers le fichier exact, commande de vérification, encadré « dernier recours » ; lien depuis la page Intégrer sans doublon de contenu | 2 h | T-172-03 | Claude | 🔲 |
| T-172-05 | [TEST] | Suite ciblée via `bin/run-tests` (`WORKER_ID=w172 …`), ECS (`src tests`), PHPStan max, Rector ; `php bin/proof-refresh` puis `php bin/proof-check` | 0,75 h | T-172-04 | Claude | 🔲 |
| T-172-06 | [TEST] | Rendu en production après déploiement : pa11y WCAG2AAA en FR et en EN, 360 px ; story en review jusque-là | 0,75 h | T-172-05, T-TECH-24-09 | Claude | 🔲 |
| T-172-07 | [DOC] | Texte du lien vers le guide pour chaque README concerné (FR et EN selon le dépôt) et diff préparé en local, **sans push** | 1 h | T-172-04 | Claude | 🔲 |
| T-172-08 | [REV] | 📅 Thibaut relit les diffs des README et donne son GO pour les PR (une par dépôt, sans tag) | 0,25 h | T-172-07 | Thibaut | 📅 |
| T-172-09 | [OPS] | PR des README après GO, CI de chaque dépôt verte avant le merge | 0,5 h | T-172-08 | Orchestrateur | 🔲 |
| T-172-10 | [REV] | Revue (exemples réels et à jour, ton neutre, parité FR et EN) et table « scénario → test » remplie | 0,25 h | T-172-06, T-172-09 | Claude | 🔲 |

**Total estimé : 7,75 h** (10 tâches, dont 0,25 h de Thibaut ; 7,5 h pour Claude et l'orchestrateur).

## Scénarios et preuves (action 1 de la rétro du sprint 22)

À remplir avant le passage en done : chaque scénario renvoie à un test (classe et méthode) ou à une preuve (commande et sortie, document et section). Une ligne vide laisse la story en review.

| Scénario | Test ou preuve |
|----------|----------------|
| Quatre cas documentés (nominal) | |
| Exemple vérifiable (alternatif) | |
| Renvoi depuis un README de plugin (alternatif) | |
| Exemple devenu faux (erreur) | |
| Surcharge qui casse une mise à jour (erreur) | |
