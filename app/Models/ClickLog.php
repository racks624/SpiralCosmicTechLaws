<?php
namespace App\Models;

use App\Core\Model;

class ClickLog extends Model
{
    protected static string $table = 'click_logs';
    protected static array $fillable = ['campaign_id', 'data'];
}
