<?php
namespace App\Models;

use App\Core\Model;

class CampaignTrack extends Model
{
    protected static string $table = 'campaign_tracks';
    protected static array $fillable = ['campaign_id', 'track_type', 'ip', 'user_agent', 'device_type', 'location', 'referrer', 'conversion_value'];

    public static function getByCampaign($campaignId, $type = null)
    {
        $sql = "SELECT * FROM campaign_tracks WHERE campaign_id = ?";
        if ($type) {
            $sql .= " AND track_type = ?";
            return self::query($sql, [$campaignId, $type])->fetchAll();
        }
        return self::query($sql, [$campaignId])->fetchAll();
    }

    public static function getStats($campaignId)
    {
        $sql = "SELECT track_type, COUNT(*) as count FROM campaign_tracks WHERE campaign_id = ? GROUP BY track_type";
        $stmt = self::query($sql, [$campaignId]);
        return $stmt->fetchAll();
    }
}
