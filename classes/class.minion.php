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
	public $latency_ms = null;
	public $latency_start = 0;

    const ALLOWED_CHARS = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ`~!@#$%^&*()-=_\\+[]{}|;':\",./<>?";


	public function __construct($predis, $internal_id)
	{
		$this->predis = $predis;
		$this->internal_id = $internal_id;
	}

	public function Log($txt)
	{
		echo "[".number_format(round(microtime(true), 2), 2, '.', '')."] ".$txt."\n";
	}

	public function StartLatency()
	{
		$this->latency_start = microtime(true);
	}

	public function EndLatency()
	{
		$this->latency_ms = round((microtime(true) - $this->latency_start) * 1000, 0);
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
				$this->predis->hdel('minion.heartbeat', $this->minion_id);
				$this->predis->hdel('minion.status', $this->minion_id);
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

			$this->Heartbeat();
		}
	}

	public function Startup()
	{
		$this->Log("Random Sleep to offset Minions");

		usleep(rand(1000000,5000000));
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
		$status->minion_id = $this->minion_id;
		$status->internal_id = $this->internal_id;
		$status->working = $this->working;
		$status->heartbeat = $time;
		$status->latency_ms = $this->latency_ms;
		$ips = Utility::GetMachineIPs();
		$status->ip = $ips[3]; // Get the internal ID
		$status->hostname = gethostname();
		$this->predis->hset('minion.status', $this->minion_id, json_encode($status));
	}

	public function Work()
	{
		$this->StartLatency();
		$percent = $this->predis->get('system.workforce') / 10;
		$this->EndLatency();

		$this->working = false;

		if($percent < 1)
		{
			usleep(4000000);
			return;
		}

		if(($this->minion_id % 10) > $percent - 1)
		{
			usleep(4000000);
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
		usleep(4000000);
		echo ".";
	}

	public function Increment()
	{
		$count = 0;
		$limit = 1000;
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
					$this->StartLatency();
					$pipe->execute();
					$this->EndLatency();
					unset($pipe);
				}
			}

			if($pipe)
			{
				$this->StartLatency();
				$pipe->execute();
				$this->EndLatency();
			}
		}
		else
		{
			while($count < $limit)
			{
				$count++;
				$this->StartLatency();
				$this->predis->incr('increment.value');
				$this->EndLatency();
			}
		}
	}

	public function Random_Number()
	{
		$count = 0;
		$limit = 1000;
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

				$num = rand(1, 5000000);
                $key_num = floor($num / 100000);

				$pipe->hset('random_number.set:'.$key_num, $num, $num);
				if($count % $this->pipeline_count == 0)
				{
					$this->StartLatency();
					$pipe->execute();
					$this->EndLatency();
					unset($pipe);
				}
			}

			if($pipe)
			{
				$this->StartLatency();
				$pipe->execute();
				$this->EndLatency();
			}
		}
		else
		{
			while($count < $limit)
			{
				$count++;
				$num = rand(1, 5000000);
                $key_num = floor($num / 100000);

				$this->StartLatency();
				$this->predis->hset('random_number.set:'.$key_num, $num, $num);
				$this->EndLatency();
			}
		}
	}

	public function Md5_Gen()
	{
        $count = 0;
        $limit = 1000;
        if($this->pipeline == 'on')
        {
            $pipe = null;
            $end = $this->predis->incr('md5_gen.value', $limit);
            $start = $count = $end - $limit;
            while($count < $end)
            {
                $count++;
                if(!$pipe)
                {
                    $pipe = $this->predis->pipeline();
                }

                $value = self::GetHashFromID($count);
                $hash = md5($value);
                $group = substr($hash, 0, 2);
                $pipe->hset('md5_gen.set:'.$group, $hash, $value);
                if($count % $this->pipeline_count == 0)
                {
                    $this->StartLatency();
                    $pipe->execute();
                    $this->EndLatency();
                    unset($pipe);
                }
            }

            if($pipe)
            {
                $this->StartLatency();
                $pipe->execute();
                $this->EndLatency();
            }
        }
        else
        {
            $end = $this->predis->incr('md5_gen.value', $limit);
            $start = $count = $end - $limit;
            while($count < $end)
            {
                $count++;
                $value = self::GetHashFromID($count);
                $hash = md5($value);
                $group = substr($hash, 0, 2);

                $this->StartLatency();
                $this->predis->hset('md5_gen.set:'.$group, $hash, $value);
                $this->EndLatency();
            }
        }
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

    static public function GetHashFromID ($integer)
    {
        $base = self::ALLOWED_CHARS;
        $length = strlen($base);
        $out = '';
        while($integer > $length - 1)
        {
            $out = $base[fmod($integer, $length)] . $out;
            $integer = floor( $integer / $length );
        }
        return $base[$integer] . $out;
    }
}