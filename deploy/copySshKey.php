<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jcarmony
 * Date: 5/22/12
 * Time: 11:14 PM
 * To change this template use File | Settings | File Templates.
 *
 * I screwed up my deploy, deployed 50 servers w/o my root key on them....
 * This is a script to help me fix that
 */


chdir(dirname(__FILE__));

$start_time = time();

// SSH ssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no

echo "**** INIT ****\n";
echo "Starting Bootstrap...\n";

require '../bootstrap.php';

$servers = $predis->hgetall('server.minions.info');

ksort($servers, SORT_NUMERIC);

echo "\nServer Count: ".count($servers)."\n";

foreach($servers as $srv_json)
{
    $srv = json_decode($srv_json);
    echo "\n\n**********\n";
    echo "Server: $srv->name Password: $srv->adminPass\n";
    $cmd = "ssh-copy-id root@".$srv->addresses->public[0];
    echo "\n\n ... Done!\n";
}

echo "DONE!!!!!\n";
