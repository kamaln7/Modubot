<?php

Class Modubot_Currency extends Modubot_Module {

	public $helpline = 'converts the amount of money from currency X to currency Y, using google\'s converter';

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		$query = explode(' in ', strtolower($args));
		if(count($query) == 2){
			$source = Str::trim(str_replace(' ', '', $query[0]));
			$target = Str::trim($query[1]);

			$query = file_get_contents('http://www.google.com/ig/calculator?hl=en&q=' . urlencode($source) . '=?' . urlencode($target));
			if(preg_match('/\{lhs: \"(.*)\",rhs: \"(.*)\",error: \"(.*)\",icc: [a-z]+\}/', $query, $matches)){
				$lhs = $matches[1];
				$rhs = $matches[2];
				$error = $matches[3];
				if(!empty($error))
					$this->privmsg($socket, $channel, "{$sender}: An error ({$error}) occurred :<");
				else
					$this->privmsg($socket, $channel, "{$sender}: {$lhs} = {$rhs}");
			}else
				$this->privmsg($socket, $channel, "{$sender}: An error occurred :<");
		}else
			$this->privmsg($socket, $channel, $sender . ': Usage: ' . $prefix . $name . ' 125 AUD in USD');
	}
}
