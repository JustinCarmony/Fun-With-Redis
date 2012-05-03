<?php
/**
 * Created by JetBrains PhpStorm.
 * User: justin
 * Date: 4/26/12
 * Time: 10:26 AM
 * To change this template use File | Settings | File Templates.
 */

chdir(dirname(__FILE__));

$stats = new stdClass();

$process_id = file_get_contents('/tmp/redis_process_id');
$cmd = "pidstat -p $process_id 1 1";
$output = array();
exec($cmd, $output);

$line = $output[3]; // get the line we're interested in
str_replace("\t", ' ', $line); // Remove Tabs
// Strip out extra spaces
while(str_replace("  ", " ", $line) != $line)
{
	$line = str_replace("  ", " ", $line);
}

$parts = explode(' ', $line);
$cpu = $parts[2];

$stats->lblCpuUsage = $cpu;
echo json_encode($stats);
exit();