<?php

/**
 * Created by (c) NanoLab
 * @website: http://www.nanolab.pw
 */
class PostCategory extends ActiveRecord\Model
{
    /**
     * @var string
     */
    static $table_name = 'post_category';

    /**
     * @var array
     */
    static $belongs_to = array(
        ['parent', 'class_name' => 'PostCategory', 'foreign_key' => 'parent_id'],
    );

    static $has_many = array(
        ['categories', 'class_name' => 'PostCategory'],
        ['posts', 'class_name' => 'Post']
    );

    /**
     * Categories list
     */
    public static function listing()
    {
        $categories = PostCategory::all();
        $id_tree = [];
        foreach ( $categories as $cat ) {
            $id_tree[$cat->id] = $cat;
        }

        return $id_tree;
    }

    /**
     * @return array
     */
    public function to_array()
    {
        $a = parent::to_array();
        $a['parent'] = $this->parent_id ? $this->parent->to_array() : null;
        return $a;
    }
}