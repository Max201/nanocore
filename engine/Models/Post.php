<?php
/**
 * Created by PhpStorm.
 * User: brain
 * Date: 08.12.15
 * Time: 23:52
 */
use ActiveRecord\Model;


/**
 * Class Post
 * @package Model
 */
class Post extends Model
{
    static $before_create = ['created_at', 'export'];
    static $before_save = ['updated_at'];

    /**
     * @var array
     */
    static $belongs_to = array(
        ['author', 'class_name' => 'User', 'foreign_key' => 'author_id'],
        ['category', 'class_name' => 'PostCategory', 'foreign_key' => 'category_id'],
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
    public function images()
    {
        // Search images
        preg_match_all('#<img.+?src="(.+?)"#', $this->content, $m);
        $images = [];
        if ( $m ) {
            foreach ( $m[1] as $img ) {
                if ( $img[0] == '/' ) {
                    $img = \System\Environment\Env::$request->getSchemeAndHttpHost() . $img;
                }

                $images[] = $img;
            }
        }

        return $images;
    }

    /**
     * @param int $max_len
     * @return string
     */
    public function content_plain($max_len=-1)
    {
        $content = $max_len > 0 ? substr($this->content, 0, $max_len) : $this->content;
        return strtr($content, [
            '</div>'    => "\n",
            '</p>'      => "\n\n",
            '<br/>'     => "\n",
        ]);
    }

    /**
     * Exporting to social
     */
    public function export()
    {
        /** @var \Service\SocialMedia\SocialMedia $smp */
        $smp = \System\Engine\NCService::load('SocialMedia');

        // Posting vk
        if ( $this->category->post_vkontakte ) {
            $vk = $smp->vk();
            $this->assign_attribute(
                'post_vkontakte',
                $vk->m_post(
                    $this->category->post_vkontakte,
                    implode("\n", $this->images()) . "\n" .
                    $this->title . "\n\n" .
                    $this->content_plain()
                )
            );
        }
    }

    /**
     * @return array
     */
    public function to_array()
    {
        return array_merge([
            'author'    => $this->author->to_array(),
            'category'  => $this->category->to_array()
        ], parent::to_array());
    }
}