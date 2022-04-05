<?php

declare(strict_types=1);

namespace Scrumble\TypeGenerator;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Scrumble\TypeGenerator\Console\Commands\GenerateTypesCommand;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var boolean
     */
    protected $defer = false;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/laravel-model-ts-type.php' => config_path('laravel-model-ts-type.php'),
        ], 'laravel-model-ts-type');

        $this->commands([GenerateTypesCommand::class]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/laravel-model-ts-type.php', 'laravel-model-ts-type');
    }
}
