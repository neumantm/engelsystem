<?php

namespace Engelsystem\Helpers;

class Assets
{
    protected string $assetsPath;

    protected string $manifestFile = 'manifest.json';

    /**
     * @param string $assetsPath Directory containing assets
     */
    public function __construct(string $assetsPath)
    {
        $this->assetsPath = $assetsPath;
    }

    public function getAssetPath(string $asset): string
    {
        $manifest = $this->assetsPath . DIRECTORY_SEPARATOR . $this->manifestFile;
        if (is_readable($manifest)) {
            $manifest = json_decode(file_get_contents($manifest), true);

            if (isset($manifest[$asset])) {
                $asset = $manifest[$asset];
            }
        }

        return $asset;
    }
}
