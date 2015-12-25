<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Admin;


use Service\Application\Translate;

class Helper
{
    /**
     * @param $root
     * @param array $skip
     * @return array
     */
    static function dirs($root, $skip = [])
    {
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

    /**
     * Installed modules list
     *
     * @return array
     */
    static function modules()
    {
        $root = ROOT . S . 'engine' . S . 'Module';
        return array_reverse(static::dirs($root, 'Admin'));
    }

    /**
     * Supported languages list
     *
     * @return array
     */
    static function languages()
    {
        $root = ROOT . S . 'engine' . S . 'Language';
        return static::dirs($root);
    }

    /**
     * Supported languages list
     *
     * @return array
     */
    static function services()
    {
        $root = ROOT . S . 'engine' . S . 'Service';
        $services = static::dirs($root, ['Application', 'Database']);
        $data = array_map(function($s){ return Helper::service($s); }, $services);
        return array_combine($services, $data);
    }

    /**
     * @param $service_name
     * @return array
     */
    static function service($service_name)
    {
        $info = ROOT . S . 'engine' . S . 'Service' . S . ucfirst($service_name) . S . 'service.ini';
        if ( !file_exists($info) ) {
            return [];
        }

        return parse_ini_file($info, false);
    }

    /**
     * Supported languages list
     *
     * @return array
     */
    static function themes()
    {
        $root = ROOT . S . 'theme';
        return static::dirs($root, ['admin', 'assets', '.tmp']);
    }

    /**
     * @param Translate $lang
     * @return array
     */
    static function build_menu(Translate $lang)
    {
        $groups = [];
        $modules = static::modules();
        foreach ( $modules as $module ) {
            // Control class
            $admin_class = '\\Module\\' . $module . '\\Control';
            if ( !class_exists($admin_class) ) {
                continue;
            }

            // Translate module name ( $module_name.module )
            $group_name = strtolower($module) . '.module';
            if ( $lang->translate($group_name) != $group_name ) {
                $group_name = $lang->translate($group_name);
            } else {
                $group_name = ucfirst($module);
            }

            // Translate menu
            $menu = [];
            foreach ( $admin_class::$menu as $key => $value ) {
                $translate = $lang->translate($key);
                $menu[$translate] = $value;
            }

            // Assign icon
            $menu['$icon'] = $admin_class::$fa_icon;

            $groups[$group_name] = $menu;
        }

        return $groups;
    }
} 