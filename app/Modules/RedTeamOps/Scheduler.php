<?php
namespace App\Modules\RedTeamOps;

use App\Core\Model;

class Scheduler extends Model
{
    // Simple cron‑like job queuing (uses database)
    public static function scheduleScan($targetId, $scanType, $scheduledTime)
    {
        $sql = "INSERT INTO redteam_scans (target_id, scan_type, status, started_at) VALUES (?, ?, 'queued', ?)";
        self::query($sql, [$targetId, $scanType, $scheduledTime]);
        return self::$db->lastInsertId();
    }
    
    public static function runPendingScans()
    {
        $sql = "SELECT * FROM redteam_scans WHERE status = 'queued' AND started_at <= NOW()";
        $stmt = self::query($sql);
        $scans = $stmt->fetchAll();
        foreach ($scans as $scan) {
            $target = TargetManager::getTargetById($scan['target_id']);
            if ($scan['scan_type'] == 'full') {
                ScanEngine::fullScan($target['id'], $target['target_value'], $target['target_type']);
            } else {
                // other scan types
                ScanEngine::portScan($target['target_value']);
                // update scan
            }
        }
        return count($scans);
    }
}
