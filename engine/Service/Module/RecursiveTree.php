<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Service\Module;

use ActiveRecord\Model;


/**
 * Class RecursiveTree
 * @package Service\Module
 */
class RecursiveTree
{
    /**
     * @var array
     */
    private $tree = [];

    /**
     * @var array
     */
    private $list = [];

    /**
     * @var string
     */
    private $key = 'parent_id';

    /**
     * @param Model[] $list
     * @param string $parent_key
     */
    public function __construct(array $list, $parent_key = 'parent_id')
    {
        $this->list = $list;
        $this->key = $parent_key;
    }

    /**
     * @param $id
     * @return array
     */
    public function childs($id = null)
    {
        $childs = [];
        if ( !is_null($id) ) {
            $childs[] = $id;
        }

        foreach ( $this->list as $item ) {
            if ( $item->{$this->key} == $id ) {
                $childs = array_merge($childs, $this->childs($item->id));
            }
        }

        return $childs;
    }

    /**
     * @param array $list
     * @param string $parent_key
     * @return RecursiveTree
     */
    public static function instance(array $list, $parent_key = 'parent_id')
    {
        return new RecursiveTree($list, $parent_key);
    }
} 