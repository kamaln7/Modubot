<?php

Class Modubot_Factoids extends Modubot_Module {

	public $helpline = 'lists all the factoids. only available to admins.';

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		if($that->getLevel($sender, '', $that->host($data)) > 6){
			try {
				$factoids = $that->db->query('SELECT `factoid` FROM `factoids` ORDER BY `factoid` ASC');
				$factoid_list = array();
				while($row = $factoids->fetch()){
					$factoid_list[] = $row->factoid;
				}
				$this->privmsg($socket, $sender, 'Current factoids: ' . implode(', ', $factoid_list));
			}catch (PDOException $e){
				$that->logE($e);
				$this->privmsg($socket, $channel, "{$sender}: An error occurred :<");
			} 
		}else {
			$this->privmsg($socket, $channel, "{$sender}: You are not authorized to do that.");
		}
	}
}
