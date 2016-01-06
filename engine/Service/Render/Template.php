<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\Render;


trait Template
{
    /**
     * @var \Twig_Environment
     */
    public $twig;

    /**
     * Render & Display selected template
     *
     * @param $template
     * @param null $context
     */
    public function display($template, $context = null)
    {
        $this->twig->display($template, $context);
    }

    /**
     * Render selected template
     *
     * @param $template
     * @param null $context
     * @return mixed
     */
    public function render($template, $context = null)
    {
        return $this->twig->render($template, $context);
    }

    /**
     * Adding new global variable
     *
     * @param $varname
     * @param null $value
     * @return mixed
     */
    public function assign($varname, $value = null)
    {
        $this->twig->addGlobal($varname, $value);
    }

    /**
     * Get existing global template variable
     * or return default value
     *
     * @param $varname
     * @param null $default
     * @return null
     */
    public function get($varname, $default = null)
    {
        $globals = $this->twig->getGlobals();
        return isset($globals[$varname]) ? $globals[$varname] : $default;
    }
} 