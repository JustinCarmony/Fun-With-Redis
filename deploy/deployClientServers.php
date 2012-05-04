<?php

/*
 * Flavors:
 * string(422) "{"flavors":[{"id":1,"ram":256,"disk":10,"name":"256 server"},{"id":2,"ram":512,"disk":20,"name":"512 server"},{"id":3,"ram":1024,"disk":40,"name":"1GB server"},{"id":4,"ram":2048,"disk":80,"name":"2GB server"},{"id":5,"ram":4096,"disk":160,"name":"4GB server"},{"id":6,"ram":8192,"disk":320,"name":"8GB server"},{"id":7,"ram":15872,"disk":620,"name":"15.5GB server"},{"id":8,"ram":30720,"disk":1200,"name":"30GB server"}]}"
 * 
 * Images:
 * string(2460) "{"images":[{"id":112,"status":"ACTIVE","updated":"2011-04-21T10:24:01-05:00","name":"Ubuntu 10.04 LTS"},{"id":81,"status":"ACTIVE","updated":"2011-10-04T08:39:34-05:00","name":"Windows Server 2008 R2 x64 - SQL Web"},{"id":58,"status":"ACTIVE","updated":"2010-09-17T07:19:20-05:00","name":"Windows Server 2008 R2 x64 - MSSQL2K8R2"},{"id":100,"status":"ACTIVE","updated":"2011-09-12T09:09:23-05:00","name":"Arch 2011.10"},{"id":31,"status":"ACTIVE","updated":"2010-01-26T12:07:44-06:00","name":"Windows Server 2008 SP2 x86"},{"id":108,"status":"ACTIVE","updated":"2011-11-01T08:32:30-05:00","name":"Gentoo 11.0"},{"id":109,"status":"ACTIVE","updated":"2011-11-03T06:28:56-05:00","name":"openSUSE 12"},{"id":24,"status":"ACTIVE","updated":"2010-01-26T12:07:04-06:00","name":"Windows Server 2008 SP2 x64"},{"id":110,"status":"ACTIVE","updated":"2011-08-17T05:11:30-05:00","name":"Red Hat Enterprise Linux 5.5"},{"id":57,"status":"ACTIVE","updated":"2010-09-17T07:16:25-05:00","name":"Windows Server 2008 SP2 x64 - MSSQL2K8R2"},{"id":111,"status":"ACTIVE","updated":"2011-09-12T10:53:12-05:00","name":"Red Hat Enterprise Linux 6"},{"id":120,"status":"ACTIVE","updated":"2012-01-03T04:39:05-06:00","name":"Fedora 16"},{"id":119,"status":"ACTIVE","updated":"2011-11-03T08:55:15-05:00","name":"Ubuntu 11.10"},{"id":116,"status":"ACTIVE","updated":"2011-08-17T05:11:30-05:00","name":"Fedora 15"},{"id":56,"status":"ACTIVE","updated":"2010-09-17T07:12:56-05:00","name":"Windows Server 2008 SP2 x86 - MSSQL2K8R2"},{"id":114,"status":"ACTIVE","updated":"2011-08-17T05:11:30-05:00","name":"CentOS 5.6"},{"id":115,"status":"ACTIVE","updated":"2011-08-17T05:11:30-05:00","name":"Ubuntu 11.04"},{"id":103,"status":"ACTIVE","updated":"2011-08-17T05:11:30-05:00","name":"Debian 5 (Lenny)"},{"id":104,"status":"ACTIVE","updated":"2011-08-17T05:11:30-05:00","name":"Debian 6 (Squeeze)"},{"id":118,"status":"ACTIVE","updated":"2011-08-17T05:11:30-05:00","name":"CentOS 6.0"},{"id":28,"status":"ACTIVE","updated":"2010-01-26T12:07:17-06:00","name":"Windows Server 2008 R2 x64"},{"id":106,"status":"ACTIVE","updated":"2011-08-17T05:11:30-05:00","name":"Fedora 14"},{"progress":100,"id":18377385,"status":"ACTIVE","created":"2012-02-02T20:40:33-06:00","updated":"2012-02-02T21:20:49-06:00","name":"daily","serverId":428406},{"progress":100,"id":18269682,"status":"ACTIVE","created":"2012-01-30T20:28:22-06:00","updated":"2012-01-30T21:06:18-06:00","name":"weekly","serverId":428406}]}"
 */

/*
object(stdClass)#18 (10) {
  ["progress"]=>
  int(0)
  ["id"]=>
  int(20788186)
  ["imageId"]=>
  int(112)
  ["flavorId"]=>
  int(2)
  ["status"]=>
  string(5) "BUILD"
  ["adminPass"]=>
  string(40) "1234"
  ["name"]=>
  string(31) "minion2-redis-justincarmony-com"
  ["hostId"]=>
  string(32) "d28257183105fa9d74fb6e27704d4328"
  ["addresses"]=>
  object(stdClass)#19 (2) {
    ["public"]=>
    array(1) {
      [0]=>
      string(13) "50.57.188.243"
    }
    ["private"]=>
    array(1) {
      [0]=>
      string(13) "10.182.97.170"
    }
  }
  ["metadata"]=>
  object(stdClass)#20 (2) {
    ["Creation"]=>
    string(23) "April 24, 2012, 8:21 pm"
    ["Original Name"]=>
    string(31) "minion2-redis-justincarmony-com"
  }
}

*/

chdir(dirname(__FILE__));

$start_time = time();

// SSH ssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no

