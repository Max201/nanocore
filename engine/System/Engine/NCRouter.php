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
        $args = func_get_args();
        if ( $args ) {
            array_shift($args);
        }

        if ( is_null($args) || !$args ) {
            $args = [];
        }

        return $this->reverse($name, $args);
    }

    /**
     * @param $name
     * @param array|null $arguments
     * @return NCRoute
     * @throws \Exception
     */
    public function reverse($name, $arguments = null)
    {
        if (is_null($arguments)) {
            $arguments = [];
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

            // Compare with source
            if ($route_name == $name) {
                $source_data = $this->_combine_args($pattern, $arguments);
                return new NCRoute(
                    $this->module_namespace . $this->module . strtr($pattern, $source_data['_replace']),
                    $pattern,
                    $callback,
                    $route_name
                );
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

                return new NCRoute($source, $route, $callback, $name, new Arguments());
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

            $pattern = '#^' . str_replace('/', '\/', $pattern) . '$#i';

            // Compare with source
            preg_match_all($pattern, $source, $matches);
            if ( $matches && !empty($matches[0]) ) {
                $values = array_map(function($i){ return $i[0]; }, $matches);
                array_shift($values);
                $args = new Arguments($args);
                $args->values($values);

                return new NCRoute($source, $pattern, $callback, $name, $args);
            }
        }

        return new NCRoute($source, 'all', '\System\Engine\NCModule::error404', 'error.404');
    }

    /**
     * @param $pattern
     * @param array $values
     * @return Arguments
     */
    private function _combine_args($pattern, $values = [])
    {
        if ( $values instanceof Arguments ) {
            $values = $values->getArrayCopy();
        }

        preg_match_all('/<(.+?)>/', $pattern, $vars);
        $keys = array_map(function($i){ return reset(explode(':', $i)); }, $vars[1]);
        $result = array_combine($keys, $values);
        $result['_replace'] = array_combine($vars[0], $values);
        return $result;
    }
} 