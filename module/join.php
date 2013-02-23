<?php

Class Modubot_Join extends Modubot_Module {

	public $helpline = 'makes the bot join channel [input]. only available to admins.';

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		if($that->getLevel($sender, '', $that->host($data)) > 6){
			$chan = explode(' ', $args);
			$chan = $chan[0];
			if(Str::beginsWith('#', $chan)){
				try {
					$insert = $that->db->prepare('INSERT INTO `channels` (`name`) VALUES (:name)');
					$insert->bindParam(':name', $chan, PDO::PARAM_STR);
					$insert->execute();

					$this->send($socket, "JOIN {$chan}");
				}catch (PDOException $e){
					$that->logE($e);
					$this->privmsg($socket, $sender, $e->getMessage());
				}

			}else
				$this->privmsg($socket, $channel, "{$sender}: Usage: {$that->prefix}join #channel");
		}else
			$this->privmsg($socket, $channel, "{$sender}: You are not authorized to do that.");
	}
}
