# Yii2 Captcha Extension

[![Latest Stable Version](https://img.shields.io/packagist/v/juliardi/yii2-captcha?label=stable)](https://packagist.org/packages/juliardi/yii2-captcha)
[![Total Downloads](https://img.shields.io/packagist/dt/juliardi/yii2-captcha)](https://packagist.org/packages/juliardi/yii2-captcha)
[![Latest Stable Release Date](https://img.shields.io/github/release-date/juliardi/yii2-captcha)](https://github.com/juliardi/yii2-captcha)
[![License](https://img.shields.io/github/license/juliardi/yii2-captcha)](https://github.com/juliardi/yii2-captcha)

> Yii2 Captcha uses [Gregwar's Captcha library](https://github.com/Gregwar/Captcha) wrapper for Yii2.

## Table of Contents

- [Yii2 Captcha Extension](#yii2-captcha-extension)
  - [Table of Contents](#table-of-contents)
  - [Instalation](#instalation)
  - [Usage](#usage)
    - [Action](#action)
    - [View](#view)
    - [Validation](#validation)

## Instalation

Package is available on [Packagist](https://packagist.org/packages/juliardi/yii2-captcha), you can install it using [Composer](https://getcomposer.org).

```shell
composer require juliardi/yii2-captcha "*"
```

or add to the require section of your `composer.json` file.

```shell
"juliardi/yii2-captcha": "*"
```

## Usage

This extension has 3 different steps. First is calling `juliardi\captcha\CaptchaAction` to provide [CAPTCHA](https://en.wikipedia.org/wiki/CAPTCHA) image - a way of preventing website spamming, then rendering CAPTCHA image in view with `juliardi\captcha\Captcha`, and validating user input against the generated CAPTCHA code with `juliardi\captcha\CaptchaValidator`.

Here is how to setup this extension for each step :

### Action

Add the following method into your Controller.

```php
public function actions()
{
    return [
        'captcha' => [
            'class' => \juliardi\captcha\CaptchaAction::class,

            /**
             * How many times should the same CAPTCHA be displayed. Defaults to 3.
             * A value less than or equal to 0 means the test is unlimited (available since version 1.1.2).
             */
            'testLimit' => 3, // int

            /**
             * The width of the generated CAPTCHA image. Defaults to 150.
             */
            'width' => 150, // int

            /**
             * The height of the generated CAPTCHA image. Defaults to 40.
             */
            'height' => 40, // int

            /**
             * The minimum & maximum length for randomly generated word. Defaults to [5, 7] | min 5 max 7.
             * 
             * If an array is provided, the first value will be used as the minimum length and the second value will be used as the maximum length.
             * 
             * **Note:** The minimum length must be at least 3 and the maximum length must be at most 20.
             * 
             */
            'length' => [5, 7], // int|int[] | // Random word length will be between 5 and 7 characters

            /**
             * The quality of the generated JPEG image. Valid values are 1 - 100. Defaults to 80.
             */
            'quality' => 80, // int

            /**
             * The fixed verification code. When this property is set,
             * 
             * This is mainly used in automated tests where we want to be able to reproduce
             * the same verification code each time we run the tests.
             * If not set, it means the verification code will be randomly generated.
             */
            // 'fixedVerifyCode' => 'testme', // string|null
        ],
    ];
}
```

### View

Add the following code to your view to render CAPTCHA image and input.

The following example shows how to use this widget with a model attribute:

```php
use juliardi\captcha\Captcha;

echo Captcha::widget([
    'model' => $model,
    'attribute' => 'captcha',
    
    // configure additional widget properties here
    /**
     * The route of the action that generates the CAPTCHA images.
     * The action represented by this route must be an action of [[CaptchaAction]].
     * Please refer to [[\yii\helpers\Url::toRoute()]] for acceptable formats.
     */
    'captchaAction' => 'site/captcha', // string|array

    /**
     * HTML attributes to be applied to the CAPTCHA image tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    'imageOptions' => [], // array

    /**
     * The template for arranging the CAPTCHA image tag and the text input tag.
     * In this template, the token `{image}` will be replaced with the actual image tag,
     * while `{input}` will be replaced with the text input tag.
     */
    'template' => '{image} {input}', // string

    /**
     * HTML attributes for the input tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    'options' => ['class' => 'form-control'], // array

]);
```

The following example will use the name property instead:

```php
use juliardi\captcha\Captcha;

echo Captcha::widget([
    'name' => 'captcha',
]);
```

You can also use this widget in an [ActiveForm](https://www.yiiframework.com/doc/api/2.0/yii-widgets-activeform) using the [widget()](https://www.yiiframework.com/doc/api/2.0/yii-widgets-activefield#widget()-detail) method, for example like this:

```php
<?= $form->field($model, 'captcha')->widget(\juliardi\captcha\Captcha::class, [
    // configure additional widget properties here
]) ?>
```

### Validation

Add the following rule to your model to validate the captcha input :

```php
use juliardi\captcha\CaptchaValidator;

public function rules()
{
    return [
        ... some other rules...
        ['captcha', CaptchaValidator::class],
    ];
}
```
