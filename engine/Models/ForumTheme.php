<?php
/**
 * Created by PhpStorm.
 * User: brain
 * Date: 08.12.15
 * Time: 23:52
 */
use ActiveRecord\Model;


/**
 * Class ForumTheme
 * @package Model
 */
class ForumTheme extends Model
{
    static $before_create = ['created_at'];
    static $before_save = ['updated_at'];
    static $before_destroy = ['dispose'];

    /**
     * @var array
     */
    static $belongs_to = array(
        ['author', 'class_name' => 'User', 'foreign_key' => 'author_id'],
        ['forum', 'class_name' => 'Forum', 'foreign_key' => 'forum_id'],
        ['close', 'class_name' => 'User', 'foreign_key' => 'close_id'],
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
     * Delete all posts
     */
    public function dispose()
    {
        // Delete posts
        $posts = ForumPost::find('all', ['conditions' => ['theme_id = ?', $this->id]]);
        foreach ( $posts as $post ) {
            $post->delete();
        }

        // Delete likes
        Like::table()->delete('post = "topic' . $this->id . '"');
    }

    /**
     * @param $who
     * @param null $reason
     * @return bool
     */
    public function close(\User $who, $reason = null)
    {
        $this->active = 0;
        $this->close_id = $who->id;
        $this->close_reason = $reason;
        return $this->save();
    }

    /**
     * @return bool
     */
    public function open()
    {
        $this->active = 1;
        $this->close_id = null;
        $this->close_reason = null;
        return $this->save();
    }

    /**
     * @return bool
     */
    public function pin()
    {
        $this->priority = 2;
        return $this->save();
    }

    /**
     * @return bool
     */
    public function unpin()
    {
        $this->priority = 0;
        return $this->save();
    }

    /**
     * @return array
     */
    public function to_array()
    {
        return array_merge([
            'author'    => $this->author->to_array(),
            'forum'     => $this->forum->to_array(),
            'close'     => $this->close_id ? $this->close->to_array() : [],
            'posts'     => intval(ForumPost::count(['conditions' => ['theme_id = ?', $this->id]]))
        ], parent::to_array());
    }
}