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
     * Static urls
     * @var array
     */
    static $static_routes = [
        '/sitemap.xml'  => '\\Service\\Application\\Application::route_sitemap',
        '/code.jpg'     => '\\Service\\Application\\Application::route_captcha',
        '/robots.txt'   => '\\Service\\Application\\Application::robots',
    ];

    /**
     * @var Settings
     */
    public $conf;

    /**
     * @var IPWall
     */
    public $ipwall;

    /**
     * @var NCModule|bool
     */
    private $app;

    /**
     * @param $url
     */
    public function __construct($url)
    {
        // Load config
        $this->conf = $this->load('Application.Settings');

        // Assign kernel variable
        Env::$kernel = &$this;

        // IP Wall
        $this->ipwall = new IPWall($this);
        if ( !$this->ipwall->allowed(Analytics::ip()) ) {
            $content = file_get_contents(ROOT . S . 'theme' . S . 'assets' . S . 'ipwall.twig');

            Env::$response->setStatusCode(403, 'Not allowed');
            Env::$response->setContent(str_replace('[ip]', Analytics::ip(), $content));
            Env::$response->send();
            die;
        }

        // Parse URL
        $url = reset(explode('?', $url, 2));

        // Static application routes
        if ( array_key_exists($url, static::$static_routes) ) {
            call_user_func(static::$static_routes[$url], $this);
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
            static::$instance = new Application($base_uri);
        }

        return static::$instance;
    }

    /**
     * @param Application $app
     */
    public static function route_sitemap(Application $app)
    {
        Env::$response->setContent( $app->sitemap() );
        Env::$response->headers->set('Content-Type', 'application/xml');
        Env::$response->send();
    }

    /**
     * @param Application $app
     */
    public static function route_captcha(Application $app)
    {
        /** @var Captcha $captcha */
        $captcha = $app->load('Application.Captcha');
        $captcha->render();
    }

    /**
     * @param Application $app
     */
    public static function robots(Application $app)
    {
        /** @var Robots $captcha */
        $robots = $app->load('Application.Robots');
        $robots->render('/sitemap.xml', Env::$request->getHttpHost());
    }
} 