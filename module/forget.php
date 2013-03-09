<?php

Class Modubot_Forget extends Modubot_Module {

	public $helpline = 'makes the bot forget factoid [input], allowing a new value to overwrite it.';
	public $alias = array('f');

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		if(!empty($args)){
            $args = preg_replace("~\x1f|\x02|\x12|\x0f|\x16|\x03(?:\d{1,2}(?:,\d{1,2})?)?~", '', $args);
            try{
            	$locked = $that->db->prepare('SELECT `locked` FROM `factoids` WHERE `factoid` = :factoid');
				$locked->bindParam(':factoid', strtolower($args), PDO::PARAM_STR);
				$locked->execute();

				if($locked->rowCount() > 0){
					$row = $locked->fetch();
					$locked = $row->locked;

					if($that->getLevel($sender, '', $that->host($data)) > 6)
						$locked = 0;

					if($locked == 1)
						$this->privmsg($socket, $channel, "{$sender}: Factoid is locked. Message a {$that->nick} admin to unlock it.");
					else {
						$forget = $that->db->prepare('DELETE FROM `factoids` WHERE `factoid` = :factoid');
						$forget->bindParam(':factoid', strtolower($args), PDO::PARAM_STR);
						$forget->execute();

						if($forget->rowCount() > 0)
							$this->privmsg($socket, $channel, "{$sender}: Factoid forgotten.");
						else
							$this->privmsg($socket, $channel, "{$sender}: An error occurred :<");
					}
				}else {
					$this->privmsg($socket, $channel, "{$sender}: Factoid does not exist.");
				}
            }catch (PDOException $e){
				$this->privmsg($socket, $channel, "{$sender}: An error occurred :<");
			}
		}else {
			$this->privmsg($socket, $channel, "{$sender}: Usage: {$that->prefix}forget factoid");
		}
	}

}
