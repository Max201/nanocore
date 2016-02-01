<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */
if ( !defined('ROOT') ) die('System error!');

# System functions
include 'engine' . S . 'functions.php';

# Components autoloaders
include 'engine' . S . 'Symfony' . S . 'Component' . S . 'Twig' . S . 'Autoloader.php';
spl_autoload_register(function($class){
    Twig_Autoloader::autoload($class);
});

/**
 * @return Twig_Environment
 */
function twig() {
    return new Twig_Environment(
        new Twig_Loader_Filesystem(['install' . S . 'view'])
    );
}

# Get data from post
function post($key, $default = null) {
    if ( !isset($_POST[$key]) || !$_POST[$key] ) {
        return $default;
    }

    return $_POST[$key];
}

# Get data from get
function get($key, $default = null) {
    if ( !isset($_GET[$key]) || !$_GET[$key] ) {
        return $default;
    }

    return $_GET[$key];
}

$act = 'main';
if ( isset($_GET['act']) ) {
    $act = strtolower($_GET['act']);
}


include 'install' . S . 'controller' . S . $act . '.php';