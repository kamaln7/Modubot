<?php

Class Modubot_Unlock extends Modubot_Module {

	public $helpline = 'unlocks a factoid, allowing it to be forgotten. only available to admins';

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		if($that->getLevel($sender, '', $that->host($data)) > 6){
			if(!empty($args)){
				try {
					$unlock = $that->db->prepare('UPDATE `factoids` SET `locked` = 0 WHERE `factoid` = :factoid');
					$unlock->bindParam(':factoid', strtolower($args), PDO::PARAM_STR);
					$unlock->execute();

					if($unlock->rowCount() == 0)
						throw new PDOException('');

					$this->privmsg($socket, $channel, "{$sender}: Unlocking factoid {$args}...");
				}catch (PDOException $e){
					$this->privmsg($socket, $channel, "{$sender}: Factoid not found.");
				}
			}else {
					$this->privmsg($socket, $channel, "{$sender}: Usage: {$that->prefix}unlock factoid");
			}
		}else {
			$this->privmsg($socket, $channel, "{$sender}: You are not authorized to do that.");
		}
	}

}