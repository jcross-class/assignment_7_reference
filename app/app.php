<?php

// Load 3rd party libraries using composer.
require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints as Assert;

// Load our custom Application class that also loads other required classes.
require_once __DIR__.'/SilexBlog/SilexBlogApplication.php';

// Instantiate the Silex service container (the application)
$app = new SilexBlog\SilexBlogApplication();
// Enable debugging so that errors are displayed via web pages.
$app['debug'] = true;

// Register a twig service provider with the path to the twig templates given.
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

// Register a URL generator to handle URL generation based on routes
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

// Register a doctrine provider with the MySQL connection parameters given.
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_mysql',
        'host'     => 'localhost',
        'dbname'   => 'silex_blog',
        'port'     => 3306,
        'username' => 'root',
        'password' => '',
        'charset'   => 'utf8mb4',
    ),
));

// Register a Post repository
$app['repository.post'] = $app->share(function ($app) {
    return new SilexBlog\PostRepository($app);
});

// Register a form handler
$app->register(new Silex\Provider\FormServiceProvider());

// Required for using the default form handler with twig and validators
$app->register(new Silex\Provider\TranslationServiceProvider());

// Register a validator to be used with forms
$app->register(new Silex\Provider\ValidatorServiceProvider());

// Register a session provider
$app->register(new Silex\Provider\SessionServiceProvider());

// Register a custom error handler for SilexBlog\PostNotFoundException exceptions
$app->error(function(SilexBlog\PostNotFoundException $e, $code) use ($app) {
    if ($code != 404) {
        return;
    }

    return $app->render('404.twig', array('message' => 'Post not found!'));
});

// Register a security service provider which controls authentication and authorization for our application
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        // The login firewall makes it to that the path /user/login doesn't require authentication to access
        'login' => array(
            'pattern' => '^/user/login$',
        ),
        // The user firewall controls access to our application.
        'user' => array(
            // The following URLs require authentication /user* and /blog/new-post* (where * is anything
            //   of characters).
            'pattern' => '(^/user)|(^/blog/new-post)',
            // Setting 'http' to true will use HTTP based authentication instead of a form
            //'http' => true,
            // Use a HTML form to login
            'form' => array('login_path' => '/user/login', 'check_path' => '/user/login_check'),
            // The path to logout.
            'logout' => array('logout_path' => '/user/logout', 'target_url' => '/user/login', 'invalidate_session' => true),
            // Define how users are looked up.
            'users' => $app->share(function () use ($app) {
                return new SilexBlog\UserProvider($app['db']);
            }),
        ),
    ),
    // For help debugging, tell the user if the username doesn't exist.
    'security.hide_user_not_found' => false,
    // Access rules define what ROLE a user must have to access a URL
    'security.access_rules' => array(
        array('^/blog/new-post', 'ROLE_USER'),
    ),
));

// The login form.
$app->get('/user/login', function(Request $request) use ($app) {
    return $app['twig']->render('login.twig', array(
        'error'         => $app['security.last_error']($request),
        'last_username' => $app['session']->get('_security.last_username'),
    ));
});

// Show a list of the current user has.
$app->get('/user/show_roles', function () use ($app) {
    // Get the token for currently logged in user.
    $token = $app['security.token_storage']->getToken();
    // If the token is null, no user is logged in.
    if (!is_null($token)) {
        // Use the token to get the user object for the currently logged in user.
        $user = $token->getUser();
    } else {
        return "There is no current user.  This shouldn't happen.";
    }

    // Show the user his/her username and his/her list of roles.
    return 'You are logged in as the user ' . $user->getUsername() . " with the following roles: " . implode(', ', $user->getRoles());
});

// A test page to see if a user is logged in ok.
$app->get('/user/test', function () use ($app) {
    return "Test Page - You're in!";
});

// A controller to process the route /blog/
$app->get('/blog/', function () use ($app) {
    // Use the static function Post::getAll to get all the posts in the database.
    $posts = $app['repository.post']->findAll();
    
    // Set the page title variable.
    $page_title = 'List of All the Blog Posts';

    // Pass the page title and posts to twig to be rendered using the list_posts.twig template.
    return $app->render('list_posts.twig', array('page_title' => $page_title, 'posts' => $posts));
})
->bind('findAllPosts');

// A controller to process the route /blog/id/{id} where id is a post id.
// Example: /blog/4
$app->get('/blog/id/{id}', function ($id) use ($app) {
    // Use the static function Post::getById to get the specified post by its id.
    $post = $app['repository.post']->find($id);

    // Set the page title to the blog post title and author
    $page_title = $post->getTitle() . ' by ' . $post->getAuthor();

    // Pass the page title and post to twig to be rendered using the list_posts.twig template.
    return $app->render('list_posts.twig', array('page_title' => $page_title, 'posts' => array($post)));
})
->bind('findPost');

// A controller to process the route for /blog/author/{author} where author is the post author.
// YOUR CODE HERE

// HINTS:
// Look at the /blog/id/{id} route to see how to specify the route.  Since you will be getting
// multiple posts, use a $app->render call like the one used in the /blog/ route that lists
// all the blog posts.
//
// You will need to add a getByAuthor method to the Post class.

$app->match('/blog/new-post', function (Request $request) use ($app) {
    $data = array(
        'author' => 'Your name',
        'title' => 'Title of the Post',
        'body' => 'Blog post content',
    );

    $form = $app->form($data)
        ->add('author',  TextType::class)
        ->add('title', TextType::class, array('constraints' => array(new Assert\NotBlank())))
        ->add('body', TextareaType::class, array('constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 20)))))
        ->getForm();

    $form->handleRequest($request);

    if ($form->isValid()) {
        $data = $form->getData();

        $post = SilexBlog\PostFactory::create($data['author'], $data['title'], $data['body']);
        $app['repository.post']->save($post);

        // redirect somewhere
        return $app->redirect('/blog/');
    }

    // display the form
    return $app->render('new-post.twig', array('form' => $form->createView()));

});
// Return the service container used by web/index.php
return $app;