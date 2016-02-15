<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Admin\SMP;


use Service\SocialMedia\GA;
use Service\SocialMedia\SocialMedia;
use Service\SocialMedia\Twitter;
use Service\SocialMedia\Vkontakte;
use System\Engine\NCService;
use System\Environment\Env;


/**
 * Class Driver
 * @package Module\Admin\SMP
 */
class Driver
{
    /**
     * @param $id
     * @param array $data
     * @return bool
     */
    public static function process($id, $data = [])
    {
        $call = '\\Module\\Admin\\SMP\\Driver::' . $id;
        if ( !is_callable($call) ) {
            return false;
        }

        return call_user_func_array($call, $data);
    }

    /**
     * @param $redirect_uri
     * @return bool|mixed|null
     */
    public static function vk($redirect_uri)
    {
        /** @var SocialMedia $smp */
        $smp = NCService::load('SocialMedia');
        /** @var Vkontakte $manager */
        $manager = $smp->vk();
        return $manager->get_token(Env::$request->get('code'), $redirect_uri);
    }

    /**
     * @param $redirect_uri
     * @return bool|mixed|null
     */
    public static function tw($redirect_uri = null)
    {
        /** @var SocialMedia $smp */
        $smp = NCService::load('SocialMedia');
        /** @var Twitter $manager */
        $manager = $smp->tw();
        return $manager->setup($_GET);
    }

    /**
     * @param null $redirect_uri
     * @return bool
     */
    public static function ga($redirect_uri = null)
    {
        /** @var SocialMedia $smp */
        $smp = NCService::load('SocialMedia');
        /** @var GA $manager */
        $manager = $smp->ga();
        return $manager->setup($_GET);
    }
} 