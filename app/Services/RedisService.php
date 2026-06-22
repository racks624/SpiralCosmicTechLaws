<?php
namespace App\Services;

use Predis\Client;

class RedisService
{
    private static ?Client $client = null;

    public static function getClient()
    {
        if (self::$client === null) {
            self::$client = new Client([
                'scheme' => 'tcp',
                'host'   => getenv('REDIS_HOST') ?: '127.0.0.1',
                'port'   => getenv('REDIS_PORT') ?: 6379,
            ]);
        }
        return self::$client;
    }

    public static function pushTask($agentId, $command)
    {
        $client = self::getClient();
        $taskId = $client->incr('task_id_counter');
        $taskData = json_encode(['id' => $taskId, 'agent_id' => $agentId, 'command' => $command, 'status' => 'pending']);
        $client->rpush("agent:{$agentId}:tasks", $taskData);
        return $taskId;
    }

    public static function getTasksForAgent($agentId)
    {
        $client = self::getClient();
        $tasks = $client->lrange("agent:{$agentId}:tasks", 0, -1);
        return array_map('json_decode', $tasks);
    }

    public static function popTaskForAgent($agentId)
    {
        $client = self::getClient();
        $task = $client->lpop("agent:{$agentId}:tasks");
        if ($task) {
            return json_decode($task, true);
        }
        return null;
    }

    public static function publishCommand($agentId, $command)
    {
        $client = self::getClient();
        $client->publish("agent:{$agentId}", json_encode(['command' => $command]));
    }
}
