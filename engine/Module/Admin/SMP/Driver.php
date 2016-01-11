<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Admin\SMP;


use Service\SocialMedia\SocialMedia;
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

    public static function vk($redirect_uri)
    {
        /** @var SocialMedia $smp */
        $smp = NCService::load('SocialMedia');
        /** @var Vkontakte $manager */
        $manager = $smp->vk();
        return $manager->get_token(Env::$request->get('code'), $redirect_uri);
    }
} 