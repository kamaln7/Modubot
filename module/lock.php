<?php

Class Modubot_Lock extends Modubot_Module {
	public $helpline = 'locks factoid [input], preventing it from being forgotten. only available to admins.';

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		if($that->getLevel($sender, '', $that->host($data)) > 6){
			if(!empty($args)){
				try {
					$lock = $that->db->prepare('UPDATE `factoids` SET `locked` = 1 WHERE `factoid` = :factoid');
					$lock->bindParam(':factoid', strtolower($args), PDO::PARAM_STR);
					$lock->execute();

					if($lock->rowCount() > 0)
						$this->privmsg($socket, $channel, "{$sender}: Locking factoid {$args}...");
					else
						$this->privmsg($socket, $channel, "{$sender}: Factoid {$args} does not exist.");
				}catch (PDOException $e){
					$that->logE($e);
					$this->privmsg($socket, $channel, "{$sender}: Factoid not locked, an error occurred :<");
				}					
			}else {
					$this->privmsg($socket, $channel, "{$sender}: Usage: {$that->prefix}lock factoid");
			}
		}else {
			$this->privmsg($socket, $channel, "{$sender}: You are not authorized to do that.");
		}
	}

}