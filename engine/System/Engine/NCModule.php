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
    const SITEMAP = false;

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
     * @param $no_output
     */
    public function __construct($url, $theme = 'default', $namespace = '', $no_output = false)
    {
        // Authentication
        $this->auth = NCService::load('User.Auth');
        $this->user = $this->auth->identify(Env::$request->cookies->get('sess'));

        if ( $no_output === false ) {
            // Renderring
            /** @var Theme view */
            $this->view = NCService::load('Render.Theme', [$theme]);

            // Translation
            /** @var Translate lang */
            $this->lang = NCService::load('Application.Translate');
        }

        // Module level routing
        /** @var NCRouter map */
        $this->map = new NCRouter($this, $namespace);

        // Adding sitemap to urls
        $this->map->addRoute('sitemap.xml', [$this, 'sitemap'], 'sitemap');

        if ( $no_output === false ) {
            // Register reverse url filter
            $this->view->twig->addFilter(new \Twig_SimpleFilter('url', [$this->map, 'reverse_filter']));

            // Register translate filters
            $this->view->twig->addFilter(new \Twig_SimpleFilter('lang', [$this->lang, 'translate']));
            $this->view->twig->addFilter(new \Twig_SimpleFilter('dlang', [$this->lang, 'translate_date']));

            // Assign user
            $this->view->assign('user', $this->user);

            // Loading modules globals
            $this->view->load_globals($this, $this->lang);
        }

        // Build current module map
        $this->route();

        /** @var NCRoute $route */
        $route = $this->map->match($url);

        // Bufferization content
        if ( $this->access() && $no_output === false ) {
            if ( is_callable($route->callback) ) {
                ob_start();
                $this->configure();
                if ( strpos($url, 'sitemap.xml') > -1 ) {
                    Env::$response->headers->set('Content-Type', 'application/xml');
                    $response = call_user_func($route->callback, new NCSitemapBuilder(), $this->map);
                    $response = strval($response);
                } else {
                    $response = call_user_func($route->callback, Env::$request, $route->matches);
                }

                $buffer = ob_get_clean();
                Env::$response->setContent(!is_null($response) ? $response : $buffer);
            } else {
                $this->error404(Env::$request);
            }
        } elseif ( $no_output === false ) {
            $this->error403(Env::$request);
        }

        if ( $no_output === false ) {
            // Flush content
            Env::$response->send();
        }
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
     * @param NCSitemapBuilder $builder
     * @return NCSitemapBuilder
     */
    public function sitemap(NCSitemapBuilder $builder)
    {
        return $builder;
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

    /**
     * @param $array
     * @param bool $pretty_print
     * @return string
     */
    static function json_response($array, $pretty_print = false)
    {
        Env::$response->headers->set('Content-Type', 'application/json');
        return json_encode($array, $pretty_print ? JSON_PRETTY_PRINT : 0);
    }
} 