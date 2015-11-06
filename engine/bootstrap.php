<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */


/*
 * Registar class loader
 */
spl_autoload_register(function($class){
    $path = ROOT . S . 'engine' . S . str_replace('\\', S, $class) . '.php';
    if ( file_exists($path) ) {
        include $path;
    }
});


/*
 * Run engine container
 */
new \System\Engine\NCContainer();