<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\Application;


use System\Engine\NCService;
use System\Engine\NCUrlSegments;


/**
 * Class Application
 * @package Service\Application
 */
class Application extends NCService
{
    const CONFIG = 'Application.config';
    const MODULE_SEGMENT = 0;
    const MODULE_URL_LEVEL = 0;

    /**
     * @param $url
     */
    public function __construct($url)
    {
        $url = new NCUrlSegments($url);
        $default_module = $this->config('application')->get('default_module');
        $module = '\\Module\\' . ucfirst($url->seg(static::MODULE_SEGMENT, $default_module)) . '\\Module';
        if ( !class_exists($module) ) {
            die(404);
        }

        $this->app = new $module( $url->level(static::MODULE_URL_LEVEL) );
    }

    /**
     * @param $base_uri
     * @return NCService
     */
    public static function instance($base_uri)
    {
        if ( is_null(static::$instance) ) {
            static::$instance = new static($base_uri);
        }

        return static::$instance;
    }
} 