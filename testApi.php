<?php
require __DIR__ . '/bootstrap.php';

$api = new Grpcphp\Api();
$api->testApi(100);