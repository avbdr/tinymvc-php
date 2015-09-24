<?php
require_once ("libs/TinyMvc.php");

error_reporting (E_ALL|E_STRICT);
$config = Array (
    'defaultController' => 'root',
    'defaultAction' => 'index',
    'db' => Array (
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'db' => 'testdb',
        'prefix' => 't_'
    ),
    'routes' => Array (
        '^/signup.*' => "users/edit/"
    )
);

function action_root_index ($arg = null, $arg2 = null) {
    $c = new Controller();
    return new View ("index");
}

function action_root_test () {
    echo "<pre>";
    print_r ($_POST);
    echo "</pre>";
}

TinyMvc::run ($config);
?>
