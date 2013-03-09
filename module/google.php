<?php

Class Modubot_Google extends Modubot_Module {
	public $alias = array('g');
	public $helpline = 'queries google about [input] and returns the result.';

	private function chrdecode($chrs){
		return chr($chrs[1]);
	}

	public function process(&$that, &$socket, $data, $input, $command, $args){
		$sender = $that->sender($data);
		$channel = $that->channel($data);
		$term = urlencode($args);
 		if(!empty($term)){
 			$body = json_decode(file_get_contents('http://ajax.googleapis.com/ajax/services/search/web?v=1.0&q=' . $term));
			$title = $body->responseData->results[0]->titleNoFormatting;
			$summary = $body->responseData->results[0]->content;
			$title = html_entity_decode(preg_replace_callback('~&#([0-9]{1,3});~', array('Modubot_Google', 'chrdecode'), $title));
                        $summary = html_entity_decode(preg_replace_callback('~&#([0-9]{1,3})+;~', array('Modubot_Google', 'chrdecode'), $summary));

 			$this->privmsg($socket, $channel, $sender . ': ' . "\x02" . $title . chr(15) . ' - ' . strip_tags(preg_replace('#\s+#', ' ', $summary)) . ' - ' . chr(31) . $body->responseData->results[0]->url);
 		}else {
 			$this->privmsg($socket, $channel, $sender . ': Usage: ' . $that->prefix . $command . ' term or terms');
 		}
	}
}
