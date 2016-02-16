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

/**
 * @param string $id
 * @param array $arr
 * @return \System\Engine\NCLazyVar
 */
function lazy_arr($id, $arr = []) {
    return new \System\Engine\NCLazyVar($id, $arr);
}

/**
 * @param $string
 * @return string
 */
function translit($string) {
    $letters = [
        'Й' => 'Y',
        'Ц' => 'C',
        'У' => 'U',
        'К' => 'K',
        'Е' => 'E',
        'Н' => 'N',
        'Г' => 'G',
        'Ш' => 'SH',
        'Щ' => 'SCH',
        'З' => 'Z',
        'Х' => 'H',
        'Ъ' => '',
        'Ф' => 'F',
        'Ы' => 'Y',
        'В' => 'V',
        'А' => 'A',
        'П' => 'P',
        'Р' => 'R',
        'О' => 'O',
        'Л' => 'L',
        'Д' => 'D',
        'Ж' => 'ZH',
        'Э' => 'E',
        'Я' => 'YA',
        'Ч' => 'CH',
        'С' => 'S',
        'М' => 'M',
        'И' => 'I',
        'Т' => 'T',
        'Ь' => '',
        'Б' => 'B',
        'Ю' => 'U',
        'Ё' => 'YO',
        'й' => 'y',
        'ц' => 'c',
        'у' => 'u',
        'к' => 'k',
        'е' => 'e',
        'н' => 'n',
        'г' => 'g',
        'ш' => 'sh',
        'щ' => 'sch',
        'з' => 'z',
        'х' => 'h',
        'ъ' => '',
        'ф' => 'f',
        'ы' => 'y',
        'в' => 'v',
        'а' => 'a',
        'п' => 'p',
        'р' => 'r',
        'о' => 'o',
        'л' => 'l',
        'д' => 'd',
        'ж' => 'zh',
        'э' => 'e',
        'я' => 'ya',
        'ч' => 'ch',
        'с' => 's',
        'м' => 'm',
        'и' => 'i',
        'т' => 't',
        'ь' => '',
        'б' => 'b',
        'ю' => 'u',
        'ї' => 'yi',
        'ё' => 'yo'
    ];

    return strtr($string, $letters);
}

/**
 * @param $string
 * @return string
 */
function urlize($string)
{
    $string = strtolower(trim(translit($string)));
    $string = preg_replace('/\s+/', '_', $string);
    return preg_replace('/[^a-z0-9\-_]+/', '', $string);
}