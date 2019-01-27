<?php

use \Intersect\Application;
use Intersect\Core\Storage\FileStorage;
use Intersect\Database\Connection\Connection;
use Intersect\Database\Connection\NullConnection;
use \Intersect\Database\Connection\ConnectionFactory;
use \Intersect\Database\Connection\ConnectionSettings;

$app = Application::instance();

$databaseConfig = $app->getRegisteredConfigs('database');
$connection = new NullConnection();

if (!is_null($databaseConfig) && is_array($databaseConfig))
{
    if (array_key_exists('host', $databaseConfig) && array_key_exists('username', $databaseConfig) && array_key_exists('password', $databaseConfig) 
        && array_key_exists('port', $databaseConfig) && array_key_exists('name', $databaseConfig) && array_key_exists('driver', $databaseConfig))
    {
        $connectionSettings = new ConnectionSettings($databaseConfig['host'], $databaseConfig['username'], $databaseConfig['password'], $databaseConfig['port'], $databaseConfig['name']);
        $connection = ConnectionFactory::get($databaseConfig['driver'], $connectionSettings);
    }
}

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