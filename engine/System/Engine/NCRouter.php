<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Engine;


use System\Environment\NamedVarBag;

class NCRouter
{
    /**
     * @var array
     */
    private $patterns = [];

    /**
     * @var array
     */
    private $matches = [];

    /**
     * @var array
     */
    private $routes = [];

    /**
     * @param string $match
     * @param $callback
     */
    public function addRoute($match, $callback)
    {
        $this->routes[$match] = $callback;
    }

    /**
     * @param string $regex
     * @param $callback
     */
    public function addMatch($regex, $callback)
    {
        $this->matches[$regex] = $callback;
    }

    /**
     * @param string $pattern
     * @param $callback
     */
    public function addPattern($pattern, $callback)
    {
        $this->patterns[$pattern] = $callback;
    }

    /**
     * @param $source
     * @return null|NCRoute
     */
    public function match($source)
    {
        // Compare routes
        foreach ( $this->routes as $route => $callback) {
            if ( $source == $route ) {
                return new NCRoute($source, $route, $callback);
            }
        }

        // Compare regex
        foreach ( $this->matches as $regex => $callback ) {
            preg_match_all($regex, $source, $matches);
            if ( $matches && !empty($matches[0]) ) {
                return new NCRoute($source, $regex, $callback, $matches);
            }
        }

        // Compare patterns
        foreach ( $this->patterns as $pattern => $callback ) {
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
                $args = new NamedVarBag($args);
                $args->values($matches[1]);

                return new NCRoute($source, $pattern, $callback, $args);
            }
        }

        return new NCRoute($source, 'all', '\System\Engine\NCModule::error404');
    }
} 