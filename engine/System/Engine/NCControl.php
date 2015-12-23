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
        // Set default admin theme & Namespace
        parent::__construct($url, 'admin', 'control');
    }

    /**
     * @return bool
     */
    public function access()
    {
        return $this->user ? $this->user->can('use_admin') : false;
    }
}