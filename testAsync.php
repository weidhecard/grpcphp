<?php
require __DIR__ . '/bootstrap.php';

$client = new Grpcphp\Client();
$client->testAsync();