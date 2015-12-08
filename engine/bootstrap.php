<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

# Components autoloaders
include 'Symfony' . S . 'Component' . S . 'Twig' . S . 'Autoloader.php';
include 'ActiveRecord' . S . 'ActiveRecord.php';


/*
 * Registar class loader
 */
spl_autoload_register(function($class){
    $path = ROOT . S . 'engine' . S . str_replace('\\', S, $class) . '.php';
    if ( is_file($path) ) {
        include $path;
    } else {
        Twig_Autoloader::autoload($class);
    }
});


/*
 * Run engine container
 */
new \System\Engine\NCContainer();