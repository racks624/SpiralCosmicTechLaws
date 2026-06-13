<?php
namespace App\Modules\RedTeamOps;

use App\Core\Model;

class TargetManager extends Model
{
    public static function addTarget($name, $type, $value, $desc = '')
    {
        $sql = "INSERT INTO redteam_targets (name, target_type, target_value, description) VALUES (?, ?, ?, ?)";
        parent::query($sql, [$name, $type, $value, $desc]);
        return parent::lastInsertId();
    }

    public static function getAllTargets()
    {
        $sql = "SELECT * FROM redteam_targets ORDER BY created_at DESC";
        $stmt = parent::query($sql);
        return $stmt->fetchAll();
    }

    public static function getTargetById($id)
    {
        $sql = "SELECT * FROM redteam_targets WHERE id = ?";
        $stmt = parent::query($sql, [$id]);
        return $stmt->fetch();
    }

    public static function updateStatus($id, $status)
    {
        $sql = "UPDATE redteam_targets SET status = ? WHERE id = ?";
        parent::query($sql, [$status, $id]);
    }
}
