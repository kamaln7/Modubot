<?php

Class Modubot_Action extends Modubot_Module {

	public $helpline = 'makes the bot do something i.e. /me kills foo';
	public $alias = array('act');

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$channel = $that->channel($data);
		if(!empty($args)){
			$this->privmsg($socket, $channel, "\001ACTION {$args}\001");
		}else {
			$this->privmsg($socket, $channel, $that->sender($data) . ": Usage: {$that->prefix}action kicks foo in the butt.");
	 	}
	}
}
