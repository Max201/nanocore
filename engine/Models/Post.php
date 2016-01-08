<?php
/**
 * Created by PhpStorm.
 * User: brain
 * Date: 08.12.15
 * Time: 23:52
 */
use ActiveRecord\Model;


/**
 * Class Post
 * @package Model
 */
class Post extends Model
{
    static $before_create = ['created_at'];
    static $before_save = ['updated_at'];

    /**
     * @var array
     */
    static $belongs_to = array(
        ['author', 'class_name' => 'User', 'foreign_key' => 'author_id']
    );

    /**
     * Defines create date
     */
    public function created_at()
    {
        $this->assign_attribute('created_at', time());
    }

    /**
     * Defines update date
     */
    public function updated_at()
    {
        $this->assign_attribute('updated_at', time());
    }

    /**
     * @return array
     */
    public function asArrayFull()
    {
        return array_merge(['author' => $this->author->to_array()], $this->to_array());
    }
}