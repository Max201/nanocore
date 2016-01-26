<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Engine;


use Service\Application\Settings;
use Service\Application\Translate;
use Service\Render\Theme;
use Service\User\Auth;
use Symfony\Component\HttpFoundation\Request;
use System\Environment\Env;


class NCModule
{
    const SITEMAP = false;

    /**
     * Visits analytics
     *
     * @var bool
     */
    public $analytics = true;

    /**
     * @var NCRouter
     */
    public $map;

    /**
     * @var Theme
     */
    public $view;

    /**
     * @var Auth
     */
    public $auth;

    /**
     * @var \User
     */
    public $user;

    /**
     * @var Translate
     */
    public $lang;

    /**
     * @var Settings
     */
    public $settings;

    /**
     * @param $url
     * @param $theme
     * @param $namespace
     */
    public function __construct($url, $theme = 'default', $namespace = '')
    {
        // Authentication
        /** @var Auth auth */
        $this->auth = NCService::load('User.Auth');
        /** @var \User user */
        $this->user = $this->auth->identify(Env::$request->cookies->get('sess'));

        // Settings
        /** @var Settings settings */
        $this->settings = NCService::load('Application.Settings');

        // Analytics module
        if ( $this->analytics ) {
            $counter = NCService::load('Application.Analytics', [$this->user ? $this->user->id : null]);
            $counter->save();
        }

        // Renderring
        /** @var Theme view */
        $this->view = NCModuleCore::load_view($theme);

        // Translation
        /** @var Translate lang */
        $this->lang = NCModuleCore::load_lang();

        // Module level routing
        /** @var NCRouter map */
        $this->map = new NCRouter($this, $namespace);

        // Adding sitemap to urls
        $this->map->addRoute('sitemap.xml', [$this, 'sitemap'], 'sitemap');

        // Register reverse url filter
        $this->view->twig->addFilter(new \Twig_SimpleFilter('url', [$this->map, 'reverse_filter']));

        // Register translate filters
        $this->view->twig->addFilter(new \Twig_SimpleFilter('lang', [$this->lang, 'translate']));
        $this->view->twig->addFilter(new \Twig_SimpleFilter('dlang', [$this->lang, 'translate_date']));

        // Assign user
        $this->view->assign('user', $this->user ? $this->user->to_array() : []);
        $this->view->assign('group', $this->user ? $this->user->group : []);

        // Assign captcha URL
        $this->view->assign('secure_image', NCModuleCore::$captcha_url);

        // Loading modules globals
        $this->view->load_globals($this, $this->lang);

        // Disable access to banned users
        if ( $this->user->ban_time > time() || $this->user->ban_time == -1 ) {
            Env::$response->setContent(
                $this->errorBanned(
                    Env::$request,
                    $this->user->ban_reason
                )
            );
            Env::$response->send();
            return;
        }

        // Check access to current module
        if ( !$this->access() ) {
            Env::$response->setContent(
                $this->error403(Env::$request)
            );
            Env::$response->send();
            return;
        }

        // Build current module map
        $this->route();

        /** @var NCRoute $route */
        $route = $this->map->match($url);

        // Check route
        if ( !is_callable($route->callback) ) {
            Env::$response->setContent(
                $this->error404(Env::$request)
            );
            Env::$response->send();
            return;
        }

        // Bufferization content
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
     * @return bool|string
     */
    public function authenticated_only()
    {
        if ( !$this->user ) {
            return static::error403(Env::$request);
        }

        return true;
    }

    /**
     * @return bool|string
     */
    public function guest_only()
    {
        if ( $this->user ) {
            return static::error403(Env::$request);
        }

        return true;
    }

    /**
     * Page not found
     */
    public function error404(Request $request, $matches = null)
    {
        Env::$response->setStatusCode(404, 'Page not found');
        return $this->view->twig->render('@assets/not_found.twig');
    }

    /**
     * Page not found
     */
    public function error403(Request $request, $matches = null)
    {
        Env::$response->setStatusCode(403, 'Permission denied');
        return $this->view->twig->render('@assets/permission_denied.twig');
    }

    /**
     * Page not found
     */
    public function errorBanned(Request $request, $matches = null)
    {
        $reason = $this->user->ban_reason;
        if ( !$reason ) {
            $reason = $this->lang->translate('user.ban.reason_unknown');
        }

        Env::$response->setStatusCode(403, 'Permission denied');
        return $this->view->twig->render('@assets/banned.twig', [
            'reason'    => $reason,
            'ban'       => $this->user->ban_user->to_array()
        ]);
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

    /**
     * @param $uri
     * @param int $status_code
     * @param int $timeout
     */
    static function redirect_response($uri, $status_code = 302, $timeout = 0)
    {
        header('Refresh:' . $timeout . '; url=' . $uri, true, $status_code);
        return;
    }
} 