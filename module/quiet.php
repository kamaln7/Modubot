<?php

Class Modubot_Quiet extends Modubot_Module {

	public $regex = '';
	public $helpline = 'multi-quiets [input1] [input2]... only available to half-ops+';

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		if($that->getLevel($sender, $channel, $that->host($data)) > 2){
			$members = explode(' ', $args);

			if(count($members) > 0){
				foreach($members as $member){
					$member = Str::trim($member);
					if(!empty($member)){
						$this->privmsg($socket, 'ChanServ', "QUIET {$channel} {$member}");
						usleep(250000);
					}
				}
			}else {
				$this->privmsg($socket, $channel, "{$sender}: Usage: {$that->prefix}quiet user1 user2 user3 ...");	
			}
		}else {
			$this->privmsg($socket, $channel, "{$sender}: You are not authorized to do that.");
		}
	}
}
