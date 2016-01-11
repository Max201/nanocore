<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\SocialMedia;


use System\Engine\NCService;
use System\Environment\Options;


/**
 * Class SocialMedia
 * @package Service\SocialMedia
 */
class SocialMedia extends NCService
{
    const CONFIG = 'SocialMedia.setup';

    /**
     * @var SocialMedia
     */
    static $instance;

    /**
     * @var Options
     */
    private $conf;

    /**
     * Create new instance
     */
    public function __construct()
    {
        $this->conf = $this->config('settings');
    }

    /**
     * @return SocialMedia
     */
    static function instance()
    {
        if ( !static::$instance ) {
            static::$instance = new SocialMedia();
        }

        return static::$instance;
    }

    /**
     * @return array
     */
    public function social_list()
    {
        return $this->conf->getArrayCopy();
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function network($id)
    {
        foreach ( $this->social_list() as $network ) {
            if ( $network['id'] == $id ) {
                $manager = $this->get_manager($id);
                $network['data'] = $manager->conf;
                $network['auth'] = $manager->authorize_url();
                return $network;
            }
        }

        return [];
    }

    /**
     * @param $id
     * @return NCService|null
     */
    public function get_manager($id)
    {
        $manager = [$this, $id];
        if ( is_callable($manager) ) {
            return call_user_func($manager);
        }

        return null;
    }

    /**
     * @return Vkontakte
     */
    public function vk()
    {
        return $this->load('SocialMedia.Vkontakte');
    }
} 