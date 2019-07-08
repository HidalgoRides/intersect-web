<?php

use \Intersect\Application;
use Intersect\Core\Storage\FileStorage;
use Intersect\Database\Connection\Connection;
use Intersect\Http\ExceptionHandler;
use Intersect\Http\DefaultExceptionHandler;

$app = Application::instance();
$connection = $app->getConnection();

return [
    'classes' => [],
    'singletons' => [
        Application::class => $app,
        Connection::class => $connection,
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