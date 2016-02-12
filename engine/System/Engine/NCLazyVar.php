<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Engine;


class NCLazyVar extends \ArrayObject
{
    /**
     * @var array
     */
    private $cache = [];

    /**
     * @param array $array
     * @throws \Exception
     */
    public function __construct($array = [])
    {
        foreach ( $array as $call => $func ) {
            if ( !is_callable($func) ) {
                throw new \Exception($func . ' is not callable');
            }

            if ( $call[0] == '$' ) {
                $this->add(substr($call, 1), $func, true);
            } else {
                $this->add($call, $func);
            }
        }
    }

    /**
     * @param $name
     * @param $func
     * @param bool $cache
     * @throws \Exception
     */
    public function add($name, $func, $cache = false)
    {
        if ( !is_callable($func) ) {
            throw new \Exception($func . ' is not callable');
        }

        parent::offsetSet($name, [
            'func'  => $func,
            'cache' => $cache
        ]);
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function get($name)
    {
        if ( !parent::offsetExists($name) ) {
            return null;
        }

        // Get call data
        $data = parent::offsetGet($name);

        // Return from the cache
        if ( $data['cache'] && isset($this->cache[$name]) ) {
            return $this->cache[$name];
        }

        // Get new value
        $value = $data['func']();
        if ( $data['cache'] ) {
            $this->cache[$name] = $value;
        }

        return $value;
    }

    /**
     * @param mixed $index
     * @return mixed|null
     */
    public function offsetGet($index)
    {
        return $this->get($index);
    }

    /**
     * @param mixed $index
     * @param mixed $callable
     * @throws \Exception
     */
    public function offsetSet($index, $callable)
    {
        $this->add($index, $callable);
    }
} 