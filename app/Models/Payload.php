<?php
namespace App\Models;

use App\Core\Model;

class Payload extends Model
{
    protected static string $table = 'payloads';
    protected static array $fillable = ['os', 'lhost', 'lport', 'filename', 'filepath', 'content', 'status', 'downloads', 'notes'];
    protected static array $guarded = ['id'];
}
