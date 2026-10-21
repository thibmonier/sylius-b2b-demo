# Mesure de la communication (US-117)

But : savoir quels contenus LinkedIn amènent des visites, des parcours lancés et des clics vers le contact, sans cookie ni donnée personnelle. Outil : Umami (US-143). Valeurs vides par défaut : sans `DEMO_UMAMI_SCRIPT_URL` ni `DEMO_UMAMI_WEBSITE_ID` valides, aucun script n'est rendu et aucune requête externe n'est émise.

## Ce qu'Umami enregistre nativement (spike T-117-01)

- **UTM** : Umami lit `utm_source`, `utm_medium`, `utm_campaign`, `utm_content` et `utm_term` dans l'URL de la page envoyée par le script, et les ventile dans son rapport « UTM » (depuis la v2.11). Le code de la démo n'a donc rien à faire pour compter une visite par campagne.
- **Référent** : `document.referrer`, normalisé de la même façon que l'URL.
- **Événements** : les attributs `data-umami-event` et `data-umami-event-<propriété>` (US-143).
- **Piège corrigé** : le script ajoutait `data-exclude-search="true"`. Le script d'Umami vide alors `location.search` avant d'envoyer l'URL (vérifié dans `script.js` : `N&&(e.search="")`) : **toute la chaîne de requête, UTM comprises, était perdue**. L'attribut est retiré. Les pages mesurées étant la liste blanche des pages publiques (`UmamiTracker::MEASURED_PAGES`), aucune URL privée ou à jeton n'est concernée.
- **Plan gratuit** : les limites (nombre d'événements par mois, durée de conservation) sont celles du plan du compte de Thibaut, à relever dans l'interface Umami ; rien dans le code ne dépend d'un plan.

## Ce que le code ajoute

- La propriété `campaign` sur les événements de **lancement de parcours** (`demo-journey-launch`), de **sortie vers « Intégrer »** et de **contact** (`demo-cta-message`, `demo-cta-rendez-vous`).
- Origine de la valeur : le `utm_campaign` de l'URL courante, ou celui de l'arrivée sur `/{locale}/guides…` conservé en session (`CampaignContext`). Le stockage en session ne se fait que sur les pages des parcours (un visiteur qui n'ouvre jamais de parcours n'a pas de session pour cela) ; ensuite, la valeur retenue marque les lancements et les clics de contact des pages de la même session.
- **Nettoyage** (`CampaignLabel`, même règle que les propriétés d'`UmamiEvent`) : minuscules, chiffres, `_` et `-`, 40 caractères au plus. Toute autre valeur (trop longue, adresse e-mail, balise, tableau) est ignorée et rien n'est stocké.

## Lire le tableau Umami

1. **Visites par campagne** : rapport « UTM » (ou Filtres > Campagne UTM), période choisie.
2. **Parcours lancés** : Événements > `demo-journey-launch`, propriété `campaign` ; `demo-journey-start` compte les ouvertures de parcours.
3. **Clics vers le contact** : Événements > `demo-cta-message` (et `demo-cta-rendez-vous`), propriétés `emplacement` et `campaign`.

Un lien de test : `https://<démo>/fr_FR/?utm_source=linkedin&utm_campaign=ouverture-demo` (T-117-05, à ouvrir par Thibaut).

## Anomalie « 0 visite »

- Le script n'est pas rendu : `DEMO_UMAMI_SCRIPT_URL` ou `DEMO_UMAMI_WEBSITE_ID` vide ou invalide (https exigé, identifiant au format UUID) ; page hors liste blanche.
- Le navigateur envoie Do Not Track (`data-do-not-track="true"` : la visite n'est pas comptée).
- Un bloqueur de contenu retire le script ; la CSP n'autorise que l'hôte Umami configuré (`DEMO_UMAMI_COLLECT_URL` si la collecte se fait ailleurs).
- UTM absentes alors que la visite est comptée : l'URL a perdu sa chaîne de requête (raccourcisseur, redirection) ; vérifier le lien tel qu'ouvert.
