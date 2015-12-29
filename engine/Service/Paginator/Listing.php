<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\Paginator;


use System\Engine\NCService;


class Listing extends NCService
{
    const CONFIG = 'Paginator.config';

    /**
     * @var Listing
     */
    static $instance;

    /**
     * @var int
     */
    public $per_page = 10;

    /**
     * @var int
     */
    public $cur_page = 1;

    /**
     * @var int
     */
    public $num_rows = 0;

    /**
     * @var int
     */
    public $pages = 1;

    /**
     * @param $page
     * @param $rows
     */
    public function __construct($page, $rows)
    {
        $this->per_page = $this->config('pagination')->get('per_page', 10);
        $this->num_rows = intval($rows);
        $this->pages = ceil($this->num_rows / $this->per_page);

        if ( $this->pages == 0 ) {
            $this->pages = 1;
        }

        $this->cur_page = $page > $this->pages ? $this->pages : intval($page);
        if ( $this->cur_page < 1 ) {
            $this->cur_page = 1;
        }
    }

    public function pages()
    {
        if ( $this->pages < 9 ) {
            return range(1, $this->pages, 1);
        }

        $visible = [];

        // Add first page
        $visible[] = 1;

        // Add previous page
        if ( ($this->cur_page - 1) > 1 ) {
            $visible[] = $this->cur_page - 1;
        }

        // Add current page
        if ( $this->cur_page != 1 ) {
            $visible[] = $this->cur_page;
        }

        // Add next page
        if ( ($this->cur_page + 1) <= $this->pages ) {
            $visible[] = $this->cur_page + 1;
        }

        // Add last page
        if ( ($this->cur_page + 1) < $this->pages && $this->pages != 1 ) {
            $visible[] = $this->pages;
        }

        return $visible;
    }

    /**
     * @return array
     */
    public function limit()
    {
        return [
            'offset'    => ($this->cur_page -1) * $this->per_page,
            'limit'     => $this->per_page
        ];
    }

    /**
     * @param null $page
     * @param null $rows
     * @return NCService|void
     */
    public static function instance($page = null, $rows = null)
    {
        if ( !static::$instance ) {
            static::$instance = new self($page, $rows);
        }

        return static::$instance;
    }
} 