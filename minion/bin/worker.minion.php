<?php
/**
 * Created by JetBrains PhpStorm.
 * User: justin
 * Date: 4/26/12
 * Time: 9:25 AM
 * To change this template use File | Settings | File Templates.
 */

define('IS_MINION', true);

echo "**** INIT ****\n";

chdir(dirname(__FILE__));

echo "Starting Bootstrap...\n";

require '../../bootstrap.php';

echo "Starting Minion Worker...\n";

$internal_id = $argv[1];

$master = new Minion($predis, $internal_id);
$master->Run();
