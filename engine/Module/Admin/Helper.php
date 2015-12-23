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
        if ( !is_file($root) ) {
            return [];
        }

        $handler = opendir($root);
        $modules = [];
        while ( $item = readdir($handler) ) {
            if ( $item[0] == '.' ) {
                continue;
            }

            $modules[] = $root . S . $item;
        }

        return $modules;
    }
} 