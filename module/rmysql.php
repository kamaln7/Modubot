<?php

Class Modubot_Rmysql extends Modubot_Module {

	public $helpline = 'revives the mysql connection in case the resource disconnects.';

	public function process(&$that, &$socket, $data, $input, $command, $args){
		if(strtolower($that->sender($data)) == 'kamaln' && $that->host($data) == 'znc.kamalnasser.net'){ //Needs an exception in case it can't access the admins table :)
			try {
				$that->db = new PDO("mysql:host={$that->dbinfo['host']};dbname={$that->dbinfo['database']}", $that->dbinfo['user'], $that->dbinfo['password'], array(PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            	$that->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

			 	$this->privmsg($socket, $that->channel($data),  'Successfully revieved the MySQL connection.');
			 }catch (PDOException $e){
			 	$that->logE($e);
			 	$this->privmsg($socket, $that->channel($data),  'Could not revive the MySQL connection.');
			 }
		}else
			$this->privmsg($socket, $that->channel($data), 'You are not authoized to do that.');
	}
}
