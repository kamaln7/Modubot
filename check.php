<?php

echo "Linting files...\n";

$lint = trim(end(explode("\n", trim(shell_exec('find . -name "*.php" -print0 | xargs -0 -n1 -P8 php -l 2>&1 >/dev/null; echo $?')))));

echo 'Status: ' . ($lint == 0 ? 'ok' : 'bad') . "\n";
if($lint != 0)
	die();
