# Dépendances — Sprint 010 (Vitrine et reports)

## Entre stories et avec les sprints précédents

```mermaid
graph TD
    S8["Sprint 8 (terminé): M5 garde sortant, US-040 M4 purge des images"]
    S9["Sprint 9: US-044 container-fill, US-006 ADR-0013, T-041-08/09"]
    US004["US-004 (livrée)"]
    US008["US-008 (livrée)"]
    US014["US-014 page Plugins (livrée)"]
    US029["US-029 Récepteur de webhooks (15.5h)"]
    US046["US-046 Encart Sylius Plus (5.5h)"]
    US047["US-047 Images des produits (22.5h)"]
    US048["US-048 Reports techniques (19h)"]
    US004 --> US029
    S8 --> US029
    S9 -.->|"threads bornés"| US029
    US014 --> US046
    US008 --> US047
    S8 --> US047
    S9 --> US048
    US047 -.->|"profilage avant/après images"| US048
```

## Entre tâches (vue transversale)

```mermaid
graph TD
    T02901["T-029-01"] --> T02902["T-029-02"] --> T02905["T-029-05"]
    T02901 --> T02904["T-029-04"] --> T02905
    T02903["T-029-03"] --> T02905
    T02905 --> T02906["T-029-06"]
    T02905 --> T02907["T-029-07 revue"]
    T04601["T-046-01"] --> T04602["T-046-02"] --> T04603["T-046-03"] --> T04605["T-046-05"]
    T04602 --> T04604["T-046-04 validation utilisateur"]
    T04701["T-047-01"] --> T04702["T-047-02"] --> T04703["T-047-03"]
    T04702 --> T04704["T-047-04 lancement unique"]
    T04704 --> T04705["T-047-05"]
    T04704 --> T04706["T-047-06"] --> T04707["T-047-07"] --> T04708["T-047-08"]
    T04706 --> T04708
    T04704 --> T04709["T-047-09"]
    T04708 --> T04710["T-047-10"]
    T04708 --> T04711["T-047-11 revue"]
    T04802["T-048-02"] --> T04803["T-048-03"] --> T04814["T-048-14 revue"]
    T04804["T-048-04 profilage"] --> T04805["T-048-05"]
    T04804 --> T04806["T-048-06"]
    T04804 --> T04813["T-048-13 mémoire Railway"]
    T04804 --> T04814
    T04708 -.->|"base de comparaison"| T04804
```

## Chemin critique

US-047 : T-047-01 → T-047-02 → T-047-04 → T-047-06 → T-047-07 → T-047-08 → T-047-11 (≈ 17,5 h). US-029 : T-029-01 → T-029-02 → T-029-05 → T-029-07 (≈ 10 h). US-046 est bloquée avant merge par T-046-04 (validation de l'utilisateur). US-048 est largement indépendante : T-048-01 à T-048-04 et les mineurs T-048-07 à T-048-10 se parallélisent.

## Ressources partagées

- Disque local ~3 Go : téléchargements bruts d'US-047 en répertoire temporaire hors dépôt, un seul lancement du script, aucun rebuild Docker local.
- `src/Demo/Http/DemoGateListener.php` : modifié par US-029 seulement.
- Fixtures du reset (`src/DataFixtures/`, importeur CSV) : US-047 (T-047-06) et US-048 (T-048-05, T-048-06, T-048-08, T-048-09) modifient des fichiers voisins ; sérialiser les fusions et mesurer le reset après chaque changement.
- Page Plugins et traductions : US-046 seulement. Page À propos : T-047-09 seulement.
- Accès Railway et GO utilisateur : T-047-04 (lancement), T-048-11 à T-048-13, T-046-04.
