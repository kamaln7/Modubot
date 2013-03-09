<?php

Class Modubot_Nick extends Modubot_Module {
    public $helpline = 'makes the bot change its nickname. only available to admins.';

    public function process(&$that, &$socket, $data, $input, $command, $args){
        $sender = $that->sender($data);
        $channel = $that->channel($data);
        if($that->getLevel($sender, '', $that->host($data)) > 6){
            if(!empty($args)){
                $nick = explode(' ', $args);
                $nick = Str::trim($nick[0]);
                $this->send($socket, "NICK {$nick}");
                $that->nick = $nick;
            }else
                $this->privmsg($socket, $channel, "{$sender}: Usage: {$that->prefix}nick newNickName");
        }else
            $this->privmsg($socket, $channel, "{$sender}: You are not authorized to do that.");
    }
}