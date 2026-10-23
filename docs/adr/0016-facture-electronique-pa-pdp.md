# ADR-0016 — Facture électronique France : plateformes agréées (PA) et e-reporting

- **Statut** : Proposé (2026-10-23), décision de Thibaut attendue
- **Contexte projet** : sylius-b2b-demo, sprint 24, spike US-132 (borné à 4 h)

> Ce document est un cadrage technique. Il ne constitue pas un avis juridique ni fiscal : les obligations d'une entreprise donnée se vérifient auprès de l'administration ou d'un conseil.

## Contexte

Le plugin `facturx` produit des factures Factur-X (PDF/A-3 avec XML EN 16931). En France, émettre une facture électronique B2B passe par une plateforme agréée (PA), et les opérations hors facturation B2B domestique relèvent de l'e-reporting. US-132 demande de cadrer ces deux flux, sans accès à une plateforme réelle.

### Méthode et datation

Chaque affirmation cite une source et sa date de consultation (toutes le **2026-10-23**). Ce qui n'est pas confirmé par un texte ou une page officielle est marqué **à vérifier**.

Limites rencontrées pendant le spike :

- `impots.gouv.fr` affichait une page de maintenance (documentation indisponible) : ni le guide pratique, ni les fiches de la réforme, ni les spécifications externes n'ont pu être lues directement.
- `economie.gouv.fr` a répondu 403 aux deux pages visées.
- Aucun autre chemin n'a été utilisé pour contourner ces échecs. Les points qui reposent sur des résultats de recherche seulement (et non sur une page lue) sont marqués **à vérifier**.

## Calendrier

| Échéance | Contenu | Source (consultée le 2026-10-23) | Statut |
|----------|---------|----------------------------------|--------|
| 2026-09-01 | Réception des factures électroniques pour toutes les entreprises ; émission pour les grandes entreprises et les ETI | [Décret n° 2024-266 du 25 mars 2024, art. 2 (Légifrance)](https://www.legifrance.gouv.fr/jorf/id/JORFTEXT000049328045) pour la date d'application ; [AIFE, facturation électronique B2B](https://aife.economie.gouv.fr/nos-applications/facturation-electronique-b2b/) pour la répartition réception / émission | Confirmé par deux sources officielles |
| 2027-09-01 | Émission pour les microentreprises et les PME | Décret n° 2024-266, art. 2 : « ne s'appliquent qu'à compter du 1er septembre 2027 » pour les microentreprises et PME du III.A de l'article 26 de la loi n° 2022-1157 ; AIFE (idem) | Confirmé |
| 2026-09-01 (opérations des art. 290 et 290 A CGI) | Même date, appliquée aux factures émises ou, à défaut, aux opérations réalisées ; même report à 2027 pour les mêmes catégories | Décret n° 2024-266, art. 2 | Confirmé pour la date ; le périmètre exact de l'e-reporting par catégorie d'entreprise est **à vérifier** |
| Report possible à une date ultérieure, au plus tard le 2026-12-01 | Une lecture de Légifrance rapportée par la recherche web mentionne qu'un décret pourrait fixer une date ultérieure | Résultat de recherche sur l'article 91 de la loi n° 2023-1322, texte non lu | **À vérifier** : aucune source lue ne montre qu'un tel décret ait été pris |

### Écart entre sources

Le décret n° 2024-266 indique lui-même tenir compte d'une **modification du calendrier** par le II de l'article 91 de la loi n° 2023-1322 de finances pour 2024, qui modifie le III de l'article 26 de la loi n° 2022-1157. Le calendrier initial de 2022 est donc **remplacé** : cet ADR retient les dates du décret de 2024 et de la page AIFE, plus récentes. Les contenus de blogs et de notices d'entreprises antérieurs à 2024 ne sont pas utilisés.

La page AIFE donne un démarrage du pilote national en mars 2026 alors qu'une citation de la même page mentionne février 2026 : écart interne à la source, sans effet sur la décision, signalé pour mémoire.

La page de l'AIFE n'affiche pas de date de mise à jour (chiffres les plus récents : 31 décembre 2025). Sa fraîcheur est **à vérifier** avant toute communication.

## Plateformes agréées, PPF et annuaire

D'après [AIFE](https://aife.economie.gouv.fr/nos-applications/facturation-electronique-b2b/) (consultée le 2026-10-23) :

- Le portail public de facturation (PPF) est recentré sur l'**annuaire** (routage des factures) et un **concentrateur** qui transmet les données de facturation et de transaction à l'administration fiscale.
- Chaque plateforme agréée gère les données de ses clients et indique leurs adresses de facturation dans l'annuaire.
- 136 plateformes sont listées (108 immatriculées définitivement, 28 sous réserve) ; chiffre daté de la page, **à vérifier** car sans date de mise à jour.

Le décret n° 2024-266 n'emploie pas « agréées » mais « opérateurs de plateforme de dématérialisation partenaires » et décrit leur immatriculation (tests d'interopérabilité sous trois mois, validation sous deux mois). La correspondance de vocabulaire entre « plateforme agréée » et « plateforme de dématérialisation partenaire » est **à vérifier** sur un texte récent.

