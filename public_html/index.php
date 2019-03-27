<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);

use vendor\core\Router;

$query = rtrim($_SERVER['QUERY_STRING'], '/');

session_start();

define('WWW', __DIR__);
define('CORE', dirname(__DIR__) . '/vendor/core');
define('ROOT', dirname(__DIR__));
define('APP', dirname(__DIR__) . '/app');
define('LAYOUT', 'default');

require '../vendor/libs/functions.php';
require '../vendor/libs/simple_html_dom.php';
require '../vendor/autoload.php';


spl_autoload_register(function ($class) {
    $file = ROOT . '/' . str_replace('\\', '/', $class) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

Router::add('^$', ['controller' => 'Main', 'action' => 'index']);
Router::add('^(?P<controller>[a-z-]+)\/?(?P<action>[a-z-]+)?$');

Router::dispatch($query);