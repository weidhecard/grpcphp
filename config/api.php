<?php
return [
    'setting' => [
        'timeout'                      => 10,
        'open_eof_check'               => true,
        'package_max_length'           => 2 * 1024 * 1024,
        'http2_max_concurrent_streams' => 1000,
        'http2_max_frame_size'         => 2 * 1024 * 1024,
        'max_retries'                  => 1
    ],
];