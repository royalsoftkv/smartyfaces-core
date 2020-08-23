<?php
// SF_SOCKET_HOST=0.0.0.0 SF_SOCKET_PORT=2050  php /var/www/html/smartyfaces/vendor/royalsoftkv/smartyfaces-core/socket/server.php start
use Workerman\Worker;

require dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . "/SFSocketIO.php";

$host = "127.0.0.1";
$port = 2080;
if(isset($_SERVER['SF_SOCKET_HOST'])) {
    $host = $_SERVER['SF_SOCKET_HOST'];
}
if(isset($_SERVER['SF_SOCKET_PORT'])) {
    $port = $_SERVER['SF_SOCKET_PORT'];
}

echo "Creating socket server on $host:$port".PHP_EOL;

$io = new SFSocketIO($host,$port);
$io->on('connection', function($socket){
    echo "socket connected";
});

Worker::runAll();
