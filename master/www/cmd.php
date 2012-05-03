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

$return = new stdClass();

// Hack because its faster to do this than call user func
$cmd = '';
if(isset($_POST['cmd']))
{
	$cmd = $_POST['cmd'];
}

if(!$cmd)
{
	$return->success = 'false';
	$return->error = 'missing cmd';
	echo json_encode($return);
	exit();
}

if(!isset($_POST['args']))
{
	$return->return = $predis->$cmd();
}
else
{
	$args = $_POST['args'];
	if(count($args) == 1)
	{
		$return->return = $predis->$cmd($args[0]);
	}
	else if(count($args) == 2)
	{
		$return->return = $predis->$cmd($args[0], $args[1]);
	}
	else if(count($args) == 3)
	{
		$return->return = $predis->$cmd($args[0], $args[1], $args[2]);
	}
	else if(count($args) == 4)
	{
		$return->return = $predis->$cmd($args[0], $args[1], $args[2], $args[3]);
	}
	else if(count($args) == 5)
	{
		$return->return = $predis->$cmd($args[0], $args[1], $args[2], $args[3], $args[4]);
	}
	else if(count($args) == 6)
	{
		$return->return = $predis->$cmd($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);
	}
	else if(count($args) == 7)
	{
		$return->return = $predis->$cmd($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6]);
	}
	else
	{
		$return->return = $predis->$cmd($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7]);
	}
}


$return->success = 'true';

echo json_encode($return);
exit();