<?php

Class Modubot_Factoid extends Modubot_Module {

	public $prefix = '?';
	// Just a list of words that factoids should not begin with to prevent permission abuse
    private $forbidden = array('!', 'esperbot', 'borg', 'dicector', 'derpserv', 'espercorn', 'minebot', 'zsh', 'faerie', 'giygas', 'chanserv', 'nyanbot', 'foreveralone', 'troll', 'bash', 'derp', 'deropina', 'wololo', 'vartor', 'x', '-', 'kaboom', 'gaygas', 'nyanbot', '\o', 'missingno', 'poop', 'nyanserv');
    public $helpline = 'processes the factoids.';
    public $noCommand = true;
	
	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$this->notice($socket, $sender, "Usage: {$that->prefix}remember someone nobody knows who someone is.");
		$this->notice($socket, $sender, "Usage: {$this->prefix}someone :user (gives: user: nobody knows who someone is.)");
		$this->notice($socket, $sender, "Usage: {$that->prefix}forget someone (deletes the factoid from the database)");
	}
	
	public function prefix(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		$input = explode(' ', Str::after($this->prefix, $input));
		$factoid = $input[0];
		if(isset($input[1]) && Str::beginsWith(':', $input[1])){
			$ping = Str::after(':', $input[1]);
			unset($input[0]);
			unset($input[1]);
			$params = implode(' ', $input);
		}else {
			$ping = $sender;
			unset($input[0]);
			$params = implode(' ', $input);
		}
        $factoid = preg_replace("~\x1f|\x02|\x12|\x0f|\x16|\x03(?:\d{1,2}(?:,\d{1,2})?)?~", '', $factoid);
        try {
        	$value = $that->db->prepare('SELECT `value` FROM `factoids` WHERE `factoid` = :factoid');
        	$value->bindParam(':factoid', $factoid, PDO::PARAM_STR);
        	$value->execute();
        	if($value->rowCount() !== 0){
        		$row = $value->fetch();
        		$value = $row->value;
        		$ping .= ': ';
	        	if(Str::beginsWith('<reply>', $value)){
					$ping = '';
					$value = Str::after('<reply>', $value);
				}
				if($factoid != 'remember' && stristr($value, '$input$')){
					if(isset($params) && !empty($params)){
						$value = str_replace('$input$', $params, $value);
	                    $first = explode(' ', $value);
	                    $first = $first[0];
	                }else {
						$this->privmsg($socket, $channel, $sender . ': The factoid "' . $factoid . '" requires input, e.g. ' . $this->prefix . $factoid . ' input');
						return;
					}
	            }
        		if(Str::beginsWith('<act>', $value)){
					$value = Str::after('<act>', $value);
					$this->privmsg($socket, $channel, "\001ACTION {$value}\001");
					return true;
        		}
				if(substr($value, 0, 1) == '!'){
					$first = '!';
	                if(empty($ping) && in_array(strtolower($first), $this->forbidden) && $that->getLevel($sender, '', $that->host($data)) < 3)
	                    $this->notice($socket, $sender, 'This factoid may only be used by [half]OPs+.');
	                else
						$this->privmsg($socket, $channel, $ping . $value);
				}else {
					$first = explode(' ', $value);
					$first = $first[0];
					if(substr($value, 0, 1) == '!')
					$first = '!';
	                if(empty($ping) && in_array(strtolower($first), $this->forbidden) && $that->getLevel($sender, '', $that->host($data)) < 3)
	                    $this->notice($socket, $sender, 'This factoid may only be used by [half]OPs+.');
	                else
	                    $this->privmsg($socket, $channel, $ping . $value);
				}
        	}
        }catch (PDOException $e){
        	$that->logE($e);
        	$this->notice($socket, $sender, 'An error occurred :<');
        }
	}

}
