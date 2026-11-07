<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\RolePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Override;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[Override]
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict();

        DB::prohibitDestructiveCommands(app()->isProduction());

        Model::automaticallyEagerLoadRelationships();

        Gate::before(fn (mixed $user): ?bool => $user instanceof User && $user->isSuperAdmin() ? true : null);

        Gate::policy(Role::class, RolePolicy::class);
    }
}
