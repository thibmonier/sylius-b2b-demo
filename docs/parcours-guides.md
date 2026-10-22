# Parcours guidés (US-105, US-106)

Cinq parcours de quelques minutes, un par rôle, sur `/{locale}/guides`. Chaque parcours emmène le visiteur d'écran en écran avec le compte de démo qui convient, peut préparer le scénario à sa place et se termine par un écran de sortie vers la page « Intégrer » et le contact.

## Principe

- **Rendu serveur**, une URL par étape : `/{locale}/guides` (liste), `/{locale}/guides/{parcours}/{étape}` (étape ; le numéro suivant la dernière étape est l'écran de sortie), `POST /{locale}/guides/{parcours}/{étape}/launch` (« Je lance ce scénario »). Aucun JavaScript n'est nécessaire.
- **Progression en session** (`JourneyProgress`) : dernière étape vue par parcours. La liste propose « Reprendre à l'étape N ».
- **Remise à zéro** : `ResetStatus::lastSuccess()` renvoie une empreinte propre à chaque reset réussi. Quand elle change, la progression est abandonnée : le visiteur est ramené à l'étape 1 avec un message annoncé une seule fois.
- **Mauvais compte** : si le visiteur n'est pas connecté avec le compte du parcours, l'étape affiche un message et un bouton de connexion en un clic (le formulaire standard de `demo/_login_form.html.twig`, sans contournement). Aucune action n'est proposée tant que le compte est le mauvais ; le lancement refuse aussi côté serveur.
- Réservé au mode démo (`demo.mode`), 404 sinon.

## Ajouter ou modifier un parcours

Tout est dans `config/demo/journeys.yaml` (paramètre `demo.journeys`) :

```yaml
demo.journeys:
    mon_parcours:                # identifiant : minuscules, chiffres, _ et -
        persona: buyer           # clé de traduction persona.<persona>
        account: claire@acme.example   # compte de démo conseillé (activé, présent dans accounts.yaml)
        minutes: 6
        steps:                   # 4 à 6 étapes
            - { route: tmonier_quick_order_shop_index }
            - { route: sylius_shop_cart_summary, scenario: cart, scenario_options: { list: Atelier } }
```

- `route` : nom d'une route existante ; `route_parameters` pour ses paramètres. `_locale` est ajouté automatiquement si le chemin le contient.
- `scenario` (facultatif) : `cart` ou `quote` ; `scenario_options.list` : nom de la liste d'achat de l'entreprise du compte.
- Les textes sont dans `translations/journeys.{fr,en}.yaml` : `journey.<id>.title`, `.goal`, `.step.<n>.title`, `.step.<n>.text`. Les deux langues sont obligatoires.
- Le catalogue est validé au premier usage : une route inconnue ou un compte absent ou désactivé est refusé avec un message qui nomme le parcours et l'étape. Le test `JourneyTest::testEveryStepOpensItsScreenWithTheAccountOfTheJourney` parcourt chaque étape, en FR et en EN, avec le bon compte.

## Préparateurs de scénario

Un préparateur implémente `App\Demo\Journey\ScenarioPreparer` et porte l'étiquette de service `app.demo.scenario_preparer` avec une clé (`#[AutoconfigureTag(ScenarioPreparer::TAG, ['key' => 'cart'])]`). `JourneyLauncher` les retrouve par cette clé.

| Clé | Effet |
|---|---|
| `cart` | vide le panier du visiteur puis y ajoute les lignes de la liste d'achat de son entreprise (rejouable) |
| `quote` | remplace le brouillon de demande de devis du plugin quote-request par les lignes de la liste |

Un préparateur n'agit que pour le visiteur connecté. Si rien n'est à préparer (pas d'entreprise, liste introuvable), il lève `ScenarioPreparationFailed` : la page affiche un message au lieu d'échouer.

**Effet sur les comptes partagés** : le scénario `cart` modifie le panier du compte de démo partagé ; le reset de 03:00 le restaure. Les tests qui lancent un scénario s'exécutent dans une transaction annulée.

## Mesure

Événements Umami (préfixe `demo-`, étiquettes simples uniquement) : `journey-start`, `journey-launch`, `journey-exit-integrate`, `cta-message` (emplacement `journey-exit`). Le lancement et le contact portent la propriété `campaign` : voir [mesure-communication.md](./mesure-communication.md).

## Simulateur PunchOut habillé (US-099, sprint 20)

`/demo/punchout-simulator` (cXML) et `/demo/punchout-simulator/oci` se présentent comme un portail d'achat fictif d'Acme Industries (monogramme neutre, aucune marque réelle) : les quatre étapes numérotées (lancer, parcourir la boutique, recevoir le panier, passer la commande ; `SimulatorStep` calcule l'étape courante, `aria-current="step"`), un vocabulaire (cXML, OCI, OrderRequest, HOOK_URL) dont chaque définition s'affiche au survol et au focus clavier, un schéma du flux en SVG avec titre et description, le panier reçu en tableau, et un lien « Relancer une session » sur les erreurs de session. Une URL de retour hors des domaines autorisés est refusée avant tout appel à la boutique (HOOK_URL OCI) et le message rappelle les domaines autorisés. Textes : `translations/simulator.{fr,en}.yaml` (mêmes clés, testé).

