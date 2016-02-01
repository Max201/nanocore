<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

/*
 * Internal encoding
 */
mb_internal_encoding('UTF-8');
mb_regex_encoding('UTF-8');

/*
 * Root directory
 */
define('ROOT', dirname(__FILE__));


/*
 * Directory separator
 */
define('S', DIRECTORY_SEPARATOR);


/*
 * Report errors
 *
 * @default 0
 */
error_reporting(E_ALL || ~E_STRICT || ~E_DEPRECATED);

/*
 * Display errors
 *
 * @default 0
 */
ini_set('display_errors', 1);


/*
 * Memory usage limit
 *
 * @default -1
 */
ini_set('memory_limit', -1);


/*
 * Engine start
 */
if ( file_exists('engine' . S . 'installed') ) {
    include 'engine' . S . 'bootstrap.php';
} else {
    include 'install.php';
}