<?php

Class Modubot_Memusage extends Modubot_Module {
	public $helpline = 'returns the memory usage of the server. only available to admins.';
	//Only works on linux
	
	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		if($that->getLevel($sender, '', $that->host($data)) > 6){
			$raw = explode("\n", file_get_contents('/proc/meminfo'));
	        $data = array(
	            $raw[0],
	            $raw[1]
	        );
	        unset($raw);

		    $meminfo = array();
		    foreach ($data as $line) {
		        list($key, $val) = explode(':', $line);
		        $meminfo[$key] = str_replace(' kB', '', trim($val));
		    }
			if(strtolower($args) == 'modubot'){
				$usage = round(memory_get_usage(true) / 1024 / 1024);
				$percent = round((memory_get_usage(true) / 1024) / $meminfo['MemTotal']);
				$this->privmsg($socket, $channel, "{$sender}: I am using {$usage}MB of memory ({$percent}% of total memory).");

				return;
			}

		    $meminfo['MemUsed'] = $meminfo['MemTotal'] - $meminfo['MemFree'];
		    $usage = round($meminfo['MemUsed'] / $meminfo['MemTotal'] * 100);
		    $meminfo['MemTotal'] = round($meminfo['MemTotal'] / 1024);
		    $meminfo['MemUsed'] = round($meminfo['MemUsed'] / 1024);
		    $meminfo['MemFree'] = round($meminfo['MemFree'] / 1024);

		    $this->privmsg($socket, $channel, "{$sender}: ({$usage}%) {$meminfo['MemUsed']}MB used - {$meminfo['MemFree']}MB free out of {$meminfo['MemTotal']}MB.");
		}else 
			$this->privmsg($socket, $channel, "{$sender}: You are not authorized to do that.");
	}
}