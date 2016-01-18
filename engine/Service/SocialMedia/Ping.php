<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\SocialMedia;


use System\Engine\NCService;
use System\Environment\Env;


/**
 * Class Ping
 * @package Service\SocialMedia
 */
class Ping extends NCService
{
    use API;

    /**
     * @var SocialMedia
     */
    static $instance;

    /**
     * Pings services
     */
    public function __construct()
    {
        $services = [
            'http://www.bing.com/webmaster/ping.aspx?siteMap=%s',
            'http://blogs.yandex.ru/pings/?status=success&url=%s',
            'http://www.google.com/webmasters/tools/ping?sitemap=%s'
        ];

        foreach ( $services as $pinger ) {
            $url = sprintf($pinger, Env::$request->getSchemeAndHttpHost() . '/sitemap.xml');
            static::GET($url);
        }
    }

    /**
     * @return Ping
     */
    static function instance()
    {
        if ( !static::$instance ) {
            static::$instance = new Ping();
        }

        return static::$instance;
    }
} 