<?php

Class Modubot_Munge extends Modubot_Module {

	public $helpline = 'munges [input].';

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		if(!empty($args)){
			$text = Str::munge($args);
			$this->privmsg($socket, $channel, "{$sender}: {$text}");
		}else 
			$this->privmsg($socket, $channel, "{$sender}: Usage: {$that->prefix}{$command} some text here...");
	}
}
