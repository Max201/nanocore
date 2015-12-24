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