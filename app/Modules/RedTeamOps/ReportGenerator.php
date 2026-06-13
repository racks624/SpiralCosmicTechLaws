<?php
namespace App\Modules\RedTeamOps;

use App\Core\Model;

class ReportGenerator extends Model
{
    public static function generateHTML($campaignName, $targetId = null)
    {
        $targets = TargetManager::getAllTargets();
        if ($targetId) {
            $targets = [TargetManager::getTargetById($targetId)];
        }
        $findings = [];
        foreach ($targets as $t) {
            $sql = "SELECT fs.*, sc.scan_type FROM redteam_findings fs JOIN redteam_scans sc ON fs.scan_id = sc.id WHERE sc.target_id = ?";
            $stmt = self::query($sql, [$t['id']]);
            $f = $stmt->fetchAll();
            $findings[$t['name']] = $f;
        }
        
        $html = "<!DOCTYPE html><html><head><title>RedTeam Report - {$campaignName}</title>";
        $html .= "<style>body{font-family:monospace; background:#0a0a0a; color:#ccc;} .severity-high{color:#ff4444;} .severity-critical{color:#ff0000;font-weight:bold;} table{border-collapse:collapse;width:100%} th,td{border:1px solid #444;padding:8px;text-align:left}</style></head><body>";
        $html .= "<h1>RedTeam Assessment Report: {$campaignName}</h1>";
        $html .= "<h2>Generated: " . date('Y-m-d H:i:s') . "</h2>";
        foreach ($findings as $targetName => $targetFindings) {
            $html .= "<h3>Target: {$targetName}</h3>";
            if (empty($targetFindings)) {
                $html .= "<p>No findings.</p>";
                continue;
            }
            $html .= "<table><tr><th>Severity</th><th>Title</th><th>Description</th><th>CVE</th><th>Recommendation</th></tr>";
            foreach ($targetFindings as $f) {
                $severityClass = "severity-{$f['severity']}";
                $html .= "<tr><td class='{$severityClass}'>{$f['severity']}</td><td>{$f['title']}</td><td>{$f['description']}</td><td>{$f['cve_id']}</td><td>{$f['recommendation']}</td></tr>";
            }
            $html .= "</table>";
        }
        $html .= "</body></html>";
        
        // Save to database
        $sql = "INSERT INTO redteam_reports (campaign_name, report_data, format) VALUES (?, ?, 'html')";
        self::query($sql, [$campaignName, $html]);
        return $html;
    }
    
    public static function generateJSON($campaignName)
    {
        // Implementation similar to HTML but returns JSON
        $targets = TargetManager::getAllTargets();
        $data = ['campaign' => $campaignName, 'generated' => date('c'), 'targets' => []];
        foreach ($targets as $t) {
            $sql = "SELECT fs.* FROM redteam_findings fs JOIN redteam_scans sc ON fs.scan_id = sc.id WHERE sc.target_id = ?";
            $stmt = self::query($sql, [$t['id']]);
            $data['targets'][] = ['target' => $t['name'], 'findings' => $stmt->fetchAll()];
        }
        $json = json_encode($data, JSON_PRETTY_PRINT);
        $sql = "INSERT INTO redteam_reports (campaign_name, report_data, format) VALUES (?, ?, 'json')";
        self::query($sql, [$campaignName, $json]);
        return $json;
    }
}
