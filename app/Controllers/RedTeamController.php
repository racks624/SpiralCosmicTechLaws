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

    public function editTarget(Request $request)
    {
        $id = $request->input('id');
        $data = $request->only(['name', 'target_type', 'target_value', 'description', 'status']);
        if (!$id) $this->json(['error' => 'ID required'], 400);
        $target = Target::find($id);
        if (!$target) $this->json(['error' => 'Target not found'], 404);
        Target::update($id, $data);
        $this->json(['success' => true]);
    }

    public function viewTarget(Request $request)
    {
        $id = $request->input('id');
        if (!$id) $this->json(['error' => 'ID required'], 400);
        $target = Target::find($id);
        if (!$target) $this->json(['error' => 'Target not found'], 404);
        // Get scans and findings
        $scans = Scan::where('target_id', $id);
        $findings = Finding::where('scan_id', $scans[0]['id'] ?? 0);
        $this->json(['target' => $target, 'scans' => $scans, 'findings' => $findings]);
    }

    public function uploadTargets(Request $request)
    {
        $file = $_FILES['csv_file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->json(['error' => 'Upload failed'], 400);
        }
        $handle = fopen($file['tmp_name'], 'r');
        $headers = fgetcsv($handle);
        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($headers, $row);
            if (isset($data['name']) && isset($data['target_value'])) {
                Target::create([
                    'name' => $data['name'],
                    'target_type' => $data['target_type'] ?? 'ip',
                    'target_value' => $data['target_value'],
                    'description' => $data['description'] ?? '',
                    'status' => 'pending'
                ]);
                $count++;
            }
        }
        fclose($handle);
        $this->json(['success' => true, 'imported' => $count]);
    }

    public function runScan(Request $request)
    {
        $targetId = $request->input('target_id');
        $scanType = $request->input('scan_type', 'full');
        $target = Target::find($targetId);
        if (!$target) $this->json(['error' => 'Target not found'], 404);
        if ($scanType === 'full') {
            $scanId = ScanEngine::fullScanWithMitre($targetId, $target['target_value'], $target['target_type']);
            $this->json(['status' => 'scan_started', 'scan_id' => $scanId]);
        } else {
            $scanId = Scan::startScan($targetId, $scanType);
            Target::updateStatus($targetId, 'scanning');
            $openPorts = ScanEngine::portScan($target['target_value']);
            foreach ($openPorts as $port) {
                $fingerprint = ScanEngine::serviceFingerprint($target['target_value'], $port);
                Finding::addFinding($scanId, 'info', "Open port: {$port}",
                    "Service: {$fingerprint['service']} - Banner: {$fingerprint['banner']}");
            }
            Scan::completeScan($scanId, 'completed', "Found " . count($openPorts) . " open ports");
            $this->json(['status' => 'scan_completed', 'open_ports' => $openPorts]);
        }
    }

    public function getFindings(Request $request)
    {
        $targetId = $request->input('target_id');
        if (!$targetId) $this->json(['error' => 'No target_id'], 400);
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

    public function exportFindings(Request $request)
    {
        $targetId = $request->input('target_id');
        if (!$targetId) $this->json(['error' => 'Target ID required'], 400);
        $findings = Finding::where('scan_id', $targetId); // simplified
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="findings.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Severity', 'Title', 'Description', 'CVE', 'MITRE Tactic', 'Risk Score']);
        foreach ($findings as $f) {
            fputcsv($out, [$f['severity'], $f['title'], $f['description'], $f['cve_id'], $f['mitre_tactic'], $f['risk_score']]);
        }
        fclose($out);
        exit;
    }
}
