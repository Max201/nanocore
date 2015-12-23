<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Engine;


use Service\Application\Translate;
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
     * @var Translate
     */
    protected $lang;

    /**
     * @param $url
     * @param $theme
     * @param $namespace
     */
    public function __construct($url, $theme = 'default', $namespace = '')
    {
        // Authentication
        $this->auth = NCService::load('User.Auth');
        $this->user = $this->auth->identify(Env::$request->cookies->get('sess'));

        // Translation
        $this->lang = NCService::load('Application.Translate');

        // Renderring
        /** @var Theme view */
        $this->view = NCService::load('Render.Theme', [$theme]);
        $this->view->assign('user', $this->user);

        // Subrouting
        $this->map = new NCRouter($this, $namespace);
        $this->route();

        // Adding sitemap to urls
        $this->map->addRoute('sitemap.xml', [$this, 'sitemap'], 'sitemap');

        // Register reverse url filter
        $this->view->twig->addFilter(new \Twig_SimpleFilter('url', [$this->map, 'reverse_filter']));

        /** @var NCRoute $route */
        $route = $this->map->match($url);

        // Bufferization content
        ob_start();
        if ( $this->access() ) {
            if ( is_callable($route->callback) ) {
                $this->configure();
                $response = call_user_func($route->callback, Env::$request, $route->matches);
                Env::$response->setContent(!is_null($response) ? $response : ob_get_clean());
            } else {
                $this->error404(Env::$request);
            }
        } else {
            $this->error403(Env::$request);
        }

        // Flush content
        Env::$response->send();
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
     * Configuring controller
     */
    public function configure()
    {

    }

    /**
     * Page not found
     */
    public function error404(Request $request)
    {
        Env::$response->setStatusCode(404, 'Page not found');
        Env::$response->setContent(
            file_get_contents($this->view->assetpath('not_found.html'))
        );
    }

    /**
     * Page not found
     */
    public function error403(Request $request)
    {
        Env::$response->setStatusCode(403, 'Permission denied');
        Env::$response->setContent(
            file_get_contents($this->view->assetpath('permission_denied.html'))
        );
    }
} 