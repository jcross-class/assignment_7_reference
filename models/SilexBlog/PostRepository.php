<?php

namespace SilexBlog;

use \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

require_once __DIR__.'/Post.php';

class PostNotFoundException extends \Symfony\Component\HttpKernel\Exception\NotFoundHttpException {}

class PostRepository
{
    private $app;

    public function __construct(\Silex\Application $app) {
        $this->app = $app;
    }

    // Look up a post by its id in the database and return a new Post object.
    public function find($id) {
        $db_result = $this->app['db']->fetchAssoc('SELECT * FROM `posts` where id = ?', array((int) $id));
        if ($db_result === false) {
            throw new PostNotFoundException('A post with id=' . ((int) $id) . ' was not found!');
        }
        $post_object = new Post($db_result['id'], $db_result['author'], $db_result['title'],
                                $db_result['created_date'], $db_result['modified_date'], $db_result['body']);
        $post_object->setPersisted(true);

        return $post_object;
    }

    // Grab all posts from the database and return an array of Post objects.
    public function findAll() {
        $db_results = $this->app['db']->fetchAll('SELECT * FROM `posts`');
        if ($db_results === false) {
            throw new PostNotFoundException('No posts were found in the database!');
        }
        $posts = array();
        foreach($db_results as $db_result) {
            $post_object = new Post($db_result['id'], $db_result['author'], $db_result['title'],
                                    $db_result['created_date'], $db_result['modified_date'], $db_result['body']);
            $post_object->setPersisted(true);
            $posts[] = $post_object;
        }

        return $posts;
    }

    public function save(Post $post_object) {
        if(is_null($post_object->getId())) {
            $this->app['db']->insert('posts', array(
                'author' => $post_object->getAuthor(),
                'title' => $post_object->getTitle(),
                'body' => $post_object->getBody(),
                'created_date' => $post_object->getCreatedDate(),
                'modified_date' => $post_object->getModifiedDate(),
            ));
            $post_object->setId($this->app['db']->lastInsertId());
            $post_object->setPersisted(true);
        } else {
            // update post
        }
    }
}