<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Environment;


/**
 * Class Options
 * @package System\Environment
 */
class Options extends \ArrayObject
{
    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        if ( !$this->offsetExists($key) ) {
            return $default;
        }

        return $this->offsetGet($key);
    }

    /**
     * @param $key
     * @param null $newval
     */
    public function set($key, $newval = null)
    {
        $this->offsetSet($key, $newval);
    }
} 