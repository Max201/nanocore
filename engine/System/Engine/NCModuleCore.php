<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Engine;


use Service\Application\Captcha;
use Service\Application\Translate;
use Service\Render\Theme;


/**
 * Class NCModuleCore
 * @package System\Engine
 */
class NCModuleCore
{
    private static $view;

    /**
     * @param $theme
     * @return Theme
     */
    static function load_view($theme = null)
    {
        if ( !static::$view ) {
            static::$view = NCService::load('Render.Theme', [$theme]);
        }

        return static::$view;
    }


    private static $lang;

    /**
     * @param $pack
     * @return Translate
     */
    static function load_lang($pack = null)
    {
        if ( !static::$lang ) {
            static::$lang = NCService::load('Application.Translate');
        }

        return static::$lang;
    }

    /**
     * @param $code
     * @return bool
     */
    static function verify_captcha($code)
    {
        /** @var Captcha $captcha */
        $captcha = NCService::load('Application.Captcha');
        return $captcha->is_valid($code);
    }

    /**
     * URL Of captcha image
     *
     * @var string
     */
    static $captcha_url = '/code.jpg';

    /**
     * URL Of sitemap XML-Document
     *
     * @var string
     */
    static $sitemap_url = '/sitemap.xml';
} 