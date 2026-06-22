<?php
namespace App\Services;

use App\Models\ScheduledTask;
use App\Models\C2AuditLog;
use App\Models\Agent;

class TaskScheduler
{
    public static function processPendingTasks()
    {
        $tasks = ScheduledTask::getPending();
        foreach ($tasks as $task) {
            $agent = Agent::find($task['agent_id']);
            if ($agent && $agent['status'] === 'active') {
                // Push to Redis queue or send immediately
                $result = self::sendCommand($agent['agent_id'], $task['command']);
                if ($result) {
                    ScheduledTask::markComplete($task['id']);
                    C2AuditLog::log('system', 'execute_scheduled', 'agent', $agent['id'], ['task_id' => $task['id']]);
                } else {
                    ScheduledTask::markFailed($task['id']);
                }
            } else {
                ScheduledTask::markFailed($task['id']);
            }
        }
    }

    private static function sendCommand($agentId, $command)
    {
        // Use RedisService to push command
        $agent = Agent::where('agent_id', $agentId);
        if ($agent) {
            RedisService::pushTask($agent[0]['id'], $command);
            return true;
        }
        return false;
    }

    public static function scheduleCommandForGroup($groupId, $command, $scheduledAt)
    {
        $agents = Agent::where('group_id', $groupId);
        $ids = [];
        foreach ($agents as $agent) {
            $ids[] = ScheduledTask::scheduleCommand($agent['id'], $command, $scheduledAt);
        }
        return $ids;
    }
}
