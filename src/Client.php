<?php
namespace Grpcphp;

use OpenSwoole\GRPC\ClientPool;
use OpenSwoole\GRPC\ClientFactory;
use Swoole\Coroutine;
use co;
use Grpcphp\Helper\Logger;

class Client
{
    private $config;
    private $coroutineSetting;
    private $connectionPool;
    private $connection;
    private $logger;
    
    public function __construct() 
    {      
        $this->config = include('config/client.php');
        $this->coroutineSetting = [
            'trace_flags' => SWOOLE_TRACE_HTTP2,
            'log_level' => 0,
        ];
        $this->logger = new Logger();
    }

    public function connect()
    {
        // Open connection to http server using http2 connection pool
        $this->connectionPool = new ClientPool(
            ClientFactory::class, 
            $this->config['connection'], 
            10000
        );
        $this->connection = $this->connectionPool->get();
    }

    public function testAsync()
    {    
        Coroutine::set($this->coroutineSetting);

        // Execute asynchronous behaviour using goroutine
        co::run(function(){
            // Establish Connection
            $this->connect();

            // First thread
            go(function (){
                $streamId = $this->connection->send("/first_thread", json_encode(["description" => "This is a sample test"]), "json");
                co::sleep(2);

                $this->logger->info("thread 1 finish with streamId: ".$streamId);
                $this->connectionPool->put($this->connection);
            });

            // Second thread
            go(function (){
                $streamId = $this->connection->send("/second_thread", json_encode(["description" => "This is a sample test"]), "json");
                co::sleep(2);

                $this->logger->info("thread 2 finish with streamId: ".$streamId);
                $this->connectionPool->put($this->connection);
            });

            // Third thread
            go(function (){
                $streamId = $this->connection->send("/third_thread", json_encode(["description" => "This is a sample test"]), "json");
                co::sleep(2);

                $this->logger->info("thread 3 finish with streamId: ".$streamId);
                $this->connectionPool->put($this->connection);
            });
            
            // Close connection
            go(function (){
                $this->connectionPool->close();
                $this->logger->info("End");
            });
        });
    }
}