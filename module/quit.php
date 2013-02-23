<?php

Class Modubot_Quit extends Modubot_Module {
	public $helpline = 'makes the bot quit and disconnect from the server. only available to super admins.';

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$host = $that->host($data);
		if($that->getLevel($sender, '', $host) == 8){
			$that->disconnect($args);
			die("\nQuit command issued by {$sender} with the reason of {$args}.\n");
		}else {
			$this->privmsg($socket, $that->channel($data), "{$sender}: You are not authorized to do that.");
		}
	}

}
