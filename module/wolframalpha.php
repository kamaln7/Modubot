<?php

Class Modubot_Wolframalpha extends Modubot_Module {
	public $regex = '';
	public $alias = array('wa');
	public $helpline = 'queries wolframalpha [input] and returns the result';
	private $appid = 'your wolframalpha app-id';

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$channel = $that->channel($data);
		$sender = $that->sender($data);
 		if(!empty($args)){
 			$url = "http://api.wolframalpha.com/v2/query?appid={$this->appid}&input=" . urlencode($args);
			$result = file_get_contents($url);
			$result = simplexml_load_string($result);
			$result = Str::trim(str_replace("\n", ' - ', $result->pod[1]->subpod->plaintext));
			if(empty($result))
				$this->privmsg($socket, $channel, $sender . ': No result.');
			else
	 			$this->privmsg($socket, $channel, $sender . ': Result: ' . "\x02{$result}\x02");
 		}else {
 			$this->privmsg($socket, $channel, $sender . ": Usage: {$that->prefix}{$command} query");
 		}
	}
}
