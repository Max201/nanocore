<?php
/**
 * @Product: NanoCore
 * @Author: Maxim P.
 */

namespace Module\Post;


use Service\SocialMedia\Google;
use Service\SocialMedia\SocialMedia;
use Service\SocialMedia\Twitter;
use Service\SocialMedia\Vkontakte;
use Symfony\Component\HttpFoundation\Request;
use Service\Paginator\Listing;
use System\Engine\NCControl;
use System\Engine\NCService;
use System\Engine\NCWidget;
use System\Environment\Arguments;


class Control extends NCControl
{
    /**
     * Control panel menu
     *
     * @var string
     */
    static $fa_icon = 'rss';
    static $menu = [
        [
            'title' => 'post.list',
            'href'  => '/control/post/',
            'counter' => 'total_posts',
        ],
        [
            'title' => 'post.categories',
            'href'  => '/control/post/categories/',
            'counter' => 'total_categories'
        ],
    ];

    /**
     * Count posts
     *
     * @return int|string
     */
    static function total_posts()
    {
        $new_posts = \Post::count([
            'conditions' => ['created_at > ?', mktime(0, 0, 0)]
        ]);

        if ( $new_posts > 0 ) {
            return '+' . $new_posts;
        }

        return \Post::count();
    }

    /**
     * Count categories
     *
     * @return int
     */
    static function total_categories()
    {
        return \PostCategory::count();
    }

    /**
     * Module routing
     */
    public function route()
    {
        $this->map->addRoute('/', [$this, 'posts_list'], 'list');
        $this->map->addRoute('moderate', [$this, 'posts_moderate'], 'list.moderation');
        $this->map->addRoute('categories', [$this, 'posts_categories'], 'list.categories');
        $this->map->addRoute('categories/edit', [$this, 'edit_category'], 'post.category_new');
        $this->map->addRoute('create', [$this, 'edit_post'], 'post.new');

        $this->map->addPattern('edit/<id:\d+?>', [$this, 'edit_post'], 'post.edit');
        $this->map->addRoute('import', [$this, 'import'], 'post.import');
    }

    /**
     * Edit category
     *
     * @param Request $request
     * @return mixed|string
     */
    public function edit_category(Request $request)
    {
        // Looking for category
        $category = null;
        if ( $request->get('id') ) {
            $category = \PostCategory::find_by_id($request->get('id'));
        }

        if ( !$category ) {
            $category = [
                'id'            => null,
                'title'         => $this->lang->translate('post.category'),
                'parent_id'     => null,
            ];
        }


        // Loading possible social postings
        /** @var SocialMedia $smp */
        $smp = NCService::load('SocialMedia');
        $posting = [];
        // VKontakte
        /** @var Vkontakte $vk */
        $vk = $smp->get_manager('vk');
        if ( $vk->configured() && $vk->active() ) {
            $posting['vk'] = $vk->m_groups();
        }

        /** @var Twitter $tw */
        $tw = $smp->get_manager('tw');
        if ( $tw->configured() && $tw->active() ) {
            $posting['tw'] = true;
        }

        // Create or update page
        if ( $request->isMethod('post') ) {
            if ( $category instanceof \PostCategory ) {
                $category->title = $request->get('title');
                $category->parent_id = $request->get('parent_id');
                $category->post_vkontakte = $request->get('post_vkontakte');
                $category->post_twitter = $request->get('post_twitter');
                $category->post_facebook = $request->get('post_facebook');
            } else {
                $category = new \PostCategory([
                    'title'             => $request->get('title'),
                    'parent_id'         => $request->get('parent_id'),
                    'post_vkontakte'    => $request->get('post_vkontakte'),
                    'post_twitter'      => $request->get('post_twitter'),
                    'post_facebook'     => $request->get('post_facebook')
                ]);
            }

            // Updating instance
            $category->save();


            return static::json_response([
                'success'   => true,
                'message'   => $this->lang->translate('form.saved')
            ]);
        }

        if ( $category instanceof \PostCategory ) {
            $category = $category->to_array();
        }

        if ( !$category['id'] ) {
            $categories = \PostCategory::all();
        } else {
            $categories = \PostCategory::all([
                'conditions'    => ['id <> ?', $category['id']]
            ]);
        }

        return $this->view->render('posts/create_category.twig', [
            'category'      => $category,
            'posting'       => $posting,
            'title'         => $this->lang->translate('post.category_new'),
            'categories'    => array_map(function($i){ return $i->to_array(); }, $categories)
        ]);
    }

