<?php

Class Modubot_Vowelkill extends Modubot_Module {
	public $helpline = 'returns a vowel-less version of [input].';

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		if(!empty($args)){
			$this->privmsg($socket, $channel, $sender . ': ' . preg_replace('/([aeiouy])/i','', $args));
		}else
			$this->privmsg($socket, $channel, $sender . ": Usage: {$that->prefix}vowelkill this sentence is going to turn vowel-less!");
	}
}