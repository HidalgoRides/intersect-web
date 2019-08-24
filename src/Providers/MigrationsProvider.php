<?php

namespace Intersect\Providers;

use Intersect\Providers\AppServiceProvider;
use Intersect\Database\Migrations\GenerateSeedCommand;
use Intersect\Database\Migrations\RunMigrationsCommand;
use Intersect\Database\Migrations\ExportMigrationsCommand;
use Intersect\Database\Migrations\GenerateMigrationCommand;
use Intersect\Database\Migrations\InstallMigrationsCommand;

class MigrationsProvider extends AppServiceProvider {

    public function initCommands()
    {
        $app = $this->app;

        $app->command('migrations:export', function() use ($app) { return new ExportMigrationsCommand($app->getConnection(), $app->getMigrationPaths()); });
        $app->command('migrations:install', function() use ($app) { return new InstallMigrationsCommand($app->getConnection()); });
        $app->command('migrations:generate', function() use ($app) { return new GenerateMigrationCommand($app->getMigrationsPath()); });
        $app->command('migrations:generate-seed', function() use ($app) { return new GenerateSeedCommand($app->getMigrationsPath()); });
        $app->command('migrations:run', function() use ($app) { return new RunMigrationsCommand($app->getConnection(), $app->getMigrationPaths()); });
    }

}