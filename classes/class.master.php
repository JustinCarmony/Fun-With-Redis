<?php
/**
 * Created by JetBrains PhpStorm.
 * User: justin
 * Date: 4/26/12
 * Time: 9:24 AM
 * To change this template use File | Settings | File Templates.
 */

class Master
{
	/**
	 * @var Predis\Client
	 */
	protected $predis = null;
	protected $instance_id = null;
	protected $reboot_id = null;
	protected $cmd_count = null;
	protected $cmd_cps = null;

	const MINION_HEARTBEAT_TIMEOUT = 15;

	public function __construct($predis)
	{
		$this->predis = $predis;
	}

	public function Log($txt)
	{
		echo "[".number_format(round(microtime(true), 2), 2, '.', '')."] ".$txt."\n";
	}

	public function Run()
	{
		$this->Startup();
		$work = true;
		$count = 0;
		while($work)
		{
			$count++;

			$this->Work();

			if($count >= 60)
			{
				$count = 0;
				$this->Log("Current Commands per Second: ".$this->cmd_cps);
			}

			// Check to see if a master reboot has been issued
			if($this->predis->get('reboot.master') != $this->reboot_id)
			{
				$this->Log("Reboot Detected!");
				$this->Log("Shutting Down...");
				return;
			}

			// Check to see if master reset has been called
			if($this->predis->get('system.reset'))
			{
				$this->Log("System Reset Detected... Executing...");
				$this->predis->del('system.reset');
				// Re-startup
				$this->Startup();
				$this->Log("System Reset Complete, New Instace ID: ".$this->instance_id);
			}

			// Wait exactly one second
			usleep(1000000);
		}
	}

	public function Work()
	{
		// Determine Current CpS
		$info = $this->predis->info();
		$last_cmd_count = $this->cmd_count;
		$this->cmd_count = $info['total_commands_processed'];
		$this->cmd_cps = $this->cmd_count - $last_cmd_count;
		$this->predis->set('stats.cps', $this->cmd_cps);
		$max_cps = $this->predis->get('stats.cps_max');
		if($this->cmd_cps > $max_cps)
		{
			$this->predis->set('stats.cps_max', $this->cmd_cps);
		}


		// Determine Current CPU
		$process_id = $info['process_id'];
		file_put_contents('/tmp/redis_process_id', $process_id);
		$cpu = trim(exec("ps S -p $process_id -o pcpu="));
		$this->predis->set('stats.cpu', $cpu);

		// Prune Workers
		$time = time();
		$heartbeats = $this->predis->hgetall('minion.heartbeats');

		foreach($heartbeats as $minion_id => $last_hb)
		{
			if($time - $last_hb > self::MINION_HEARTBEAT_TIMEOUT)
			{
				$this->Log("Heartbeat Expired for $minion_id");
				$this->predis->hdel('minion.heartbeats', $minion_id);
				$this->predis->hdel('minion.status', $minion_id);
			}
		}

	}

	public function Startup()
	{
		$this->Log("Executing Master Startup...");
		$this->predis->set('minion.incr', 0);
		$this->instance_id = $this->predis->incr('system.instance');
		$this->Log("Instance ID: ".$this->instance_id);
		$this->reboot_id = $this->predis->get('reboot.master');
		if(!$this->reboot_id)
		{
			$this->predis->set('reboot.master', 1);
			$this->reboot_id = 1;
		}

		$this->Log("Reboot ID: ".$this->reboot_id);

		// Set Defaults for new instance
		$this->Log("Setting system.workforce to 0");
		$this->predis->set('system.workforce', 0);

		$this->Log("Setting system.mode to idle");
		$this->predis->set('system.mode', 'idle');

		// Reset minion heartbeats and statuses
		$this->predis->del('minion.heartbeats');
		$this->predis->del('minion.status');
	}
}