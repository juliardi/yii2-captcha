Yii2 Captcha Extension
======================

[Gregwar's Captcha library](https://github.com/Gregwar/Captcha) wrapper for Yii2.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).
Either run
```
php composer.phar require --prefer-dist juliardi/yii2-captcha "*"
```
or add
```
"juliardi/yii2-captcha": "*"
```
to the require section of your `composer.json` file.

Usage
-----
This extension has 3 different steps. First is calling `CaptchaAction` to generate CAPTCHA image, then rendering CAPTCHA image in view, and validating user input against the generated CAPTCHA code.
Here is how to setup this extension for each step :
##### Action
Add the following method into your Controller.
```php
public function actions()
{
    return [
        'captcha' => [
            'class' => 'juliardi\captcha\CaptchaAction',
            //'length' => 5, // captcha character count
            //'width' => 150, // width of generated captcha image
            //'height' => 40, // height of generated captcha image
        ],
    ];
}
```
Some configurable attributes are :
- `length`
An integer value to set the generated CAPTCHA character count
- `width` 
An integer value to set the width of generated CAPTCHA image
- `height`
An integer value to set the height of generated CAPTCHA image

##### View file
Add the following code to your view to render CAPTCHA image and input. 
```php
use juliardi\captcha\Captcha;
...
<?php echo Captcha::widget([
    'model' => $model,
    'attribute' => 'captcha',
    //'captchaAction' => 'site/captcha',    // captcha action, default to site/captcha
    //'template' => '{image} {input}',      // template for rendering CAPTCHA image and input
    //'options' => [                        // HTML attribute for rendering text input
    //    'class' => 'form-control',
    //],
]) ?>
```
You can also use ActiveForm instance to render CAPTCHA input.
```php
use juliardi\captcha\Captcha;
...
<?php echo $form->field($model, 'captcha')->widget(Captcha::className()) ?>

```
Some configurable attributes are :
- `captchaAction`
Captcha action, default to `site/captcha`
- `template`
Template for rendering CAPTCHA image and input. In this template, the token `{image}` will be replaced with the actual image tag, while `{input}` will be replaced with the text input tag.
- `options`
the HTML attributes for the input tag.

##### Validation
Add the following rule to your model to validate the captcha input :
```php
use juliardi\captcha\CaptchaValidator;
...
public function rules()
{
    return [
        ... some other rules...
        ['captcha', CaptchaValidator::className()],
    ];
}
```