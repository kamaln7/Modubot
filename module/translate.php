<?php

Class Modubot_Translate extends Modubot_Module {
	public $helpline = 'queries google translate and returns the translation.';

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		if(!empty($args)){
			$modifiers = explode(' ', $args);
			$tp = explode('|', $modifiers[0]);
			unset($modifiers[0]);
			if(count($tp) == 2){
				$text = implode(' ', $modifiers);
				if(!empty($text)){
					$translation = json_decode(file_get_contents('http://translate.google.com/translate_a/t?client=o&hl=en&sl=' . urlencode($tp[0]) . '&tl=' . urlencode($tp[1]) . '&text=' . urlencode($text)), true);
					if(isset($translation['sentences'][0]['trans']))
						$this->privmsg($socket, $channel, "{$sender}: {$translation['sentences'][0]['trans']}");
					else{
						$this->privmsg($socket, $channel, "{$sender}: An error occurred :< blame Google!");
					}
				}else {
					$this->privmsg($socket, $channel, "{$sender}: Usage: {$that->prefix}translate de|en Ich liebe dich <3. de is the source language and en is the target language.");
				}
			}else {
				$this->privmsg($socket, $channel, "{$sender}: Usage: {$that->prefix}translate de|en Ich liebe dich <3. de is the source language and en is the target language.");
			}
		}else {
			$this->privmsg($socket, $channel, "{$sender}: Usage: {$that->prefix}translate de|en Ich liebe dich <3. de is the source language and en is the target language.");
		}
	}
}
