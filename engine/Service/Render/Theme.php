<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\Render;


use System\Engine\NCService;
use System\Environment\Options;


/**
 * Class Theme
 * @package Service\Render
 */
class Theme extends NCService
{
    const CONFIG = 'Render.config';

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
     * @var Options
     */
    private $conf;

    /**
     * @var string
     */
    private $cdn_cache_dir;

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

        # Assets url
        $this->assets_url = $this->conf->get('asset_url');
        $this->assets_path = ROOT . S . trim($this->conf->get('asset_dir'), S);

        # CDN Cache dir
        $this->cdn_cache_dir = ROOT . S . $this->conf->get('cdn_cache');

        # Twig
        $this->loader = new \Twig_Loader_Filesystem([$this->path]);
        $this->twig = new \Twig_Environment($this->loader);

        # Default vars
        $this->twig->addGlobal('THEME', $this->path);

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
     * @return string
     */
    public function filepath($path, $dir = null)
    {
        return $this->path . S . (!is_null($dir) ? str_replace('.', S, $dir) . S : '') . trim($path, S);
    }

    /**
     * @param $path
     * @return mixed
     */
    public function fileurl($path, $dir = null)
    {
        return $this->url . '/' . (!is_null($dir) ? str_replace('.', '/', $dir) . '/' : '') . $path;
    }

    /**
     * @param $path
     * @return string
     */
    public function assetpath($path, $dir = null)
    {
        return $this->assets_path . S . (!is_null($dir) ? str_replace('.', S, $dir) . S : '') . trim($path, S);
    }

    /**
     * @param $path
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
} 