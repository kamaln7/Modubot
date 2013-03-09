<?php

Class Modubot_Remember extends Modubot_Module {
	public $helpline = 'remembers a factoid.';
	public $alias = array('r');

	public function process(&$that, &$socket, $data, $input, $command, $args){
        if(!isset($that->modules['factoid']))
            $this->privmsg($socket, $channel, "{$sender}: The factoid module is not loaded, please try again later.");
        else {
    		$sender = $that->sender($data);
    		$channel = $that->channel($data);
    		$options = explode(' ', $args);
    		$factoid = $options[0];
    		unset($options[0]);
    		$value = preg_replace("~\x1f|\x02|\x12|\x0f|\x16|\x03(?:\d{1,2}(?:,\d{1,2})?)?~", '', implode(' ', $options));
    		$factoid = preg_replace("~\x1f|\x02|\x12|\x0f|\x16|\x03(?:\d{1,2}(?:,\d{1,2})?)?~", '', strtolower($factoid));
    		if(!empty($factoid) && !empty($value)){
                if(strlen($factoid) > 12)
                    $this->privmsg($socket, $channel, "{$sender}: factoid name \x02{$factoid}\x02 is too long, not saving.");
                else {
                    try {
                        $insert = $that->db->prepare('INSERT INTO `factoids` (`factoid`, `value`, `locked`) VALUES (:factoid, :value, 1)');
                        $insert->bindParam(':factoid', $factoid, PDO::PARAM_STR);
                        $insert->bindParam(':value', $value, PDO::PARAM_STR);
                        $insert->execute();

                        $this->privmsg($socket, $channel, "{$sender}: remembered \x02{$value}\x02 for \x02{$factoid}\x02. Type {$that->modules['factoid']->prefix}{$factoid} to see it.");
                    }catch (PDOException $e){
                        $that->logE($e);
                        $this->privmsg($socket, $channel, "{$sender}: factoid not saved, it already exists :(");
                    }
                }
    		}else {
                $this->privmsg($socket, $channel, "{$sender}: Usage: {$that->prefix}remember factoid value. Prepending <reply> to the value makes it take out the user's nick from the reply.");
            }
        }
	}

}
