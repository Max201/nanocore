<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\Render;


use Service\Module\Module;
use Service\Application\Translate;
use Symfony\Component\HttpFoundation\Request;
use System\Engine\NCBlock;
use System\Engine\NCModule;
use System\Engine\NCService;
use System\Environment\Options;


/**
 * Class Theme
 * @package Service\Render
 */
class Theme extends NCService
{
    use Template;

    /**
     * Config dir path
     */
    const CONFIG = 'Render.config';

    /**
     * @var Theme
     */
    static $instance;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $assets_url;

    /**
     * @var string
     */
    private $assets_path;

    /**
     * @var string
     */
    private $admin_url;

    /**
     * @var string
     */
    private $admin_path;

    /**
     * @var Options
     */
    private $conf;

    /**
     * @var string
     */
    private $cdn_cache_dir;

    /**
     * @var string
     */
    private $tpl_cache_dir;

    /**
     * @var \Twig_Loader_Filesystem
     */
    public $loader;

    /**
     * @var \Twig_Environment
     */
    public $twig;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        # Theme name
        $this->name = $name;
        $this->conf = $this->config('settings');

        # Theme url
        $this->url = $this->conf->get('theme_url', '');
        $this->url = str_replace('[theme]', $this->name, $this->url);

        # Theme dir
        $theme_dir = $this->conf->get('theme_dir');
        $theme_dir = str_replace('/', S, $theme_dir);
        $theme_dir = str_replace('[theme]', $this->name, $theme_dir);
        $this->path = ROOT . S . $theme_dir;

        # Assets
        $this->assets_url = $this->conf->get('asset_url');
        $this->assets_path = ROOT . S . trim($this->conf->get('asset_dir'), S);

        # Admin
        $this->admin_url = $this->conf->get('admin_url');
        $this->admin_path = ROOT . S . trim($this->conf->get('admin_dir'), S);

        # CDN Cache dir
        $this->cdn_cache_dir = ROOT . S . $this->conf->get('cdn_cache');

        # Templates Cache dir
        $this->tpl_cache_dir = ROOT . S . trim($this->conf->get('cache_dir'), S);

        # Twig
        $this->loader = new \Twig_Loader_Filesystem([$this->path]);

        # Env path
        $this->loader->addPath($this->assets_path, 'assets');
        $this->loader->addPath($this->admin_path, 'admin');

        # Initialize twig
        $this->twig = new \Twig_Environment($this->loader, array(
            'cache' => $this->tpl_cache_dir,
            'auto_reload' => true,
        ));

        # Default vars
        $this->twig->addGlobal('THEME', [
            'url' => $this->url,
            'dir' => $this->path
        ]);

        # Custom filters
        $custom_filters = $this->config('settings')->get('custom_filters', []);
        foreach ($custom_filters as $filter => $callback) {
            # Register current class callback
            if ( is_array($callback) and count($callback) > 1 and !is_callable($callback) ) {
                $callback[0] = $this;
            }

            $filter = new \Twig_SimpleFilter($filter, $callback);
            $this->twig->addFilter($filter);
        }
    }

    /**
     * @param null $name
     * @return Theme
     */
    public static function instance($name = null)
    {
        if ( !static::$instance ) {
            static::$instance = new static($name);
        }

        return static::$instance;
    }

    /**
     * @param $path
     * @param $dir
     * @return string
     */
    public function filepath($path, $dir = null)
    {
        return $this->path . S . (!is_null($dir) ? str_replace('.', S, $dir) . S : '') . trim($path, S);
    }

    /**
     * @param $path
     * @param $dir
     * @return mixed
     */
    public function fileurl($path, $dir = null)
    {
        return $this->url . '/' . (!is_null($dir) ? str_replace('.', '/', $dir) . '/' : '') . $path;
    }

    /**
     * @param $path
     * @param $dir
     * @return string
     */
    public function assetpath($path, $dir = null)
    {
        return $this->assets_path . S . (!is_null($dir) ? str_replace('.', S, $dir) . S : '') . trim($path, S);
    }

    /**
     * @param $path
     * @param $dir
     * @return mixed
     */
    public function asseturl($path, $dir = null)
    {
        return $this->assets_url . '/' . (!is_null($dir) ? str_replace('.', '/', $dir) . '/' : '') . $path;
    }

    /**
     * @param $name
     * @param $file_ends
     * @param $version
     * @return string
     */
    public function lib($file_ends, $name, $version = Repository::VERSION_LATEST)
    {
        $repository = new Repository($this->cdn_cache_dir);
        $repository = $repository->get_repository($name, $version);
        if ( ! $repository ) {
            return '';
        }

        $len = strlen($file_ends);
        $files = $repository['files'];
        foreach ($files as $file => $path) {
            if ( substr($file, -1 * $len) == $file_ends ) {
                return $path;
            }
        }

        return '';
    }

    /**
     * @param Request $request
     * @param Translate $lang
     */
    public function load_globals(NCModule $module, Translate $lang)
    {
        /** @var array $modules */
        $modules = NCService::load('Module')->modules('all');
        foreach ( $modules as $mdl_dir ) {
            $globalize = '\\Module\\' . $mdl_dir . '\\Module::globalize';
            if ( !is_callable($globalize) ) {
                continue;
            }

            $globals = call_user_func($globalize, $module, $this, $lang);
            if ( $globals ) {
                foreach ( $globals as $k => $v ) {
                    if ( is_int($k) ) {
                        break;
                    }

                    if ( $v instanceof NCBlock ) {
                        $this->assign($k, $v->render($this));
                        continue;
                    }

                    $this->assign($k, $v);
                }
            }
        }
    }
} 