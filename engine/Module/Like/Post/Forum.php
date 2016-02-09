<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Like\Post;


class Forum implements BaseLike
{
    /**
     * @param $postId
     * @param \User $author
     * @return bool|void
     */
    public static function plus($postId, \User $author)
    {
        $forum = \Forum::find(intval($postId));
        if ( !$forum ) {
            return false;
        }

        if ( $forum->author_id == $author->id ) {
            return $forum->author_id;
        }

        $forum->author->rating = $forum->author->rating + 1;
        if ( $forum->author->save() ) {
            return $forum->author_id;
        }

        return false;
    }

    /**
     * @param $postId
     * @param \User $author
     * @return bool|void
     */
    public static function minus($postId, \User $author)
    {
        $forum = \Forum::find(intval($postId));
        if ( !$forum ) {
            return false;
        }

        if ( $forum->author_id == $author->id ) {
            return $forum->author_id;
        }

        $forum->author->rating = $forum->author->rating - 1;
        if ( $forum->author->save() ) {
            return $forum->author_id;
        }

        return false;
    }
} 