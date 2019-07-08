<?php

namespace Intersect\Utils;

use Intersect\Application;
use Intersect\Core\Storage\FileStorage;

class ComposerUtils {

    public static function getVersions($includeDevDependencies = false)
    {
        $composerData = self::getComposerData();
        $versionData = [];

        $packages = self::getValue('packages');

        if (!is_null($packages) && is_array($packages))
        {
            $versions = [];

            foreach ($packages as $package)
            {
                $versions[$package['name']] = $package['version'];
            }

            $versionData['packages'] = $versions;
        }

        if ($includeDevDependencies && array_key_exists('packages-dev', $composerData))
        {
            $versions = [];

            foreach ($composerData['packages-dev'] as $package)
            {
                $versions[$package['name']] = $package['version'];
            }

            $versionData['packages-dev'] = $versions;
        }

        return $versionData;
    }

    public static function getValue($key, array $composerData = null)
    {
        if (is_null($composerData))
        {
            $composerData = self::getComposerData();
        }

        return (array_key_exists($key, $composerData) ? $composerData[$key] : null);
    }

    public static function getComposerData()
    {
        $app = Application::instance();
        $fs = new FileStorage();

        $composerPath = $app->getBasePath() . '/composer.lock';

        if (!$fs->directoryExists($composerPath))
        {
            throw new \Exception('composer.lock file not found in application base path ("' . $app->getBasePath() . '")');
        }

        return json_decode($fs->getFile($composerPath), true);
    }

}