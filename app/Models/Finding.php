<?php
namespace App\Models;

use App\Core\Model;

class Finding extends Model
{
    protected static string $table = 'redteam_findings';
    protected static array $fillable = ['scan_id', 'severity', 'title', 'description', 'cve_id', 'recommendation', 'proof'];
    protected static array $guarded = ['id'];

    public static function addFinding($scanId, $severity, $title, $desc, $cve = '', $rec = '', $proof = null)
    {
        return self::create([
            'scan_id' => $scanId,
            'severity' => $severity,
            'title' => $title,
            'description' => $desc,
            'cve_id' => $cve,
            'recommendation' => $rec,
            'proof' => $proof ? json_encode($proof) : null
        ]);
    }
}
