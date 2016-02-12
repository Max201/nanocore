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
        $forum = \ForumTheme::find(intval($postId));
        if ( !$forum ) {
            return false;
        }

        if ( $forum->author_id == $author->id ) {
            return $forum->author_id;
        }

        if ( $forum->author->rate(1) ) {
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
        $forum = \ForumTheme::find(intval($postId));
        if ( !$forum ) {
            return false;
        }

        if ( $forum->author_id == $author->id ) {
            return $forum->author_id;
        }

        if ( $forum->author->rate(-1) ) {
            return $forum->author_id;
        }

        return false;
    }
} 