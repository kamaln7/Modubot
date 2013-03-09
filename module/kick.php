<?php

Class Modubot_Kick extends Modubot_Module {

	public $regex = '';
	public $helpline = 'multi-kicks [input1] [input2]... only available to half-ops+';

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		if($that->getLevel($sender, $channel, $that->host($data)) > 2){
			$members = explode(' ', $args);

			if(!empty($members)){
				try {
					$reason = $that->db->query('SELECT `value` FROM `misc` WHERE `key` = \'kickreason\'');
					$row = $reason->fetch();
					$reason = $row->value;

					foreach($members as $member){
						$member = Str::trim($member);
						if(!empty($member)){
							$this->privmsg($socket, 'ChanServ', "KICK {$channel} {$member} {$reason}");
							usleep(250000);
						}
					}
				}catch (PDOException $e){
					$this->logE($e);
					$this->privmsg($socket, $channel, "{$sender}: An error occurred :<");
				}
			}else {
				$this->privmsg($socket, $channel, "{$sender}: Usage: {$that->prefix}kick user1 user2 user3 ... Use {$that->prefix}reason to set the reason.");	
			}
		}else {
			$this->privmsg($socket, $channel, "{$sender}: You are not authorized to do that.");
		}
	}
}
