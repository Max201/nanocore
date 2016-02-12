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
     * @var array
     */
    static $services = [
        'http://www.bing.com/webmaster/ping.aspx?siteMap=%s',
        'http://blogs.yandex.ru/pings/?status=success&url=%s',
        'http://www.google.com/webmasters/tools/ping?sitemap=%s',
        'http://submissions.ask.com/ping?sitemap=%s'
    ];

    /**
     * Pings services
     */
    public function __construct()
    {

        foreach ( static::$services as $pinger ) {
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