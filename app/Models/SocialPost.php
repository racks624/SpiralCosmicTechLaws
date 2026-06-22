<?php
namespace App\Models;

use App\Core\Model;

class SocialPost extends Model
{
    protected static string $table = 'social_posts';
    protected static array $fillable = ['campaign_id', 'platform', 'content', 'image_url', 'scheduled_at', 'status', 'posted_at'];

    public static function getPending()
    {
        return self::where('status', 'pending');
    }

    public static function markPosted($id)
    {
        self::update($id, ['status' => 'posted', 'posted_at' => date('Y-m-d H:i:s')]);
    }

    public static function markFailed($id)
    {
        self::update($id, ['status' => 'failed']);
    }
}