## Formats

- Les formats et profils des factures et des statuts de cycle de vie sont définis par la norme AFNOR **XP Z12-012**. D'après la page [FNFE-MPE, ressources](https://fnfe-mpe.org/ressources/) (consultée le 2026-10-23), les versions 1.4.0 des normes XP Z12-012, -013 et -014 sont datées du 30 juin 2026, et des schematrons et XSD Factur-X existent pour les profils EN16931 et EXTENDED-CTC-FR.
- Le profil EXTENDED-CTC-FR est présenté par cette source comme un profil de la réforme française. Les formats UBL et CII sont à confirmer dans la norme : **à vérifier** (norme non lue, site `impots.gouv.fr` indisponible).
- Statuts de cycle de vie : un résultat de recherche attribue à une fiche de la DGFiP un minimum de quatre statuts (dépôt, rejet, refus, encaissée). **À vérifier** : la fiche n'a pas pu être lue.

## Flux de facturation et flux d'e-reporting

| | Facturation électronique | E-reporting |
|---|---|---|
| Nature | Facture structurée émise et reçue via une PA | Transmission de données de transaction et de paiement à l'administration |
| Opérations visées | Ventes B2B entre assujettis établis en France (art. 289 bis CGI) | Opérations des art. 290 et 290 A CGI, qui ne sont pas des factures au sens de l'art. 289 bis ; B2C et international selon le périmètre officiel : **à vérifier** |
| Canal | Émetteur, sa PA, la PA du destinataire (adresse lue dans l'annuaire) | Via la PA de l'assujetti ou le PPF : **à vérifier** |
| Sources | Décret n° 2024-266, AIFE | Décret n° 2024-266 (renvoie aux art. 290 et 290 A, ne détaille pas le contenu des données) |

Le contenu des données d'e-reporting et les statuts de retour ne sont pas confirmés par une source lue : à approfondir dans les spécifications externes de l'administration quand le site sera de nouveau accessible.

## Écart avec le plugin `facturx`

Lu dans `sylius-facturx-plugin` (README, `composer.json`, `src/`, version 1.1.0, 2026-10-23).

| Besoin | Couvert par `facturx` | Manque |
|--------|----------------------|--------|
| Génération du PDF/A-3 avec XML EN 16931 | Oui (`FacturXGenerator`, `CiiXmlWriter`, `FacturXPdfEmbedder`) | - |
| Données légales du vendeur (SIREN, SIRET, TVA, adresse électronique) par canal | Oui (`ChannelSettings`) | - |
| Identifiants B2B de l'acheteur | Oui, saisis par un administrateur (`CustomerIdentifiers`) | Capture au checkout |
| Point d'accroche vers un connecteur | Oui : événement `tmonier_facturx.invoice_generated` (XML + PDF), émis à chaque rendu, y compris re-rendu | Idempotence et suivi à la charge du connecteur |
| Profil EXTENDED-CTC-FR | Non : profil EN 16931 uniquement | Selon la décision sur les formats exigés par les PA (à vérifier) |
| Transmission à une PA | Non (limite déclarée dans le README) | Connecteur, authentification, reprise sur erreur |
| Consultation de l'annuaire | Non | Résolution de l'adresse du destinataire |
| Statuts de cycle de vie reçus | Non | Modèle de statuts, historique, affichage admin |
| E-reporting | Non (limite déclarée) | Constitution et envoi des données |
| Avoirs | Non (le plugin Invoicing n'en a pas) | À vérifier côté PA |

Conclusion : `facturx` couvre la génération du document ; la transmission, les statuts et l'e-reporting forment un plugin ou module distinct, branché sur l'événement existant.

## Démonstration sans plateforme réelle

Aucun accès à une plateforme réelle. La démonstration vise une **plateforme fictive**, nommée « PA fictive de démonstration » à l'écran, dans la documentation et dans le code, sans lien avec une offre existante.

1. Commande de démonstration payée, facture Sylius générée.
2. `facturx` produit le Factur-X ; l'événement `invoice_generated` déclenche le connecteur.
3. Le connecteur dépose le XML et le PDF auprès d'une API de la PA fictive (conteneur ou contrôleur de la démo), avec idempotence par identifiant de facture.
4. La PA fictive répond avec des statuts simulés selon un scénario fixé par les données de démonstration : déposée, rejetée (motif), refusée, encaissée.
5. L'admin affiche les statuts de la facture ; un rapport d'e-reporting fictif liste les opérations hors facturation B2B.
6. Un bandeau précise que les statuts sont simulés et que la démonstration n'a aucune valeur réglementaire.

## Décision proposée

1. Ne pas intégrer de connecteur vers une PA réelle dans la démo. Réaliser un **module de transmission** (nom provisoire `einvoice-transmission`) avec une interface de port `InvoiceTransmitterInterface`, un adaptateur **PA fictive** pour la démo, et un point d'extension pour d'autres adaptateurs.
2. Suivre les statuts de cycle de vie dans une table du module, alimentée par l'adaptateur.
3. Traiter l'e-reporting dans une story séparée, une fois le périmètre confirmé par une source officielle lue.
4. Garder `facturx` centré sur la génération : seules des évolutions de format (par exemple EXTENDED-CTC-FR) y entrent, après confirmation.
5. Aucun nouveau dépôt, paquet ni publication sans GO de Thibaut.

## Découpage en stories (8 points au plus)

| Story provisoire | Contenu | Points |
|------------------|---------|--------|
| A | Port de transmission, journal des envois et statuts, écouteur de `invoice_generated` idempotent | 5 |
| B | PA fictive de démonstration (API fictive, scénarios de statuts simulés), bandeau explicatif | 5 |
| C | Affichage admin des statuts et relance d'un envoi en erreur | 3 |
| D | Rapport d'e-reporting fictif (après confirmation du périmètre officiel) | 5 |
| E | Évolution de format de `facturx` si les spécifications exigent un autre profil (conditionnel) | 5 |

## Alternatives considérées

- **Connecteur vers une PA réelle** : exige un compte, des identifiants et un choix de fournisseur ; hors périmètre du spike, aucune accréditation.
- **Tout dans `facturx`** : mélange génération et transmission (SRP) et alourdit un plugin publié.
- **Ne rien faire** : le plugin documente déjà l'absence de transmission, mais la démo ne montrerait pas le flux complet.

## Conséquences

Un module de plus à maintenir si la décision est acceptée. Les stories A à C livrent une démonstration de bout en bout ; D et E dépendent de sources encore à lire.

## Questions ouvertes

1. Quel périmètre d'e-reporting (B2C, international) est confirmé par les spécifications externes et le guide pratique, une fois `impots.gouv.fr` de nouveau accessible ?
2. Le profil EN 16931 suffit-il aux PA, ou le profil EXTENDED-CTC-FR est-il attendu ?
3. Un décret a-t-il fixé une date ultérieure à celles retenues ? (aucune source lue ne le montre)
4. Statuts obligatoires : quatre ou davantage, et lesquels sont transmis à l'administration ?
5. Le module doit-il être un nouveau plugin publié ou rester dans la démo ? (GO de Thibaut requis pour toute création de dépôt)
6. Faut-il capturer SIREN et adresse électronique de l'acheteur dès le checkout ?

## Sources

| Source | Consultée le | Usage |
|--------|--------------|-------|
| [Décret n° 2024-266 du 25 mars 2024, Légifrance](https://www.legifrance.gouv.fr/jorf/id/JORFTEXT000049328045) | 2026-10-23 | Dates, report 2027, immatriculation, art. 290 et 290 A |
| [AIFE, facturation électronique B2B](https://aife.economie.gouv.fr/nos-applications/facturation-electronique-b2b/) | 2026-10-23 | Calendrier, PPF, annuaire, nombre de plateformes |
| [FNFE-MPE, ressources](https://fnfe-mpe.org/ressources/) | 2026-10-23 | Normes XP Z12-012 v1.4.0, schematrons Factur-X |
| Dépôt `sylius-facturx-plugin` (lecture seule) | 2026-10-23 | Couverture du plugin |
