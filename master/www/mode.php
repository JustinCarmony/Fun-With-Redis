<?php
/**
 * Created by JetBrains PhpStorm.
 * User: justin
 * Date: 4/26/12
 * Time: 10:26 AM
 * To change this template use File | Settings | File Templates.
 */

set_time_limit(0);

chdir(dirname(__FILE__));

require '../../bootstrap.php';

$return = new stdClass();

$prev_mode = $predis->get('system.mode');



// Hack because its faster to do this than call user func
$mode = $_POST['mode'];

$predis->set('system.mode', $mode);

switch($mode)
{
	case 'increment':
		$predis->set('increment.value', 0);
		break;
	case 'idle':
	default:
		// Do nothing! We're Idling
		break;
}

if($mode != 'increment')
{
	$predis->del('increment.value');
}

if($mode != 'random_number')
{
	$keys = $predis->keys('random_number.*');
    foreach($keys as $k)
    {
        $predis->del($k);
    }
}

echo '{ success: "true" }';