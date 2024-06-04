<?php

namespace juliardi\captcha;

use yii\web\AssetBundle;

/**
 * This asset bundle provides the javascript files for client validation.
 */
class ValidationAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/assets';

    public $js = [
        'juliardi.validation.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
    ];
}
