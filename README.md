# Laravel Starter Kit Vue

A Laravel 13 + Inertia v3 + Vue 3 starter kit with built-in strict typing, enhanced code quality tools, safety features, modular architecture, AI-readiness via Laravel Boost, and production-ready containerization.

## Stack

| Layer | Technology |
| --- | --- |
| Framework | Laravel 13, PHP 8.4+ |
| Frontend | Inertia v3, Vue 3.5, Vite 7 |
| Styling | Tailwind CSS 4, shadcn-vue, reka-ui |
| Routing (JS) | Ziggy |
| Server | Laravel Octane (FrankenPHP / RoadRunner) |
| Real-time | Laravel Reverb + Echo-Vue |
| Monitoring | Laravel Nightwatch |
| Auth | Laravel Sanctum |
| Testing | Pest |
| Code quality | Duster (Laravel Pint + Rector + PHPStan) |
| AI-assisted development | Laravel Boost (MCP server for agents) |

## Requirements

- PHP 8.4+
- Composer 2
- Node.js 22+

## Installation

```bash
laravel new --using pyaesoneaung/laravel-starter-kit-vue --git
```

Setup Git Hooks

```bash
npm install
```

Copy the environment file and generate an application key, then migrate the default SQLite database:

```bash
cp .env.example .env
php artisan key:generate
php artisan migrate
```

## Development

Run all development processes (Laravel dev server, queue worker, log tailing with Pail, and Vite) in one command:

```bash
composer run dev
```

Run the development server with Inertia SSR:

```bash
composer run dev:ssr
```

Build frontend assets:

```bash
npm run build
```

## Features

### ✅ Strict Models

```php
Model::shouldBeStrict();
```

This does three things:
1. Prevents lazy loading.
2. Prevents silently discarding attributes.
3. Prevents accessing missing attributes.

---

### ✋ Prevent Destructive Commands

Preventing the execution of destructive commands in production environments.

```php
DB::prohibitDestructiveCommands(app()->isProduction());
```

---

### ⚡️ Automatic Relation Loading

Laravel can automatically eager load the relationships you access.

```php
Model::automaticallyEagerLoadRelationships();
```

---

### 🧩 Modular

This starter kit uses `internachi/modular` for domain modules under `app-modules/`.

```bash
php artisan make:module {name}
php artisan make:model {Name} --module={module}
php artisan modules:list
php artisan modules:sync
php artisan test app-modules/{module-name}/tests
```

Each module is a self-contained Composer package (`Modules\{Name}\`) with its own routes, controllers, models, migrations, factories, seeders, Vue pages, and tests. A full reference implementation is included in the `demo` module, showcasing a complete Product CRUD:

- `app-modules/demo/src/Http/Controllers/Backend/ProductController.php`
- `app-modules/demo/src/Models/Product.php`
- `app-modules/demo/src/Enums/`
- `app-modules/demo/database/migrations/`, `factories/`, `seeders/`
- `app-modules/demo/resources/js/pages/products/`
- `app-modules/demo/routes/demo-routes.php`
- `app-modules/demo/tests/Feature/ProductCrudTest.php`

Run the demo module tests with:

```bash
php artisan test app-modules/demo/tests
```

---

### ⚡️ Inertia + Vue

Pages live under `resources/js/pages/` and module pages under `app-modules/*/resources/js/pages/`, rendered server-side with `Inertia::render()`:

```php
return Inertia::render('demo::products/Index', [
    'products' => $this->model->query()->latest()->paginate(),
]);
```

UI components are powered by shadcn-vue (built on reka-ui) with Tailwind CSS 4.

---

### 🤖 AI-Ready with Laravel Boost

This starter kit is AI-ready out of the box. [Laravel Boost](https://github.com/laravel/boost) exposes your application to AI coding agents through a Model Context Protocol (MCP) server, giving them live access to:

- Database schema and read-only queries
- Application and browser error logs
- Version-specific Laravel documentation search
- Absolute URL resolution
- Durable project rules via `record-rule`

It's preconfigured for OpenCode in `opencode.json`:

```json
{
    "mcp": {
        "laravel-boost": {
            "type": "local",
            "enabled": true,
            "command": ["php", "artisan", "boost:mcp"]
        }
    }
}
```

Project-wide settings live in `boost.json`, including enabled agents, domain skills (Pest, modular, Octane, Inertia, Tailwind, Echo, and more), and package-specific guidance. Each skill provides step-by-step, version-aware instructions so agents follow this project's exact conventions without guessing.

---

### 🪝 Git Commit Hook

Run duster lint for PHPStan, duster fix for refactor with Rector, and format with Laravel Pint before every commit via husky + lint-staged.

#### Default Laravel Rector Rules

The following Rector rules are applied via `LaravelSetList::LARAVEL_CODE_QUALITY`, `LARAVEL_COLLECTION`, and `LARAVEL_TYPE_DECLARATIONS` sets, targeting `app/`, `config/`, `routes/`, `tests/`, and all module source, route, and test directories.

```php
// rector.php
return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/config',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
        __DIR__ . '/app-modules/*/src',
        __DIR__ . '/app-modules/*/routes',
        __DIR__ . '/app-modules/*/tests',
    ])
    ->withSetProviders(LaravelSetProvider::class)
    ->withComposerBased(laravel: true)
    ->withSets([
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelSetList::LARAVEL_COLLECTION,
        LaravelSetList::LARAVEL_TYPE_DECLARATIONS,
    ])
    ->withPhpSets();
