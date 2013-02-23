<?php

Class Modubot_Cycle extends Modubot_Module {
	public $helpline = 'makes the bot cycle the channel it is in, as in part and rejoin immediately. only available to admins.';

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		if($that->getLevel($sender, '', $that->host($data)) > 6){
			$this->send($socket, "PART {$channel}");
			$this->send($socket, "JOIN {$channel}");
		}else 
			$this->privmsg($socket, $channel, "{$sender}: You are not authorized to do that.");
	}
}
