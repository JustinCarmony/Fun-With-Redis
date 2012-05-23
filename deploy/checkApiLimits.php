<?php
/**
 * Created by JetBrains PhpStorm.
 * User: jcarmony
 * Date: 5/22/12
 * Time: 10:22 PM
 * To change this template use File | Settings | File Templates.
 */

chdir(dirname(__FILE__));

$start_time = time();

// SSH ssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no

echo "**** INIT ****\n";
echo "Starting Bootstrap...\n";

require '../bootstrap.php';

$cloud = new Cloud_Server(API_ID, API_KEY);
var_dump($cloud->getLimits());