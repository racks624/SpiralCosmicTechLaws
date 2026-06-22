<?php
namespace App\Models;

use App\Core\Model;

class Campaign extends Model
{
    protected static string $table = 'phishing_campaigns';
    protected static array $fillable = [
        'name', 'type', 'platform', 'template', 'targets', 'status',
        'clicks', 'emails_sent', 'sent_count', 'opened_count',
        'clicked_count', 'converted_count', 'from_name', 'from_email',
        'reply_to', 'scheduled_at', 'goal', 'updated_at'
    ];
    protected static array $guarded = ['id'];

    public static function createCampaign($data)
    {
        $data['targets'] = json_encode($data['targets'] ?? []);
        return self::create($data);
    }

    public static function updateCampaign($id, $data)
    {
        if (isset($data['targets']) && is_array($data['targets'])) {
            $data['targets'] = json_encode($data['targets']);
        }
        self::update($id, $data);
    }

    public static function incrementClicks($id)
    {
        $campaign = self::find($id);
        if ($campaign) {
            $clicks = ($campaign['clicks'] ?? 0) + 1;
            self::update($id, ['clicks' => $clicks, 'clicked_count' => ($campaign['clicked_count'] ?? 0) + 1]);
            CampaignMetric::record($id, 'click', 1);
        }
    }

    public static function incrementOpens($id)
    {
        $campaign = self::find($id);
        if ($campaign) {
            $opens = ($campaign['opened_count'] ?? 0) + 1;
            self::update($id, ['opened_count' => $opens]);
            CampaignMetric::record($id, 'open', 1);
        }
    }

    public static function incrementConversions($id, $value = 1)
    {
        $campaign = self::find($id);
        if ($campaign) {
            $conversions = ($campaign['converted_count'] ?? 0) + $value;
            self::update($id, ['converted_count' => $conversions]);
            CampaignMetric::record($id, 'conversion', $value);
        }
    }
}
