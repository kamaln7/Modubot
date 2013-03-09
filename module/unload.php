<?php

Class Modubot_Unload extends Modubot_Module {
    public $helpline = 'unloads a module. only available to super admins.';

    public function process(&$that, &$socket, $data, $input, $command, $args){
        $channel = $that->channel($data);
        $sender = $that->sender($data);
        if ($that->getLevel($sender, '', $that->host($data))  == 8) {
            $class = explode(' ', $args);
            $class = $class[0];
            if (!empty($class)) {
                $class = strtolower($class);
                if (isset($that->modules[$class])) {
                    unset($that->modules[$class]);

                    if(isset($that->modules_regex[$class]))
                        unset($that->modules_regex[$class]);

                    //TODO: Check if class/method exists in Modubot (core) before calling it.

                    $this->privmsg($socket, $channel, $sender . ": Unloaded {$class}!");
                } else {
                    $this->privmsg($socket, $channel, $sender . ": Module not loaded.");
                }
            } else {
                $this->privmsg($socket, $channel, $sender . ": Usage: {$that->prefix}unload module");
            }
        } else {
            $this->privmsg($socket, $channel, $sender . ": You are not authorized to do that.");
        }
    }
}
