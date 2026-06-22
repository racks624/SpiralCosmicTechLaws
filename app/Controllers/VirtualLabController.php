<?php
namespace App\Controllers;

use App\Core\Request;
use App\Models\VirtualMachine;
use App\Services\VirtualLabService;

class VirtualLabController extends Controller
{
    public function index()
    {
        $machines = VirtualMachine::all();
        $this->view('virtuallab/index', ['machines' => $machines]);
    }

    public function spawnMachine(Request $request)
    {
        $os = $request->input('os', 'ubuntu');
        $config = $request->input('config', []);
        $id = VirtualLabService::spawnMachine($os, $config);
        $this->json(['status' => 'spawning', 'machine_id' => $id]);
    }

    public function editVM(Request $request)
    {
        $id = $request->input('id');
        $data = $request->only(['os', 'status', 'config']);
        if (!$id) $this->json(['error' => 'ID required'], 400);
        $vm = VirtualMachine::find($id);
        if (!$vm) $this->json(['error' => 'VM not found'], 404);
        if (isset($data['config']) && is_array($data['config'])) {
            $data['config'] = json_encode($data['config']);
        }
        VirtualMachine::update($id, $data);
        $this->json(['success' => true]);
    }

    public function viewVM(Request $request)
    {
        $id = $request->input('id');
        if (!$id) $this->json(['error' => 'ID required'], 400);
        $vm = VirtualMachine::find($id);
        if (!$vm) $this->json(['error' => 'VM not found'], 404);
        $this->json(['machine' => $vm]);
    }

    public function stopMachine(Request $request)
    {
        $machineId = $request->input('machine_id');
        if ($machineId) {
            VirtualLabService::stopMachine($machineId);
            $this->json(['status' => 'stopped']);
        }
        $this->json(['error' => 'Machine ID required'], 400);
    }

    public function deleteVM(Request $request)
    {
        $id = $request->input('id');
        if (!$id) $this->json(['error' => 'ID required'], 400);
        VirtualMachine::delete($id);
        $this->json(['success' => true]);
    }

    public function deployPayload(Request $request)
    {
        $machineId = $request->input('machine_id');
        $payloadId = $request->input('payload_id');
        if (!$machineId || !$payloadId) {
            $this->json(['error' => 'Missing parameters'], 400);
        }
        $payload = \App\Models\Payload::find($payloadId);
        if (!$payload) {
            $this->json(['error' => 'Payload not found'], 404);
        }
        VirtualLabService::deployPayload($machineId, $payload['filepath']);
        $this->json(['status' => 'deployed']);
    }

    public function runCommand(Request $request)
    {
        $machineId = $request->input('machine_id');
        $command = $request->input('command');
        if (!$machineId || !$command) {
            $this->json(['error' => 'Missing parameters'], 400);
        }
        $result = VirtualLabService::runCommand($machineId, $command);
        $this->json(['output' => $result['output']]);
    }

    public function list()
    {
        $machines = VirtualMachine::all();
        $this->json(['machines' => $machines]);
    }
}
