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
    static $before_destroy = ['dispose'];

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
     * Delete child forums & topics
     */
    public function dispose()
    {
        // Drop subjects
        $subjects = Forum::find('all', ['conditions' => ['forum_id = ?', $this->id]]);
        foreach ( $subjects as $sbj ) {
            $sbj->delete();
        }

        // Drop themes
        $themes = ForumTheme::find('all', ['conditions' => ['forum_id = ?', $this->id]]);
        foreach ( $themes as $thm ) {
            $thm->delete();
        }
    }

    /**
     * @return array
     */
    public function to_array()
    {
        return array_merge([
            'author'    => $this->author->to_array(),
            'forum'     => $this->forum_id ? $this->forum->to_array() : [],
            'subjects'  => $this->forum_id ? 0 : Forum::count(['conditions' => ['forum_id = ?', $this->id]]),
            'topics'    => $this->forum_id ? ForumTheme::count(['conditions' => ['forum_id = ?', $this->id]]) : 0,
        ], parent::to_array());
    }
}