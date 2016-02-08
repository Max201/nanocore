<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Like\Post;


class Comment implements BaseLike
{
    /**
     * @param $postId
     * @param \User $author
     * @return bool|void
     */
    public static function plus($postId, \User $author)
    {
        $comment = \Comment::find(intval($postId));
        if ( !$comment ) {
            return false;
        }

        $comment->author->rating = $comment->author->rating + 1;
        if ( $comment->author->save() ) {
            return $comment->author_id;
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
        $comment = \Comment::find(intval($postId));
        if ( !$comment ) {
            return false;
        }

        $comment->author->rating = $comment->author->rating - 1;
        if ( $comment->author->save() ) {
            return $comment->author_id;
        }

        return false;
    }
} 