<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\Application;


include_once ROOT . S . 'engine' . S . 'Service' . S . 'Application' . S . 'kcaptcha' . S . 'kcaptcha.php';


use System\Engine\NCService;
use System\Environment\Env;


/**
 * Class Captcha
 * @package Service\Application
 */
class Captcha extends NCService
{
    /**
     * @var Captcha
     */
    private static $instance;

    /**
     * @return Captcha
     */
    static function instance()
    {
        if ( !static::$instance ) {
            static::$instance = new Captcha();
        }

        return static::$instance;
    }

    /**
     * Render captcha
     */
    public function render()
    {
        Env::$response->headers->set('Content-Type', 'image/jpeg');
        Env::$response->sendHeaders();
        $captcha = new \KCAPTCHA();
        $_SESSION['captcha_key'] = strtolower($captcha->getKeyString());
        exit;
    }

    /**
     * Validate captcha
     *
     * @param $key
     * @return bool
     */
    public function is_valid($key)
    {
        if ( isset($_SESSION['captcha_key']) && $_SESSION['captcha_key'] == strtolower(trim($key)) ) {
            return true;
        }

        return false;
    }
} 