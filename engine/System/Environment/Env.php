<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Environment;


use Service\Application\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class Env
 * @package System\Environment
 */
class Env
{
    /**
     * Application kernel service
     *
     * @var Application
     */
    static $kernel;

    /**
     * @var Request
     */
    static $request;

    /**
     * @var Response
     */
    static $response;

    /**
     * @param $key
     * @param null $newval
     * @return bool
     */
    public static function set($key, $newval = null)
    {
        return putenv($key . '=' . $newval);
    }

    /**
     * @param $key
     * @return string
     */
    public static function get($key)
    {
        return getenv($key);
    }
}