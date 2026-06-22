<?php
namespace App\Models;

use App\Core\Model;

class SmsLog extends Model
{
    protected static string $table = 'sms_logs';
    protected static array $fillable = ['campaign_id', 'phone', 'message', 'status', 'sent_at'];
}
