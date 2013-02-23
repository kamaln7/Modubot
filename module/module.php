<?php

Class Modubot_Module {

	protected function send($socket, $signal){
		fputs($socket, Str::trim($signal) . "\n");
        usleep(100000);
		echo $signal . "\n";
	}

    protected function privmsg($socket, $target, $message){
        $this->send($socket, 'PRIVMSG ' . Str::trim($target) . ' :' . Str::trim($message));
        usleep(100000);
    }

    protected function notice($socket, $target, $message){
        $this->send($socket, 'NOTICE ' . Str::trim($target) . ' :' . Str::trim($message));
        usleep(100000);
    }

	
}
