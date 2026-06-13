<?php
namespace App\Models;

use App\Core\Model;

class Campaign extends Model
{
    protected static string $table = 'phishing_campaigns';
    protected static array $fillable = ['name', 'status', 'clicks', 'emails_sent'];
    protected static array $guarded = ['id'];

    public static function createCampaign($name)
    {
        return self::create([
            'name' => $name,
            'status' => 'active',
            'clicks' => 0,
            'emails_sent' => 0
        ]);
    }

    public static function incrementClicks($id)
    {
        $campaign = self::find($id);
        if ($campaign) {
            $clicks = $campaign['clicks'] + 1;
            self::update($id, ['clicks' => $clicks]);
        }
    }
}
