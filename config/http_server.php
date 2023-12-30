<?php
return  [
    'connection' => [
        'host' => (string) $_ENV['HOST'],
        'port' => (int) $_ENV['PORT'],
        'process'=> SWOOLE_PROCESS,
        'socket_tcp' => SWOOLE_SOCK_TCP
    ],
    
    'setting' => [
        'open_http2_protocol' => 1,
        'enable_static_handler' => TRUE,
        'document_root' => __DIR__,
        'enable_coroutine' => true,
        'log_level' => SWOOLE_LOG_TRACE,
        'trace_flags' => SWOOLE_TRACE_ALL,
    ],
];

