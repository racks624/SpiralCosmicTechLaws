<?php
namespace App\Models;

use App\Core\Model;

class EmailTemplate extends Model
{
    protected static string $table = 'email_templates';
    protected static array $fillable = ['campaign_id', 'name', 'subject', 'body', 'attachments', 'ab_group'];
}
