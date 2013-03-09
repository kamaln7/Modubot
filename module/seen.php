<?php

Class Modubot_Seen extends Modubot_Module {
	public $regex = '/PRIVMSG [#a-zA-Z0-9]+ :(.*)/';
	private $users = array();
	public $helpline = 'returns when [user] has been active.';

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
			$user = explode(' ', $args);
			$user = $user[0];
			if(strtolower($user) == strtolower($sender)){
				$this->notice($socket, $sender, 'Not really, no.');
				return;
			}

			if(isset($this->users[strtolower($user)])){
				$data = $this->users[strtolower($user)];
				$timediff = time() - $data['time'];
				$time = ($timediff == 0) ? 'just now' : $this->getAgoTime($timediff);
				$this->notice($socket, $sender, "{$user} was last seen in " . Formatting::BOLD . $data['channel'] . Formatting::CLEAR . ' ' . $time . ' saying: ' . $data['message']);
			}else {
				$this->notice($socket, $sender, "I haven't seen {$user} yet.");
			}
 		}else 
 			$this->notice($socket, $sender, "Usage: \x02{$that->prefix}seen someguy");
	}

	public function match(&$that, &$socket, $data, $matches){
		$sender = $that->sender($data);
		$channel = $that->channel($data);

		if(Str::beginsWith('#', $channel)){
			$this->users[strtolower($sender)] = [
				'channel' => $channel,
				'time' => time(),
				'message' => $that->input($data)
			];
		}
	}
}
