<?php

Class Modubot_Help extends Modubot_Module {

    public $helpline = 'returns a list of all the available commands. %help command returns help about that command.';

    public function __construct($that){
        $this->helpline = "returns a list of all the available commands. {$that->prefix}help command returns help about that command.";
    }

	public function process(&$that, &$socket, $data, $input, $command, $args){
        $sender = $that->sender($data);
        $channel = $that->channel($data);
        if(empty($args)){
            $commands = array();
            foreach ($that->modules as $key => $module) {
                $module = strtolower(str_replace('Modubot_', '', get_class($module)));
	        if($module !== 'module' && !isset($that->modules[$key]->noCommand))
	                $commands[] = $module;
            }
            foreach ($that->modules_alias as $alias => $module) {
                $commands[] = $alias;
            }
            natcasesort($commands);
            $this->notice($socket, $sender, "Available {$that->prefix}commands: " . implode(', ', $commands));
            $this->notice($socket, $sender, "Type {$that->prefix}help command to learn more about a command.");
        }else {
            $help = explode(' ', $args);
            $help= $help[0];
            if(isset($that->modules[strtolower($help)]) || isset($that->modules_alias[strtolower($help)])){
                if(isset($that->modules[strtolower($help)]->helpline) && !empty($that->modules[strtolower($help)]->helpline))
                    $this->notice($socket, $sender, "{$help}--" . $that->modules[strtolower($help)]->helpline);
                elseif(isset($that->modules[$that->modules_alias[strtolower($help)]]->helpline) || !empty($that->modules[$that->modules_alias[strtolower($help)]]->helpline))
                    $this->notice($socket, $sender, "{$help}--" . $that->modules[$that->modules_alias[strtolower($help)]]->helpline);
                else
                    $this->notice($socket, $sender, "Command {$help} does not have any help information.");
            }else
                $this->notice($socket, $sender, "Command {$help} does not exist.");
        }
	}
}
