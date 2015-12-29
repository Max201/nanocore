<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\Application;


use System\Engine\NCModule;
use System\Engine\NCService;
use System\Engine\NCSitemapBuilder;
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
    static $instance = null;

    /**
     * @var Options
     */
    public $conf;

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

        // Parse URL
        $url = reset(explode('?', $url, 2));

        // If sitemap request
        if ( $url == '/sitemap.xml' ) {
            Env::$response->setContent( $this->sitemap() );
            Env::$response->headers->set('Content-Type', 'application/xml');
            Env::$response->send();
            exit;
        }

        // Call request URL
        $this->app = $this->call($url);
        if ( !$this->app || $url == '/' ) {
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

        // Call module controller
        if ( $url->seg(0) == 'control' ) {
            $url = new NCUrlSegments($url->level(1));
            $module_class = 'Control';
        } else {
            $module_class = 'Module';
        }

        $module_name = ucfirst($url->seg(0));
        $module_url = $url->level(1);

        $module_class = '\\Module\\' . $module_name . '\\' . $module_class;

        // If class does not exists
        if ( !class_exists($module_class) ) {
            return false;
        }

        return new $module_class($module_url, $this->conf->get('theme', 'default'));
    }

    /**
     * @return string
     */
    public function sitemap()
    {
        $builder = new NCSitemapBuilder([], NCSitemapBuilder::TYPE_SITEMAP_INDEX);
        $modules = $this->load('Module')->modules('all');

        foreach ( $modules as $mdl_dir ) {
            /** @var NCModule $class_name */
            $class_name = '\\Module\\' . $mdl_dir . '\\Module';
            if ( !class_exists($class_name) || !$class_name::SITEMAP ) {
                continue;
            }

            $builder->add_sitemap('/' . strtolower($mdl_dir) . '/sitemap.xml');
        }

        return strval($builder);
    }

    /**
     * @param $base_uri
     * @return NCService
     */
    public static function instance($base_uri = null)
    {
        if ( !static::$instance ) {
            static::$instance = new static($base_uri);
        }

        return static::$instance;
    }
} 