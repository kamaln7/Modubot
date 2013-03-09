<?php

Class Modubot_Who extends Modubot_Module {

	public $regex = '';
	public $alias = array('?');
	public $helpline = 'tells you more about the bot.';

	public function process(&$that, &$socket, $data, $input, $command, $args){
	 	$this->privmsg($socket, $that->channel($data),  "I am a PHP IRC bot coded by Kamal. Join #modubot for more information. Type {$that->prefix}help for commands. Visit my website: \x1fhttp://modubot.net\x1f!");
	}
}
