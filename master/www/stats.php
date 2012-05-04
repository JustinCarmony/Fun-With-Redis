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

// Get Minion Statuses
$info = $predis->info();
$stats->lblTotalCommands = $info['total_commands_processed'];

$minions = $predis->hgetall('minion.status');

//var_dump($minions);

$minions_total = count($minions);
$minions_active = 0;
foreach($minions as $minion_json)
{
	$data = json_decode($minion_json);
	if($data->working)
	{
		$minions_active++;
	}
}

$stats->lblMinionsCount = $minions_total;
$stats->lblMinionsActive = $minions_active;

// Get Status for Mode
$mode = $predis->get('system.mode');

$modeStatus = '<h1 style="text-align:center;"> Unknown Status for Mode: '.$mode.'</h1>';

switch ($mode)
{
	case 'idle':
		$modeStatus = '<h1 style="text-align:center;">I\'m Idling... Please touch my buttons...</h1>';
		break;
	case 'increment':
		$val = number_format($predis->get('increment.value'));
		$modeStatus = '<h1 style="text-align:center;">Current Increment Value: '.$val.'</h1>';
		break;
	case 'random_number':
		$len = $predis->hlen('random_number.set');
		$modeStatus = '<h1 style="text-align:center;">Randomly Generated '.number_format($len).' of 10,000,000</h1>';
		break;
}
$stats->lblModeStatus = $modeStatus;

// Get the Commands Per Second
$stats->lblCmdPerSec = number_format($predis->get('stats.cps'));
echo json_encode($stats);
exit();