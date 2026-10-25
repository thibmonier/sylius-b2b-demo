# Tâches : US-201 : Tableau de bord des achats plus lisible, dans la continuité de la home

## Informations US

- **Epic** : EPIC-12 (lien : EPIC-15)
- **Persona** : Isabelle (administratrice d'Acme Industries) ; Camille (recruteuse IT)
- **Story Points** : 5 (Should, **sacrifice : n°2**)
- **Sprint** : sprint-30-compte-tableau-de-bord-et-tva
- **Worker proposé** : w201 (orchestrateur pour la maquette)
- **Story** : [US-201](../../../backlog/user-stories/US-201.md)

## Résumé de la US

**En tant qu'** administratrice d'entreprise  
**Je veux** un tableau de bord des achats qui fait ressortir l'essentiel en quelques secondes  
**Afin de** savoir combien ma société dépense, comment cela évolue et où agir

## Règles et contraintes

- **Surcharge dans la démo** (décision de Thibaut du 2026-10-25) : hooks Twig et styles du thème B2B ; si aucun hook ne suffit, surcharge de gabarit dans `templates/bundles/` du seul gabarit concerné. **Aucune modification, aucun tag ni release du plugin company-insights** ; `composer.lock` inchangé pour ce plugin.
- **Maquette d'abord** : maquette Claude Design (bureau et téléphone) dans une nouvelle page « Tableau de bord des achats » du canevas unique, **validée par Thibaut (T-201-04) avant toute intégration Twig**. Les tâches de code T-201-05 à T-201-11 sont 📅 tant que son GO n'est pas donné et noté au registre. La maquette réserve la place d'US-154 (conversion des devis, économies des paliers), hors périmètre.
- **Données réelles** : l'historique d'Acme Industries couvre 18 mois (US-155) ; la maquette et les tests utilisent ces données (comparaison N-1 possible).
- Graphique mensuel : comparaison N-1 qui ne repose pas sur la seule couleur, valeurs accessibles au clavier et au lecteur d'écran (table de données en repli si le graphique du plugin ne le permet pas).
- Reprise d'US-186 et d'US-189 : jetons de `b2b.scss`, polices auto-hébergées, AAA, cibles de 44 px, `prefers-reduced-motion`, 360 et 1280 px sans défilement horizontal (tableaux larges dans une boîte qui défile).
- Accès : inchangé (Isabelle oui, Claire refusée comme avant) ; l'export CSV et les pages « voir tout » gardent leur comportement.
- Fichiers communs (règle 21) : hooks dans `config/packages/demo_theme_b2b_insights.yaml` (fichier propre à la story), styles dans `assets/shop/styles/_insights.scss` (une ligne d'import dans `b2b.scss`), domaine de traduction `insights` ; `docs/theme-b2b.md` : seulement la section « Tableau de bord des achats ».
- Ton neutre, aucune comparaison avec Sylius Plus ; aucun identifiant saisi en production.
- Tests uniquement par `WORKER_ID=w201 TEST_DB_NAME=b2b_w201 TEST_CACHE_DIR=var/cache-w201 bin/run-tests`, dans l'arbre partagé (règle 22) ; preuve : `bin/proof-rebase`.

## Lieu de vérification

**Local et image reconstruite** (Isabelle, période par défaut, autre période, période sans commande ; Claire refusée) : pa11y WCAG2AAA FR et EN, 360 et 1280 px. **Production** : sans objet pour la page connectée ; relevé que l'adresse renvoie vers la connexion.

## Fichiers principaux touchés

- `templates/shop/theme_b2b/insights/` (nouveau) et, si nécessaire, `templates/bundles/` (gabarit du plugin surchargé, un seul à la fois, justifié dans le cadrage)
- `config/packages/demo_theme_b2b_insights.yaml` (nouveau), `assets/shop/styles/_insights.scss` (nouveau), `translations/insights.{fr,en}.yaml`
- `tests/Functional/Demo/` (nouveaux fichiers ; tests existants du tableau de bord relus)
- `config/demo/proof.json` (par `bin/proof-rebase`), `docs/theme-b2b.md` (section du tableau de bord), `docs/images/insights-*`

## Vue d'ensemble des tâches

| ID | Type | Tâche | Estimation | Dépend de | Réalisé par | Statut |
|----|------|-------|------------|-----------|-------------|--------|
| T-201-01 | [DOC] | Cadrage : gabarits et hooks du plugin relus contre le code (`dashboard`, `widgets`, `monthly`, `rankings`, `variation`, `sorts`, `breakdown`, widgets approbations et crédit, export), ce qui se surcharge par hook et ce qui demande `templates/bundles/` ; données d'Acme sur 18 mois ; droits (Isabelle, Claire) ; tests existants ; plan des tests | 0,75 h | - | Claude | 🔲 |
| T-201-02 | [TEST] | État de référence « avant » : tests du tableau de bord verts sur `main` ; captures connectées locales (bureau et 360 px) d'Isabelle (période par défaut, période sans commande) | 0,5 h | T-201-01, T-TECH-30-01 | Claude | 🔲 |
| T-201-03 | [FE-WEB] | Maquette Claude Design (bureau 1440 px et téléphone 390 px), page « Tableau de bord des achats » du canevas : rangée d'indicateurs (valeur, évolution, période de comparaison), graphique mensuel avec N-1 distinct sans la seule couleur, tops compacts avec parts, sélecteur de période, états « aucune donnée » et « période partielle », place réservée à US-154 ; vraies données ; table « Acquis » mise à jour | 2 h | T-201-01, T-TECH-30-01 | Orchestrateur | 🔲 |
| T-201-04 | [OPS] | 📅 Thibaut valide la maquette (ou demande des retouches) : **aucun gabarit Twig avant son GO**, noté au registre des décisions | 0,5 h | T-201-03 | Thibaut | 📅 |
| T-201-05 | [TEST] | 📅 Tests rouges, commités seuls et cités dans la PR : indicateurs en premier avec période de comparaison, graphique annonçant chaque mois et N-1, changement de période qui met tout à jour, Claire refusée, état vide, 360 px, charte des textes, plugin non modifié (`composer.lock`) | 1 h | T-201-04 | Claude | 📅 |
| T-201-06 | [FE-WEB] | 📅 Surcharge dans la démo : fichier de hooks propre, gabarits, partial `_insights.scss`, graphique accessible (ou table de repli), tops compacts, AAA, `prefers-reduced-motion` ; canal B2B seulement | 3 h | T-201-05 | Claude | 📅 |
| T-201-07 | [DOC] | 📅 Textes FR et EN (domaine `insights`), charte `PublicTextCharter` ; texte relu contre la maquette validée | 0,5 h | T-201-06 | Claude | 📅 |
| T-201-08 | [TEST] | 📅 Suite ciblée par `bin/run-tests` (tableau de bord, classements, export, droits), PHPStan max sur tout le dépôt, ECS, Rector, lint Twig et YAML, pa11y local FR et EN, 360 et 1280 px ; commande exacte et dernière ligne citées | 0,75 h | T-201-06, T-201-07 | Claude | 📅 |
| T-201-09 | [DOC] | 📅 Captures `docs/images/insights-*` par `bin/render-page` (Isabelle, local, bureau et 360 px), section du tableau de bord dans `docs/theme-b2b.md` | 0,5 h | T-201-08 | Claude | 📅 |
| T-201-10 | [TEST] | 📅 Image reconstruite et conteneur neuf : tableau de bord d'Isabelle vérifié (règle 16 : rien hors base) ; production après le déploiement, lecture seule : l'adresse renvoie vers la connexion ; mesures consignées le jour du déploiement | 0,5 h | merge de la PR | Claude | 📅 |
| T-201-11 | [REV] | 📅 Revue (ton neutre, parité FR et EN, plugin non modifié) et table « scénario → test ou preuve » remplie | 0,5 h | T-201-10 | Claude | 📅 |

**Total estimé : 10,5 h** (11 tâches ; 10 h pour Claude et l'orchestrateur, 0,5 h de Thibaut).

## Table « scénario → test ou preuve »

| Scénario | Tâche | Test ou preuve |
|----------|-------|----------------|
| L'essentiel en premier (nominal) | T-201-05, T-201-09 | |
| Graphique mensuel accessible (alternatif) | T-201-05, T-201-08 | |
| Changement de période (alternatif) | T-201-05 | |
| Accès refusé (erreur) | T-201-05 | |
| Aucune donnée sur la période (erreur) | T-201-05 | |
| Débordement sur téléphone (erreur) | T-201-05, T-201-08 | |
| Régression d'accessibilité (erreur) | T-201-08 | |
