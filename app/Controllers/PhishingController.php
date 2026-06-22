<?php
namespace App\Controllers;

use App\Core\Request;
use App\Models\Campaign;
use App\Models\EmailTemplate;
use App\Models\SocialPost;
use App\Models\SmsLog;
use App\Models\CampaignMetric;
use App\Models\CampaignTrack;
use App\Services\PhishingService;

class PhishingController extends Controller
{
    // ---- Dashboard ----
    public function index()
    {
        $this->view('phishing/index');
    }

    // ---- Campaign CRUD (with validation & CSRF) ----
    public function createCampaign(Request $request)
    {
        $this->validateCsrf($request);
        $data = $request->only([
            'name', 'type', 'platform', 'template', 'targets',
            'scheduled_at', 'from_name', 'from_email', 'reply_to'
        ]);
        $errors = $this->validateCampaignData($data);
        if (!empty($errors)) {
            $this->json(['error' => 'Validation failed', 'details' => $errors], 400);
        }
        $data['status'] = 'draft';
        $id = PhishingService::createCampaign($data);
        $this->json(['status' => 'created', 'id' => $id]);
    }

    public function editCampaign(Request $request)
    {
        $this->validateCsrf($request);
        $id = $request->input('id');
        if (!$id) $this->json(['error' => 'ID required'], 400);
        $campaign = Campaign::find($id);
        if (!$campaign) $this->json(['error' => 'Campaign not found'], 404);
        $data = $request->only([
            'name', 'type', 'platform', 'template', 'targets', 'status',
            'scheduled_at', 'from_name', 'from_email', 'reply_to'
        ]);
        $errors = $this->validateCampaignData($data, true);
        if (!empty($errors)) {
            $this->json(['error' => 'Validation failed', 'details' => $errors], 400);
        }
        PhishingService::updateCampaign($id, $data);
        $this->json(['success' => true]);
    }

    public function viewCampaign(Request $request)
    {
        $id = $request->input('id');
        if (!$id) $this->json(['error' => 'ID required'], 400);
        $stats = PhishingService::getCampaignStats($id);
        if (!$stats) $this->json(['error' => 'Campaign not found'], 404);
        $this->json($stats);
    }

    public function listCampaigns(Request $request)
    {
        $page = max(1, (int)$request->input('page', 1));
        $limit = min(50, (int)$request->input('limit', 20));
        $search = $request->input('search', '');
        $offset = ($page - 1) * $limit;
        $campaigns = Campaign::all();
        if ($search) {
            $campaigns = array_filter($campaigns, function($c) use ($search) {
                return stripos($c['name'], $search) !== false;
            });
        }
        $total = count($campaigns);
        $campaigns = array_slice($campaigns, $offset, $limit);
        $this->json([
            'campaigns' => $campaigns,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ]);
    }

    public function exportCampaign(Request $request)
    {
        $id = $request->input('id');
        if (!$id) $this->json(['error' => 'ID required'], 400);
        $campaign = Campaign::find($id);
        if (!$campaign) $this->json(['error' => 'Campaign not found'], 404);
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="campaign_' . $id . '.json"');
        echo json_encode($campaign, JSON_PRETTY_PRINT);
        exit;
    }

    public function duplicateCampaign(Request $request)
    {
        $this->validateCsrf($request);
        $id = $request->input('id');
        if (!$id) $this->json(['error' => 'ID required'], 400);
        $campaign = Campaign::find($id);
        if (!$campaign) $this->json(['error' => 'Campaign not found'], 404);
        unset($campaign['id']);
        $campaign['name'] = $campaign['name'] . ' (copy)';
        $campaign['status'] = 'draft';
        $newId = Campaign::create($campaign);
        $this->json(['success' => true, 'new_id' => $newId]);
    }

    public function deleteCampaign(Request $request)
    {
        $this->validateCsrf($request);
        $id = $request->input('id');
        if (!$id) $this->json(['error' => 'ID required'], 400);
        Campaign::delete($id);
        $this->json(['success' => true]);
    }

    // ---- Email Templates ----
    public function listTemplates(Request $request)
    {
        $campaignId = $request->input('campaign_id');
        if ($campaignId) {
            $templates = EmailTemplate::where('campaign_id', $campaignId);
        } else {
            $templates = EmailTemplate::all();
        }
        $this->json(['templates' => $templates]);
    }

    public function createTemplate(Request $request)
    {
        $this->validateCsrf($request);
        $data = $request->only(['campaign_id', 'name', 'subject', 'body', 'attachments', 'ab_group']);
        if (empty($data['campaign_id']) || empty($data['name'])) {
            $this->json(['error' => 'Campaign ID and name required'], 400);
        }
        $id = PhishingService::createTemplate($data);
        $this->json(['status' => 'created', 'id' => $id]);
    }

    public function editTemplate(Request $request)
    {
        $this->validateCsrf($request);
        $id = $request->input('id');
        $data = $request->only(['name', 'subject', 'body', 'attachments', 'ab_group']);
        if (!$id) $this->json(['error' => 'ID required'], 400);
        PhishingService::updateTemplate($id, $data);
        $this->json(['success' => true]);
    }

    public function deleteTemplate(Request $request)
    {
        $this->validateCsrf($request);
        $id = $request->input('id');
        if (!$id) $this->json(['error' => 'ID required'], 400);
        PhishingService::deleteTemplate($id);
        $this->json(['success' => true]);
    }

