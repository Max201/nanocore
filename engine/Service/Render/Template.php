<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\Render;


trait Template
{
    /**
     * Render & Display selected template
     *
     * @param $template
     * @param null $context
     */
    public function display($template, $context = null)
    {
        echo $this->twig->render($template, $context);
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
        return $this->twig->addGlobal($varname, $value);
    }
} 