# Gabarit de user story

> Structure d'une fiche `backlog/user-stories/US-XXX.md`. La rubrique **Prérequis d'environnement** est obligatoire pour toute story de captation ou d'intégration externe (US-161, action 5 de la rétrospective du sprint 17) ; elle peut valoir « aucun » pour les autres.

```markdown
# US-XXX : <titre>

> Epic : [EPIC-XX](../epics/EPIC-XX.md) · Persona : <P-XXX> · MoSCoW : **<Must|Should|Could>** · Points : **<n>** · Sprint : <cible>
> Dépendances : <US ou documents>

## Story

En tant que <persona>, je veux <capacité>, afin de <bénéfice>.

## Contexte

<faits, contraintes, décisions déjà prises>

### Validation INVEST

- [ ] Independent / Negotiable / Valuable / Estimable / Sized ≤ 8 / Testable

## Prérequis d'environnement

<!-- Obligatoire pour une story de captation (vidéo, capture, démo en direct) ou d'intégration externe (API tierce, protocole, compte, domaine). Mettre « aucun » sinon. -->

| Prérequis | Exemple | Disponible ? | Responsable | Échéance |
|-----------|---------|--------------|-------------|----------|
| <HTTPS, compte, jeton, outil, accès réseau, autorisation> | <PunchOut exige HTTPS> | oui / non / à confirmer | <Thibaut / Claude> | <date> |

## Versions et publication

<!-- Obligatoire pour une story qui touche un plugin publié. Mettre « sans objet » sinon. -->

- **Versions supportées** : PHP, Symfony et Sylius de la matrice CI du dépôt, lue au cadrage (`.github/workflows/`) ; impact de version (patch, mineure, majeure) et ce qu'il casse chez les utilisateurs.
- **Ordre de publication** : dépôts dans l'ordre des dépendances (plugin dépendu tagué et visible sur Packagist avant son dépendant), puis montée de la démo ; tags après le GO de Thibaut.

## Critères d'acceptation

(Gherkin : au moins 1 nominal, 2 alternatifs, 2 erreurs.)

## Definition of Done

- [ ] <critères mesurables>
```

## Contrôle au planning

Au Sprint Planning, avant d'engager une story de captation ou d'intégration externe, le Scrum Master vérifie que la rubrique **Prérequis d'environnement** est remplie et qu'aucun prérequis n'est « non » sans échéance ni responsable. Sinon la story **n'est pas engagée** : elle retourne à l'affinage. Les prérequis des stories engagées sont recopiés dans le tableau du `README.md` des tâches du sprint (section « Prérequis d'environnement »).