```

##### EloquentMagicMethodToQueryBuilderRector

```diff
 use App\Models\User;

-$user = User::find(1);
+$user = User::query()->find(1);
```

##### EloquentWhereRelationTypeHintingParameterRector

```diff
-User::whereHas('posts', function ($query) {
+User::whereHas('posts', function (Builder $query) {
     $query->where('is_published', true);
 });

-$query->whereHas('posts', function ($query) {
+$query->whereHas('posts', function (Builder $query) {
     $query->where('is_published', true);
 });
```

##### EloquentWhereTypeHintClosureParameterRector

```diff
 /** @var \Illuminate\Contracts\Database\Query\Builder $query */
-$query->where(function ($query) {
+$query->where(function (Builder $query) {
     $query->where('id', 1);
 });
```

##### ModelCastsPropertyToCastsMethodRector

```diff
 use Illuminate\Database\Eloquent\Model;

 class Person extends Model
 {
-    protected $casts = [
-        'age' => 'integer',
-    ];
+    protected function casts(): array
+    {
+        return [
+            'age' => 'integer',
+        ];
+    }
 }
```

##### ScopeNamedClassMethodToScopeAttributedClassMethodRector

```diff
 class User extends Model
 {
-    public function scopeActive($query)
+    #[\Illuminate\Database\Eloquent\Attributes\Scope]
+    protected function active($query)
     {
         return $query->where('active', 1);
     }
 }
```

##### WhereToWhereLikeRector

```diff
-$query->where('name', 'like', 'Rector');
-$query->orWhere('name', 'like', 'Rector');
-$query->where('name', 'like binary', 'Rector');
+$query->whereLike('name', 'Rector');
+$query->orWhereLike('name', 'Rector');
+$query->whereLike('name', 'Rector', true);
```

---

### 🧪 Testing

Tests are written with Pest and organized into unit, feature, and module suites.

```bash
php artisan test
php artisan test --compact
php artisan test --compact --filter=ProductCrudTest
php artisan test app-modules/demo/tests
```

---

### 🐳 Docker & Production

The container runs PHP with FrankenPHP (via `serversideup/php`) and serves the app through Laravel Octane. Frontend assets are built at image build time.

```bash
docker compose up -d api
```

The `compose.yml` file provides the following services:

| Service | Purpose |
| --- | --- |
| `api` | Main HTTP server (Octane/FrankenPHP, port 80) |
| `backend` | Secondary HTTP server (Octane/FrankenPHP, port 8080) |
| `reverb` | WebSocket server for Laravel Reverb |
| `horizon` | Queue worker |
| `task` | Scheduled task runner (`schedule:work`) |
| `nightwatch-agent` | Collects Nightwatch monitoring data |

---

### 😺 GitHub Actions

Automated workflows for continuous integration and deployment:

- **Tests** — runs the Pest suite on every pull request.
- **Duster Lint** — runs `duster lint` (PHPStan) on every pull request.
- **Duster Fix** — runs `duster fix` (Pint) and auto-commits the fixes on every pull request.
- **OpenCode** — triggers OpenCode on `/oc` or `/opencode` comments in issues and pull requests.
- **Deploy to DigitalOcean** — manually triggered deployment of the selected branch and service to a DigitalOcean droplet via SSH and `docker compose up`.

---

### ⚡️ Real-time Broadcasting

Broadcasting is configured with Laravel Reverb and Echo-Vue. Reverb settings live in `config/reverb.php` and are exposed to the frontend via `VITE_REVERB_*` environment variables. Start the Reverb server locally with:

```bash
php artisan reverb:start
```

The application is also instrumented with Laravel Nightwatch for production monitoring — configure `NIGHTWATCH_TOKEN` to enable it.
