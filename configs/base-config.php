<?php

return [
    'database' => [
        'connections' => [
            'default' => [
                'driver' => 'mysql',
                'host' => 'db',
                'username' => 'root',
                'password' => 'password',
                'port' => '3306',
                'name' => 'app',
                'schema' => null,
                'charset' => 'utf8'
            ]
        ],
        'aliases' => []
    ],
    'paths' => [
        'cache' => '/cache',
        'logs' => '/logs',
        'migrations' => '/migrations',
        'templates' => '/templates',
    ],
    'twig' => [
        'options' => [
            'auto_reload' => false,
            'base_template_class' => 'Twig_Template',
            'cache' => false,
            'charset' => 'utf-8',
            'debug' => false,
            'optimizations' => -1,
            'autoescape' => 'name',
            'strict_variables' => false
        ],
        'extensions' => []
    ]
];