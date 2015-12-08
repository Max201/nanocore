<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Engine;


use Service\Render\Theme;
use Symfony\Component\HttpFoundation\Request;
use System\Environment\Env;


class NCModule
{
    /**
     * @var NCRouter
     */
    protected $map;

    /**
     * @var Theme
     */
    protected $view;

    /**
     * @param $url
     */
    public function __construct($url)
    {
        $this->view = NCService::load('Render.Theme', ['default']);
        $this->map = new NCRouter($this);
        $this->urls();

        /** @var NCRoute $route */
        $route = $this->map->match($url);

        if ( is_callable($route->callback) ) {
            ob_start();
            $response = call_user_func($route->callback, Env::$request, $route->matches);
            $buffer = ob_get_clean();

            Env::$response->setContent($response ? $response : $buffer);
            Env::$response->sendHeaders();
            Env::$response->sendContent();
        } else {
            $this->error404(Env::$request);
        }
    }

    /**
     * Create module route map
     */
    public function urls()
    {

    }

    /**
     * Build sitemap of your module
     *
     * @param $builder
     */
    public function url_map($builder)
    {

    }

    /**
     * Page not found
     */
    public static function error404(Request $request)
    {
        echo 'Page not found!';
    }
} 