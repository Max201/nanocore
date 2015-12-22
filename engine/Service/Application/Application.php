<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\Application;


use System\Engine\NCService;
use System\Engine\NCUrlSegments;
use System\Environment\Env;
use System\Environment\Options;


/**
 * Class Application
 * @package Service\Application
 */
class Application extends NCService
{
    const CONFIG = 'Application.config';
    const MODULE_SEGMENT = 0;
    const MODULE_URL_LEVEL = 0;

    static $instance;

    /**
     * @var Options
     */
    private $conf;

    /**
     * @param $url
     */
    public function __construct($url)
    {
        $this->conf = $this->config('application');

        // Call request URL
        $this->app = $this->call($url);
        if ( !$this->app ) {
            $this->app = $this->call($this->conf->get('home', '/'));
        }

        // If no module found
        if ( !$this->app ) {
            Env::$response->setStatusCode(404, 'Page not found');
            die;
        }
    }

    public function call($route)
    {
        // Get module from url
        $url = new NCUrlSegments($route);
        $default_module = $this->conf->get('default_module');
        $module = '\\Module\\' . ucfirst($url->seg(static::MODULE_SEGMENT, $default_module)) . '\\Module';

        // If class does not exists
        if ( !class_exists($module) ) {
            return false;
        }

        // Call module controller
        return new $module( $url->level(static::MODULE_URL_LEVEL) );
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