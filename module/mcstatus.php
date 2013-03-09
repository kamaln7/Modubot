<?php

Class Modubot_Mcstatus extends Modubot_Module {

	public $helpline = 'returns minecraft\'s servers\' status.';

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$channel = $that->channel($data);
		$sender = $that->sender($data);
		$data = HTTP::get('http://status.mojang.com/check');
		if(!$data)
			$this->privmsg($socket, $channel, "{$sender}: Unable to get minecraft server status.");
		else {
			$data = str_replace('}', '', $data);
			$data = str_replace('{', '', $data);
			$data = str_replace(']', '}', $data);
			$data = str_replace('[', '{', $data);
			$data = json_decode($data, true);
			$response = [];
			foreach($data as $server => $status){
				$up = ($status == 'green');
				$color = $up ? Formatting::GREEN : Formatting::RED;
				$status = $up ? 'online' : 'offline';

				$response[] = "{$server} is " . Formatting::COLOR . "{$color}{$status}" . Formatting::CLEAR;
			}
			$this->privmsg($socket, $channel, "{$sender}: " . implode(', ', $response));
		}
	}
}
