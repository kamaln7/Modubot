<?php

Class Modubot_Reason extends Modubot_Module {
	public $helpline = 'gets or sets the reason for the kick command. only available to half-ops+';

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		if($that->getLevel($sender, $channel, $that->host($data)) > 2){
			try{
				if(!empty($args)){
					$reason = $that->db->prepare('UPDATE `misc` SET `value` = :value WHERE `key` = \'kickreason\'');
					$reason->bindParam(':value', $args, PDO::PARAM_STR);
					$reason->execute();

					if($reason->rowCount() > 0){
						$this->privmsg($socket, $channel, "{$sender}: Reason set to: {$args}");	
					}else {
						throw new PDOException('');
					}
				}else {
					$reason = $that->db->query('SELECT `value` FROM `misc` WHERE `key` = \'kickreason\'');
					$row = $reason->fetch();
					$reason = $row->value;
					$this->privmsg($socket, $channel, "{$sender}: Current reason is set to: {$reason}");	
				}
			}catch (PDOException $e){
				$that->logE($e);
				$this->privmsg($socket, $channel, "{$sender}: An error occurred :<");
			}
		}else {
			$this->privmsg($socket, $channel, "{$sender}: You are not authorized to do that.");
		}
	}
}