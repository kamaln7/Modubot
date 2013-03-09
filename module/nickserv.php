<?php

Class Modubot_Nickserv extends Modubot_Module {
    public $helpline = 'returns the nickserv status about user [input].';

    public function process(&$that, &$socket, $data, $input, $command, $args){
        $sender = $that->sender($data);
        $channel = $that->channel($data);

	if(empty($args))
		$args = $sender;

    	$args = explode(' ', $args);
    	$args = $args[0];

        $this->send($socket, "WHOIS {$args}");
        $i = 0;
        $gotit = false;
        while(!$gotit){
            if($i == 10){
               $this->privmsg($socket, $channel, "{$sender}: Request timed out.");
               $gotit = true;
            }else ++$i;
            
            $data = $that->listen();
            $registered = $that->expect($data, 'nickserv', array('who' => $args));
            $end = $that->expect($data, 'endofwhois', array('who' => $args));
            $nosuchnick = $that->expect($data, 'nosuchnickchannel', array('who' => $args));
            if($nosuchnick !== false){
                $this->privmsg($socket, $channel, "{$sender}: {$args} is not online.");
                $gotit = true;
            }elseif($registered !== false){
                $this->privmsg($socket, $channel, "{$sender}: {$registered[1]} is logged in.");
                $gotit = true;
            }elseif($end !== false) {
                $this->privmsg($socket, $channel, "{$sender}: {$args} is not logged in.");
                $gotit = true;
            }else {
                $that->process($data);
            }
        }
    }
}
