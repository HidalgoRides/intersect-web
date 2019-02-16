<?php

use \Intersect\Application;
use Intersect\Core\Storage\FileStorage;
use Intersect\Database\Connection\Connection;

$app = Application::instance();
$connection = $app->getConnection();

return [
    'classes' => [],
    'singletons' => [
        Application::class => $app,
        Connection::class => $connection,
        FileStorage::class => FileStorage::class
    ],
    'commands' => [
        'migrations:install' => new \Intersect\Database\Migrations\InstallMigrationsCommand($connection),
        'migrations:generate' => new \Intersect\Database\Migrations\GenerateMigrationCommand($app->getMigrationsPath()),
        'migrations:run' => new \Intersect\Database\Migrations\RunMigrationsCommand($connection, $app->getMigrationsPath())
    ]
];