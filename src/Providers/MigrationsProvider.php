<?php

namespace Intersect\Providers;

use Intersect\Providers\AppServiceProvider;
use Intersect\Database\Migrations\Commands\GenerateSeedCommand;
use Intersect\Database\Migrations\Commands\RunMigrationsCommand;
use Intersect\Database\Migrations\Commands\ExportMigrationsCommand;
use Intersect\Database\Migrations\Commands\GenerateMigrationCommand;
use Intersect\Database\Migrations\Commands\InstallMigrationsCommand;
use Intersect\Database\Migrations\Commands\RollbackMigrationsCommand;

class MigrationsProvider extends AppServiceProvider {

    public function initCommands()
    {
        $app = $this->app;

        $app->command('migrations:export', function() use ($app) { return new ExportMigrationsCommand($app->getConnection(), $app->getMigrationPaths()); });
        $app->command('migrations:install', function() use ($app) { return new InstallMigrationsCommand($app->getConnection()); });
        $app->command('migrations:generate', function() use ($app) { return new GenerateMigrationCommand($app->getMigrationsPath()); });
        $app->command('migrations:generate-seed', function() use ($app) { return new GenerateSeedCommand($app->getMigrationsPath()); });
        $app->command('migrations:rollback', function() use ($app) { return new RollbackMigrationsCommand($app->getConnection(), $app->getMigrationPaths()); });
        $app->command('migrations:run', function() use ($app) { return new RunMigrationsCommand($app->getConnection(), $app->getMigrationPaths()); });
    }

}