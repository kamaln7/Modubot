<?php

Class Modubot_Say extends Modubot_Module {

	public $regex = '';
	private $forbidden = array('!', 'esperbot', 'borg', 'dicector', 'derpserv', 'espercorn', 'minebot', 'zsh', 'faerie', 'giygas', 'chanserv', 'nyanbot', 'kamalserv', 'foreveralone', 'troll', 'bash', 'derp', 'deropina', 'wololo', 'vartor', 'x', '-', 'kaboom', 'gaygas', 'nyanbot', 'missingno', 'poop', '\o', 'nyanserv');
	public $helpline = 'makes the bot say [input] in the same channel the command was sent from.';

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		if(!empty($args)){
			$ok = true;
			$forbidden_used = '';

			$first = explode(' ', $args);
			$first = $first[0];
			if(strtolower(substr($first, -1, 4)) == 'serv' || substr($first, 0, 1) == '!'){
				$ok = false;
				$forbidden_used = $first;
			}
			foreach($this->forbidden as $forbidden){
				if(substr(strtolower($first), 0, strlen($forbidden)) == $forbidden){
					$ok = false;	
					$forbidden_used = $first;
				}
			}

			if($that->getLevel($sender, $channel, $that->host($data)) > 3)
				$ok = true;

			if($ok)
				$this->privmsg($socket, $channel, $args);
			else
				$this->privmsg($socket, $channel, $sender . ': You are not authorized to begin the text with ' . $forbidden_used . ($forbidden_used == '!' ? '.' : '!'));
		}else {
	 		$this->privmsg($socket, $channel, $sender . ": Usage: {$that->prefix}say hello :3");
	 	}
	}
}
