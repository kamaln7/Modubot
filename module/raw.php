<?php

Class Modubot_Raw extends Modubot_Module {
	public $regex = '';
	public $helpline = 'sends raw IRC data. only available to admins.';

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		if($that->getLevel($sender, '', $that->host($data)) > 6){
	 		if(!empty($args)){
	 			$this->send($socket, $args);
	 		}else {
	 			$this->privmsg($socket, $channel, $sender . ": Usage: {$that->prefix}raw IRC Command");
	 		}
 		}else {
 			$this->privmsg($socket, $channel, $sender . ': You are not authorized to do that.');
 		}
	}
}
