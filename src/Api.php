<?php
namespace Grpcphp;

use OpenSwoole\Core\Coroutine\WaitGroup;
use Swoole\Coroutine;
use co;
use Grpcphp\Helper\Logger;

class Api
{
    private $config;
    private $coroutineSetting;
    private $logger;

    public function __construct() 
    {      
        $this->config = include('config/api.php');
        $this->coroutineSetting = [
            'trace_flags' => SWOOLE_TRACE_HTTP2,
            'log_level' => 0,
        ];
        $this->logger = new Logger();
    }

    public function testApi(int $totalOperation = 100)
    {
        Coroutine::set($this->coroutineSetting);

        // Request api concurrently
        co::run(function() use ($totalOperation){
            $waitGroup = new WaitGroup();
            $startTime = microtime(true);

            while ($totalOperation-- > 1) {
                $waitGroup->add(); 
                go(function () use ($waitGroup, $totalOperation) {
                    $clientHttp2 = new \OpenSwoole\Coroutine\Http2\Client('jsonplaceholder.typicode.com', 443, true);
                    $clientHttp2->set($this->config['setting']);
                    $clientHttp2->connect();
                    
                    $request = new \OpenSwoole\Http2\Request();
                    $request->path = "/posts/1";
                    $request->headers = [
                        'host' => "jsonplaceholder.typicode.com"
                    ];

                    $clientHttp2->send($request);

                    $response = $clientHttp2->recv(5);

                    if(!isset($response->statusCode)){
                        $response = "fail";
                    }else{
                        $response = $response->statusCode;
                    }
                    $this->logger->info("API " . $totalOperation . ": status ". $response);

                    $waitGroup->done(); 
                });
            }
            $waitGroup->wait();
            
            go(function () use ( $startTime) {
                $this->logger->info('Duration: ' . round((microtime(true) - $startTime),2) . " s");
                $this->logger->info("End");
            });
        });
    }
}