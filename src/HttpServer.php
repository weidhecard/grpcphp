<?php

namespace Grpcphp;

use Swoole\Coroutine;
use swoole_http_server;
use swoole_http_request;
use swoole_http_response;
use Grpcphp\Helper\Logger;

class HttpServer
{
    private $config;
    private $coroutineSetting;
    private $logger;

    public function __construct()
    {
        $this->config = include('config/http_server.php');
        $this->coroutineSetting = [
            'trace_flags' => SWOOLE_TRACE_HTTP2,
            'log_level' => 0,
        ];
        $this->logger = new Logger();
    }

    public function run()
    {
        Coroutine::set($this->coroutineSetting);

        $http = new swoole_http_server(
            $this->config['connection']['host'], 
            $this->config['connection']['port'], 
            $this->config['connection']['process'], 
            $this->config['connection']['socket_tcp']
        );

        $http->set($this->config['setting']);

        $http->on('request', function (swoole_http_request $request, swoole_http_response $response) {
            $response->header('Access-Control-Allow-Origin', '*');
            $response->header('Content-Type', 'text/event-stream');
            $response->header('Cache-Control', 'no-cache');
            $response->header('X-Accel-Buffering', 'no');

            $responseData = [
                "streamId" => null,
                "path" => $request->server['request_uri'],
                "time" => date(DATE_ATOM)
            ];

            if(!empty($request->getContent()))
            {
                $responseData['streamId'] = $request->streamId;
                $responseData['data'] = $request->getContent();
                $responseData['data'] = trim($responseData['data'],"\x00\x00\x00\x00");
                $responseData['data'] = trim($responseData['data'],"'");
            }
                
            // Log response
            $this->logger->info(json_encode($responseData));
            
            // Write response
            $response->write(json_encode($responseData));
            $response->end($this->config['setting']['document_root']);
        });

        // Log server
        $this->logger->info("HTTP server starting at: " . $this->config['connection']['host'] . ':'. $this->config['connection']['port'] . "\n");

        $http->start();
    }
}
