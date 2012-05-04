<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

set_time_limit(0);

chdir(dirname(__FILE__));

require 'config.php';
require 'library/php-cloudservers/Cloud/Cloud.php';
require 'library/predis/lib/Predis/Autoloader.php';
require 'classes/class.master.php';
require 'classes/class.minion.php';
require 'classes/class.bootstrap.php';

Predis\Autoloader::register();

if(defined('IS_MINION'))
{
	define('REDIS_CONN', REDIS_MINION_CONN);
}
else
{
	define('REDIS_CONN', REDIS_MASTER_CONN);
}

$predis = new Predis\Client(REDIS_CONN);