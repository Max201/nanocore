<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\SocialMedia;
require_once 'codebird/codebird.php';


use System\Engine\NCService;
use System\Environment\Options;
use Codebird\Codebird;


/**
 * Class Twitter
 * @package Service\SocialMedia
 */
class Twitter extends NCService
{
    use API;

    /**
     * Social settings
     */
    const CONFIG = 'SocialMedia.setup';

    /**
     * @var Twitter
     */
    static $instance;

    /**
     * @var string
     */
    static $redirect_uri = null;

    /**
     * @var Options
     */
    public $conf;

    /**
     * @var Codebird
     */
    public $cb;

    /**
     * Initialize service
     */
    public function __construct()
    {
        $this->conf = $this->config('twitter');
        if ( $this->configured() ) {
            Codebird::setConsumerKey($this->conf['key'], $this->conf['secret']);
            $this->cb = Codebird::getInstance();
            $this->cb->setToken($this->conf['token'], $this->conf['token_secret']);
        }
    }

    /**
     * @return Vkontakte
     */
    static function instance()
    {
        if ( !static::$instance ) {
            static::$instance = new Twitter();
        }

        return static::$instance;
    }

    /**
     * @param array $config
     * @return bool
     */
    public function setup($config)
    {
        $this->conf['key'] = $config['key'];
        $this->conf['secret'] = $config['secret'];
        $this->conf['token'] = $config['token'];
        $this->conf['token_secret'] = $config['token_secret'];

        return $this->update_config('twitter', $this->conf->getArrayCopy());
    }

    /**
     * @return bool
     */
    public function active()
    {
        return $this->configured();
    }

    /**
     * @return bool
     */
    public function configured()
    {
        $key = $this->conf->get('key') && $this->conf->get('secret');
        $token = $this->conf->get('token') && $this->conf->get('token_secret');
        return $key && $token;
    }

    /**
     * @param null $redirect
     * @return bool
     */
    public function authorize_url($redirect = null)
    {
        return false;
    }

    /**
     * @param $tweet
     * @param null $image
     * @return bool
     */
    public function m_post($tweet, $image = null)
    {
        $status = [
            'status'    => substr($tweet, 0, 140),
        ];

        if ( !$image ) {
            return $this->cb->statuses_update($status);
        }

        $status['media[]'] = $image;
        return $this->cb->statuses_updateWithMedia($status);
    }
} 