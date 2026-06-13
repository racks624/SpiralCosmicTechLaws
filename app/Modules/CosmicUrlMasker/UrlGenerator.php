<?php
namespace App\Modules\CosmicUrlMasker;

class UrlGenerator
{
    public static function mask($original, $domain)
    {
        return "https://{$domain}/" . bin2hex(random_bytes(4));
    }
}
