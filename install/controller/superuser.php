<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

$twig = twig();

// Connection information
$database = json_decode(base64_decode(urldecode(get('s'))), true);

// User session
$hash = md5(get('s'));

function encrypt($src = '') {
    return md5(substr(md5($src), 4, -4));
}

// Form data
$data = [
    'username'  => post('login', 'admin'),
    'password'  => post('password'),
    'email'     => post('email', 'admin@' . $_SERVER['HTTP_HOST']),
];

// Save form
if ( isset($_POST['create']) ) {
    if ( !@mysql_connect($database['host'], $database['user'], $database['password']) || !@mysql_select_db($database['name']) ) {
        $twig->addGlobal('error', 'Connection to database failed. ' . mysql_error());
    } else {
        mysql_query('SET NAMES utf8');
        if ( strlen($data['password']) < 6 ) {
            $twig->addGlobal('error', 'Password length must be more than 5 characters');
        } else {
            $data['password'] = encrypt($data['password']);
            if ( mysql_query("INSERT INTO users SET username = '{$data['username']}', group_id = 5, password = '{$data['password']}', email = '{$data['email']}', session_id = '{$hash}'") ) {
                file_put_contents(ROOT . S . 'engine' . S . 'installed', '1');
                setcookie('sess', $hash, time() + 365 * 24 * 3600);
                header('Location: /admin/');
                exit;
            } else {
                $twig->addGlobal('error', 'Some error happend while creation user: ' . mysql_error());
            }
        }
    }
}


// Render page
$twig->display('superuser.twig', [
    's' => get('s'),
    'data' => $data,
    'title' => 'Registration administrator'
]);