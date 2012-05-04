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
	$key = str_replace('.', '_', $m->ip);
	$m = json_decode($m);
	if(!isset($servers[$key]))
	{
		$servers[$key] = array();
	}

	$servers[$key]['minion_'.$m->minion_id] = $m;
}

$stats->servers = $servers;

echo json_encode($stats);
exit();