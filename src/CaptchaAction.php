<?php

namespace juliardi\captcha;

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;
use Yii;
use yii\base\Action;
use yii\helpers\Url;
use yii\web\Response;

/**
 * CaptchaAction renders a CAPTCHA image.
 *
 * CaptchaAction is used together with [[Captcha]] and [[\juliardi\captcha\CaptchaValidator]]
 * to provide the [CAPTCHA](https://en.wikipedia.org/wiki/CAPTCHA) feature.
 *
 * By configuring the properties of CaptchaAction, you may customize the appearance of
 * the generated CAPTCHA images, such as the font color, the background color, etc.
 *
 * Note that CaptchaAction requires either GD2 extension or ImageMagick PHP extension.
 *
 * Using CAPTCHA involves the following steps:
 *
 * 1. Override [[\yii\web\Controller::actions()]] and register an action of class CaptchaAction with ID 'captcha'
 * 2. In the form model, declare an attribute to store user-entered verification code, and declare the attribute
 *    to be validated by the 'captcha' validator.
 * 3. In the controller view, insert a [[Captcha]] widget in the form.
 *
 * @property-read string $verifyCode The verification code.
 */
class CaptchaAction extends Action
{
    /**
     * The name of the GET parameter indicating whether the CAPTCHA image should be regenerated.
     */
    const REFRESH_GET_VAR = 'refresh';

    /**
     * @var int how many times should the same CAPTCHA be displayed. Defaults to 3.
     * A value less than or equal to 0 means the test is unlimited (available since version 1.1.2).
     */
    public $testLimit = 3;

    /**
     * @var int the width of the generated CAPTCHA image. Defaults to 150.
     */
    public $width = 150;

    /**
     * @var int the height of the generated CAPTCHA image. Defaults to 40.
     */
    public $height = 40;

    /**
     * @var int|int[] the minimum & maximum length for randomly generated word. Defaults to [5, 7] | min 5 max 7.
     * 
     * If an array is provided, the first value will be used as the minimum length and the second value will be used as the maximum length.
     * ```php
     * 'length' => [5, 7], // Random word length will be between 5 and 7 characters
     * ```
     * 
     * **Note:** The minimum length must be at least 3 and the maximum length must be at most 20.
     * 
     */
    public $length = [5, 7];

    /**
     * @var int the quality of the generated JPEG image. Valid values are 1 - 100. Defaults to 80.
     */
    public $quality = 80;

    /**
     * @var string|null the fixed verification code. When this property is set,
     * [[getVerifyCode()]] will always return the value of this property.
     * This is mainly used in automated tests where we want to be able to reproduce
     * the same verification code each time we run the tests.
     * If not set, it means the verification code will be randomly generated.
     */
    public $fixedVerifyCode;

    /**
     * CaptchaBuilder instance
     *
     * @var \Gregwar\Captcha\CaptchaBuilder
     */
    protected $captchaBuilder;

    /**
     * PhraseBuilder instance
     *
     * @var \Gregwar\Captcha\PhraseBuilder
     */
    protected $phraseBuilder;


    /**
     * Initializes the action.
     */
    public function init()
    {
        parent::init();

        $this->phraseBuilder = new PhraseBuilder($this->normalizeLength());
        $this->captchaBuilder = new CaptchaBuilder($this->fixedVerifyCode, $this->phraseBuilder);
    }

    /**
     * Runs the action.
     */
    public function run()
    {
        if (Yii::$app->request->getQueryParam(self::REFRESH_GET_VAR) !== null) {
            // AJAX request for regenerating code
            $code = $this->getVerifyCode(true);
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'hash1' => $this->generateValidationHash($code),
                'hash2' => $this->generateValidationHash(strtolower($code)),
                // we add a random 'v' parameter so that FireFox can refresh the image
                // when src attribute of image tag is changed
                'url' => Url::to([$this->id, 'v' => uniqid('', true)]),
            ];
        }

        $this->setHttpHeaders();
        Yii::$app->response->format = Response::FORMAT_RAW;

        $this->captchaBuilder->build($this->width, $this->height);

        return $this->captchaBuilder->get($this->quality);
    }

    /**
     * Generates a hash code that can be used for client-side validation.
     * @param string $code the CAPTCHA code
     * @return string a hash code generated from the CAPTCHA code
     */
    public function generateValidationHash($code)
    {
        for ($h = 0, $i = strlen($code) - 1; $i >= 0; --$i) {
            $h += ord($code[$i]) << $i;
        }

        return $h;
    }

    /**
     * Gets the verification code.
     * @param bool $regenerate whether the verification code should be regenerated.
     * @return string the verification code.
     */
    public function getVerifyCode($regenerate = false)
    {
        if ($this->fixedVerifyCode !== null) {
            return $this->fixedVerifyCode;
        }

        $session = Yii::$app->getSession();
        $session->open();
        $name = $this->getSessionKey();
        if ($session[$name] === null || $regenerate) {
            $session[$name] = $this->generateVerifyCode();
            $session[$name . 'count'] = 1;
        }

        return $session[$name];
    }

    /**
     * Validates the input to see if it matches the generated code.
     * @param string $input user input
     * @param bool $caseSensitive whether the comparison should be case-sensitive
     * @return bool whether the input is valid
     */
    public function validate($input, $caseSensitive)
    {
        $code = $this->getVerifyCode();
        $valid = $caseSensitive ? ($input === $code) : strcasecmp($input, $code) === 0;
        $session = Yii::$app->getSession();
        $session->open();
        $name = $this->getSessionKey() . 'count';
        $session[$name] += 1;
        if ($valid || $session[$name] > $this->testLimit && $this->testLimit > 0) {
            $this->getVerifyCode(true);
        }

        return $valid;
    }

    /**
     * Generates a new verification code.
     * @return string the generated verification code
     */
    protected function generateVerifyCode()
    {
        return $this->captchaBuilder->getPhrase();
    }

    /**
     * Returns the session variable name used to store verification code.
     * @return string the session variable name
     */
    protected function getSessionKey()
    {
        return '__captcha/' . $this->getUniqueId();
    }

    /**
     * Sets the HTTP headers needed by image response.
     */
    protected function setHttpHeaders()
    {
        Yii::$app->getResponse()->getHeaders()
            ->set('Pragma', 'public')
            ->set('Expires', '0')
            ->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->set('Content-Transfer-Encoding', 'binary')
            ->set('Content-type', 'image/jpeg');
    }

    /**
     * Normalize the length property
     * 
     * @return int
     */
    protected function normalizeLength()
    {
        if (is_array($this->length)) {
            $minLength = (int) $this->length[0];
            $maxLength = (int) $this->length[1];
        } else {
            $minLength = (int) $this->length;
            $maxLength = (int) $this->length;
        }

        if ($minLength > $maxLength) {
            $maxLength = $minLength;
        }
        if ($minLength < 3) {
            $minLength = 3;
        }
        if ($maxLength > 20) {
            $maxLength = 20;
        }

        return random_int($minLength, $maxLength);
    }
}
