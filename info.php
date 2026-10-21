<?php
// Temporary diagnostic page. Remove after checking the server's PHP version.

if (isset($_GET['full'])) {
    phpinfo();
    exit;
}

header('Content-Type: text/plain; charset=utf-8');
echo 'PHP version: ' . PHP_VERSION . PHP_EOL;
echo 'SAPI: ' . PHP_SAPI . PHP_EOL;
echo 'OS: ' . PHP_OS . PHP_EOL;
echo 'Loaded ini: ' . (php_ini_loaded_file() ?: 'none') . PHP_EOL;
echo PHP_EOL . 'Full phpinfo() output: append ?full=1 to the URL' . PHP_EOL;
