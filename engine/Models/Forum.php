<?php
/**
 * Created by PhpStorm.
 * User: brain
 * Date: 08.12.15
 * Time: 23:52
 */
use ActiveRecord\Model;


/**
 * Class Page
 * @package Model
 */
class Forum extends Model
{
    static $before_create = ['created_at'];
    static $before_save = ['updated_at'];

    /**
     * @var array
     */
    static $belongs_to = array(
        ['author', 'class_name' => 'User', 'foreign_key' => 'author_id'],
        ['forum', 'class_name' => 'Forum', 'foreign_key' => 'forum_id']
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
    public function to_array()
    {
        return array_merge([
            'author' => $this->author->to_array(),
            'forum'  => $this->forum_id ? $this->forum->to_array() : []
        ], parent::to_array());
    }
}