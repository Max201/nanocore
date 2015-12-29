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

        // Module level routing
        $this->map = new NCRouter($this, $namespace);

        // Adding sitemap to urls
        $this->map->addRoute('sitemap.xml', [$this, 'sitemap'], 'sitemap');

        // Build current module map
        $this->route();

        // Translation
        $this->lang = NCService::load('Application.Translate');

        // Renderring
        $this->view = NCService::load('Render.Theme', [$theme]);

        // Register reverse url filter
        $this->view->twig->addFilter(new \Twig_SimpleFilter('url', [$this->map, 'reverse_filter']));

        // Register translate filter
        $this->view->twig->addFilter(new \Twig_SimpleFilter('lang', [$this->lang, 'translate']));

        // Assign user
        $this->view->assign('user', $this->user);

        // Loading modules globals
        $this->view->load_globals($this, $this->lang);

        /** @var NCRoute $route */
        $route = $this->map->match($url);

        // Bufferization content
        if ( $this->access() ) {
            if ( is_callable($route->callback) ) {
                ob_start();
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
     * Globalize array into templates
     *
     * @param NCModule $module
     * @param Theme $view
     * @param Translate $lang
     * @return array|NCBlock[]
     */
    static function globalize(NCModule $module, Theme $view, Translate $lang)
    {
        return [];
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