<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Engine;


class NCUrlSegments 
{
    /**
     * @var string
     */
    private $uri;

    /**
     * @param string $base_uri
     */
    public function __construct($base_uri = '/')
    {
        $this->uri = trim($base_uri, '/');
    }

    /**
     * @param int $index
     * @param string $default
     * @return null|string
     */
    public function seg($index, $default = '/')
    {
        $segments = explode('/', $this->uri);
        if ( !isset($segments[$index]) ) {
            return $default;
        }

        return $segments[$index];
    }

    /**
     * @param $level
     * @param string $default
     * @return string
     */
    public function level($level, $default = '/')
    {
        $segments = explode('/', $this->uri);
        if ( count($segments) < intval($level) ) {
            return $default;
        }

        return implode('/', array_splice($segments, -1 * (count($segments) - $level - 1)));
    }
} 