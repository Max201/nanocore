<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\SocialMedia;


use System\Engine\NCService;
use System\Environment\Options;


/**
 * Class Google
 * @package Service\SocialMedia
 */
class Google extends NCService
{
    use API;

    /**
     * Social settings
     */
    const CONFIG = 'SocialMedia.setup';

    /**
     * @var Google
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
        $this->conf = $this->config('google');
    }

    /**
     * @return Google
     */
    static function instance()
    {
        if ( !static::$instance ) {
            static::$instance = new Google();
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
        $this->conf['key'] = $opt->get('key');
        return $this->update_config('google', $this->conf->getArrayCopy());
    }

    /**
     * @return bool
     */
    public function active()
    {
        return $this->conf->get('key');
    }

    /**
     * @return bool
     */
    public function configured()
    {
        return $this->conf->get('key');
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
     * @param $url
     * @param array $data
     * @return mixed
     */
    public function request($url, $data = [])
    {
        $data['key'] = $this->conf->get('key');
        return static::GET($url, $data);
    }
}