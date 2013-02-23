<?php

Class Modubot_Part extends Modubot_Module {
	public $helpline = 'makes the bot part for the channel the command was sent from. only available to admins.';

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		if($that->getLevel($sender, '', $that->host($data)) > 6){
			try {
				$delete = $that->db->prepare('DELETE FROM `channels` WHERE `name` = :name');
				$delete->bindParam(':name', strtolower($channel));
				$delete->execute();

				if($delete->rowCount() == 0)
					throw new PDOException('');

				$this->send($socket, "PART {$channel}");
			}catch (PDOException $e){
				$that->logE($e);
				$this->privmsg($socket, $channel, "{$sender}: Could not delete channel from database.");
			}
		}else 
			$this->privmsg($socket, $channel, "{$sender}: You are not authorized to do that.");
	}
}
