<?php
namespace App\Services;

use App\Models\VirtualMachine;

class VirtualLabService
{
    public static function spawnMachine($os, $config = [])
    {
        // For demo, we create a record and simulate Docker run
        $machineId = uniqid('vm_');
        $data = [
            'machine_id' => $machineId,
            'os' => $os,
            'status' => 'running',
            'ip' => '172.17.0.' . rand(2, 254),
            'config' => json_encode($config)
        ];
        $id = VirtualMachine::create($data);
        // In production, we would call Docker API
        return $id;
    }

    public static function stopMachine($machineId)
    {
        $vm = VirtualMachine::where('machine_id', $machineId);
        if ($vm) {
            VirtualMachine::update($vm[0]['id'], ['status' => 'stopped']);
            // Call Docker stop
            return true;
        }
        return false;
    }

    public static function deployPayload($machineId, $payloadPath)
    {
        // Simulate copying payload to VM
        // In production, use SCP or volume mount
        return true;
    }

    public static function runCommand($machineId, $command)
    {
        // Simulate executing command on VM
        // In production, use Docker exec
        return ['output' => "Command executed: $command"];
    }

    public static function testUrl($machineId, $url)
    {
        // Simulate opening browser in VM
        return ['status' => 'visited'];
    }
}
