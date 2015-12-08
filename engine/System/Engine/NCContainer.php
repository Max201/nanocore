<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Engine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use System\Environment\Env;


/**
 * Class NCContainer
 * @package System\Engine
 */
class NCContainer
{
    public function __construct()
    {
        session_start();
        Env::$request = Request::createFromGlobals();
        Env::$response = Response::create();

        // Loads database
        NCService::load('Database');

        // Starts application kernel
        Env::$kernel = NCService::load('Application', [Env::$request->server->get('REQUEST_URI')]);
    }
} 