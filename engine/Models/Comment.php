<?php
/**
 * Created by PhpStorm.
 * User: brain
 * Date: 08.12.15
 * Time: 23:52
 */
use ActiveRecord\Model;


/**
 * Class Comment
 * @package Model
 */
class Comment extends Model
{
    static $before_create = ['created_at'];

    /**
     * @var array
     */
    static $belongs_to = array(
        ['author', 'class_name' => 'User', 'foreign_key' => 'author_id'],
        ['parent', 'class_name' => 'Comment', 'foreign_key' => 'parent_id'],
    );

    /**
     * Defines create date
     */
    public function created_at()
    {
        $this->assign_attribute('created_at', time());
    }

    /**
     * @return array
     */
    public function to_array()
    {
        return array_merge([
            'author' => $this->author->to_array(),
            'parent' => $this->parent ? $this->parent->to_array() : [],
        ], parent::to_array());
    }
}