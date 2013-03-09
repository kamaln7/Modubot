<?php

Class Modubot_Ignore extends Modubot_Module {

    public $helpline = 'allows the admins to make the bot ignore a user, thus disallowing him from interacting with the bot.';
    public $hooks = array('beforeCommand', 'beforePrefix');
    private $ignored = array();

    public function __construct(&$that){
        try{
            $ignored = $that->db->query('SELECT `name` FROM `ignore` ORDER BY `id` DESC');

            while($row = $ignored->fetch()){
                $this->ignored[] = $row->name;
            }
        }catch (PDOException $e){
            $this->logE($e);
        }
    }

    public function beforeCommand(&$that, &$socket, $data, $input, $command, $args){
        if($that->getLevel($that->sender($data), '', $that->host($data)) > 6)
            return true;
        
        return !in_array(strtolower($that->sender($data)), $this->ignored);
    }

    public function beforePrefix(&$that, &$socket, $data, $input, $command, $args){
        if($that->getLevel($that->sender($data), '', $that->host($data)) > 6)
            return true;
        
        return !in_array(strtolower($that->sender($data)), $this->ignored);
    }

    public function process(&$that, &$socket, $data, $input, $command, $args){
        $sender = $that->sender($data);
        $channel = $that->channel($data);
        if($that->getLevel($sender, '', $that->host($data)) > 6){
            $options = explode(' ', $args);
            $option = $options[0];
            unset($options[0]);
            $text = implode(' ', $options);
            try {
                switch(strtolower($option)){
                    case 'add':
                        $user = str_replace(' ', '', $text);
                        try {
                            $ignore = $that->db->prepare('INSERT INTO `ignore` (`name`) VALUES (:name)');
                            $ignore->bindParam(':name', strtolower($user), PDO::PARAM_STR);
                            $ignore->execute();

                            $this->ignored[] = strtolower($user);
                            $this->privmsg($socket, $channel, "{$sender}: Ignoring {$user}...");
                        }catch (PDOException $e){
                            $this->privmsg($socket, $channel, "{$sender}: But {$user} is already ignored!");
                        }
                        break;
                    case 'del':
                        $user = str_replace(' ', '', $text);
                        $ignore = $that->db->prepare('DELETE FROM `ignore` WHERE `name` = :name');
                        $ignore->bindParam(':name', strtolower($user), PDO::PARAM_STR);
                        $ignore->execute();

                        $id = array_search(strtolower($user), $this->ignored);
                        if($id !== false)
                            unset($this->ignored[$id]);

                        $this->privmsg($socket, $channel, "{$sender}: Unignoring {$user}...");
                        break;
                    case 'reload':
                        try{
                            $ignored = $that->db->query('SELECT `name` FROM `ignore` ORDER BY `id` DESC');

                            $this->ignored = array();
                            while($row = $ignored->fetch()){
                                $this->ignored[] = $row->name;
                            }
                            $this->privmsg($socket, $channel, "{$sender}: Reloaded ignore list.");
                        }catch (PDOException $e){
                            $this->privmsg($socket, $channel, "{$sender}: Database error :<");
                        }
                        break;
                    case 'list':
                            if(count($this->ignored) == 0)
                                $this->privmsg($socket, $channel, 'I am not ignoring anybody.');
                            else
                                $this->privmsg($socket, $channel, 'I am ignoring: ' . implode(', ', $this->ignored) . '.');
                        break;
                    default:
                        $this->privmsg($socket, $channel, "{$sender}: Available options: add, del, list and reload.");
                        break;
                }
            }catch (PDOException $e){
                $this->logE($e);
            }
        }else
            $this->privmsg($socket, $channel, "{$sender}: You are not authorized to do that.");
    }
}
