<?php
/**
 * Created by JetBrains PhpStorm.
 * User: justin
 * Date: 4/26/12
 * Time: 9:23 AM
 * To change this template use File | Settings | File Templates.
 */

class Minion
{
	/**
	 * @var Predis\Client
	 */
	public $predis;
	public $internal_id;
	public $instance_id;
	public $reboot_id;
	public $minion_id;
	public $working = false;
	public $method = 'idle';
	public $pipeline = 'off';
	public $pipeline_count = 100;


	public function __construct($predis, $internal_id)
	{
		$this->predis = $predis;
		$this->internal_id = $internal_id;
	}

	public function Log($txt)
	{
		echo "[".number_format(round(microtime(true), 2), 2, '.', '')."] ".$txt."\n";
	}

	public function Run()
	{
		$this->Startup();
		$count = 0;

		while(1)
		{
			$count++;

			$this->method = $this->predis->get('system.mode');
			$this->pipeline = $this->predis->get('system.pipeline');
			$this->pipeline_count = $this->predis->get('system.pipeline_count');

			$this->Work();

			if($count >= 60)
			{
				$count = 0;
			}

			// Check to see if a master reboot has been issued
			if($this->predis->get('reboot.minion') != $this->reboot_id)
			{
				$this->Log("Reboot Detected!");
				$this->Log("Shutting Down...");
				return;
			}

			// Check to see if master reset has been called
			if($this->predis->get('system.instance') != $this->instance_id)
			{
				$this->Log("System Reset Detected... Executing...");
				// Re-startup
				$this->Startup();
				$this->Log("System Reset Complete, New Instace ID: ".$this->instance_id);
			}
		}
	}

	public function Startup()
	{
		$this->Log("Executing Minion Startup...");
		$this->Log("Internal ID: ".$this->internal_id);
		$this->instance_id = $this->predis->get('system.instance');
		$this->Log("Instance ID: ".$this->instance_id);

		$this->minion_id = $this->predis->incr('minion.incr');
		$this->log("Minion ID: ".$this->minion_id);

		$this->reboot_id = $this->predis->get('reboot.minion');
		if(!$this->reboot_id)
		{
			$this->predis->set('reboot.minion', 1);
			$this->reboot_id = 1;
		}

		$this->Log("Reboot ID: ".$this->reboot_id);

		$this->method = 'idle';

		// Check in with a hearbeat
		$this->Heartbeat();
	}

	public function Heartbeat()
	{
		$time = time();
		$this->predis->hset('minion.heartbeats', $this->minion_id, $time);
		$status = new stdClass();
		$status->working = $this->working;
		$status->heartbeat = $time;
		$ips = Utility::GetMachineIPs();
		$status->ip = $ips[3]; // Get the internal ID
		$status->hostname = gethostname();
		$this->predis->hset('minion.status', $this->minion_id, json_encode($status));
	}

	public function Work()
	{
		$percent = $this->predis->get('system.workforce') / 10;

		$this->working = false;

		if($percent < 1)
		{
			usleep(1000000);
			return;
		}

		if($this->minion_id % 10 > $percent)
		{
			usleep(1000000);
			return;
		}

		$this->working = true;

		$method = $this->method;
		if(!$method)
		{
			$method = 'Idle';
		}

		$this->$method();
	}

	public function Idle()
	{
		usleep(1000000);
		echo ".";
	}

	public function Increment()
	{
		$count = 0;
		$limit = 5000;
		if($this->pipeline == 'on')
		{
			$pipe = null;
			while($count < $limit)
			{
				$count++;
				if(!$pipe)
				{
					$pipe = $this->predis->pipeline();
				}
				$pipe->incr('increment.value');
				if($count % $this->pipeline_count == 0)
				{
					$pipe->execute();
					unset($pipe);
				}
			}

			if($pipe)
			{
				$pipe->execute();
			}
		}
		else
		{
			while($count < $limit)
			{
				$count++;
				$this->predis->incr('increment.value');
			}
		}
	}

	public function Random_Number()
	{
		$count = 0;
		$limit = 5000;
		if($this->pipeline == 'on')
		{
			$pipe = null;
			while($count < $limit)
			{
				$count++;
				if(!$pipe)
				{
					$pipe = $this->predis->pipeline();
				}

				$num = rand(1, 10000000);

				$pipe->set('random_number.set', $num, $num);
				if($count % $this->pipeline_count == 0)
				{
					$pipe->execute();
					unset($pipe);
				}
			}

			if($pipe)
			{
				$pipe->execute();
			}
		}
		else
		{
			while($count < $limit)
			{
				$count++;
				$num = rand(1, 10000000);

				$this->predis->set('random_number.set', $num, $num);
			}
		}
	}

	public function Md5_Gen()
	{
		usleep(1000000);
		echo ".";
	}

	public function Rand_Read()
	{
		usleep(1000000);
		echo ".";
	}

	public function Rand_Write()
	{
		usleep(1000000);
		echo ".";
	}

	public function Bench()
	{
		usleep(1000000);
		echo ".";
	}
}