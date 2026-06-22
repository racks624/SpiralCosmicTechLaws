<?php
namespace App\Models;

use App\Core\Model;

class C2AuditLog extends Model
{
    protected static string $table = 'c2_audit_log';
    protected static array $fillable = ['operator', 'action', 'target_type', 'target_id', 'details', 'ip'];
    protected static array $guarded = ['id'];

    public static function log($operator, $action, $targetType = null, $targetId = null, $details = null)
    {
        return self::create([
            'operator' => $operator,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'details' => $details ? json_encode($details) : null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }
}
