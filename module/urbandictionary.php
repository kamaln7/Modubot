<?php

Class Modubot_Urbandictionary extends Modubot_Module {
	public $regex = '';
	public $alias = array('ud');
	public $helpline = 'queries urban dictionary about [input] and returns the result. if the last word of the input is supplied as a number, it returns result number X of the total results.';

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		$term = Str::after($that->prefix . $command, $input);
 		if(!empty($term)){
 			$parts = explode(' ', $term);
 			if(is_numeric($parts[count($parts) - 1])){
 				$id = $parts[count($parts) - 1];
 				unset($parts[count($parts) - 1]);
 				$term = implode(' ', $parts);
 			}else
 				$id = 1;

 			$term = urlencode($term);
 			$api = json_decode(file_get_contents('http://api.urbandictionary.com/v0/define?term=' . $term), true);
 			$list = isset($api['list']) ? $api['list'] : array();
 			if($api['result_type'] == 'no_results' || $id > count($list))
 				$this->privmsg($socket, $channel, $sender . ': Not found.');
 			else {
 				try{
 					$this->privmsg($socket, $channel, "{$sender}: [{$id}/" . count($list) . '] ' . Str::trim($list[$id - 1]['word']) . ': ' . preg_replace('~[\s]+~', ' ', Str::trim($list[$id - 1]['definition'])));
				}catch(Exception $e){
					$this->privmsg($socket, $channel, $sender . ': An error occurred :<');
				}
 			}
 		}else {
 			$this->privmsg($socket, $channel, $sender . ': Usage: ' . $that->prefix . $command . ' term [result index]');
 		}
	}
}
