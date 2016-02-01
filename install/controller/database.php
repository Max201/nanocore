<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

$twig = twig();

// Connection file
$cfile = ROOT . S . 'engine' . S . 'Service' . S . 'Database' . S . 'config' . S . 'database.json';

// Default database connection config
$config = [
    "models" => "engine/Models",
    "default_connection" => "default",
    "connections" => [
        "default" => ""
    ]
];

// Connection mask
$mask = "mysql://%s:%s@%s/%s?charset=utf8";

// Connection information
$database = [
    'user'      => post('root', 'root'),
    'password'  => post('password', ''),
    'host'      => post('host', 'localhost'),
    'name'      => post('name', 'nano')
];

$config['connections']['default'] = call_user_func_array('sprintf', array_merge($mask, array_values($database)));

// Save form
if ( isset($_POST['save']) ) {
    if ( !file_put_contents($cfile, json_encode($config, JSON_PRETTY_PRINT))) {
        $twig->addGlobal(
            'error',
            'Unable to write db settings. Maybe needs to set recursive chmod on '
            . ROOT . S . 'engine' . S . 'Service' . S . 'Database' . S . 'config' . S
        );
    } else {
        header('Location: ?act=scheme&s=' . urlencode(base64_encode(json_encode($database))));
        exit;
    }
}


// Test connection
if ( isset($_POST['test']) ) {
    if ( @mysql_connect($database['host'], $database['user'], $database['password']) && @mysql_select_db($database['name']) ) {
        $twig->addGlobal(
            'success',
            'Connection successfully!'
        );
    } else {
        $twig->addGlobal(
            'error',
            'Connection failed! ' . mysql_error()
        );
    }
}


// Render page
$twig->display('database.twig', [
    'db' => $database,
    'title' => 'MySQL Database Setup'
]);