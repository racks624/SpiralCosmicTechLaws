<?php
namespace App\Controllers;

use App\Core\Request;
use App\Models\Agent;
use App\Models\Task;
use App\Models\AgentGroup;
use App\Models\ScheduledTask;
use App\Models\C2AuditLog;
use App\Services\RedisService;
use App\Services\TaskScheduler;

class C2Controller extends Controller
{
    public function index()
    {
        $groups = AgentGroup::all();
        $agents = Agent::all();
        $this->view('c2/index', ['groups' => $groups, 'agents' => $agents]);
    }

    // ---- Agent Management ----
    public function listAgents()
    {
        $agents = Agent::all();
        $this->json(['agents' => $agents]);
    }

    public function registerAgent(Request $request)
    {
        $data = $request->only(['agent_id', 'hostname', 'os', 'ip_address', 'protocol', 'description', 'tags', 'group_id']);
        if (empty($data['agent_id'])) {
            $this->json(['error' => 'agent_id required'], 400);
        }
        $agent = Agent::register(
            $data['agent_id'],
            $data['hostname'] ?? 'unknown',
            $data['os'] ?? 'unknown',
            $data['ip_address'] ?? $_SERVER['REMOTE_ADDR'],
            $data['protocol'] ?? 'https'
        );
        if (isset($data['group_id'])) {
            Agent::update($agent['id'], ['group_id' => $data['group_id']]);
        }
        C2AuditLog::log('operator', 'register', 'agent', $agent['id'], ['agent_id' => $data['agent_id']]);
        $this->json(['status' => 'registered', 'agent' => $agent]);
    }

    public function editAgent(Request $request)
    {
        $id = $request->input('id');
        $data = $request->only(['hostname', 'os', 'ip_address', 'status', 'description', 'tags', 'group_id', 'jitter', 'protocol']);
        if (!$id) $this->json(['error' => 'ID required'], 400);
        $agent = Agent::find($id);
        if (!$agent) $this->json(['error' => 'Agent not found'], 404);
        Agent::update($id, $data);
        C2AuditLog::log('operator', 'edit', 'agent', $id, ['data' => $data]);
        $this->json(['success' => true]);
    }

    public function viewAgent(Request $request)
    {
        $id = $request->input('id');
        if (!$id) $this->json(['error' => 'ID required'], 400);
        $agent = Agent::find($id);
        if (!$agent) $this->json(['error' => 'Agent not found'], 404);
        $tasks = Task::where('agent_id', $id);
        $scheduled = ScheduledTask::where('agent_id', $id);
        $this->json(['agent' => $agent, 'tasks' => $tasks, 'scheduled' => $scheduled]);
    }

    public function deleteAgent(Request $request)
    {
        $id = $request->input('id');
        if (!$id) $this->json(['error' => 'ID required'], 400);
        C2AuditLog::log('operator', 'delete', 'agent', $id);
        Agent::delete($id);
        $this->json(['success' => true]);
    }

    public function shareAgent(Request $request)
    {
        $id = $request->input('id');
        if (!$id) $this->json(['error' => 'ID required'], 400);
        $agent = Agent::find($id);
        if (!$agent) $this->json(['error' => 'Agent not found'], 404);
        $token = bin2hex(random_bytes(16));
        $shareUrl = getenv('APP_URL') . '/c2/share/' . $token;
        C2AuditLog::log('operator', 'share', 'agent', $id);
        $this->json(['share_url' => $shareUrl]);
    }

    // ---- Heartbeat & Tasks ----
    public function heartbeat(Request $request)
    {
        $agentId = $request->input('agent_id');
        $protocol = $request->input('protocol');
        $uptime = $request->input('uptime');
        if (!$agentId) $this->json(['error' => 'agent_id required'], 400);
        $agent = Agent::heartbeat($agentId, $protocol);
        if ($uptime) Agent::updateUptime($agentId, $uptime);
        if ($agent) {
            $tasks = RedisService::getTasksForAgent($agent['id']);
            if (empty($tasks)) {
                $tasks = Task::getPendingForAgent($agent['id']);
            }
            $this->json(['status' => 'ok', 'tasks' => $tasks]);
        } else {
            $this->json(['error' => 'Agent not found'], 404);
        }
    }

