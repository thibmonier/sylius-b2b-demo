# Gabarits de tests pour les plugins et la démo (T-TECH-38-01, T-TECH-38-05)

Au sprint 37, la plupart des « rouges » échouaient avant la moindre assertion : entité sans locale, client connecté sur le mauvais pare-feu, canal incomplet, namespace Twig erroné, variable de hook inexistante, contrôleur privé. Chaque erreur coûtait un push et un run de CI de 5 à 10 minutes. Ces gabarits donnent la mise en place qui fonctionne ; on les copie dans le test, puis on écrit seulement l'assertion du comportement.

Règle : un test rouge n'est valide que s'il échoue sur une assertion de comportement **après** une mise en place réussie. Lancez-le en local avant de pousser (règle 46 de la consigne worker).

## 1. Test d'installation d'un nouveau plugin (T-TECH-38-05)

`Kernel::getBundles()` est indexé par le **nom** du bundle, pas par sa classe : vérifier `kernel.bundles`, dont les valeurs sont les classes.

```php
final class InstallationTest extends KernelTestCase
{
    public function testThePluginBundleIsRegistered(): void
    {
        self::bootKernel();
        $bundles = self::getContainer()->getParameter('kernel.bundles');

        self::assertIsArray($bundles);
        self::assertContains(MyPlugin::class, $bundles);
    }
}
```

La TestApplication de Sylius lit ses réglages dans `phpunit.xml.dist`, sans fichier `.env` versionné. Sans ces trois lignes, le plugin n'est jamais chargé dans ses tests :

```xml
<server name="SYLIUS_TEST_APP_BUNDLES_PATH" value="tests/TestApplication/config/bundles.php"/>
<server name="SYLIUS_TEST_APP_CONFIGS_TO_IMPORT" value="@MyPlugin/tests/TestApplication/config/config.yaml"/>
<server name="SYLIUS_TEST_APP_ROUTES_TO_IMPORT" value="@MyPlugin/tests/TestApplication/config/routes.yaml"/>
```

## 2. Test fonctionnel (T-TECH-38-01)

```php
final class MyFeatureTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        // createClient() d'abord, une seule fois : il démarre le kernel.
        $this->client = static::createClient();
        $entityManager = static::getContainer()->get('doctrine.orm.entity_manager');
        \assert($entityManager instanceof EntityManagerInterface);
        $this->entityManager = $entityManager;
    }
}
```

### Produit et variante

Codes uniques (la base n'est pas vidée entre les tests), locale courante et locale de repli, canal et prix.

```php
private function createVariant(string $name): ProductVariant
{
    $channel = static::getContainer()->get('sylius.repository.channel')->findOneBy([]);
    $code = 'test-' . uniqid();

    $product = new Product();
    $product->setCode($code);
    $product->setCurrentLocale('en_US');
    $product->setFallbackLocale('en_US');
    $product->setName($name);
    $product->setSlug($code);
    $product->setEnabled(true);
    $product->addChannel($channel);

    $variant = new ProductVariant();
    $variant->setCode($code . '-variant');
    $variant->setCurrentLocale('en_US');
    $variant->setFallbackLocale('en_US');
    $variant->setName($name);
    $variant->setProduct($product);

    $pricing = new ChannelPricing();
    $pricing->setChannelCode($channel->getCode());
    $pricing->setPrice(1000);
    $variant->addChannelPricing($pricing);

    $this->entityManager->persist($product);
    $this->entityManager->persist($variant);
    $this->entityManager->flush();

    return $variant;
}
```

### Client de la boutique connecté

Le pare-feu de la boutique s'appelle `shop` ; `loginUser()` sans pare-feu utilise `main`, qui n'existe pas dans Sylius, et la page redirige vers la connexion (302).

```php
$customer = new Customer();
$customer->setEmail('test_' . uniqid() . '@example.com');
$customer->setFirstName('Test');
$customer->setLastName('User');

$shopUser = new ShopUser();
$shopUser->setUsername($customer->getEmail());
$shopUser->setPlainPassword('password123');
$shopUser->setEnabled(true);
$shopUser->setCustomer($customer);

$this->entityManager->persist($customer);
$this->entityManager->persist($shopUser);
$this->entityManager->flush();

$this->client->loginUser($shopUser, 'shop');
```

### Administrateur connecté

Pare-feu `admin` ; un `AdminUser` a déjà `DEFAULT_ADMIN_ROLE` mais il lui faut une locale.

```php
$admin = new AdminUser();
$admin->setUsername('admin_' . uniqid());
$admin->setEmail('admin_' . uniqid() . '@example.com');
$admin->setPlainPassword('password123');
$admin->setEnabled(true);
$admin->setLocaleCode('en_US');
$this->entityManager->persist($admin);
$this->entityManager->flush();

$this->client->loginUser($admin, 'admin');
```

### Second canal (B2B / B2C)

Le canal est choisi par le nom d'hôte (`HostnameBasedRequestResolver`). Recopier les champs obligatoires du canal existant, sinon `tax_calculation_strategy` (entre autres) est nul.

```php
$default = static::getContainer()->get('sylius.repository.channel')->findOneBy([]);

$channel = new Channel();
$channel->setCode('B2C_' . strtoupper(uniqid()));
$channel->setName('B2C test');
$channel->setHostname('b2c.localhost');
$channel->setEnabled(true);
$channel->setTaxCalculationStrategy($default->getTaxCalculationStrategy());
$channel->setBaseCurrency($default->getBaseCurrency());
$channel->setDefaultLocale($default->getDefaultLocale());
foreach ($default->getCurrencies() as $currency) { $channel->addCurrency($currency); }
foreach ($default->getLocales() as $locale) { $channel->addLocale($locale); }
$this->entityManager->persist($channel);
$this->entityManager->flush();

$this->client->request('GET', '/en_US/account/dashboard', server: ['HTTP_HOST' => 'b2c.localhost']);
```

### Requêtes et assertions

- Routes de boutique sous `/{_locale}` : toujours `/en_US/...` (sinon 404).
- Viser un bloc par un attribut dédié (`data-test="..."`) plutôt qu'un mot de la page : `assertSelectorExists('[data-test="my-block"]')`, et `assertSelectorNotExists(...)` pour l'absence. Un mot comme « compatible » peut déjà figurer ailleurs dans la page.

## 3. Plugin : pièges de configuration

- Namespace Twig d'un bundle `TmonierXxxPlugin` : `@TmonierXxxPlugin/...` (le suffixe `Plugin` reste).
- Dans un template de hook Twig, les données passent par `hookable_metadata.context.*` (par exemple `hookable_metadata.context.resource` dans les sections de l'édition produit) ; vérifier le nom dans `vendor/sylius/sylius/src/Sylius/Bundle/*/templates`.
- Un contrôleur déclaré en service doit être public ou porter le tag `controller.service_arguments`, sinon 500 « controller is not callable ».
- Contrôleurs Stimulus d'un plugin : les enregistrer dans `assets/shop/entrypoint.js` (`application.register('tmonier-xxx', XxxController)`), comme company-insights.
