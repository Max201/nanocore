<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */


/*
 * Root directory
 */
define('ROOT', dirname(__FILE__));


/*
 * Directory separator
 */
define('S', DIRECTORY_SEPARATOR);


error_reporting(E_ALL || ~E_STRICT);


/*
 * Engine start
 */
include 'engine' . S . 'bootstrap.php';