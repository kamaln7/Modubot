-- admins

  CREATE TABLE IF NOT EXISTS `admins` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `nick` varchar(255) NOT NULL,
    `host` varchar(255) NOT NULL,
    `super` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
  ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

  INSERT INTO `admins` (`nick`, `host`, `super`) VALUES
  ('kamaln', 'znc.kamalnasser.net', 1);

-- channels

  CREATE TABLE IF NOT EXISTS `channels` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `chanflags` int(11) NOT NULL DEFAULT '1',
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
  ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

  INSERT INTO `channels` (`name`) VALUES
  ('#modubot');

-- factoids

  CREATE TABLE IF NOT EXISTS `factoids` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `factoid` varchar(55) NOT NULL,
    `value` text NOT NULL,
    `locked` tinyint(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    UNIQUE KEY `factoid` (`factoid`)
  ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- ignore

  CREATE TABLE IF NOT EXISTS `ignore` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `name` (`name`)
  ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


  INSERT INTO `ignore` (`name`) VALUES
  ('abuser');

-- misc

  CREATE TABLE IF NOT EXISTS `misc` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `key` varchar(255) DEFAULT NULL,
    `value` text,
    PRIMARY KEY (`id`),
    UNIQUE KEY `key` (`key`)
  ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

  INSERT INTO `misc` (`key`, `value`) VALUES
  ('kickreason', 'because Chuck Norris');

-- todo

  CREATE TABLE IF NOT EXISTS `todo` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `nick` varchar(255) NOT NULL,
    `item` text NOT NULL,
    PRIMARY KEY (`id`),
    FULLTEXT KEY `item` (`item`)
  ) ENGINE=MyISAM  DEFAULT CHARSET=latin1;