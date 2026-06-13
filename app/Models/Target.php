<?php
namespace App\Models;

use App\Core\Model;

class Target extends Model
{
    protected static string $table = 'redteam_targets';
    protected static array $fillable = ['name', 'target_type', 'target_value', 'description', 'status'];
    protected static array $guarded = ['id'];

    public static function getWithStats()
    {
        $sql = "SELECT t.*, 
                (SELECT COUNT(*) FROM redteam_findings f JOIN redteam_scans s ON f.scan_id = s.id WHERE s.target_id = t.id) as findings_count
                FROM redteam_targets t ORDER BY t.created_at DESC";
        $stmt = self::query($sql);
        return $stmt->fetchAll();
    }

    public static function updateStatus($id, $status)
    {
        self::update($id, ['status' => $status]);
    }
}
