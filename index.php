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
        '^/register/(.+)' => "root/edit/\\1"
    )
);

function action_root_index ($arg = null, $arg2 = null) {
    $v = new View ("index");
    $v->render();
}

TinyMvc::run ($config);
?>
