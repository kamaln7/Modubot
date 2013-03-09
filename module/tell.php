<?php

Class Modubot_Tell extends Modubot_Module {
	public $regex = '/PRIVMSG [#a-zA-Z0-9]+ :(.*)/';
	private $tells = array();
	private $notified = array();
	public $helpline = 'relays [message] to [user].';

	private function getAgoTime($secs){
	    $bit = array(
	        ' year'        => $secs / 31556926 % 12,
	        ' week'        => $secs / 604800 % 52,
	        ' day'        => $secs / 86400 % 7,
	        ' hour'        => $secs / 3600 % 24,
	        ' minute'    => $secs / 60 % 60,
        	' second'    => $secs % 60
        	);
       
    	foreach($bit as $k => $v){
        	if($v > 1)$ret[] = $v . $k . 's';
        	if($v == 1)$ret[] = $v . $k;
        	}
	    array_splice($ret, count($ret)-1, 0, 'and');
	    $ret[] = 'ago';
   
    	return join(' ', $ret);
    }

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		if(!empty($args)){
			$options = explode(' ', $args);
			$option = $options[0];
			unset($options[0]);
			$text = implode(' ', $options);
	 		if($option == 'list'){
	 			if(isset($this->tells[strtolower($sender)]) && count($this->tells[strtolower($sender)]) > 0){
	 				foreach($this->tells[strtolower($sender)] as $tell){
	 					$ago = $this->getAgoTime(time() - $tell['time']);
	 					$teller = $tell['teller'];
	 					$tell = $tell['tell'];
	 					$this->notice($socket, $sender, "{$teller} said: \x02{$tell}\x02 {$ago}.");
                        usleep(100000);
	 				}
	 				unset($this->tells[strtolower($sender)]);
	 				$this->notice($socket, $sender, "\x02All\x02 of your tells have been marked as read. If you want to deal with a tell later, use {$that->prefix}todo.");
	 			}else
	 				$this->notice($socket, $sender, 'You do not have any tells.');
				unset($this->notified[strtolower($sender)]);
	 		}else {
	 			if(!empty($text)){
	 				!isset($this->tells[strtolower($option)]) && $this->tells[strtolower($option)] = array();
	 				$this->tells[strtolower($option)][] = array('time' => time(), 'teller' => $sender, 'tell' => $text);
	 				$this->notice($socket, $sender, 'I will pass that along.');
	 			}else
	 				$this->notice($socket, $sender, 'You cannot send an empty tell.');
	 			unset($this->notified[strtolower($option)]);
	 		}
 		}else 
 			$this->notice($socket, $sender, "Usage: \x02{$that->prefix}tell someguy Don't forget the milk!\x02 OR \x02{$that->prefix}tell list\x02 to show your pending tells.");
	}

	public function match(&$that, &$socket, $data, $matches){
		$sender = $that->sender($data);
		if(!isset($this->notified[strtolower($sender)]) && isset($this->tells[strtolower($sender)]) && count($this->tells[strtolower($sender)]) > 0){
			$this->notice($socket, $sender, "You have pending tells, type \x02{$that->prefix}tell list\x02 to list them.");
			$this->notified[strtolower($sender)] = true;
		}
	}
}
