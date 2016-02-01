<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

$twig = twig();

// Connection information
$database = json_decode(base64_decode(urldecode(get('s'))), true);

// SQL File
$sql = ROOT . S . 'install' . S . 'install.sql';

// Save form
if ( isset($_POST['import']) ) {
    if ( !@mysql_connect($database['host'], $database['user'], $database['password']) || !@mysql_select_db($database['name']) ) {
        $twig->addGlobal('error', 'Connection to database failed. ' . mysql_error());
    } else {
        mysql_query('SET NAMES utf8');
        $status = $errors = [];
        $install = explode(';', file_get_contents($sql));
        $status['total'] = count($install);
        $status['success'] = 0;

        foreach ( $install as $query ) {
            if ( !@mysql_query(trim($query)) ) {
                $errors[] = [
                    'query' => nl2br($query),
                    'errno' => mysql_errno(),
                    'error' => mysql_error()
                ];
            } else {
                $status['success'] += 1;
            }
        }

        $status['result'] = round($status['success'] / $status['total'] * 100, 2);

        $twig->addGlobal('status', $status);
        $twig->addGlobal('import', true);
    }
}


// Render page
$twig->display('scheme.twig', [
    's' => get('s'),
    'db' => $database,
    'title' => 'MySQL Database Scheme Setup'
]);