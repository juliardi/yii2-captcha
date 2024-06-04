<?php

namespace juliardi\captcha;

use yii\web\AssetBundle;

/**
 * This asset bundle provides the javascript files needed for the [[Captcha]] widget.
 */
class CaptchaAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/assets';

    public $js = [
        'juliardi.captcha.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
    ];
}
