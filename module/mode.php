<?php

Class Modubot_Mode extends Modubot_Module {

	public $regex = '';
    public $helpline = 'if supplied with a channel, returns the channel\'s modes, else it returns user [input] role in the current channel.';

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		$input = $args;
		if(substr($input, 0, 1) == '#'){
			$channame = Str::after('#', $input);
			if(empty($channame))
				$chan = $channel;
			else
				$chan = $input;

            $this->send($socket, 'MODE ' . $chan);
            $gotit = false;
            while(!$gotit){
                $data = $that->listen();
                $channelmodes = $that->expect($data, 'modechannel', array('channel' => $chan));
                $nosuchchannel = $that->expect($data, 'nosuchnickchannel', array('who' => $chan));
                if($channelmodes !== false){
                    $chflags = $channelmodes[1];
                    $this->privmsg($socket, $channel, $sender . ': Channel ' . $chan . ' has modes of ' . $chflags . '.');
                    $gotit = true;
                }elseif($nosuchchannel !== false){
                    $this->privmsg($socket, $channel, $sender . ': Channel ' . $chan . ' does not exist.');
                    $gotit = true;
                }else {
                    $that->process($data);
                }
            }
		}else {
			if(empty($input))
				$user = $sender;
			else
				$user = $input;

			$level = $that->getLevel($user, $channel);
            $modulevel = $that->getLevel($user, '', $that->host($data));

            switch($level){
                case 6:
                    $mode = 'an owner';
                    break;
                case 5:
                    $mode = 'protected';
                    break;
                case 4:
                    $mode = 'an operator';
                    break;
                case 3:
                    $mode = 'a half operator';
                    break;
                case 2:
                    $mode = 'voiced';
                    break;
                default:
                    $mode = 'regular';
                    break;
           }

			$specialtext = '.';
            if($modulevel > 6){
                if($modulevel == 7){
					$modubotmode = 'admin';
				}elseif($modulevel == 8) {
					$modubotmode = 'super admin';
				}
				$specialtext = ' and is a Modubot ' . $modubotmode . '.';
			}
			
			$this->privmsg($socket, $channel, $sender . ': ' . $user . ' is ' . $mode . ' in ' . $channel . $specialtext);
		}
	}
}
