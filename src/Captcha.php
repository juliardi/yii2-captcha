<?php

namespace juliardi\captcha;

use yii\widgets\InputWidget;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * 
 */
class Captcha extends InputWidget
{
    /**
     * @var string|array the route of the action that generates the CAPTCHA images.
     * The action represented by this route must be an action of [[CaptchaAction]].
     * Please refer to [[\yii\helpers\Url::toRoute()]] for acceptable formats.
     */
    public $captchaAction = 'site/captcha';

    /**
     * @var string the template for arranging the CAPTCHA image tag and the text input tag.
     * In this template, the token `{image}` will be replaced with the actual image tag,
     * while `{input}` will be replaced with the text input tag.
     */
    public $template = '{image} {input}';

    /**
     * @var array the HTML attributes for the input tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = ['class' => 'form-control'];

    /**
     * Renders the widget.
     */
    public function run()
    {
        $input = $this->renderInputHtml('text');
        $route = Url::toRoute($this->captchaAction);

        $image = Html::img($route);
        echo strtr($this->template, [
            '{input}' => $input,
            '{image}' => $image,
        ]);
    }

}
