<?php

namespace juliardi\captcha;

use Gregwar\Captcha\CaptchaBuilder;
use yii\base\Action;
use Yii;
use Gregwar\Captcha\PhraseBuilder;

class CaptchaAction extends Action {

    /**
     * Captcha image width
     *
     * @var integer
     */
    public $width = 150;

    /**
     * Captcha image height
     *
     * @var integer
     */
    public $height = 40;

    /**
     * Captcha character count
     *
     * @var integer
     */
    public $length = 5;

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


    public function init() {
        parent::init();

        $this->phraseBuilder = new PhraseBuilder($this->length);
        $this->captchaBuilder = new CaptchaBuilder(null, $this->phraseBuilder);
    }
    
    public function run() {
        $this->captchaBuilder->build($this->width, $this->height);
        $this->saveCaptcha();
        $this->setHttpHeaders();
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        $this->captchaBuilder->output();
    }

    /**
     * Saves CAPTCHA phrase to session
     *
     * @return void
     */
    protected function saveCaptcha() {
        $captchaPhrase = $this->captchaBuilder->getPhrase();
        $sessionKey = $this->getSessionKey();

        Yii::$app->session->set($sessionKey, $captchaPhrase);
    }

    /**
     * Returns the session variable name used to store verification code.
     * @return string the session variable name
     */
    protected function getSessionKey()
    {
        return '__captcha_' . $this->getUniqueId();
    }

    /**
     * Returns CAPTCHA phrase for validation
     *
     * @return string
     */
    protected function getCaptchaPhrase() {
        $sessionKey = $this->getSessionKey();
        $captchaPhrase = Yii::$app->session->get($sessionKey);

        return $captchaPhrase;
    }

    /**
     * Validates the input to see if it matches the generated code.
     * @param string $input user input
     * @param bool $caseSensitive whether the comparison should be case-sensitive
     * @return bool whether the input is valid
     */
    public function validate($input, $caseSensitive)
    {
        $captchaPhrase = $this->getCaptchaPhrase();
        $valid = $caseSensitive ? ($input === $captchaPhrase) : strcasecmp($input, $captchaPhrase) === 0;

        return $valid;
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
            ->set('Content-type', 'image/png');
    }
}