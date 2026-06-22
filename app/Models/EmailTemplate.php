<?php
namespace App\Models;

use App\Core\Model;

class EmailTemplate extends Model
{
    protected static string $table = 'email_templates';
    protected static array $fillable = ['campaign_id', 'name', 'subject', 'body', 'attachments', 'ab_group'];

    public static function getTemplatesForCampaign($campaignId)
    {
        return self::where('campaign_id', $campaignId);
    }

    public static function getABGroups($campaignId)
    {
        $sql = "SELECT DISTINCT ab_group FROM email_templates WHERE campaign_id = ?";
        $stmt = self::query($sql, [$campaignId]);
        return $stmt->fetchAll();
    }
}
