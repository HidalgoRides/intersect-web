<?php

namespace Intersect\Commands;

use Intersect\Application;
use Intersect\Core\Command\Command;
use Intersect\Core\Storage\FileStorage;

class GenerateApplicationKeyCommand implements Command {

    /** @var Application */
    private $app;
    /** @var FileStorage */
    private $fileStorage;

    public function __construct()
    {
        $this->app = Application::instance();
        $this->fileStorage = new FileStorage();
    }

    public function execute($data = [])
    {
        $currentKeyValue = $this->app->getRegisteredConfigs('app.key');
        $configPath = $this->app->getConfigsPath() . '/config.php';

        $generatedKey = base64_encode(random_bytes(32));

        $pattern = '/' . preg_quote('\'key\' => \''. $currentKeyValue . '\'', '/') . '/m';

        $contents = preg_replace($pattern, '\'key\' => ' . '\'' . $generatedKey . '\'', $this->fileStorage->getFile($configPath));

        $this->fileStorage->writeFile($configPath, $contents);
    }

    public function getDescription()
    {
        return 'Generates a random key to be used through the application.';
    }
    
    public function getParameters()
    {
        return [];
    }

}