    // ---- Social Posts ----
    public function listSocial(Request $request)
    {
        $campaignId = $request->input('campaign_id');
        if ($campaignId) {
            $posts = SocialPost::where('campaign_id', $campaignId);
        } else {
            $posts = SocialPost::all();
        }
        $this->json(['social_posts' => $posts]);
    }

    public function createSocial(Request $request)
    {
        $this->validateCsrf($request);
        $campaignId = $request->input('campaign_id');
        $platform = $request->input('platform');
        $content = $request->input('content');
        $imageUrl = $request->input('image_url');
        $scheduledAt = $request->input('scheduled_at');
        if (!$campaignId || !$platform || !$content) {
            $this->json(['error' => 'Campaign ID, platform, and content required'], 400);
        }
        $result = PhishingService::postToSocial($campaignId, $platform, $content, $imageUrl, $scheduledAt);
        $this->json($result);
    }

    public function deleteSocial(Request $request)
    {
        $this->validateCsrf($request);
        $id = $request->input('id');
        if (!$id) $this->json(['error' => 'ID required'], 400);
        SocialPost::delete($id);
        $this->json(['success' => true]);
    }

    // ---- SMS ----
    public function listSms(Request $request)
    {
        $campaignId = $request->input('campaign_id');
        if ($campaignId) {
            $sms = SmsLog::where('campaign_id', $campaignId);
        } else {
            $sms = SmsLog::all();
        }
        $this->json(['sms_logs' => $sms]);
    }

    public function sendSms(Request $request)
    {
        $this->validateCsrf($request);
        $campaignId = $request->input('campaign_id');
        $phones = $request->input('phones', []);
        $message = $request->input('message');
        if (!$campaignId || !$phones || !$message) {
            $this->json(['error' => 'Missing parameters'], 400);
        }
        $result = PhishingService::sendSms($campaignId, $phones, $message);
        $this->json($result);
    }

    // ---- Email Sending ----
    public function sendEmails(Request $request)
    {
        $this->validateCsrf($request);
        $campaignId = $request->input('campaign_id');
        if (!$campaignId) $this->json(['error' => 'Campaign ID required'], 400);
        $smtp = [
            'host' => $request->input('smtp_host', 'smtp.example.com'),
            'port' => $request->input('smtp_port', 587),
            'user' => $request->input('smtp_user', 'user'),
            'pass' => $request->input('smtp_pass', 'pass')
        ];
        $result = PhishingService::sendEmails($campaignId, $smtp);
        $this->json($result);
    }

    // ---- Tracking ----
    public function trackOpen(Request $request)
    {
        $campaignId = $request->input('campaign_id');
        if (!$campaignId) {
            header('Content-Type: image/gif');
            echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
            exit;
        }
        PhishingService::trackOpen($campaignId, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $_SERVER['HTTP_REFERER'] ?? null);
        header('Content-Type: image/gif');
        echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        exit;
    }

    public function trackClick(Request $request)
    {
        $campaignId = $request->input('campaign_id');
        $url = $request->input('url', '/');
        if ($campaignId) {
            PhishingService::trackClick($campaignId, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $_SERVER['HTTP_REFERER'] ?? null);
        }
        header("Location: $url");
        exit;
    }

    public function trackConversion(Request $request)
    {
        $this->validateCsrf($request);
        $campaignId = $request->input('campaign_id');
        $value = (int)$request->input('value', 1);
        if (!$campaignId) $this->json(['error' => 'campaign_id required'], 400);
        PhishingService::trackConversion($campaignId, $value);
        $this->json(['status' => 'conversion_tracked']);
    }

    public function listTracks(Request $request)
    {
        $campaignId = $request->input('campaign_id');
        $type = $request->input('type');
        if ($campaignId) {
            $tracks = CampaignTrack::getByCampaign($campaignId, $type);
        } else {
            $tracks = CampaignTrack::all();
        }
        $this->json(['tracks' => $tracks]);
    }

    // ---- Analytics ----
    public function getStats(Request $request)
    {
        $campaignId = $request->input('campaign_id');
        if (!$campaignId) {
            $stats = PhishingService::getOverallStats();
            $this->json($stats);
        } else {
            $stats = PhishingService::getCampaignStats($campaignId);
            if (!$stats) $this->json(['error' => 'Campaign not found'], 404);
            $this->json($stats);
        }
    }

    public function getMetrics(Request $request)
    {
        $campaignId = $request->input('campaign_id');
        if (!$campaignId) $this->json(['error' => 'campaign_id required'], 400);
        $metrics = CampaignMetric::where('campaign_id', $campaignId);
        $this->json(['metrics' => $metrics]);
    }

    // ---- Validation helper ----
    private function validateCampaignData($data, $isEdit = false)
    {
        $errors = [];
        if (empty($data['name'])) $errors[] = 'Campaign name is required.';
        if (!empty($data['platform']) && !in_array($data['platform'], ['email','sms','social'])) {
            $errors[] = 'Platform must be email, sms, or social.';
        }
        if (!empty($data['type']) && !in_array($data['type'], ['phishing','marketing'])) {
            $errors[] = 'Type must be phishing or marketing.';
        }
        if (!empty($data['targets']) && !is_array($data['targets'])) {
            $errors[] = 'Targets must be an array.';
        }
        return $errors;
    }
}
