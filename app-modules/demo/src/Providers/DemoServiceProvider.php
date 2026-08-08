<?php

namespace Modules\Demo\Providers;

use Illuminate\Support\ServiceProvider;
use Override;

class DemoServiceProvider extends ServiceProvider
{
    #[Override]
    public function register(): void {}

    public function boot(): void {}
}
