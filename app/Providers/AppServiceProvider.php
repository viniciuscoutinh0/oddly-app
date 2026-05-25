<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureModels();
        $this->configureDates();
    }

    public function configureModels(): void
    {
        Model::unguard();
        Model::shouldBeStrict(! app()->isProduction());
        Model::preventAccessingMissingAttributes(! app()->isProduction());
        Model::preventLazyLoading(! app()->isProduction());
    }

    public function configureDates(): void
    {
        Date::use(CarbonImmutable::class);
    }
}
