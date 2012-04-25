<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


require_once '../library/predis/lib/Predis/Autoloader.php';

Predis\Autoloader::register();

$redis = new Predis\Client('tcp://10.0.0.1:6379');

$key = 'cache.user:justin';

$data_str = $redis->get($key);

if($data_str)
{
	$data = unserialize($data_str);
}
else
{
	// Really Expensive Method of Getting This Data
	$data = MyDatabase::GetExpensiveData();
	$redis->setex($key, 60, serialize($data));
}

/* Do something with the $data */
