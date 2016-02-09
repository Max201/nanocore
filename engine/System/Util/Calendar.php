<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace System\Util;


/**
 * Class Calendar
 * @package System\Util
 */
class Calendar extends \ArrayObject
{
    /**
     * @var int
     */
    private $month = 1;

    /**
     * @var bool|int|string
     */
    private $year = 1972;

    /**
     * @var int
     */
    private $date;

    /**
     * @var int
     */
    private $cur_day = 1;

    /**
     * @param int $month
     * @param string $year
     */
    public function __construct($month = null, $year = null)
    {
        if ( is_null($month) ) {
            $month = date('m');
        }

        if ( is_null($year) ) {
            $year = date('Y');
        }

        $this->month = $month;
        $this->year = $year;
        $this->date = $this->date();
        $this->generate();
    }

    /**
     * Generates array of numerated days of weeks
     * 0 1, 0 2, 0 3, 0 4, 0 5, 0 6, 0 7
     * 1 8, 1 9, 1 10, 1 11, 1 12, 1 13, 1 14
     * ...
     */
    private function generate()
    {
        // Generate first week array
        $this->start_week();

        // Generate next weeks
        $this->next_weeks();
    }

    private function day_number($day)
    {
        $num = date('w', mktime(12, 12, 0, $this->month, $day, $this->year));
        return ($num - 1) < 0 ? 6 : $num - 1;
    }

    private function date()
    {
        return mktime(12, 1, 1, $this->month, 1, $this->year);
    }

    public function count()
    {
        return date('t', $this->date);
    }

    private function start_week()
    {
        $this[0] = [];
        $days = date('t', $this->date);
        for ( $i = 0; $i < 7; $i++ ) {
            $dayofweek = $this->day_number($this->cur_day);
            if ( $dayofweek == $i ) {
                $this[0][$i] = $this->cur_day;
                $this->cur_day++;
            } else {
                $this[0][$i] = null;
            }
        }
    }

    private function next_weeks()
    {
        $week = 1;
        while ( $this->cur_day <= $this->count() ) {
            for ( $i = 0; $i < 7; $i ++ ) {
                if ( $this->cur_day > $this->count() ) {
                    break;
                }

                $this[$week][$i] = $this->cur_day;
                $this->cur_day++;
            }

            $week++;
        }
    }
} 