<?php
require 'Here.class.php';

$here = new Here("sua api key");

$address = "Rua José Clemente, 604, Maringa - Paraná";
$location = $here->getLatLog($address);

$address = "Av João Paulino Vieira Filho, 672, Maringá - Paraná";
$location2 = $here->getLatLog($address);

$coordinatesA = "{$location->coordinates->lat},{$location->coordinates->lon}";
$coordinatesB = "{$location2->coordinates->lat},{$location2->coordinates->lon}";
$direction = $here->getDirection($coordinatesA, $coordinatesB);

var_dump([
    $location,
    $location2,
    $direction
]);
