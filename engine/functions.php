<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */


/**
 * @param $str
 * @param int $start
 * @param null $len
 * @return string
 */
function cut($str, $start = 0, $len = null) {
    if ( function_exists('mb_substr') ) {
        return mb_substr($str, $start, $len);
    }

    return substr($str, $start, $len);
}

/**
 * @param $str
 * @return int
 */
function len($str) {
    if ( function_exists('mb_strlen') ) {
        return mb_strlen($str);
    }

    return strlen($str);
}

/**
 * @param $pattern
 * @param $substring
 * @param string $options
 * @return array
 */
function match($pattern, $substring, $options = 'ms') {
    $result = [];
    if ( function_exists('mb_ereg_search') ) {
        mb_eregi($pattern, $substring, $result);
        return $result;
    }

    preg_match($pattern, $substring, $result);
    return $result;
}