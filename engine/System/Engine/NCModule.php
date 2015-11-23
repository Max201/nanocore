<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Engine;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use System\Environment\Env;


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
     * Page not found
     */
    public static function error404(Request $request)
    {
        echo 'Page not found!';
    }
} 