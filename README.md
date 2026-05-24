# Laravel Starter Kit Vue

Laravel 12 Vue starter kit with built-in strict typing, enhanced code quality tools, and safety features.

## Installation

```bash
laravel new --using pyaesoneaung/laravel-starter-kit-vue --git
```

Setup Git Hooks

```bash
npm install
```

## Features

### ✅ Strict Models

```php
Model::shouldBeStrict();
```
This does three things:
1. Prevents lazy loading
2. It prevents silently discarding attributes.
3. It prevents accessing missing attributes.

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

### 🪝 Git Commit Hook

Run duster lint for PHPStan, duster fix for refactor with Rector, and format with Laravel Pint before every commit.

#### Default Laravel Rector Rules

The following Rector rules are applied via `LaravelSetList::LARAVEL_CODE_QUALITY`, `LARAVEL_COLLECTION`, and `LARAVEL_TYPE_DECLARATIONS` sets, targeting `app/`, `config/`, `routes/`, and all module source directories.

```php
// rector.php
return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/config',
        __DIR__.'/routes',
        __DIR__.'/app-modules/*/src',
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

### 🧩 Modular

This starter kit uses `internachi/modular` for domain modules under `app-modules/`.

```bash
php artisan make:module {name}
php artisan make:model {Name} --module={module}
php artisan modules:list
php artisan modules:sync
php artisan test app-modules/{module-name}/tests
```

---

### 😺 GitHub Actions

Automated workflows for continuous integration, running Laravel Pint and PHPStan on every push and pull request.