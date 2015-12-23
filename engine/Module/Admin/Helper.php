<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Admin;


class Helper 
{
    /**
     * @return array
     */
    static function modules()
    {
        $root = ROOT . S . 'engine' . S . 'Module';
        if ( !is_dir($root) ) {
            return [];
        }

        $handler = opendir($root);
        $modules = [];
        while ( $item = readdir($handler) ) {
            if ( $item[0] == '.' ) {
                continue;
            }

            $modules[] = $item;
        }

        return array_reverse($modules);
    }

    /**
     * @return array
     */
    static function build_menu()
    {
        $groups = [];
        $modules = static::modules();
        foreach ( $modules as $module ) {
            $admin_class = '\\Module\\' . $module . '\\Control';
            if ( !class_exists($admin_class) ) {
                continue;
            }

            $groups[$module] = $admin_class::$menu;
        }

        return $groups;
    }
} 