    /**
     * Categories list
     *
     * @param Request $request
     * @return mixed
     */
    public function posts_categories(Request $request)
    {
        $title = $this->lang->translate('post.categories');

        // Delete post
        if ( $request->get('delete') ) {
            $post = \PostCategory::find_by_id(intval($request->get('delete')));
            if ( $post && $post->delete() ) {
                $this->view->assign('message', $this->lang->translate('form.deleted'));
            }
        }

        // Filter categories
        $filter = [];
        if ( $request->order ) {
            $filter['order'] = $request->order;
        }

        if ( $request->get('category') ) {
            $category = \PostCategory::find($request->get('category'));
            $filter['conditions'] = ['parent_id = ?', $category->id];
            $title = $this->lang->translate('post.category') . ' ' . $category->title;
        }

        /** @var Listing $paginator */
        $paginator = NCService::load('Paginator.Listing', [$request->page, \PostCategory::count()]);
        $filter = array_merge($filter, $paginator->limit());

        // Listing categories
        $categories = \PostCategory::all($filter);
        $categories = array_map(function($i){ $a = $i->to_array(); $a['posts'] = \Post::count(['conditions' => ['category_id = ?', $a['id']]]); return $a; }, $categories);
        return $this->view->render('posts/list_categories.twig', [
            'title'         => $title,
            'category_list' => $categories,
            'listing'       => $paginator->pages(),
            'page'          => $paginator->cur_page
        ]);
    }

    /**
     * List moderate posts
     *
     * @param Request $request
     * @return mixed
     */
    public function posts_moderate(Request $request)
    {
        return $this->posts_list($request, new Arguments(['mod' => true]));
    }

    /**
     * Posts list
     *
     * @param Request $request
     * @param $opts
     * @return mixed
     */
    public function posts_list(Request $request, $opts)
    {
        $title = $this->lang->translate('post.list');

        // Delete post
        if ( $request->get('delete') ) {
            $post = \Post::find_by_id(intval($request->get('delete')));
            if ( $post && $post->delete() ) {
                $this->view->assign('message', $this->lang->translate('form.deleted'));
            }
        }

        // Publish post
        if ( $request->get('accept') ) {
            $post = \Post::find_by_id(intval($request->get('accept')));
            if ( $post ) {
                $post->moderate = '0';
                $post->created_at = time();
                if ( $post->save() ) {
                    $this->view->assign('message', $this->lang->translate('post.published'));
                }

                // Export to social
                $post->export();

                // Pinging sitemap
                NCService::load('SocialMedia.Ping');
            } else {
                $this->view->assign('message', $this->lang->translate('post.error_publish'));
            }
        }

        // Filter
        $filter['order'] = 'id DESC';
        if ( $request->order ) {
            $filter['order'] = $request->order;
        }

        $conditions = [];
        $values = [];

        // By Category
        if ( $request->get('category') ) {
            $category = \PostCategory::find($request->get('category'));
            if ( $category ) {
                $conditions[] = 'category_id = ?';
                $values[] = $category->id;
            }
        }

        // By Author
        if ( $request->get('author') ) {
            $author = \User::find($request->get('author'));
            if ( $author ) {
                $conditions[] = 'author_id = ?';
                $values[] = $author->id;
            }
        }

        // Premoderate
        $conditions[] = 'moderate = ?';
        if ( $opts->get('mod') ) {
            $title = $this->lang->translate('post.onmoderation');
            $values[] = '1';
        } else {
            $values[] = '0';
        }

        if ( $conditions ) {
            $filter['conditions'] = array_merge([implode(' AND ', $conditions)], $values);
        }

        /** @var Listing $paginator */
        $paginator = NCService::load('Paginator.Listing', [$request->page, \Post::count($filter)]);
        $filter = array_merge($filter, $paginator->limit());

        // Filter users
        $posts = \Post::all($filter);

        // Mass managing
        // Accept all visible
        if ( $request->get('accept') == 'all' ) {
            foreach ( $posts as $item ) {
                $item->moderate = '0';
                $item->save();

                // Export to social
                Module::export($item, $this->view);
            }

            // Pinging sitemap
            NCService::load('SocialMedia.Ping');
            return static::redirect_response($request->getPathInfo());
        }

        // Sent to moderate all visible
        if ( $request->get('moderate') == 'all' ) {
            foreach ( $posts as $item ) {
                $item->moderate = '1';
                $item->save();
            }

            return static::redirect_response($request->getPathInfo());
        }

        // Delete all visible
        if ( $request->get('delete') == 'all' ) {
            foreach ( $posts as $item ) {
                $item->delete();
            }

            return static::redirect_response($request->getPathInfo());
        }


        $posts = array_map(function($i){ return $i->to_array(); }, $posts);
        return $this->view->render('posts/list.twig', [
            'title'         => $title,
            'posts_list'    => $posts,
            'listing'       => $paginator->pages(),
            'page'          => $paginator->cur_page,
            'moderate'      => $opts->get('mod')
        ]);
    }

