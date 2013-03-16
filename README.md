Modubot
=======

Modubot, a very modular IRC bot written in PHP (with utilisation of MySQL/SQLite)


Installation
============

* Import the .sql file provided in the root directory.
* php -f start.php

Modules
=======

The bot is modular. This means you can easily add/take out any part of it whenever you want. See the modules directory for examples.

Dependencies
============

Modubot depends on the PDO php extension, the runkit php extension for dynamic module manipulation (tested with https://github.com/zenovich/runkit), and the shmop php extension.
Some modules use PHP 5.4 array syntax. However, the core is compatible with PHP 5.3.

License
=======

See the file LICENSE.
