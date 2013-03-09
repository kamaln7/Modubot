<?php

Class Modubot_Chanflags extends Modubot_Module {

    public $helpline = 'allows channel ops to specify who can talk to the bot in said channel.';
    public $hooks = array('beforeCommand', 'beforePrefix');
    private $cache = array();
    private $flags = array();

    public function __construct(&$that){
    	$i = 0;
    	foreach($that->db->query('SELECT `name`, `chanflags` FROM `channels` WHERE `chanflags` > 1') as $chanflag){
    		$this->flags[strtolower($chanflag->name)] = $chanflag->chanflags;
    		++$i;
    	}
    	
    	echo "Loaded {$i} chanflags from the database\n";
    }

    public function beforeCommand(&$that, &$socket, $data, $input, $command, $args){
        $whitelist = array(
            'tell',
            'todo',
    	    'seen'
        );

        if(in_array(strtolower($command), $whitelist))
            return true;

        $channel = $that->channel($data);
        $sender = $that->sender($data);
        if(!Str::beginsWith('#', $channel))
            return true;

        if($that->getLevel($sender, '', $that->host($data)) > 6)
            return true;

        $level = 1;

        $required = isset($this->flags[strtolower($channel)]) ? $this->flags[strtolower($channel)] : 1;
        if($required == 1)
            return true;
        
        if(isset($this->cache[strtolower($channel)][strtolower($sender)])){
            $cache = $this->cache[strtolower($channel)][strtolower($sender)];
            if(time() - $cache['time'] > 120){
                $level = $that->getLevel($sender, $channel, $that->host($data));

                $this->cache[strtolower($channel)][strtolower($sender)] = array('level' => $level, 'time' => time());
            }else {
                $level = $cache['level'];
            }
        }else {
            $level = $that->getLevel($sender, $channel, $that->host($data));
            if(!isset($this->cache[strtolower($channel)]))
                $this->cache[strtolower($channel)] = array();

            $this->cache[strtolower($channel)][strtolower($sender)] = array('level' => $level, 'time' => time());
        }

        if($level < $required){
            return false;
        }else {
            return true;
        }
    }

    public function beforePrefix(&$that, &$socket, $data, $input, $command, $args){
        return $this->beforeCommand($that, $socket, $data, $input, $command, $args);
    }

    public function process(&$that, &$socket, $data, $input, $command, $args){
        $sender = $that->sender($data);
        $channel = $that->channel($data);
        if($that->getLevel($sender, $channel, $that->host($data)) > 3){
            $flags = array(
                'admin' => 7,
                'op' => 4,
                'voice' => 2,
                'all' => 1
            );

            $flag = explode(' ', $args);
            $flag = strtolower($flag[0]);

            if($flag == 'clearcache'){
                $this->cache = array();
                $this->notice($socket, $sender, 'Cleared cache.');
                return true;
            }

            if(!isset($flags[$flag])){
                $flags_names = array_keys($flags);
                $this->privmsg($socket, $channel, "{$sender}: Allowed options: " . implode(', ', array_map('strtoupper', $flags_names)));
            }else {
                $this->flags[strtolower($channel)] = $flags[$flag];
        		try {
        			$chanflag = $that->db->prepare('UPDATE `channels` SET `chanflags` = :chanflags WHERE `name` = :name');
        			$chanflag->bindParam(':chanflags', $flags[$flag], PDO::PARAM_INT);
        			$chanflag->bindParam(':name', $channel, PDO::PARAM_STR);
                    $chanflag->execute();
        		}catch (PDOException $e){
        			$that->logE($e);
        		}

                $this->privmsg($socket, $channel, "{$sender}: Set required permission to {$flag}");
            }
        }else
            $this->privmsg($socket, $channel, "{$sender}: You are not authorized to do that.");
    }
}
