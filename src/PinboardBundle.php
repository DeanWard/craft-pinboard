<?php
namespace deanward\craftpinboard;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class PinboardBundle extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@deanward/craftpinboard/resources';

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'dist/js/field.js',
        ];

        $this->css = [
            'dist/css/field.css',
        ];

        parent::init();
    }
}