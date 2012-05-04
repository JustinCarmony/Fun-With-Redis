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

// Get the Commands Per Second
$stats->lblCmdPerSec = number_format($predis->get('stats.cps'));
echo json_encode($stats);
exit();