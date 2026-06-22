<?php
namespace App\Models;

use App\Core\Model;

class MaskedUrl extends Model
{
    protected static string $table = 'masked_urls';
    protected static array $fillable = ['original_url', 'masked_url', 'token', 'clicks', 'campaign_id', 'agent_id', 'last_click', 'share_token'];
    protected static array $guarded = ['id'];

    public static function incrementClicks($id)
    {
        $url = self::find($id);
        if ($url) {
            $clicks = ($url['clicks'] ?? 0) + 1;
            self::update($id, ['clicks' => $clicks, 'last_click' => date('Y-m-d H:i:s')]);
        }
    }
}
