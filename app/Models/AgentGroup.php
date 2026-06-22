<?php
namespace App\Models;

use App\Core\Model;

class AgentGroup extends Model
{
    protected static string $table = 'agent_groups';
    protected static array $fillable = ['name', 'description'];
    protected static array $guarded = ['id'];

    public static function getAgents($groupId)
    {
        return Agent::where('group_id', $groupId);
    }
}
