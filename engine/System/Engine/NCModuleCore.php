<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Engine;


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
     * @return NCService
     */
    static function load_lang($pack = null)
    {
        if ( !static::$lang ) {
            static::$lang = NCService::load('Application.Translate');
        }

        return static::$lang;
    }
} 