<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\Application;


use System\Engine\NCModule;
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

    /**
     * @var Application
     */
    static $instance;

    /**
     * @var Options
     */
    private $conf;

    /**
     * @var NCModule|bool
     */
    private $app;

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
            Env::$response->setContent(file_get_contents(ROOT . S . 'theme' . S . 'assets' . S . 'not_found.html'));
            Env::$response->send();
        }
    }

    /**
     * @param $route
     * @return bool|NCModule
     */
    public function call($route)
    {
        // Split URL By sleshes
        $url = new NCUrlSegments($route);
        $default_module = $this->conf->get('default_module');

        // Define module name & module class
        $module_name = ucfirst($url->seg(static::MODULE_SEGMENT, $default_module));
        $module_class = 'Module';

        // For admin panel
        if ($module_name == 'Control') {
            $module_name = ucfirst($url->seg(static::MODULE_SEGMENT+1, $default_module));
            $module_class = 'Control';
        }

        $module = '\\Module\\' . $module_name . '\\' . $module_class;
        // If class does not exists
        if ( !class_exists($module) ) {
            return false;
        }

        // Call module controller
        return new $module(
            $url->level(static::MODULE_URL_LEVEL + ($module_class == 'Control' ? 1 : 0)),
            $this->conf->get('theme', 'default')
        );
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