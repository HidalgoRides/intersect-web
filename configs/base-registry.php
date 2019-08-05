<?php

use Intersect\Core\Storage\FileStorage;
use Intersect\Http\ExceptionHandler;
use Intersect\Http\DefaultExceptionHandler;

return [
    'classes' => [],
    'singletons' => [
        FileStorage::class => FileStorage::class,
        ExceptionHandler::class => DefaultExceptionHandler::class
    ],
    'commands' => [
        'migrations:export' => function() use ($app, $connection) { return new \Intersect\Database\Migrations\ExportMigrationsCommand($connection, $app->getMigrationsPath()); },
        'migrations:install' => function() use ($connection) { return new \Intersect\Database\Migrations\InstallMigrationsCommand($connection); },
        'migrations:generate' => function() use ($app) { return new \Intersect\Database\Migrations\GenerateMigrationCommand($app->getMigrationsPath()); },
        'migrations:run' => function() use ($app, $connection) { return new \Intersect\Database\Migrations\RunMigrationsCommand($connection, $app->getMigrationsPath()); }
    ]
];