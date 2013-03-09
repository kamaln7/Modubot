<?php

Class Modubot_Digitalroot extends Modubot_Module {

	public $alias = array('dr');
	public $helpline = 'returns the digital root of [input].';

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		$number = (float) $args;
		if($number != 0){
			$this->privmsg($socket, $channel, "{$sender}: The digital root of {$number} is " . (1 + (($number - 1) % 9)));
		}else
			$this->privmsg($socket, $channel, "{$sender}: Usage: {$that->prefix}{$command} number");
	}
}
