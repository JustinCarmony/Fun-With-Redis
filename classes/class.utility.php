<?php
/**
 * Created by JetBrains PhpStorm.
 * User: justin
 * Date: 5/4/12
 * Time: 1:47 AM
 * To change this template use File | Settings | File Templates.
 */

class Utility
{


	static public function GetMachineIPs()      {
		$ips = array();
		exec("/sbin/ifconfig", $catch);
		foreach($catch as $line){
			if (preg_match('/inet addr\:/i', $line)) {
				$line = str_replace(" ", ":", $line);
				$line = explode(":", $line);
				$line = array_filter($line);
				foreach ($line as $v)   {
					if (ip2long($v))        {
						$ips[] = $v;
					}
				}
			}
		}
		return $ips;
	}
}