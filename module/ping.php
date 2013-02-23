<?php

Class Modubot_Ping extends Modubot_Module {

	public $regex = '';
	public $helpline = 'returns a PONG! message confirming that the bot (or your connection) is alive.';

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$this->privmsg($socket, $that->channel($data), "{$sender}, pong!");
	}
}
