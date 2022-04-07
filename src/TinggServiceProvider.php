<?php

namespace GloCurrency\Tingg;

use Illuminate\Support\ServiceProvider;
use GloCurrency\Tingg\Config;
use BrokeYourBike\Tingg\Interfaces\ConfigInterface;

class TinggServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerMigrations();
        $this->registerPublishing();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->configure();
        $this->bindConfig();
    }

    /**
     * Setup the configuration for Tingg.
     *
     * @return void
     */
    protected function configure()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/tingg.php', 'services.tingg'
        );
    }

    /**
     * Bind the Tingg config interface to the Tingg config.
     *
     * @return void
     */
    protected function bindConfig()
    {
        $this->app->bind(ConfigInterface::class, Config::class);
    }

    /**
     * Register the package migrations.
     *
     * @return void
     */
    protected function registerMigrations()
    {
        if (Tingg::$runsMigrations && $this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/tingg.php' => $this->app->configPath('tingg.php'),
            ], 'tingg-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => $this->app->databasePath('migrations'),
            ], 'tingg-migrations');
        }
    }
}
