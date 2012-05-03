<?php
/**
 * Created by JetBrains PhpStorm.
 * User: justin
 * Date: 4/26/12
 * Time: 10:59 AM
 * To change this template use File | Settings | File Templates.
 */

chdir(dirname(__FILE__));

echo "Starting Bootstrap...\n";

require '../../bootstrap.php';

$count = 800000;
$limit = 1000000;

$pipe = $predis->pipeline();
while($count < $limit)
{
	$md5 = md5($count);
	$pipe->set('junk.'.$md5, $md5.$md5.$md5.$md5.$md5.$md5.$md5.$md5.$md5.$md5.$md5.$md5.$md5.$md5.$md5.$md5.$md5.$md5.$md5.$md5.$md5.$md5);
	$count++;
	if($count % 1000 == 0)
	{
		echo ".";
	}
	if($count % 10000 == 0)
	{
		echo "\n$count\n";
		$pipe->execute();
	}
}

$pipe->execute();

echo "  Done!\n";