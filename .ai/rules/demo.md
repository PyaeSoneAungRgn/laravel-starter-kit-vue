---
paths:
  - 'app-modules/demo/**'
---

# Demo

## db:seed --module requires a module DatabaseSeeder
`php artisan db:seed --module={module}` resolves `Modules\{Module}\Database\Seeders\DatabaseSeeder` and fails if it doesn't exist. Give each module a `DatabaseSeeder` that calls the module's seeders (e.g. `$this->call(ProductSeeder::class)`), instead of running a specific seeder directly.
