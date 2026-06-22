<?php
namespace App\Models;

use App\Core\Model;

class Finding extends Model
{
    protected static string $table = 'redteam_findings';
    protected static array $fillable = [
        'scan_id', 'severity', 'title', 'description', 'cve_id',
        'recommendation', 'proof', 'mitre_tactic', 'mitre_technique',
        'cvss_score', 'risk_score'
    ];
    protected static array $guarded = ['id'];

    public static function addFinding($scanId, $severity, $title, $desc, $cve = '',
                                      $rec = '', $proof = null, $mitreTactic = null,
                                      $mitreTechnique = null, $cvss = 0, $risk = 0)
    {
        return self::create([
            'scan_id' => $scanId,
            'severity' => $severity,
            'title' => $title,
            'description' => $desc,
            'cve_id' => $cve,
            'recommendation' => $rec,
            'proof' => $proof ? json_encode($proof) : null,
            'mitre_tactic' => $mitreTactic,
            'mitre_technique' => $mitreTechnique,
            'cvss_score' => $cvss,
            'risk_score' => $risk
        ]);
    }

    public static function getMitreMatrix()
    {
        $sql = "SELECT mitre_tactic, mitre_technique, COUNT(*) as count 
                FROM redteam_findings 
                WHERE mitre_tactic IS NOT NULL 
                GROUP BY mitre_tactic, mitre_technique";
        $stmt = self::query($sql);
        return $stmt->fetchAll();
    }
}
