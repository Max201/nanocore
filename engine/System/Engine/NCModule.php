<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Engine;


use Service\Render\Theme;
use Service\User\Auth;
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
     * @var Auth
     */
    protected $auth;

    /**
     * @var \User
     */
    protected $user;

    /**
     * @param $url
     */
    public function __construct($url)
    {
        // Authentication
        $this->auth = NCService::load('User.Auth');
        $this->user = $this->auth->identify(Env::$request->cookies->get('session'));

        // Renderring
        /** @var Theme view */
        $this->view = NCService::load('Render.Theme', ['admin']);

        // Subrouting
        $this->map = new NCRouter($this);
        $this->urls();

        /** @var NCRoute $route */
        $route = $this->map->match($url);

        // Call method
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
    public function error404(Request $request)
    {
        return $this->view->render('@assets/not_found.twig');
    }
} 