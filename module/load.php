<?php

Class Modubot_Load extends Modubot_Module {

    public $helpline = '[re]loads a module. only available to super admins.';

    public function process(&$that, &$socket, $data, $input, $command, $args){
        $channel = $that->channel($data);
        $sender = $that->sender($data);
        if ($that->getLevel($sender, '', $that->host($data))  == 8) {
            $class = explode(' ', $args);
            $class = $class[0];
            if (!empty($class)) {
                if (isset($that->modules[strtolower($class)])) {
                    unset($that->modules[strtolower($class)]);
                    runkit_import($that->path . 'module/' . strtolower($class) . '.php', RUNKIT_IMPORT_CLASSES | RUNKIT_IMPORT_OVERRIDE);
                    $that->load($class);
                    $this->privmsg($socket, $channel, $sender . ": Reloaded {$class}!");
                } elseif (file_exists($that->path . 'module/' . strtolower($class) . '.php')) {
                    $that->load($class);
                    $this->privmsg($socket, $channel, $sender . ": Loaded {$class}!");
                } else {
                    $this->privmsg($socket, $channel, $sender . ": Module {$class} is neither loaded nor exists.");
                }
            } else {
                $this->privmsg($socket, $channel, $sender . ": Usage: {$that->prefix}load module");
            }
        } else {
            $this->privmsg($socket, $channel, $sender . ": You are not authorized to do that.");
        }
    }
}
