<?php
namespace App\Models;

use App\Core\Model;

class Agent extends Model
{
    protected static string $table = 'agents';
    protected static array $fillable = [
        'agent_id', 'hostname', 'os', 'ip_address', 'last_seen', 'status',
        'description', 'tags', 'jitter', 'group_id', 'last_heartbeat', 'uptime', 'protocol'
    ];
    protected static array $guarded = ['id'];

    public static function register($agentId, $hostname, $os, $ip, $protocol = 'https')
    {
        $existing = self::where('agent_id', $agentId);
        if ($existing) {
            return self::update($existing[0]['id'], [
                'last_seen' => date('Y-m-d H:i:s'),
                'last_heartbeat' => date('Y-m-d H:i:s'),
                'status' => 'active'
            ]);
        } else {
            return self::create([
                'agent_id' => $agentId,
                'hostname' => $hostname,
                'os' => $os,
                'ip_address' => $ip,
                'last_seen' => date('Y-m-d H:i:s'),
                'last_heartbeat' => date('Y-m-d H:i:s'),
                'status' => 'active',
                'protocol' => $protocol
            ]);
        }
    }

    public static function heartbeat($agentId, $protocol = null)
    {
        $agent = self::where('agent_id', $agentId);
        if ($agent) {
            $data = ['last_seen' => date('Y-m-d H:i:s'), 'last_heartbeat' => date('Y-m-d H:i:s')];
            if ($protocol) $data['protocol'] = $protocol;
            self::update($agent[0]['id'], $data);
            return $agent[0];
        }
        return null;
    }

    public static function updateUptime($agentId, $uptime)
    {
        $agent = self::where('agent_id', $agentId);
        if ($agent) {
            self::update($agent[0]['id'], ['uptime' => $uptime]);
        }
    }

    public static function getByGroup($groupId)
    {
        return self::where('group_id', $groupId);
    }

    public static function getActiveAgents()
    {
        $sql = "SELECT * FROM agents WHERE status = 'active' AND last_seen > datetime('now', '-5 minutes')";
        $stmt = self::query($sql);
        return $stmt->fetchAll();
    }
}
