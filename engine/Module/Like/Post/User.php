<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Like\Post;


class User implements BaseLike
{
    /**
     * @param $postId
     * @param \User $author
     * @return bool|void
     */
    public static function plus($postId, \User $author)
    {
        $user = \User::find(intval($postId));
        if ( !$user ) {
            return false;
        }

        if ( $user->author_id == $author->id ) {
            return $user->author_id;
        }

        if ( $user->rate(1) ) {
            return $user->id;
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
        $user = \User::find(intval($postId));
        if ( !$user ) {
            return false;
        }

        if ( $user->author_id == $author->id ) {
            return $user->author_id;
        }

        if ( $user->save(-1) ) {
            return $user->id;
        }

        return false;
    }
} 