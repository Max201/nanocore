<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\Module;


use System\Engine\NCService;


class Module extends NCService
{
    static function install($zip_file)
    {
        $already_installed = static::modules();

        # Extract archive
        $zip = new \ZipArchive();
        if ( $zip->open($zip_file) ) {
            if ( !$zip->extractTo(ROOT) ) {
                return 'admin.insta.error.101';
            }
        }

        # Migrate database
        $db = NCService::load('Database')->connection();
        $new = array_diff($already_installed, static::modules());
        foreach ( $new as $module_name ) {
            $migration = ROOT . S . 'engine' . S . 'Module' . S . ucfirst($module_name) . S . 'migration.sql';
            if ( !file_exists($migration) ) {
                continue;
            }

            $queries = file_get_contents($migration);
            $queries = explode(';', $queries);

            foreach ( $queries as $sql ) {
                $db->query($sql);
            }
        }

        return $new;
    }

    static function info($module_name)
    {
        $info_file = ROOT . S . 'engine' . S . 'Module' . S . ucfirst($module_name) . S . 'module.ini';
        if ( !file_exists($info_file) ) {
            return [];
        }

        return parse_ini_file($info_file);
    }

    static function modules($all = false)
    {
        $skip = [];
        if ( !$all ) {
            $skip = ['Admin', 'User'];
        }
        $root = ROOT . S . 'engine' . S . 'Module';
        if ( !is_dir($root) ) {
            return [];
        }

        $handler = opendir($root);
        $dirs = [];
        while ( $item = readdir($handler) ) {
            if ( $item[0] == '.' || in_array($item, $skip) || !is_dir($root . S . $item) ) {
                continue;
            }

            $dirs[] = $item;
        }

        return $dirs;
    }

    static function modules_dict()
    {
        $modules = static::modules();
        $keys = [];
        $values = [];

        foreach ( $modules as $mdl ) {
            $info = ROOT . S . 'engine' . S . 'Module' . S . $mdl . S . 'module.ini';
            if ( !file_exists($info) ) {
                $keys[] = $mdl;
                $values[] = [];
                continue;
            }

            $data = parse_ini_file($info);
            $keys[] = isset($data['name']) ? $data['name'] : $mdl;
            $values[] = $data;
        }

        return array_combine($keys, $values);
    }
}