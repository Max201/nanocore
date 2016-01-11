<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\SocialMedia;


use System\Environment\Env;

trait API
{
    /**
     * @param array $params
     * @return string
     */
    static function build_request($params = [])
    {
        $request = [];
        foreach ( $params as $k => $v ) {
            $request[] = $k . '=' . $v;
        }

        return implode('&', $request);
    }

    static function build_url($uri, $params = [])
    {
        $url = strval($uri);
        if ( $url[0] == '/' ) {
            $url = Env::$request->getSchemeAndHttpHost() . $url;
        }

        if ( $params ) {
            $url .= '?' . static::build_request($params);
        }

        return $url;
    }
} 