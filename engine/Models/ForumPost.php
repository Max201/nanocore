<?php
/**
 * Created by PhpStorm.
 * User: brain
 * Date: 08.12.15
 * Time: 23:52
 */
use ActiveRecord\Model;


/**
 * Class ForumPost
 * @package Model
 */
class ForumPost extends Model
{
    static $before_create = ['created_at'];
    static $before_save = ['updated_at'];
    static $before_destroy = ['dispose'];

    /**
     * @var array
     */
    static $belongs_to = array(
        ['author', 'class_name' => 'User', 'foreign_key' => 'author_id'],
        ['theme', 'class_name' => 'ForumTheme', 'foreign_key' => 'theme_id']
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
     * Delete likes
     */
    public function dispose()
    {
        // Delete likes
        Like::table()->delete('post = "topicpost' . $this->id . '"');
    }

    /**
     * @return array
     */
    public function to_array()
    {
        return array_merge([
            'author'    => $this->author->to_array(),
            'theme'     => $this->theme->to_array(),
        ], parent::to_array());
    }
}