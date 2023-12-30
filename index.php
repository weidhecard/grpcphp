<?php
require __DIR__ . '/bootstrap.php';

$httpServer = new Grpcphp\HttpServer();
$httpServer->run();