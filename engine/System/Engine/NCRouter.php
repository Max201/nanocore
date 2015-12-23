<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Engine;


use System\Environment\Arguments;


/**
 * Class NCRouter
 * @package System\Engine
 */
class NCRouter
{
    /**
     * @var array
     */
    private $patterns = [];

    /**
     * @var array
     */
    private $routes = [];

    /**
     * @var string
     */
    private $module = '';

    /**
     * @var string
     */
    private $module_namespace = '';

    /**
     * @param NCModule $module
     * @param string $namespace
     */
    public function __construct(NCModule $module = null, $namespace = '')
    {
        if ( !is_null($module) ) {
            $this->module = strtolower(explode('\\', get_class($module))[1]);
        }

        if ( substr($this->module, strlen($this->module) - 1, 1) != '/' ) {
            $this->module .= '/';
        }

        $this->setNameSpace($namespace);
    }

    /**
     * @param string $ns
     */
    public function setNameSpace($ns = '')
    {
        $this->module_namespace = $ns ? '/' . $ns . '/' : '/';
    }

    /**
     * @param string $match
     * @param $callback
     * @param $name
     */
    public function addRoute($match, $callback, $name)
    {
        $this->routes[$match] = [$callback, $name];
    }

    /**
     * @param string $pattern
     * @param $callback
     * @param $name
     */
    public function addPattern($pattern, $callback, $name)
    {
        $this->patterns[$pattern] = [$callback, $name];
    }

    /**
     * @param $name
     * @param null $args
     * @return NCRoute
     */
    public function reverse_filter($name, $args = null)
    {
        if ( is_null($args) ) {
            $args = [];
        }

        if ( !($args instanceof Arguments) ) {
            $args = new Arguments($args);
        }

        return $this->reverse($name, $args);
    }

    /**
     * @param $name
     * @param Arguments|null $arguments
     * @return NCRoute
     * @throws \Exception
     */
    public function reverse($name, Arguments $arguments = null)
    {
        if (is_null($arguments)) {
            $arguments = new Arguments();
        }

        // Compare routes
        foreach ( $this->routes as $route => $data) {
            list($callback, $route_name) = $data;
            if ($route_name == $name) {
                return new NCRoute($this->module_namespace . $this->module . $route, $route, $callback, $data[1]);
            }
        }

        // Compare patterns
        foreach ( $this->patterns as $pattern => $data ) {
            list($callback, $route_name) = $data;

            $args = [];
            preg_match_all('/<(.+?)>/', $pattern, $vars);

            // Compare with source
            if ($route_name == $name) {
                for ( $i = 0; $i < count($vars[0]); $i++ ) {
                    $full = '<' . $vars[1][$i] . '>';
                    list($key, $mask) = explode(':', $vars[1][$i]);
                    $args[$key] = $arguments->get($key);
                    $route = str_replace($full, $args[$key], $pattern);
                    return new NCRoute($this->module_namespace . $this->module . $route, $pattern, $callback, $route_name);
                }
            }
        }

        throw new \Exception('Reverse route for name ' . $name . ' not found');
    }

    /**
     * @param $source
     * @return null|NCRoute
     */
    public function match($source)
    {
        // Compare routes
        foreach ( $this->routes as $route => $data) {
            if ( $source == $route ) {
                list($callback, $name) = $data;

                return new NCRoute($source, $route, $callback, $name);
            }
        }

        // Compare patterns
        foreach ( $this->patterns as $pattern => $data ) {
            list($callback, $name) = $data;

            $args = [];
            preg_match_all('/<(.+?)>/', $pattern, $vars);
            // Compile pattern to regex
            for ( $i = 0; $i < count($vars[0]); $i++ ) {
                $src = $vars[0][$i];
                list($key, $mask) = explode(':', $vars[1][$i]);
                $args[$key] = null;
                $pattern = str_replace($src, '(' . $mask . ')', $pattern);
            }

            $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/i';
            // Compare with source
            preg_match_all($pattern, $source, $matches);
            if ( $matches && !empty($matches[0]) ) {
                $args = new Arguments($args);
                $args->values($matches[1]);

                return new NCRoute($source, $pattern, $callback, $name, $args);
            }
        }

        return new NCRoute($source, 'all', '\System\Engine\NCModule::error404', 'error.404');
    }
} 