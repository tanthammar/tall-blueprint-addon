<?php

namespace Tanthammar\TallBlueprintAddon;

use Blueprint\Blueprint;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Tanthammar\TallBlueprintAddon\Generators\ViewGenerator;
use Tanthammar\TallBlueprintAddon\Tasks\AddIdentifierField;
use Tanthammar\TallBlueprintAddon\Tasks\AddRegularFields;
use Tanthammar\TallBlueprintAddon\Tasks\AddRelationshipFields;
use Tanthammar\TallBlueprintAddon\Tasks\AddTimestampFields;
use Tanthammar\TallBlueprintAddon\Tasks\OnCreate;
use Tanthammar\TallBlueprintAddon\Tasks\OnDelete;
use Tanthammar\TallBlueprintAddon\Tasks\OnUpdate;

class TallBlueprintAddonServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                dirname(__DIR__) . '/config/tall_forms_blueprint.php' => config_path('tall_forms_blueprint.php'),
            ], 'tall_forms_blueprint');
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/config/tall_forms_blueprint.php',
            'tall-forms-blueprint'
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
            $blueprint->registerGenerator(new TallMethodsBlueprintGenerator($app['files']));

            $blueprint->swapGenerator(
                \Blueprint\Generators\Statements\ViewGenerator::class,
                new ViewGenerator($app['files'])
            );

            return $blueprint;
        });

    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            'command.blueprint.build',
            TallBlueprintGenerator::class,
            Blueprint::class,
        ];
    }
}
