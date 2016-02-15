<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\SocialMedia;


use System\Engine\NCService;
use System\Environment\Options;


/**
 * Class GA
 * @package Service\SocialMedia
 */
class GA extends NCService
{
    use API;

    /**
     * Social settings
     */
    const CONFIG = 'SocialMedia.setup';

    /**
     * @var Vkontakte
     */
    static $instance;

    /**
     * @var null
     */
    static $redirect_uri = null;

    /**
     * @var Options
     */
    public $conf;

    /**
     * Initialize service
     */
    public function __construct()
    {
        $this->conf = $this->config('ga');
    }

    /**
     * @return Vkontakte
     */
    static function instance()
    {
        if ( !static::$instance ) {
            static::$instance = new GA();
        }

        return static::$instance;
    }

    /**
     * @param array $config
     * @return bool
     */
    public function setup($config)
    {
        $opt = new Options($config);
        $this->conf['id'] = $opt->get('id');
        return $this->update_config('ga', $this->conf->getArrayCopy());
    }

    /**
     * @return bool
     */
    public function active()
    {
        return $this->conf->get('id');
    }

    /**
     * @return bool
     */
    public function configured()
    {
        return $this->conf->get('id');
    }

    /**
     * @param $redirect
     * @return string
     */
    public function authorize_url($redirect)
    {
        return null;
    }

    /**
     * @return string
     */
    public function code()
    {
        if ( $this->configured() ) {
            return "<script>(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');ga('create', '{$this->conf->get('id')}', 'auto');ga('send', 'pageview');</script>";
        }

        return null;
    }
}