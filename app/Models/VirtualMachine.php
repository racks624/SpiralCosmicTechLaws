<?php
namespace App\Models;

use App\Core\Model;

class VirtualMachine extends Model
{
    protected static string $table = 'virtual_machines';
    protected static array $fillable = ['machine_id', 'os', 'status', 'ip', 'config'];
    protected static array $guarded = ['id'];
}
