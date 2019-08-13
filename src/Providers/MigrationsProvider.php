<?php

namespace Intersect\Providers;

use Intersect\Providers\AppServiceProvider;
use Intersect\Database\Migrations\RunMigrationsCommand;
use Intersect\Database\Migrations\ExportMigrationsCommand;
use Intersect\Database\Migrations\GenerateMigrationCommand;
use Intersect\Database\Migrations\InstallMigrationsCommand;

class MigrationsProvider extends AppServiceProvider {

    public function init()
    {
        $connection = $this->app->getConnection();
        $migrationsPath = $this->app->getMigrationsPath();

        $this->app->command('migrations:export', function() use ($connection, $migrationsPath) { return new ExportMigrationsCommand($connection, $migrationsPath); });
        $this->app->command('migrations:install', function() use ($connection) { return new InstallMigrationsCommand($connection); });
        $this->app->command('migrations:generate', function() use ($migrationsPath) { return new GenerateMigrationCommand($migrationsPath); });
        $this->app->command('migrations:run', function() use ($connection, $migrationsPath) { return new RunMigrationsCommand($connection, $migrationsPath); });
    }

}