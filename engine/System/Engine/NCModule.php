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
     * @param $theme
     */
    public function __construct($url, $theme = 'default')
    {
        // Authentication
        $this->auth = NCService::load('User.Auth');
        $this->user = $this->auth->identify(Env::$request->cookies->get('session'));

        // Renderring
        /** @var Theme view */
        $this->view = NCService::load('Render.Theme', [$theme]);

        // Subrouting
        $this->map = new NCRouter($this);
        $this->urls();

        // Adding sitemap to urls
        $this->map->addRoute('sitemap.xml', [$this, 'sitemap'], 'sitemap');

        /** @var NCRoute $route */
        $route = $this->map->match($url);

        // Bufferization content
        ob_start();
        if ( $this->access() ) {
            $response = is_callable($route->callback) ? call_user_func($route->callback, Env::$request, $route->matches) : $this->error404(Env::$request);
        } else {
            $response = $this->error403(Env::$request);
        }

        $buffer = ob_get_clean();

        // Flush content
        Env::$response->setContent($response ? $response : $buffer);
        Env::$response->sendHeaders();
        Env::$response->sendContent();
    }

    /**
     * Create module route map
     */
    public function route()
    {

    }

    /**
     * Build sitemap of your module
     *
     * @param $builder
     */
    public function sitemap($builder)
    {

    }

    /**
     * Checks the access to requested page
     *
     * @return bool
     */
    public function access()
    {
        return true;
    }

    /**
     * Page not found
     */
    public function error404(Request $request)
    {
        Env::$response->setStatusCode(404, 'Page not found');
        return $this->view->render('@assets/not_found.html');
    }

    /**
     * Page not found
     */
    public function error403(Request $request)
    {
        Env::$response->setStatusCode(403, 'Permission denied');
        return $this->view->render('@assets/permission_denied.html');
    }
} 