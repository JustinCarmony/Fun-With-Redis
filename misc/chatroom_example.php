<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


require_once '../library/predis/lib/Predis/Autoloader.php';

Predis\Autoloader::register();

$redis = new Predis\Client('tcp://10.0.0.1:6379');

