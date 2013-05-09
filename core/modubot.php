<?php
$path = str_replace('\\', '/', str_replace(array('core/modubot.php', 'core\modubot.php'), '', __FILE__));

require_once($path . 'module/module.php');
require_once($path . 'core/lib/string.lib.php');
require_once($path . 'core/lib/http.lib.php');
require_once($path . 'core/lib/formatting.lib.php');

class Modubot
{

    public $socket;
    public $nick;
    public $size = 512;
    public $prefix = '%';
    public $modules;
    public $modules_regex = array();
    public $modules_prefix = array();
    public $modules_alias = array();
    public $modules_hooks = array();
    public $config;
    public $db;
	public $dbinfo;
    public $path;
    public $shmop;

    private function send($signal)
    {
        fputs($this->socket, Str::trim($signal) . "\n");
        echo trim($signal) . "\n";
    }

    private function privmsg($target, $message)
    {
        $this->send('PRIVMSG ' . Str::trim($target) . ' :' . Str::trim($message));
    }

    public function command($input)
    {
        $input = explode(' ', Str::trim($input));
        $command = isset($input[0]) ? Str::after($this->prefix, $input[0]) : '';

        return $command;
    }

    public function input($data)
    {
        $input = explode(':', Str::trim($data));
        $input = isset($input[0], $input[1]) ? Str::trim(str_replace("{$input[0]}:{$input[1]}:", '', $data)) : '';

        return $input;
    }

    public function sender($data)
    {
        $e = explode(' ', Str::trim($data));
        $sender = isset($e[0]) ? explode('!', Str::after(':', $e[0])) : array('');
        return $sender[0];
    }

    public function channel($data)
    {
        $e = explode(' ', Str::trim($data));
        $chan = isset($e[2]) ? $e[2] : '';
        if (!Str::beginsWith('#', $chan)) $chan = $this->sender($data);

        return $chan;
    }

    public function host($data)
    {
        $e = explode(' ', Str::trim($data));
        $e = explode('@', $e[0]);
        $host = isset($e[1]) ? $e[1] : '';
        return $host;
    }

    public function getLevel($user, $channel, $host = '')
    {
	$user = trim($user);
	$host = trim($host);
        if (!empty($host)) {
            //Override in case mysql connection is dead.
            $admins = array(
                /*Nick*/'kamal' => array(
                    'host' => 'kamalnasser.net',
                    'super' => true
                )
            );
            if(in_array(strtolower($user), $admins))
                if($admins[strtolower($user)]['host'] == strtolower($host))
                    return $admins[strtolower($user)]['super'] ? 8 : 7;

            //Otherwise, check mysql
            try {
                $admin = $this->db->prepare('SELECT `super` FROM `admins` WHERE `nick` = :nick AND `host` = :host');
                $admin->bindParam(':nick', strtolower($user), PDO::PARAM_STR);
                $admin->bindParam(':host', strtolower($host), PDO::PARAM_STR);
                $admin->execute();
                if($admin->rowCount() > 0){
                    $super = $admin->fetch();
                    if ($super->super == true)
                        return 8;
                    else
                        return 7;
                }
            }catch (PDOException $e){
                $this->logE($e);
            }
        }

        if(!empty($channel)){
            $this->send('WHOIS ' . $user);
            $gotit = false;
            while (!$gotit) {
                $data = $this->listen();
                $channelflag = $this->expect($data, 'whoischannel', array('who' => $user, 'channel' => $channel));
                $end = $this->expect($data, 'endofwhois', array('who' => $user));
                if ($channelflag !== false) {
                    $gotit = true;
                    switch ($channelflag[2]) {
                        case '~':
                            return 6;
                            break;
                        case '&':
                            return 5;
                            break;
                        case '@':
                            return 4;
                            break;
                        case '%':
                            return 3;
                            break;
                        case '+':
                            return 2;
                            break;
                        default:
                            return 1;
                            break;
                    }
                } elseif ($end !== false) {
                    $gotit = true;
                    return 1;
                } else {
                    $this->process($data);
                }
            }
        }

        return 1;
    }

    public function logE($e)
    {
        if(is_object($e) && get_class($e) == 'PDOException'){
            echo "PDOException encountered:\n", $e->getMessage(), "\n";
        }
    }

