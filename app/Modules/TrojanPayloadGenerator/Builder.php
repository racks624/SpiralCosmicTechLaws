<?php
namespace App\Modules\TrojanPayloadGenerator;

class Builder
{
    public static function build($type, $lhost, $lport)
    {
        // Generate encrypted payload
        return base64_encode("PAYLOAD_{$type}_{$lhost}_{$lport}");
    }
}
