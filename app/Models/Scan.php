<?php
namespace App\Models;

use App\Core\Model;

class Scan extends Model
{
    protected static string $table = 'redteam_scans';
    protected static array $fillable = ['target_id', 'scan_type', 'parameters', 'status', 'result_summary'];
    protected static array $guarded = ['id'];

    public static function startScan($targetId, $type, $params = null)
    {
        return self::create([
            'target_id' => $targetId,
            'scan_type' => $type,
            'parameters' => $params ? json_encode($params) : null,
            'status' => 'running'
        ]);
    }

    public static function completeScan($scanId, $status, $summary)
    {
        self::update($scanId, [
            'status' => $status,
            'completed_at' => date('Y-m-d H:i:s'),
            'result_summary' => $summary
        ]);
        // Update target status
        $scan = self::find($scanId);
        if ($scan) {
            Target::updateStatus($scan['target_id'], $status === 'completed' ? 'completed' : 'failed');
        }
    }

    public static function getByTarget($targetId)
    {
        return self::where('target_id', $targetId);
    }
}