    public function __construct($server, $port, $user, $mysql, &$shmop, $config = array())
    {
        global $path;
        $this->path = $path;
        $ssl = Str::beginsWith('+', $port);
        if ($ssl) {
            $server = 'ssl://' . $server;
            $port = Str::after('+', $port);
        }
        $this->nick = Str::trim($user);
        if (isset($config['prefix'])) $this->prefix = Str::trim($config['prefix']);
        $this->config = $config;
        $this->shmop = $shmop;

        try {
            $this->db = new PDO("mysql:host={$mysql['host']};dbname={$mysql['database']}", $mysql['user'], $mysql['password'], array(PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        }catch (PDOException $e){
            $this->logE($e);
            die();
        }

		$this->dbinfo = $mysql;

        $this->socket = fsockopen($server, $port);
        if ($this->socket) {
	    if (isset($config['password']) )
		$this->send("PASS {$config['password']}");
            $this->send("NICK {$this->nick}");
		$ident = isset($config['ident']) ? $config['ident'] : $this->nick;
            $this->send("USER {$ident} * * :http://modubot.net");

            $pinged = false;
            $nickserv = !isset($config['nickServ']);
            $joined = false;
            while (!$pinged) {
                $data = fgets($this->socket, $this->size);
                echo trim($data) . "\n";

                if (preg_match("/:Nickname is already in use.$/", Str::trim($data))) {
                    die('Nickname not available.');
                }

                if (Str::beginsWith('PING :', $data)) {
                    $ping = Str::after('PING :', $data);
                    $this->send('PONG :' . $ping);
                    $pinged = true;
                }
            }
            while (!$nickserv) {
                $data = fgets($this->socket, $this->size);
                echo trim($data) . "\n";

                if (preg_match("/^\:NickServ\!NickServ@.* NOTICE {$this->nick} :This nickname is registered./i", Str::trim($data))) {
                    $this->privmsg('NickServ', "identify {$config['nickServ']}");
                    $nickserv = true;
                }
            }
            while (!$joined) {
                if (isset($this->config['debug']) && Str::beginsWith('#', $this->config['debug'])) {
                    $this->send("JOIN {$this->config['debug']}");
                    $joined = true;
                } else {
                    try {
                        $channels = $this->db->query('SELECT `name` FROM `channels` ORDER BY `name` ASC');
                        while ($row = $channels->fetch()) {
                            $this->send('JOIN ' . $row->name);
                            usleep(250000);
                        }
                        $joined = true;
                    }catch (PDOException $e){
                        $this->logE($e);
                        die();
                    }
                    
                }
            }
        }
    }

    public function load($modules)
    {
        $allowed_hooks = array(
            'beforeCommand',
            'beforePrefix',
        );

        if (is_array($modules)) {
            foreach ($modules as $module) {
                $module = strtolower($module);
                require_once($this->path . 'module/' . $module . '.php');
                $class = 'Modubot_' . ucfirst($module);
                $this->modules[$module] = new $class($this);
                echo "Loaded: {$class}\n";

                if (!empty($this->modules[$module]->regex)) {
                    $this->modules_regex[$module] = $this->modules[$module]->regex;
                }
                if (!empty($this->modules[$module]->prefix)) {
                    $this->modules_prefix[$module] = $this->modules[$module]->prefix;
                }
                if (!empty($this->modules[$module]->alias) && is_array($this->modules[$module]->alias)) {
                    foreach ($this->modules[$module]->alias as $alias)
                        $this->modules_alias[$alias] = $module;
                }

                if(!empty($this->modules[$module]->hooks) && is_array($this->modules[$module]->hooks)){
                    foreach ($this->modules[$module]->hooks as $hook)
                        if(in_array($hook, $allowed_hooks))
                            $this->modules_hooks[$hook][] = array('hook' => $hook, 'module' => $module);
                    else
                        echo "{$module}'s hook is not supported.";
                }
            }
        } elseif (is_bool($modules) && $modules === true) {
            foreach (glob($this->path . 'module/*.php') as $module) {
                require_once($module);
                $module = strtolower(Str::trim(str_replace('.php', '', basename($module))));
                $class = 'Modubot_' . ucfirst($module);
                $this->modules[$module] = new $class($this);
                echo "Loaded: {$class}\n";

                if (!empty($this->modules[$module]->regex)) {
                    $this->modules_regex[$module] = $this->modules[$module]->regex;
                }
                if (!empty($this->modules[$module]->prefix)) {
                    $this->modules_prefix[$module] = $this->modules[$module]->prefix;
                }
                if (!empty($this->modules[$module]->alias) && is_array($this->modules[$module]->alias)) {
                    foreach ($this->modules[$module]->alias as $alias)
                        $this->modules_alias[$alias] = $module;
                }

                if(!empty($this->modules[$module]->hooks) && is_array($this->modules[$module]->hooks)){
                    foreach ($this->modules[$module]->hooks as $hook)
                        if(in_array($hook, $allowed_hooks))
                            $this->modules_hooks[$hook][] = array('hook' => $hook, 'module' => $module);
                        else
                            echo "{$module}'s hook is not supported.";
                }
            }
        } elseif (file_exists($this->path . 'module/' . strtolower($modules) . '.php')) {
            $module = strtolower($modules);
            require_once($this->path . 'module/' . $module . '.php');
            $class = 'Modubot_' . ucfirst($module);
            $this->modules[$module] = new $class($this);
            echo "Loaded: {$class}\n";

            if (!empty($this->modules[$module]->regex)) {
                $this->modules_regex[$module] = $this->modules[$module]->regex;
            }
            if (!empty($this->modules[$module]->prefix)) {
                $this->modules_prefix[$module] = $this->modules[$module]->prefix;
            }
            if (!empty($this->modules[$module]->alias) && is_array($this->modules[$module]->alias)) {
                foreach ($this->modules[$module]->alias as $alias)
                    $this->modules_alias[$alias] = $module;
            }

            if(!empty($this->modules[$module]->hooks) && is_array($this->modules[$module]->hooks)){
                foreach ($this->modules[$module]->hooks as $hook)
                    if(in_array($hook, $allowed_hooks))
                        $this->modules_hooks[$hook][] = array('hook' => $hook, 'module' => $module);
                    else
                        echo "{$module}'s hook is not supported.";
            }
        }
    }

    public function expect($data, $what, $with)
    {
        switch ($what) {
            case 'nick':
		        $who = preg_quote($with['who'], '/');
                if (preg_match("/{$who}![a-zA-Z0-9~]+@.+ NICK :(.+)/i", $data, $matches)) {
                    return $matches;
                } else {
                    return false;
                }
                break;
            case 'nickserv':
                $who = preg_quote($with['who'], '/');
                if (preg_match("/:[a-zA-Z0-9\.]+ 307 {$this->nick} ({$who}) :is a registered nick/i", $data, $matches) || preg_match("/:[a-zA-Z0-9\.]+ 330 {$this->nick} ({$who}) .*?:is logged.*?/i", $data, $matches)) {
                    return $matches;
                } else {
                    return false;
                }
                break;
            case 'endofwhois':
                $who = preg_quote($with['who'], '/');
                if (preg_match("/:[a-zA-Z0-9\.]+ 318 {$this->nick} {$who} :End of \/WHOIS list./i", $data, $matches)) {
                    return $matches;
                } else {
                    return false;
                }
                break;
            case 'nosuchnickchannel':
                $who = preg_quote($with['who'], '/');
                if (preg_match("/:[a-zA-Z0-9\.]+ 401 {$this->nick} {$who} :No such nick\/channel/i", $data, $matches)) {
                    return $matches;
                } else {
                    return false;
                }
                break;
            case 'whoischannel':
                $who = preg_quote($with['who'], '/');
                $channel = preg_quote($with['channel'], '/');
                if (preg_match("/:[a-zA-Z0-9\.]+ 319 {$this->nick} ({$who}) :.*?([~&@%\+]*){$channel}[\s|$]/i", $data, $matches)) {
                    return $matches;
                } else {
                    return false;
                }
                break;
            case 'modechannel':
                $channel = preg_quote($with['channel'], '/');
                if (preg_match("/:[a-zA-Z0-9\.]+ 324 {$this->nick} {$channel} ([\+|-][a-zA-Z]+)/i", $data, $matches)) {
                    return $matches;
                } else {
                    return false;
                }
                break;
            case 'topicchange':
                $channel = preg_quote($with['channel'], '/');
                return preg_match("/![a-zA-Z0-9~]+@.+ TOPIC {$channel}/i", $data);
                break;
            default:
                return false;
                break;
        }
    }

	public function isConnected()
	{
		return is_resource($this->socket) && !feof($this->socket);
	}

    public function listen()
    {
		if(!$this->isConnected()) {
			die("\n\nReached end of socket.\n");
		}

        $data = fgets($this->socket, $this->size);
        echo $data;
        return $data;
    }

    public function process($data)
    {
        $input = $this->input($data);
        if(!is_resource($this->db)){
            try{
                $this->db = new PDO("mysql:host={$this->dbinfo['host']};dbname={$this->dbinfo['database']}", $this->dbinfo['user'], $this->dbinfo['password'], array(PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
                $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            }catch (PDOException $e){}
        }

        if (Str::beginsWith('PING :', $data)) {
            $ping = Str::after('PING :', $data);
            $this->send('PONG :' . $ping);
            return;
        }

        if (isset($this->config['joinOnInvite']) && $this->config['joinOnInvite'] === true) {
            if (preg_match("/.*INVITE {$this->nick} :(#[#a-zA-Z0-9]+)/", $data, $match)) {
                try {
                    $channel = $this->db->prepare('INSERT INTO `channels` (`name`) VALUES (:channel)');
                    $channel->bindParam(':channel', $match[1], PDO::PARAM_STR);
                    $channel->execute();
                }catch (PDOException $e){
                    $this->logE($e);
                }
                $this->send('JOIN ' . $match[1]);
                return;
            }
        }

        foreach ($this->modules_regex as $class => $regex) {
            if (preg_match($regex, $data, $matches)) {
                $this->modules[$class]->match($this, $this->socket, Str::trim($data), $matches);
            }
        }

        foreach ($this->modules_prefix as $class => $prefix) {
            if (Str::beginsWith($prefix, $input)) {
                $command = explode(' ', Str::after($this->prefix, $input));
                $command = $command[0];
                $pinput = explode(' ', $input);
                unset($pinput[0]);
                $pinput = implode(' ', $pinput);
                $okay = true;
                if(isset($this->modules_hooks['beforePrefix']))
                    foreach($this->modules_hooks['beforePrefix'] as $hook){
                        if($hook['hook'] == 'beforePrefix'){
                            $okay = $this->modules[$hook['module']]->beforePrefix($this, $this->socket, Str::trim($data), $input, $command, $pinput);
                            if($okay == false)
                                break;
                        }
                    }
                if($okay)
                    $this->modules[$class]->prefix($this, $this->socket, Str::trim($data), $input, $command, $pinput);
            }
        }

        if (Str::beginsWith($this->prefix, $input)) {
            $command = strtolower($this->command($input));

            if (!($command == 'module') && (isset($this->modules[$command]) || (isset($this->modules_alias[$command]) && isset($this->modules[$this->modules_alias[$command]])))) {
                /*
                 LEVELS:
                 1 - regular
                 2 - voiced
                 3 - halfop
                 4 - op
                 5 - protected
                 6 - owner
                 7 - admin
                 8 - super admin
                */

                $okay = true;

                if(isset($this->modules_hooks['beforeCommand']))
                    foreach($this->modules_hooks['beforeCommand'] as $hook){
                        if($hook['hook'] == 'beforeCommand'){
                            $okay = $this->modules[$hook['module']]->beforeCommand($this, $this->socket, Str::trim($data), $input, $command, Str::after($this->prefix . $this->command($input), $input));
                            if($okay == false)
                                break;
                        }
                    }
                if($okay){
                    if (isset($this->modules[$command])) {
                        $this->modules[$command]->process($this, $this->socket, Str::trim($data), $input, $command, Str::after($this->prefix . $this->command($input), $input));
                    } elseif (isset($this->modules_alias[$command]) && isset($this->modules[$this->modules_alias[$command]])) {
                        $this->modules[$this->modules_alias[$command]]->process($this, $this->socket, Str::trim($data), $input, $command, Str::after($this->prefix . $this->command($input), $input));
                    }
                }
            }
        }
    }

    public function disconnect($message = 'Quit command issued.')
    {
        $this->send('QUIT :' . Str::trim($message));
        fclose($this->socket);
    }
}
