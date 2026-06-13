<?php
namespace App\Controllers;

use App\Core\Request;
use App\Models\Target;
use App\Models\Scan;
use App\Models\Finding;
use App\Modules\RedTeamOps\ScanEngine;

class RedTeamController extends Controller
{
    public function index()
    {
        $targets = Target::getWithStats();
        $this->view('redteam/index', ['targets' => $targets]);
    }

    public function addTarget(Request $request)
    {
        $data = $request->only(['name', 'target_type', 'target_value', 'description']);
        if (empty($data['name']) || empty($data['target_value'])) {
            $this->json(['error' => 'Missing required fields'], 400);
        }
        $data['status'] = 'pending';
        $id = Target::create($data);
        $this->json(['success' => true, 'id' => $id]);
    }

    public function runScan(Request $request)
    {
        $targetId = $request->input('target_id');
        $scanType = $request->input('scan_type', 'full');
        $target = Target::find($targetId);
        if (!$target) {
            $this->json(['error' => 'Target not found'], 404);
        }
        // Start scan record
        $scanId = Scan::startScan($targetId, $scanType);
        Target::updateStatus($targetId, 'scanning');

        // Perform actual scanning based on type
        $findings = [];
        if ($scanType === 'full' || $scanType === 'port') {
            $openPorts = ScanEngine::portScan($target['target_value']);
            foreach ($openPorts as $port) {
                $service = ScanEngine::serviceFingerprint($target['target_value'], $port);
                $findingTitle = "Open port: {$port}";
                $findingDesc = "Service: {$service['service']} - Banner: {$service['banner']}";
                $findings[] = Finding::addFinding($scanId, 'info', $findingTitle, $findingDesc, '', 'Consider closing unnecessary ports');
            }
        }
        if ($scanType === 'full' || $scanType === 'web') {
            if ($target['target_type'] === 'url' || in_array(80, $openPorts) || in_array(443, $openPorts)) {
                $protocol = (in_array(443, $openPorts)) ? 'https' : 'http';
                $url = ($target['target_type'] === 'url') ? $target['target_value'] : "{$protocol}://{$target['target_value']}";
                $webFindings = ScanEngine::webScan($url);
                foreach ($webFindings as $wf) {
                    $findings[] = Finding::addFinding($scanId, $wf['severity'], $wf['title'], $wf['description'], $wf['cve_id'] ?? '', $wf['recommendation'] ?? '');
                }
            }
        }
        Scan::completeScan($scanId, 'completed', "Found " . count($findings) . " issues");
        $this->json(['status' => 'scan_completed', 'findings_count' => count($findings)]);
    }

    public function getFindings(Request $request)
    {
        $targetId = $request->input('target_id');
        if (!$targetId) {
            $this->json(['error' => 'No target_id'], 400);
        }
        $sql = "SELECT f.*, s.scan_type FROM redteam_findings f JOIN redteam_scans s ON f.scan_id = s.id WHERE s.target_id = ? ORDER BY 
                CASE f.severity 
                    WHEN 'critical' THEN 1 
                    WHEN 'high' THEN 2 
                    WHEN 'medium' THEN 3 
                    WHEN 'low' THEN 4 
                    ELSE 5 END";
        $stmt = \App\Core\Model::query($sql, [$targetId]);
        $findings = $stmt->fetchAll();
        $this->json(['findings' => $findings]);
    }

    public function deleteTarget(Request $request)
    {
        $targetId = $request->input('target_id');
        if (!$targetId) $this->json(['error' => 'No target_id'], 400);
        Target::delete($targetId);
        $this->json(['success' => true]);
    }

    public function listScans()
    {
        $scans = Scan::all();
        $this->json(['scans' => $scans]);
    }
}
