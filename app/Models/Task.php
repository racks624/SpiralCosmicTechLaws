<?php
namespace App\Models;

use App\Core\Model;

class Task extends Model
{
    protected static string $table = 'tasks';
    protected static array $fillable = ['agent_id', 'command', 'status', 'output'];
    protected static array $guarded = ['id'];

    public static function queueCommand($agentId, $command)
    {
        return self::create([
            'agent_id' => $agentId,
            'command' => $command,
            'status' => 'pending'
        ]);
    }

    public static function getPendingForAgent($agentId)
    {
        $stmt = self::query("SELECT * FROM tasks WHERE agent_id = ? AND status = 'pending' ORDER BY created_at ASC", [$agentId]);
        return $stmt->fetchAll();
    }
}
