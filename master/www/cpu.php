<?php
/**
 * Created by JetBrains PhpStorm.
 * User: justin
 * Date: 4/26/12
 * Time: 10:26 AM
 * To change this template use File | Settings | File Templates.
 */

chdir(dirname(__FILE__));

ini_set('display_errors', 1);
error_reporting(E_ALL);

$stats = new stdClass();

$process_id = file_get_contents('/tmp/redis_process_id');
if(count($process_id) > 0)
{
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
}
else
{
    $stats->error = 'Process ID Missing';
}

echo json_encode($stats);
exit();