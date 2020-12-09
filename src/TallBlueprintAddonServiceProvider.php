<?php

namespace Tanthammar\TallBlueprintAddon;

use Blueprint\Blueprint;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Tanthammar\TallBlueprintAddon\Tasks\AddIdentifierField;
use Tanthammar\TallBlueprintAddon\Tasks\AddRegularFields;
use Tanthammar\TallBlueprintAddon\Tasks\AddRelationshipFields;
use Tanthammar\TallBlueprintAddon\Tasks\AddTimestampFields;

class TallBlueprintAddonServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                dirname(__DIR__).'/config/nova_blueprint.php' => config_path('nova_blueprint.php'),
            ], 'nova_blueprint');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/config/nova_blueprint.php',
            'blueprint-nova-config'
        );

        $this->app->singleton(TallBlueprintGenerator::class, function ($app) {
            $generator = new TallBlueprintGenerator($app['files']);

            $generator->registerTask(new AddIdentifierField());
            $generator->registerTask(new AddRegularFields());
            $generator->registerTask(new AddRelationshipFields());
            $generator->registerTask(new AddTimestampFields());

            return $generator;
        });

        $this->app->extend(Blueprint::class, function ($blueprint, $app) {
            $blueprint->registerGenerator($app[TallBlueprintGenerator::class]);

            return $blueprint;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'command.blueprint.build',
            TallBlueprintGenerator::class,
            Blueprint::class,
        ];
    }
}
