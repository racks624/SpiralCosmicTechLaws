<?php
namespace App\Services;

use App\Models\Campaign;
use App\Models\EmailTemplate;
use App\Models\SocialPost;
use App\Models\SmsLog;
use App\Models\CampaignMetric;
use App\Models\CampaignTrack;

class PhishingService
{
    // ---- Campaign Management ----
    public static function createCampaign($data)
    {
        return Campaign::createCampaign($data);
    }

    public static function updateCampaign($id, $data)
    {
        Campaign::updateCampaign($id, $data);
    }

    // ---- Email Templates ----
    public static function createTemplate($data)
    {
        return EmailTemplate::create($data);
    }

    public static function updateTemplate($id, $data)
    {
        EmailTemplate::update($id, $data);
    }

    public static function deleteTemplate($id)
    {
        EmailTemplate::delete($id);
    }

    // ---- Email Sending (using EmailService) ----
    public static function sendEmails($campaignId, $smtpConfig = null)
    {
        $campaign = Campaign::find($campaignId);
        if (!$campaign) return ['error' => 'Campaign not found'];
        $targets = json_decode($campaign['targets'], true);
        if (empty($targets)) return ['error' => 'No targets'];

        $templates = EmailTemplate::where('campaign_id', $campaignId);
        if (empty($templates)) {
            $templates = [['subject' => $campaign['name'], 'body' => $campaign['template'] ?? '']];
        }

        $emailService = new EmailService();
        $sent = 0;
        $errors = [];
        foreach ($targets as $index => $email) {
            $templateIdx = $index % count($templates);
            $template = $templates[$templateIdx];
            $subject = $template['subject'] ?? $campaign['name'];
            $body = $template['body'] ?? "Test email from campaign {$campaign['name']}";

            // Add tracking pixel and click tracking
            $trackUrl = getenv('APP_URL') . "/phishing/track/open?campaign_id={$campaignId}";
            $body .= "<img src='{$trackUrl}' width='1' height='1' />";
            $clickUrl = getenv('APP_URL') . "/phishing/track/click?campaign_id={$campaignId}&url=";
            $body = preg_replace_callback('/<a href="([^"]+)"/', function($matches) use ($clickUrl) {
                return '<a href="' . $clickUrl . urlencode($matches[1]) . '"';
            }, $body);

            try {
                $emailService->send(
                    $email,
                    $subject,
                    $body,
                    $campaign['from_email'] ?? '',
                    $campaign['from_name'] ?? ''
                );
                $sent++;
            } catch (\Exception $e) {
                $errors[] = "Failed to send to $email: " . $e->getMessage();
            }
        }

        Campaign::update($campaignId, [
            'sent_count' => ($campaign['sent_count'] ?? 0) + $sent,
            'emails_sent' => ($campaign['emails_sent'] ?? 0) + $sent
        ]);
        CampaignMetric::record($campaignId, 'sent', $sent);
        return ['status' => 'sent', 'count' => $sent, 'errors' => $errors];
    }

    // ---- Social Media ----
    public static function postToSocial($campaignId, $platform, $content, $imageUrl = null, $scheduledAt = null)
    {
        $data = [
            'campaign_id' => $campaignId,
            'platform' => $platform,
            'content' => $content,
            'image_url' => $imageUrl,
            'status' => $scheduledAt ? 'pending' : 'queued'
        ];
        if ($scheduledAt) $data['scheduled_at'] = $scheduledAt;
        $id = SocialPost::create($data);
        if (!$scheduledAt) {
            $posted = self::executeSocialPost($platform, $content, $imageUrl);
            if ($posted) {
                SocialPost::markPosted($id);
                CampaignMetric::record($campaignId, 'social_post', 1);
            } else {
                SocialPost::markFailed($id);
            }
        }
        return ['status' => $scheduledAt ? 'scheduled' : 'queued', 'id' => $id];
    }

    private static function executeSocialPost($platform, $content, $image)
    {
        // In production, integrate with actual APIs (Facebook, Twitter, etc.)
        return true;
    }

