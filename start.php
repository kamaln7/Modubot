#!/usr/bin/php
<?php
require('check.php');
set_time_limit(0);
require('core/modubot.php');

$mysql = array(
	'host' => 'localhost',
	'user' => 'modubot',
	'password' => 'mypassword',
	'database' => 'modubot'
);

//NickServ password is found in the file called nspass.
//If a password is not needed, remove the nickServ key.

$config = array(
	'nickServ' => 'mynickservpassword',
	'joinOnInvite' => true,
	'ident' => 'modubot',
);

$shmop = shmop_open(0xff4, "c", 0644, 1);
shmop_write($shmop, '0', 0);

$bot = new Modubot('ircserver.net', '+6697', 'Modubot', $mysql, $shmop, $config);
/*

- Load an array of modules:
$bot->load(array('Version', 'Ping'));

- Load single modules:
$bot->load('Who');
$bot->load('Gender');

- Load ALL the modules!
*/
$bot->load(true);

class reload {
	public function __construct($file){
		runkit_import($file, RUNKIT_IMPORT_CLASSES | RUNKIT_IMPORT_OVERRIDE);
	}
}

while(true){
	if(shmop_read($shmop, 0, 1) == 1){
		new reload('core/modubot.php');
		shmop_write($shmop, '1', 0);
	}

	if($data = $bot->listen())
		$bot->process($data);
}

$bot->disconnect();
