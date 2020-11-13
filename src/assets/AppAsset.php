<?php

namespace moxuandi\apidoc\assets;

use yii\web\AssetBundle;

/**
 * Class AppAsset
 * @package moxuandi\apidoc\assets
 */
class AppAsset extends AssetBundle
{
    public $css = [
        'https://cdn.jsdelivr.net/npm/jquery.json-viewer@1.4.0/json-viewer/jquery.json-viewer.css',
    ];
    public $js = [
        'https://cdn.jsdelivr.net/npm/vue',
        'https://cdn.jsdelivr.net/npm/axios@0.21.0/dist/axios.min.js',
        'https://cdn.jsdelivr.net/npm/jquery.json-viewer@1.4.0/json-viewer/jquery.json-viewer.js',
    ];
    public $depends = [
        'moxuandi\apidoc\assets\LoginAsset',
    ];
}
