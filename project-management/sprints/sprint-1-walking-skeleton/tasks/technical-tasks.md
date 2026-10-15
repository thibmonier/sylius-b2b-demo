# Tâches techniques transverses — Sprint 1

## Vue d'ensemble

Ces tâches sont transverses au sprint et ne dépendent pas d'une US spécifique.

---

## Infrastructure et configuration

### T-TECH-01 — Structure du dépôt Git et .gitignore

- **Type** : [OPS]
- **Estimation** : 0.5h
- **Dépend de** : —

**Description** :
Configurer le dépôt avant de commencer le développement :
- Fichier `.gitignore` adapté à Sylius 2.3 + Docker (exclure `.env.local`, `compose.override.yaml`, `var/`, `vendor/`, `public/build/`, `public/media/`)
- Fichier `.editorconfig` (PHP 8.4 : 4 espaces)
- Fichier `.github/CODEOWNERS` (Thibaut = owner de tout)

**Fichiers** :
- `.gitignore`
- `.editorconfig`
- `.github/CODEOWNERS`

**Critères** :
- [ ] `vendor/` et `var/` non versionnés
- [ ] `compose.override.yaml` et `.env.local` non versionnés
- [ ] `docker/crontab` et `docker/Dockerfile` versionnés

---

### T-TECH-02 — Configuration de base php.ini

- **Type** : [OPS]
- **Estimation** : 0.5h
- **Dépend de** : —

**Description** :
Créer `docker/php.ini` avec les valeurs adaptées aux contraintes de la tech-spec §4 :

```ini
[PHP]
memory_limit = 512M
upload_max_filesize = 32M
post_max_size = 32M
max_execution_time = 60

[opcache]
opcache.enable = 1
opcache.memory_consumption = 256
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = 0

[apcu]
apc.enabled = 1
apc.shm_size = 64M
```

Pour la CLI (commandes reset, fixtures) : surcharge `memory_limit = 1G` via `PHP_CLI_SERVER_WORKERS` ou argument `-d memory_limit=1G`.

**Fichiers** : `docker/php.ini`

---

### T-TECH-03 — Makefile de commodité

- **Type** : [OPS]
- **Estimation** : 0.5h
- **Dépend de** : —

**Description** :
Créer un `Makefile` avec les cibles courantes pour le développement :

```makefile
.PHONY: up down build console fixtures test quality

up:       ## Démarrer l'environnement local
down:     ## Arrêter l'environnement
build:    ## Reconstruire l'image
console:  ## Ouvrir un shell dans le conteneur web
fixtures: ## Recharger les fixtures demo
test:     ## Lancer les tests PHPUnit
quality:  ## Lancer ECS et PHPStan
reset:    ## Lancer la commande de remise à zéro
```

**Fichiers** : `Makefile`

---

### T-TECH-04 — Structure src/Demo/ initiale

- **Type** : [BE]
- **Estimation** : 0.5h
- **Dépend de** : T-001-01

**Description** :
Créer la structure de dossiers `src/Demo/` conformément à la tech-spec §3 :

```
src/Demo/
├── DemoBundle.php           # bundle optionnel (ou DemoKernel extension)
├── Badge/                   # placeholder sprint 4
├── Banner/                  # placeholder sprint 4
├── Controller/
│   └── HealthController.php # GET /healthz → 200
├── Reset/
│   └── DemoResetCommand.php # placeholder app:demo:reset
├── Security/                # placeholder sprint 6
├── Webhook/                 # placeholder sprint 5
└── Fixture/
    └── AbstractDemoFixture.php  # classe de base commune
```

**Route `/healthz`** :
```php
#[Route('/healthz', name: 'demo_health', methods: ['GET'])]
public function health(): Response {
    return new Response('OK', 200, ['Content-Type' => 'text/plain']);
}
```

**Fichiers** :
- `src/Demo/Controller/HealthController.php`
- `src/Demo/Fixture/AbstractDemoFixture.php`
- `config/routes/demo.yaml` (route `/healthz`)

---

## CI / Qualité

### T-TECH-05 — Baseline PHPStan et configuration ECS

- **Type** : [OPS]
- **Estimation** : 0.5h
- **Dépend de** : T-001-08

**Description** :
Générer la baseline PHPStan pour le code Sylius-Standard (erreurs non liées à la démo) et configurer ECS pour PER-CS 3.0.

```bash
vendor/bin/phpstan analyse --generate-baseline
```

```php
// ecs.php
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
return ECSConfig::configure()
    ->withPaths([__DIR__ . '/src'])
    ->withRules([...])
    ->withSets([SetList::PER_CS]);
```

**Fichiers** :
- `phpstan.neon` (avec `includeFiles` de baseline)
- `phpstan-baseline.neon`
- `ecs.php`

---

## Résumé des tâches techniques

| ID | Type | Tâche | Estimation |
|----|------|-------|------------|
| T-TECH-01 | [OPS] | Structure Git et .gitignore | 0.5h |
| T-TECH-02 | [OPS] | docker/php.ini | 0.5h |
| T-TECH-03 | [OPS] | Makefile | 0.5h |
| T-TECH-04 | [BE] | Structure src/Demo/ initiale + /healthz | 0.5h |
| T-TECH-05 | [OPS] | Baseline PHPStan + ECS | 0.5h |
| **TOTAL** | | | **2.5h** |
