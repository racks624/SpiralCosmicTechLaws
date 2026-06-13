<?php
namespace App\Models;

use App\Core\Model;

class Agent extends Model
{
    protected static string $table = 'agents';
    protected static array $fillable = ['agent_id', 'hostname', 'os', 'ip_address', 'last_seen', 'status'];
    protected static array $guarded = ['id'];

    public static function register($agentId, $hostname, $os, $ip)
    {
        // Check if exists
        $existing = self::where('agent_id', $agentId);
        if ($existing) {
            return self::update($existing[0]['id'], [
                'last_seen' => date('Y-m-d H:i:s'),
                'status' => 'active'
            ]);
        } else {
            return self::create([
                'agent_id' => $agentId,
                'hostname' => $hostname,
                'os' => $os,
                'ip_address' => $ip,
                'last_seen' => date('Y-m-d H:i:s'),
                'status' => 'active'
            ]);
        }
    }

    public static function heartbeat($agentId)
    {
        $agent = self::where('agent_id', $agentId);
        if ($agent) {
            self::update($agent[0]['id'], ['last_seen' => date('Y-m-d H:i:s')]);
            return true;
        }
        return false;
    }
}
