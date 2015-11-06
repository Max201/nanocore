<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Engine;


use System\Environment\Response;

class NCModule
{
    /**
     * @var NCRouter
     */
    protected $map;

    /**
     * @param $url
     */
    public function __construct($url)
    {
        $this->map = new NCRouter();
        $this->urls();

        /** @var NCRoute $route */
        $route = $this->map->match($url);
        if ( is_callable($route->callback) ) {
            ob_start();
            $response = call_user_func($route->callback, Response::instance(), $route->matches);
            $buffer = ob_get_clean();

            Response::instance()->setContent($response ? $response : $buffer);
            Response::instance()->output();
        } else {
            die(404);
        }
    }

    /**
     * Create module route map
     */
    public function urls()
    {

    }

    /**
     * Page not found
     */
    public static function error404(Response $response)
    {
        echo 'Page not found!'; die;
    }
} 