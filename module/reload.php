<?php

Class Modubot_Reload extends Modubot_Module {
	public $helpline = 'reloads the bot\'s core. only available to super admins.';
	
	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		if($that->getLevel($sender, '', $that->host($data)) == 8){
			$this->privmsg($socket, $channel, "{$sender}: Socket gets overwritten when reloading core, temporarily disabled.");
			return;

			if(isset($that->shmop) && shmop_write($that->shmop, '1', 0)){
				$this->privmsg($socket, $channel, "{$sender}: Reload byte written to memory.");
			}else 
				$this->privmsg($socket, $channel, "{$sender}: An error occurred while writing to memory.");
		}else 
			$this->privmsg($socket, $channel, "{$sender}: You are not authorized to do that.");
	}
}