    // ---- SMS Sending (using SmsService) ----
    public static function sendSms($campaignId, $phoneNumbers, $message)
    {
        $campaign = Campaign::find($campaignId);
        if (!$campaign) return ['error' => 'Campaign not found'];
        $smsService = new SmsService();
        $sent = 0;
        $errors = [];
        foreach ($phoneNumbers as $phone) {
            if (empty($phone)) continue;
            try {
                $smsService->send($phone, $message);
                SmsLog::create([
                    'campaign_id' => $campaignId,
                    'phone' => $phone,
                    'message' => $message,
                    'status' => 'sent',
                    'sent_at' => date('Y-m-d H:i:s')
                ]);
                $sent++;
            } catch (\Exception $e) {
                $errors[] = "Failed to send SMS to $phone: " . $e->getMessage();
                SmsLog::create([
                    'campaign_id' => $campaignId,
                    'phone' => $phone,
                    'message' => $message,
                    'status' => 'failed',
                ]);
            }
        }
        Campaign::update($campaignId, ['sent_count' => ($campaign['sent_count'] ?? 0) + $sent]);
        CampaignMetric::record($campaignId, 'sms_sent', $sent);
        return ['status' => 'sent', 'count' => $sent, 'errors' => $errors];
    }

    // ---- Tracking ----
    public static function trackOpen($campaignId, $ip, $userAgent, $referrer = null)
    {
        $device = self::detectDevice($userAgent);
        $location = self::geoip($ip);
        Campaign::incrementOpens($campaignId);
        CampaignMetric::record($campaignId, 'open', 1);
        return CampaignTrack::create([
            'campaign_id' => $campaignId,
            'track_type' => 'open',
            'ip' => $ip,
            'user_agent' => $userAgent,
            'device_type' => $device,
            'location' => $location,
            'referrer' => $referrer
        ]);
    }

    public static function trackClick($campaignId, $ip, $userAgent, $referrer = null)
    {
        $device = self::detectDevice($userAgent);
        $location = self::geoip($ip);
        Campaign::incrementClicks($campaignId);
        CampaignMetric::record($campaignId, 'click', 1);
        return CampaignTrack::create([
            'campaign_id' => $campaignId,
            'track_type' => 'click',
            'ip' => $ip,
            'user_agent' => $userAgent,
            'device_type' => $device,
            'location' => $location,
            'referrer' => $referrer
        ]);
    }

    public static function trackConversion($campaignId, $value = 1)
    {
        Campaign::incrementConversions($campaignId, $value);
        CampaignMetric::record($campaignId, 'conversion', $value);
        return CampaignTrack::create([
            'campaign_id' => $campaignId,
            'track_type' => 'convert',
            'conversion_value' => $value,
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        ]);
    }

    private static function detectDevice($userAgent)
    {
        if (strpos($userAgent, 'Mobile') !== false) return 'mobile';
        if (strpos($userAgent, 'Tablet') !== false) return 'tablet';
        return 'desktop';
    }

    private static function geoip($ip)
    {
        $data = @file_get_contents("http://ip-api.com/json/{$ip}");
        if ($data) {
            $json = json_decode($data, true);
            if (isset($json['city'], $json['countryCode'])) {
                return $json['city'] . ', ' . $json['countryCode'];
            }
        }
        return 'Unknown';
    }

    // ---- Analytics ----
    public static function getCampaignStats($campaignId)
    {
        $campaign = Campaign::find($campaignId);
        if (!$campaign) return null;
        $metrics = CampaignMetric::where('campaign_id', $campaignId);
        $tracks = CampaignTrack::where('campaign_id', $campaignId);
        $templates = EmailTemplate::where('campaign_id', $campaignId);
        return [
            'campaign' => $campaign,
            'metrics' => $metrics,
            'tracks' => $tracks,
            'templates' => $templates,
            'open_rate' => $campaign['sent_count'] > 0 ? round(($campaign['opened_count'] / $campaign['sent_count']) * 100, 2) : 0,
            'click_rate' => $campaign['sent_count'] > 0 ? round(($campaign['clicked_count'] / $campaign['sent_count']) * 100, 2) : 0,
            'conversion_rate' => $campaign['sent_count'] > 0 ? round(($campaign['converted_count'] / $campaign['sent_count']) * 100, 2) : 0,
        ];
    }

    public static function getOverallStats()
    {
        $campaigns = Campaign::all();
        $total = count($campaigns);
        $sent = array_sum(array_column($campaigns, 'sent_count'));
        $opens = array_sum(array_column($campaigns, 'opened_count'));
        $clicks = array_sum(array_column($campaigns, 'clicked_count'));
        $conversions = array_sum(array_column($campaigns, 'converted_count'));
        return [
            'total_campaigns' => $total,
            'total_sent' => $sent,
            'total_opens' => $opens,
            'total_clicks' => $clicks,
            'total_conversions' => $conversions,
            'overall_open_rate' => $sent > 0 ? round(($opens / $sent) * 100, 2) : 0,
            'overall_click_rate' => $sent > 0 ? round(($clicks / $sent) * 100, 2) : 0,
            'overall_conversion_rate' => $sent > 0 ? round(($conversions / $sent) * 100, 2) : 0,
        ];
    }
}
