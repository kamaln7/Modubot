<?php

Class Modubot_Youtube extends Modubot_Module {

	public $regex = "#(?:https?://)?(?:(?:(?:www\\.)?youtube\\.com/watch\\?.*?v=([a-zA-Z0-9_\\-]+))|(?:(?:www\\.)?youtu\\.be/([a-zA-Z0-9_\\-]+)))#i";
	public $helpline = 'parses YouTube links and outputs information about them.';
	private $cache = array();
	public $noCommand = true;

	public function match(&$that, &$socket, $data, $matches){
		if($that->expect($data, 'topicchange', ['channel' => $that->channel($data)]))
			return;

		$sender = $that->sender($data);
		$id = $matches[1];
		if(isset($this->cache[$id])){
			if($this->cache[$id]['time'] < (time() - 120)){
				unset($this->cache[$id]);
			}
		}

		if(isset($this->cache[$id])){
			$title = $this->cache[$id]['title'];
			$uploader = $this->cache[$id]['uploader'];
			$views = $this->cache[$id]['views'];
			$this->privmsg($socket, $that->channel($data), "({$sender}) \x02{$title}\x02 {$uploader} - {$views} views.");

			return true;
		}

		$ydata = file_get_contents("http://gdata.youtube.com/feeds/api/videos/{$id}?v=2&alt=jsonc");
		$ydata = json_decode($ydata);
		if(isset($ydata->data, $ydata->data->title)){
			$ydata = $ydata->data;
			$title = $ydata->title;
			$uploader = isset($ydata->uploader) ? "[{$ydata->uploader}]" : '';
			$views = $ydata->viewCount;

			$this->cache[$id] = array(
				'title' => $title,
				'uploader' => $uploader,
				'views' => $views,
				'time' => time()
			);

			$this->privmsg($socket, $that->channel($data), "({$sender}) \x02{$title}\x02 {$uploader} - {$views} views.");
		}
	}

	public function process(&$that, &$socket, $data, $input, $command, $args){
		if($args == 'clearcache' && $that->getLevel($that->sender($data), '', $that->host($data)) > 6)
			$this->cache = array();
	}
}
