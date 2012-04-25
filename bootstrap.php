<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require 'config.php';
require 'library/php-cloudservers/Cloud/Cloud.php';
require 'library/predis/lib/Predis/Autoloader.php';

Predis\Autoloader::register();

$predis = new Predis\Client(REDIS_MASTER_CONN);