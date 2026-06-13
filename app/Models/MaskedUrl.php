<?php
namespace App\Models;

use App\Core\Model;

class MaskedUrl extends Model
{
    protected static string $table = 'masked_urls';
    protected static array $fillable = ['original_url', 'masked_url', 'token', 'clicks'];
    protected static array $guarded = ['id'];

    public static function incrementClicks($id)
    {
        $url = self::find($id);
        if ($url) {
            self::update($id, ['clicks' => $url['clicks'] + 1]);
        }
    }
}
