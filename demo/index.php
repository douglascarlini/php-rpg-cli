<?php

define('DS', DIRECTORY_SEPARATOR);
define('DEMO', dirname(__FILE__));
define('LIB', dirname(dirname(__FILE__)) . DS . "lib");

spl_autoload_register(function ($class) {
    $path = DEMO . DS . "{$class}.php";
    if (file_exists($path)) {
        include $path;
    }
    $path = LIB . DS . "{$class}.php";
    if (file_exists($path)) {
        include $path;
    }
});

$game = new Awesome('Awesome Game');

$actor = new Actor('Doug Mage', 'plant', $game->newData());
$actor->weapons[] = new Weapon('Sword', $game->newData());
$actor->weapons[] = new Weapon('Bite', $game->newData());
$actor->weapons[] = new Weapon('Bow', $game->newData());
$actor->supplies = $game->newSupplies();
$actor->states = $game->newStates();
$actor->stats = $game->newStats();
$actor->state = 'idle';
$actor->weapon = 0;

$game->actors[] = $actor;

$game->createThreat('Dragon Evil', 'fire', 'Breath of Fire');
$game->createThreat('Math Ork', 'plant', 'Mace of Destine');
$game->createThreat('Silver Wolf', 'rock', 'Cursed Bite');

$game->run();
