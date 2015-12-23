<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Engine;


class NCControl extends NCModule
{
    /**
     * @param $url
     * @param $theme
     */
    public function __construct($url, $theme = 'default')
    {
        parent::__construct($url, 'admin');
    }
}