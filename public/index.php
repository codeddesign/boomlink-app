<?php
use \Phalcon\Loader;
use \Phalcon\DI\FactoryDefault;
use \Phalcon\Mvc\View;
use \Phalcon\Session\Adapter\Files;
use \Phalcon\Db\Adapter\Pdo\Mysql;
use \Phalcon\Mvc\Collection\Manager as CollectionManager;
use \Phalcon\Mvc\Model\Manager as ModelsManager;
use \Phalcon\Mvc\Application;
use \Phalcon\Exception;

/* configuration handle: */
function custom_array_merge_recursive(array &$config, array &$dev)
{
    $merged = $config;
    foreach ($dev as $key => &$value) {
        if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
            $merged[$key] = custom_array_merge_recursive($merged[$key], $value);
        } else {
            $merged[$key] = $value;
        }
    }

    return $merged;
}

// get config data:
$config = require_once('config.php');
$dev_mode = false;

// overwrite with data from config.dev.php (if file exists):
if (file_exists('config.dev.php')) {
    $dev = require_once('config.dev.php');
    $config = custom_array_merge_recursive($config, $dev);

    $dev_mode = true;
}

// run app:
try {
    // register an autoloader
    $loader = new Loader();
    $loader->registerDirs(array(
        '../app/controllers/',
        '../app/models/'
    ))->register();

    // autoload PHPMailer;
    $loader->registerClasses(
        array(
            "PhpMailer" => "PHPMailer/class.phpmailer.php",
        )
    );

    // create a DI
    $di = new FactoryDefault();

    // setting up the models component
    $di->setShared('modelsManager', function () {
        return new ModelsManager();
    });

    // setting up the view component
    $di->setShared('view', function () {
        $view = new View();
        $view->setViewsDir('../app/views/');
        return $view;
    });

    // setting up the collection component
    $di->set('collectionManager', function () {
        return new CollectionManager();
    }, true);

    // global Initialization of Phalcon Sessions
    $di->setShared('session', function () {
        $session = new Files();
        $session->start();

        // if an user is logged in then we add some extra information to the session:
        if ($session->has("user-id")) {
            $user = Administration::findfirst("id=" . $session->get("user-id"));
            if (isset($user->id)) {
                $session->set("user-id", $user->id);
                $session->set("user-name", $user->username);
                $session->set("user-role", $user->type);
                $session->set("user-domains", json_decode($user->domains, true)); // allowed domains for editing
                $session->set("user-pages", json_decode($user->pages, true)); // - contains blocked pages

                if ($user->type === "admin" or $user->type === "master" or $user->editing === "true") {
                    $session->set("user-editing", true);
                } else {
                    $session->set("user-editing", false);
                }
            }
        }

        return $session;
    });

    // setting up the database service
    $di->setShared('db', function () use ($config) {
        return new Mysql($config['db']);
    });

    // setup SMTP:
    $di->setShared("email_config", function () use ($config) {
        return $config['email_config'];
    });

    // returns something like: http(s)://localhost
    $di->setShared('app_link', function () use ($config) {
        $scheme = (!isset($_SERVER['REQUEST_SCHEME'])) ? 'http' : $_SERVER['REQUEST_SCHEME'];
        
        return sprintf('%s://%s/%s', $scheme, $_SERVER['SERVER_NAME'], $config['app_extra_path']);
    });

    // setup the rest of the needed information:
    $di->setShared('dev_mode', function () use ($dev_mode) {
        return $dev_mode;
    });

    $di->setShared('pages_no_auth', function () use ($config) {
        return $config['pages_no_auth'];
    });

    $di->setShared('pages_do_log', function () use ($config) {
        return $config['pages_do_log'];
    });

    $di->setShared('log_skip_actions', function () use ($config) {
        return $config['log_skip_actions'];
    });

    // handle the request
    $application = new Application($di);

    // het output contents
    echo $application->handle()->getContent();
} catch (Exception $e) {
    // fired when there is any error or warning occur during the program work;
    echo "PhalconException: ", $e->getMessage();
}