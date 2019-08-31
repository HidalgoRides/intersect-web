<?php

namespace Intersect\Http\Response;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

/**
 * composer dependencies required
 * "twig/twig": "^2.0",
 */
class TwigResponse extends AbstractResponse {

    private $data = [];
    private $templateFile;

    public function __construct($templateFile, array $data = [], int $status = 200)
    {
        parent::__construct(null, $status);

        $this->data = $data;
        $this->templateFile = $templateFile;
    }

    public function handle($templatesPath = null, $configs = [])
    {
        $loader = new FilesystemLoader($templatesPath);

        $options = (array_key_exists('options', $configs) ? $configs['options'] : []);
        $extensions = (array_key_exists('extensions', $configs) ? $configs['extensions'] : []);

        $twig = new Environment($loader, $options);

        foreach ($extensions as $extension)
        {
            $extensionInstace = new $extension();
            if ($extensionInstace instanceof \Twig_ExtensionInterface)
            {
                $twig->addExtension($extensionInstace);
            }
        }

        echo $twig->render($this->templateFile, $this->data);
    }

}