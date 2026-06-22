<?php
namespace App\Models;

use App\Core\Model;

class ScheduledTask extends Model
{
    protected static string $table = 'scheduled_tasks';
    protected static array $fillable = ['agent_id', 'command', 'scheduled_at', 'status', 'priority', 'retry_count', 'max_retries'];
    protected static array $guarded = ['id'];

    public static function scheduleCommand($agentId, $command, $scheduledAt, $priority = 0)
    {
        return self::create([
            'agent_id' => $agentId,
            'command' => $command,
            'scheduled_at' => $scheduledAt,
            'status' => 'pending',
            'priority' => $priority,
            'retry_count' => 0,
            'max_retries' => 3
        ]);
    }

    public static function getPending()
    {
        $sql = "SELECT * FROM scheduled_tasks WHERE status = 'pending' AND scheduled_at <= datetime('now') ORDER BY priority DESC, scheduled_at ASC";
        $stmt = self::query($sql);
        return $stmt->fetchAll();
    }

    public static function markComplete($id)
    {
        self::update($id, ['status' => 'completed']);
    }

    public static function markFailed($id)
    {
        $task = self::find($id);
        if ($task) {
            $retries = $task['retry_count'] + 1;
            if ($retries >= $task['max_retries']) {
                self::update($id, ['status' => 'failed', 'retry_count' => $retries]);
            } else {
                self::update($id, ['status' => 'pending', 'retry_count' => $retries]);
            }
        }
    }
}
