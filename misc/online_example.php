<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


require_once '../library/predis/lib/Predis/Autoloader.php';

Predis\Autoloader::register();

$redis = new Predis\Client('tcp://10.0.0.1:6379');

/* Store Current User */
// Current User ID
$user_id = 1234;
// Get Current Time
$now = time();
$min = date("i",$now);
// Generate the Key
$key = "online:".$min;
// Adding user to online users
$redis->sadd($key, $user_id);
$redis->expire($key, 60 * 10); // Expire in 10 minutes

/* Getting Onling Users */
$keys = array();
// Get Current Time
$min = date("i",time());

$count = 0;
$minutes_ago = 5;
while($count < $minutes_ago)
{
	$keys[] = "online:".$min;
	$count++;
	$min--;
	if($min < 0) { $min = 59; }
}

$scmd = $redis->createCommand("sunion",$keys);
$online_ids = $redis->executeCommand($scmd);

/* My Friends Online */
$keys = array('online_users');
$user_id = '1234';
// Get Current Time
$min = date("i",time());

$count = 0;
$minutes_ago = 5;
while($count < $minutes_ago)
{
	$keys[] = "online:".$min;
	$count++;
	$min--;
	if($min < 0) { $min = 59; }
}

// SUNIONSTORE online_users online:10 online:9 online:8 online:7 online:6
$scmd = $redis->createCommand("sunionstore",$keys);
$redis->executeCommand($scmd);

$online_friend_ids = $redis->sinter('online_users'
	, 'user:'.$user_id.'.friend_ids');

/* Do something with the $data */
