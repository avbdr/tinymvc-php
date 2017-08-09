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
    ),
    'salt' => 'test'
);

function action_root_index ($arg = null, $arg2 = null) {
    $c = new Controller();
    return new View ("index");
}

function action_test_index () {
    return new View ("test");
}

function action_test_json() {
    return [
        "page" => 1,
        "limit" =>  10,
        "total" => 4, 
        'test' => [
            ['part1' => 'hello2', 'part2' => 'world'],
            ['part1' => 'hello', 'part2' => 'world2'],
            ['part1' => 'hello', 'part2' => 'world3'],
            ['part1' => 'hello', 'part2' => 'world4'],
            ['part1' => 'hello', 'part2' => 'world5'],
        ],
    ];
}


TinyMvc::run ($config);
?>
