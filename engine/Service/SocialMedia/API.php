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
            $request[] = $k . '=' . urlencode($v);
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

    /**
     * @param $url
     * @param array $params
     * @param null $ref
     * @return mixed
     */
    static function GET($url, $params=[], $ref=null)
    {
        if ( is_null($ref) ) {
            $ref = Env::$request->getSchemeAndHttpHost() . '?' . Env::$request->getQueryString();
        }

        $ch = curl_init($url . '?' . static::build_request($params));
        curl_setopt($ch, CURLOPT_REFERER, $ref);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * @param $url
     * @param array $params
     * @param null $ref
     * @return mixed
     */
    static function POST($url, $params=[], $ref = null)
    {
        if ( is_null($ref) ) {
            $ref = Env::$request->getSchemeAndHttpHost() . '?' . Env::$request->getQueryString();
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_REFERER, $ref);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, count(array_keys($params)));
        curl_setopt($ch, CURLOPT_POSTFIELDS, static::build_request($params));
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
} 