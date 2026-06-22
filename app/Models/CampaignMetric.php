<?php
namespace App\Models;

use App\Core\Model;

class CampaignMetric extends Model
{
    protected static string $table = 'campaign_metrics';
    protected static array $fillable = ['campaign_id', 'metric_type', 'value'];

    public static function record($campaignId, $metricType, $value = 1)
    {
        return self::create([
            'campaign_id' => $campaignId,
            'metric_type' => $metricType,
            'value' => $value
        ]);
    }
}
