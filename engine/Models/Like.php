<?php
/**
 * Created by PhpStorm.
 * User: brain
 * Date: 08.12.15
 * Time: 23:52
 */
use ActiveRecord\Model;


/**
 * Class Like
 * @package Model
 */
class Like extends Model
{
    static $before_create = ['created_at'];

    /**
     * @var array
     */
    static $belongs_to = array(
        ['author', 'class_name' => 'User', 'foreign_key' => 'author_id'],
        ['user', 'class_name' => 'User', 'foreign_key' => 'user_id'],
    );

    /**
     * Defines create date
     */
    public function created_at()
    {
        $this->assign_attribute('created_at', time());
    }

    /**
     * @param $post
     * @return int
     */
    public static function rating($post)
    {
        return reset(static::find_by_sql('SELECT SUM(vote) AS rating FROM likes WHERE post = ?', [$post]))->rating;
    }

    /**
     * @return array
     */
    public function to_array()
    {
        return array_merge([
            'author' => $this->author->to_array(),
            'user'   => $this->user->to_array()
        ], parent::to_array());
    }
}