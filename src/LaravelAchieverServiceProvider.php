<?php

namespace Aenzenith\LaravelAchiever;

use Illuminate\Support\ServiceProvider;

class LaravelAchieverServiceProvider extends ServiceProvider
{

     protected $commands = [
         "Aenzenith\LaravelAchiever\Commands\GenerateAchievementOperator"
     ];

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'aenzenith');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'aenzenith');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-achiever.php', 'laravel-achiever');

        // Register the service the package provides.
        $this->app->singleton('laravel-achiever', function ($app) {
            return new LaravelAchiever;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['laravel-achiever'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/laravel-achiever.php' => config_path('laravel-achiever.php'),
        ], 'laravel-achiever.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/aenzenith'),
        ], 'laravel-achiever.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/aenzenith'),
        ], 'laravel-achiever.assets');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/aenzenith'),
        ], 'laravel-achiever.lang');*/

        // Registering package commands.
        $this->commands($this->commands);
    }
}
