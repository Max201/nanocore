<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Engine;


use Service\Render\Theme;


/**
 * Class NCWidget
 * Admin panel widget for dashboard
 *
 * @package System\Engine
 */
class NCWidget
{
    /**
     * @var string
     */
    private $name = 'Untitled';

    /**
     * @var string
     */
    private $template = '';

    /**
     * @var array
     */
    private $context = [];

    /**
     * @param $name
     * @param $template
     * @param array $context
     */
    public function __construct($name, $template, $context = [])
    {
        $this->name = $name;
        $this->template($template);
        $this->context($context);
    }

    /**
     * @param $context
     * @return $this|array
     */
    public function context($context)
    {
        if ( is_null($context) ) {
            return $this->context;
        }

        $this->context = $context;
        return $this;
    }

    /**
     * @param null $template_name
     * @return $this|string
     */
    public function template($template_name = null)
    {
        if ( is_null($template_name) ) {
            return $this->template;
        }

        $this->template = $template_name;
        return $this;
    }

    /**
     * @param Theme $view
     * @return string
     */
    public function render(Theme $view)
    {
        $this->context['name'] = $this->name;
        return $view->render($this->template, $this->context);
    }
} 