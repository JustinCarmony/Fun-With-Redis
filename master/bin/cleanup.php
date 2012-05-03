<?php
/**
 * Created by JetBrains PhpStorm.
 * User: justin
 * Date: 4/26/12
 * Time: 11:15 AM
 * To change this template use File | Settings | File Templates.
 */

chdir(dirname(__FILE__));

echo "Starting Bootstrap...\n";

require '../../bootstrap.php';

$keys = $predis->keys('junk*');
$pipe = $predis->pipeline();

$count = 0;
foreach($keys as $k)
{
	$count++;
	$pipe->del($k);
	if($count % 10000 == 0)
	{
		$pipe->execute();
	}
}

$pipe->execute();