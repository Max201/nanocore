<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\SocialMedia;


use System\Environment\Env;
use System\Environment\Options;

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
     * @param array $headers
     * @param bool $return_headers
     * @return mixed
     */
    static function GET($url, $params=[], $ref=null, $headers = [], $return_headers = false)
    {
        if ( is_null($ref) ) {
            $ref = Env::$request->getSchemeAndHttpHost() . '?' . Env::$request->getQueryString();
        }

        $ch = curl_init($url . '?' . static::build_request($params));
        curl_setopt($ch, CURLOPT_REFERER, $ref);
        curl_setopt($ch, CURLOPT_PROXY, '81.94.162.140:8080');
        curl_setopt($ch, CURLOPT_HTTPHEADER, static::build_headers($headers));
        if ( $return_headers ) {
            curl_setopt($ch, CURLOPT_HEADER, true);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * @param $url
     * @param array $params
     * @param null $ref
     * @param array $headers
     * @param bool $return_headers
     * @return mixed
     */
    static function POST($url, $params=[], $ref = null, $headers = [], $return_headers = false)
    {
        if ( is_null($ref) ) {
            $ref = Env::$request->getSchemeAndHttpHost() . '?' . Env::$request->getQueryString();
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_REFERER, $ref);
        curl_setopt($ch, CURLOPT_HTTPHEADER, static::build_headers($headers));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        if ( $return_headers ) {
            curl_setopt($ch, CURLOPT_HEADER, true);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * @param array $headers
     * @return string
     */
    static function build_headers($headers = [])
    {
        $result = [];
        foreach ( $headers as $k => $v ) {
            $result[] = $k . ': ' . $v;
        }

        return $result;
    }
} 