    public function sendCommand(Request $request)
    {
        $agentId = $request->input('agent_id');
        $command = $request->input('command');
        $priority = $request->input('priority', 0);
        if (!$agentId || !$command) {
            $this->json(['error' => 'agent_id and command required'], 400);
        }
        $agent = Agent::where('agent_id', $agentId);
        if (!$agent) {
            $this->json(['error' => 'Agent not found'], 404);
        }
        $taskId = RedisService::pushTask($agent[0]['id'], $command);
        Task::queueCommand($agent[0]['id'], $command);
        C2AuditLog::log('operator', 'send_command', 'agent', $agent[0]['id'], ['command' => $command]);
        $this->json(['status' => 'queued', 'task_id' => $taskId]);
    }

    public function pushCommand(Request $request)
    {
        $agentId = $request->input('agent_id');
        $command = $request->input('command');
        if ($agentId && $command) {
            RedisService::publishCommand($agentId, $command);
            C2AuditLog::log('operator', 'push_websocket', 'agent', null, ['agent_id' => $agentId, 'command' => $command]);
            $this->json(['status' => 'published']);
        } else {
            $this->json(['error' => 'Missing parameters'], 400);
        }
    }

    public function taskResult(Request $request)
    {
        $taskId = $request->input('task_id');
        $output = $request->input('output');
        if (!$taskId) $this->json(['error' => 'task_id required'], 400);
        $task = Task::find($taskId);
        if ($task) {
            Task::update($taskId, ['status' => 'completed', 'output' => $output, 'executed_at' => date('Y-m-d H:i:s')]);
            C2AuditLog::log('system', 'task_result', 'task', $taskId);
            $this->json(['status' => 'updated']);
        } else {
            $this->json(['error' => 'Task not found'], 404);
        }
    }

    // ---- Scheduling ----
    public function scheduleCommand(Request $request)
    {
        $agentId = $request->input('agent_id');
        $command = $request->input('command');
        $scheduledAt = $request->input('scheduled_at');
        $priority = $request->input('priority', 0);
        if (!$agentId || !$command || !$scheduledAt) {
            $this->json(['error' => 'agent_id, command, and scheduled_at required'], 400);
        }
        $agent = Agent::find($agentId);
        if (!$agent) $this->json(['error' => 'Agent not found'], 404);
        $taskId = ScheduledTask::scheduleCommand($agentId, $command, $scheduledAt, $priority);
        C2AuditLog::log('operator', 'schedule', 'agent', $agentId, ['command' => $command, 'scheduled_at' => $scheduledAt]);
        $this->json(['status' => 'scheduled', 'task_id' => $taskId]);
    }

    public function listScheduled()
    {
        $tasks = ScheduledTask::all();
        $this->json(['scheduled_tasks' => $tasks]);
    }

    public function cancelScheduled(Request $request)
    {
        $id = $request->input('id');
        if (!$id) $this->json(['error' => 'ID required'], 400);
        ScheduledTask::delete($id);
        C2AuditLog::log('operator', 'cancel_scheduled', 'task', $id);
        $this->json(['success' => true]);
    }

    // ---- Groups ----
    public function listGroups()
    {
        $groups = AgentGroup::all();
        $this->json(['groups' => $groups]);
    }

    public function createGroup(Request $request)
    {
        $name = $request->input('name');
        $description = $request->input('description');
        if (!$name) $this->json(['error' => 'Name required'], 400);
        $id = AgentGroup::create(['name' => $name, 'description' => $description]);
        C2AuditLog::log('operator', 'create_group', 'group', $id);
        $this->json(['status' => 'created', 'id' => $id]);
    }

    public function deleteGroup(Request $request)
    {
        $id = $request->input('id');
        if (!$id) $this->json(['error' => 'ID required'], 400);
        // Reset agents in group
        $agents = Agent::where('group_id', $id);
        foreach ($agents as $agent) {
            Agent::update($agent['id'], ['group_id' => null]);
        }
        AgentGroup::delete($id);
        C2AuditLog::log('operator', 'delete_group', 'group', $id);
        $this->json(['success' => true]);
    }

    // ---- Analytics ----
    public function analytics()
    {
        $totalAgents = count(Agent::all());
        $activeAgents = count(Agent::where('status', 'active'));
        $pendingTasks = count(Task::where('status', 'pending'));
        $scheduledTasks = count(ScheduledTask::where('status', 'pending'));
        $groups = AgentGroup::all();
        $this->json([
            'total_agents' => $totalAgents,
            'active_agents' => $activeAgents,
            'pending_tasks' => $pendingTasks,
            'scheduled_tasks' => $scheduledTasks,
            'groups' => $groups
        ]);
    }

    // ---- System: process scheduler (called from cron) ----
    public function processScheduler()
    {
        TaskScheduler::processPendingTasks();
        $this->json(['status' => 'processed']);
    }
}