    /**
     * Edit post or create new
     *
     * @param Request $request
     * @param $matches
     * @return mixed|string
     */
    public function edit_post(Request $request, $matches)
    {
        $title = $this->lang->translate('post.create');

        // Get post for updating
        $id = intval($matches->get('id', $request->get('id')));
        if ( $id > 0 ) {
            $post = \Post::find_by_id($id);
            $title = $this->lang->translate('post.editing', $post->title);
        } else {
            $category = \PostCategory::last();
            $post = [
                'title'     => $this->lang->translate('post.name'),
                'content'   => '',
                'category'  => $category ? $category->to_array() : null
            ];
        }

        // Create or update page
        if ( $request->isMethod('post') ) {
            if ( $post instanceof \Post ) {
                $post->title = $request->get('title');
                $post->content = $request->get('content');
                $post->category_id = $request->get('category');
                $post->keywords = $request->get('keywords');
                $post->slug = $request->get('slug');
                $post->moderate = $request->get('moderate');
            } else {
                $post = new \Post([
                    'title'         => $request->get('title'),
                    'content'       => $request->get('content'),
                    'category_id'   => $request->get('category'),
                    'keywords'      => $request->get('keywords'),
                    'slug'          => $request->get('slug'),
                    'author_id'     => $this->user->id,
                    'moderate'      => $request->get('moderate')
                ]);
            }

            // Updating instance
            $post->save();

            if ( !$post->moderate ) {
                // Exporting to social
                Module::export($post, $this->view);

                // Ping sitemap
                NCService::load('SocialMedia.Ping');
            }

            return static::json_response([
                'success'   => true,
                'message'   => $this->lang->translate('form.saved')
            ]);
        }

        if ( $post instanceof \Post ) {
            $post = $post->to_array();
        }

        return $this->view->render('posts/create.twig', [
            'post'          => $post,
            'title'         => $title,
            'categories'    => \PostCategory::as_array(\PostCategory::all())
        ]);
    }

    /**
     * Import videos from youtube
     *
     * @param Request $request
     * @param $match
     * @return string
     */
    public function import(Request $request, $match)
    {
        if ( $request->isMethod('post') ) {
            $items = [];
            $query = [
                'part' => 'snippet,id',
                'channelId' => $request->get('channel')
            ];

            // Fetch videos
            /** @var Google $sm */
            $sm = NCService::load('SocialMedia.Google');
            while ( $response = $sm->request('https://www.googleapis.com/youtube/v3/search', $query) ) {
                $response = json_decode($response, true);
                $items = array_merge($items, $response['items']);

                // Move to next page or break
                if ( isset($response['nextPageToken']) && $response['nextPageToken'] ) {
                    $query['pageToken'] = $response['nextPageToken'];
                } else {
                    break;
                }
            }

            // Save videos
            foreach ( $items as $video ) {
                $id = $video['id']['videoId'];
                $url = 'https://www.youtube.com/watch?v=' . $id;
                $title = $video['snippet']['title'];
                $description = $video['snippet']['description'];
                $iframe = '<iframe class="youtube" width="560" height="315" src="https://www.youtube.com/embed/' . $id . '" frameborder="0" allowfullscreen></iframe>';
                $created_at = strtotime($video['snippet']['publishedAt']);

                switch ( $request->get('type') ) {
                    default: // Full
                        $body = '<p style="text-align: center;" class="media-wrapper">' . $iframe . '</p>';
                        $body .= '<p>' . nl2br($description) . '</p>';
                }

                $entity = new \Post([
                    'author_id' => $this->user->id,
                    'title'     => $title,
                    'content'   => $body,
                    'moderate'  => 1,
                    'category_id'   => $request->get('category'),
                    'created_at'    => $created_at,
                    'updated_at'    => $created_at
                ]);

                $entity->save();
            }
        }

        return $this->view->render('posts/import.twig', [
            'title'         => $this->lang->translate('post.import'),
            'categories'    => \PostCategory::as_array(\PostCategory::all())
        ]);
    }
} 