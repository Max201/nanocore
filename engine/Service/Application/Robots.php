<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\Application;


use System\Engine\NCService;
use System\Environment\Env;


/**
 * Class Robots
 * @package Service\Application
 */
class Robots extends NCService
{
    const CONFIG = 'Application.config';

    /**
     * @var Robots
     */
    static $instance;

    /**
     * @var \System\Environment\Options
     */
    private $rules = [];

    /**
     * Create robots TXT file
     */
    public function __construct()
    {
        $this->rules = $this->config('robots')->getArrayCopy();
    }

    /**
     * @return Robots
     */
    static function instance()
    {
        if ( !static::$instance ) {
            static::$instance = new Robots();
        }

        return static::$instance;
    }

    /**
     * Render file
     */
    public function render($sitemap, $host)
    {
        Env::$response->headers->set('Content-Type', 'text/plain');
        Env::$response->sendHeaders();

        // Assign current host
        $this->sitemap($sitemap);
        $this->host($host);

        // Render file
        foreach ( $this->rules as $ua => $rules ) {
            echo "User-Agent: " . $ua . "\n";
            foreach ( $rules as $r ) {
                echo $r . "\n";
            }

            echo "\n";
        }
    }

    public function save()
    {
        return $this->update_config('robots', $this->rules);
    }

    /**
     * @param $path
     */
    public function sitemap($path)
    {
        $this->addGlobalRule('Sitemap: ' . Env::$request->getSchemeAndHttpHost() . $path);
    }

    /**
     * @param $host
     */
    public function host($host)
    {
        $this->addGlobalRule('Host: ' . $host);
    }

    /**
     * @param $path
     * @param string $ua
     */
    public function allow($path, $ua = '*')
    {
        $this->addRule($ua, 'Allow: ' . $path);
    }

    /**
     * @param $path
     * @param string $ua
     */
    public function disallow($path, $ua = '*')
    {
        $this->addRule($ua, 'Disallow: ' . $path);
    }

    /**
     * @param $ua
     * @param $rule
     */
    public function addRule($ua, $rule)
    {
        if ( !isset($this->rules[$ua]) ) {
            $this->rules[$ua] = [];
        }

        $this->rules[$ua][] = $rule;
    }

    /**
     * @param $rule
     */
    public function addGlobalRule($rule)
    {
        foreach ( $this->rules as $ua => $rules ) {
            $this->rules[$ua][] = $rule;
        }
    }
} 