echo "**** INIT ****\n";
echo "Starting Bootstrap...\n";

require '../bootstrap.php';

// Our CWD is the root dir where bootstrap.php is at

define('SSH_CMD', 'ssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no');
define('SCP_CMD', 'scp -o "StrictHostKeyChecking false" -o "UserKnownHostsFile /dev/null"');

echo "**** CLEAN UP ****\n";
echo "Clearing any previous deploying servers...\n";
//$predis->del('server.minions.deploying');


$servers_to_deploy = 1;
if(isset($argv[1]) && is_numeric($argv[1]))
{
	$servers_to_deploy = (int)$argv[1];
}

$servers_to_deploy = 0;



echo "Clean Up Complete\n";

echo "\n***** Number of Servers to Deploy: $servers_to_deploy ******\n";

$cloud = new Cloud_Server(API_ID, API_KEY);
//$cloud->enableDebug();
$cloud->addServerFile('/root/.ssh/authorized_keys', ROOT_PUB_KEY);


$server_start = $predis->get('server.minions.autoincr');
if(!$server_start)
{
	$server_start = 0;
	$predis->set('server.minions.autoincr', $server_start);
}

$server_end = $servers_to_deploy + $server_start;

$server_count = $server_start;


echo "**** STARTING TO CREATE SERVERS ****\n";
while($server_count < $server_end)
{
	$server_count = $predis->incr('server.minions.autoincr');
	$name = $server_name = MINION_PREFIX.$server_count.MINION_SUFFIX;
	echo "  Creating Server Number $server_count [$server_name]... ";
	
	if(!$predis->hget('server.minions.info', $server_count))
	{
		$response = $cloud->createServer($name, SERVER_IMAGE, SERVER_FLAVOR);
		if($response)
		{
			$server = json_decode($response);
			$server = $server->server;
			$predis->hset('server.minions.info', $server_count, json_encode($server));
			$predis->hset('server.minions.deploying', $server_count, $server_count);
			echo " Done!\n";
			echo "New Server #".$server_count
				."\n\tName: ".$server->name
				."\n\tPublic IP: ".$server->addresses->public[0]
				."\n\tPrivate IP: ".$server->addresses->private[0]
				."\n\tRoot Password: ".$server->adminPass
				."\n";
		}
		else
		{
			echo "\nTHERE WAS AN ERROR DEPLOYING $name !\n\n";
		}
	}
	echo "  Waiting 15 seconds...";
	sleep(15);
	echo " Done!\n";
}

echo "\nAll Servers Created.\n";

echo "\nMonitoring Servers for ACTIVE state...\n";

// Lets check on our deployed servers to see if they are done
while($predis->hlen('server.minions.deploying') > 0)
{

	$deploying_numbers = $predis->hgetall('server.minions.deploying');
	echo "Servers Still Deploying: ".count($deploying_numbers)."\n";
	foreach($deploying_numbers as $server_number)
	{
		echo "  Checking Server Number $server_number... ";
		// Get the server info
		$server_info = json_decode($predis->hget('server.minions.info', $server_number));
		$server_id = $server_info->id;
		$response = $cloud->getServer($server_id);
		$server_status = json_decode($response)->server;

		echo "Done!\n";

		if($server_status->status == 'ACTIVE')
		{
			echo "**** Server $server_count [{$server_info->name}] is Active! ****\n\n";
			$predis->hdel('server.minions.deploying', $server_number);
			// Do SSH Stuff!
			$cmd = SCP_CMD." deploy/scripts/minion-setup.sh root@".$server_info->addresses->public[0].":/tmp/minion-setup.sh";

			echo "Preparing to execute command: $cmd \n";
			echo "Executing... \n";
			system($cmd);
			echo "\n\n.... Done!\n";

			// Do SSH Stuff!
			$cmd = SCP_CMD." deploy/scripts/minion root@".$server_info->addresses->public[0].":/tmp/minion";

			echo "Preparing to execute command: $cmd \n";
			echo "Executing... \n";
			system($cmd);
			echo "\n\n.... Done!\n";

			// Do SSH Stuff!
			$cmd = SSH_CMD." root@".$server_info->addresses->public[0]." bash /tmp/minion-setup.sh";

			echo "Preparing to execute command: $cmd \n";
			echo "Executing... \n";
			system($cmd);
			echo "\n\n.... Done!\n";

			sleep(10);
			echo "Accepting New Keys\n";
			system("salt-key -A");
			system('salt -t 1 "'.$server_info->name.'" state.highstate');

		}
		else if($server_status->status == 'BUILD')
		{
			echo "  Server Number $server_number [{$server_info->name}] BUILD Progress: ".$server_status->progress."\n";
		}
		else
		{
			echo "  Server Number $server_number [{$server_info->name}] has Unknown status: ".$server_status->status."\n";
		}

		echo "  Waiting 15 seconds...";
		sleep(15);
		echo " Done!\n";
	}
}

echo "\n\n******* SERVERS DEPLOYED *********\n";

echo "\nWe're waiting a minute before executing salt highstate...\n";
sleep(60);

echo "\n\n******* Updating Via Salt *********\n";

system('salt "*" state.highstate');
system('salt "*" state.highstate');
system('salt "*" state.highstate');
system('salt "*" state.highstate');
system('salt "*" state.highstate');

echo "\n\n******** FINISHED! ***********\n";

$end_time = time();

$diff = $end_time - $start_time;

echo "Start Time: $start_time   End Time: $end_time     Seconds: $diff\n\n";