<?php
/**
 * Created by JetBrains PhpStorm.
 * User: justin
 * Date: 4/26/12
 * Time: 10:26 AM
 * To change this template use File | Settings | File Templates.
 */

chdir(dirname(__FILE__));

require '../../bootstrap.php';

$stats = new stdClass();

$minions = $predis->hgetall('minion.status');
ksort($minions, SORT_NUMERIC);

$servers = array();
foreach($minions as $m)
{

	$m = json_decode($m);
	$key = 'server_'.str_replace('.', '_', $m->ip);
	if(!isset($servers[$key]))
	{
		$servers[$key] = array();
	}

	$m->server_id = $key;

	$servers[$key]['minion_'.$m->minion_id] = $m;

}

$stats->servers = $servers;

echo json_encode($stats);
exit();