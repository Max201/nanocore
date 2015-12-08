<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Engine;


/**
 * Class NCRoute
 * @package System\Engine
 */
class NCRoute 
{
    /**
     * @var
     */
    public $name;

    /**
     * @var string
     */
    public $source;

    /**
     * @var string
     */
    public $pattern;

    /**
     * @var \ArrayObject
     */
    public $matches = [];

    /**
     * @var callable
     */
    public $callback;

    /**
     * @param $source
     * @param $pattern
     * @param $matches
     * @param $callback
     */
    public function __construct($source, $pattern, $callback, $name, $matches = [])
    {
        $this->source = $source;
        $this->callback = $callback;
        $this->pattern = $pattern;
        $this->matches = $matches;
        $this->name = $name;
    }
} 