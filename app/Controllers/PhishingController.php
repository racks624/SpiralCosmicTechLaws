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

    // ---- Campaign CRUD ----
    public function createCampaign(Request $request)
    {
        try {
            $this->validateCsrf($request);
            $data = $request->only([
                'name', 'type', 'platform', 'template', 'targets',
                'scheduled_at', 'from_name', 'from_email', 'reply_to'
            ]);
            $errors = $this->validateCampaignData($data);
            if (!empty($errors)) {
                return $this->json(['error' => 'Validation failed', 'details' => $errors], 400);
            }
            $data['status'] = 'draft';
            $id = PhishingService::createCampaign($data);
            $this->json(['status' => 'created', 'id' => $id]);
        } catch (\Exception $e) {
            error_log('PhishingController::createCampaign error: ' . $e->getMessage());
            $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function editCampaign(Request $request)
    {
        try {
            $this->validateCsrf($request);
            $id = $request->input('id');
            if (!$id) return $this->json(['error' => 'ID required'], 400);
            $campaign = Campaign::find($id);
            if (!$campaign) return $this->json(['error' => 'Campaign not found'], 404);
            $data = $request->only([
                'name', 'type', 'platform', 'template', 'targets', 'status',
                'scheduled_at', 'from_name', 'from_email', 'reply_to'
            ]);
            $errors = $this->validateCampaignData($data, true);
            if (!empty($errors)) {
                return $this->json(['error' => 'Validation failed', 'details' => $errors], 400);
            }
            PhishingService::updateCampaign($id, $data);
            $this->json(['success' => true]);
        } catch (\Exception $e) {
            error_log('PhishingController::editCampaign error: ' . $e->getMessage());
            $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function viewCampaign(Request $request)
    {
        try {
            $id = $request->input('id');
            if (!$id) return $this->json(['error' => 'ID required'], 400);
            $stats = PhishingService::getCampaignStats($id);
            if (!$stats) return $this->json(['error' => 'Campaign not found'], 404);
            $this->json($stats);
        } catch (\Exception $e) {
            error_log('PhishingController::viewCampaign error: ' . $e->getMessage());
            $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function listCampaigns(Request $request)
    {
        try {
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
        } catch (\Exception $e) {
            error_log('PhishingController::listCampaigns error: ' . $e->getMessage());
            $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function exportCampaign(Request $request)
    {
        try {
            $id = $request->input('id');
            if (!$id) return $this->json(['error' => 'ID required'], 400);
            $campaign = Campaign::find($id);
            if (!$campaign) return $this->json(['error' => 'Campaign not found'], 404);
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="campaign_' . $id . '.json"');
            echo json_encode($campaign, JSON_PRETTY_PRINT);
            exit;
        } catch (\Exception $e) {
            error_log('PhishingController::exportCampaign error: ' . $e->getMessage());
            $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function duplicateCampaign(Request $request)
    {
        try {
            $this->validateCsrf($request);
            $id = $request->input('id');
            if (!$id) return $this->json(['error' => 'ID required'], 400);
            $campaign = Campaign::find($id);
            if (!$campaign) return $this->json(['error' => 'Campaign not found'], 404);
            unset($campaign['id']);
            $campaign['name'] = $campaign['name'] . ' (copy)';
            $campaign['status'] = 'draft';
            $newId = Campaign::create($campaign);
            $this->json(['success' => true, 'new_id' => $newId]);
        } catch (\Exception $e) {
            error_log('PhishingController::duplicateCampaign error: ' . $e->getMessage());
            $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function deleteCampaign(Request $request)
    {
        try {
            $this->validateCsrf($request);
            $id = $request->input('id');
            if (!$id) return $this->json(['error' => 'ID required'], 400);
            Campaign::delete($id);
            $this->json(['success' => true]);
        } catch (\Exception $e) {
            error_log('PhishingController::deleteCampaign error: ' . $e->getMessage());
            $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    // ---- Email Templates ----
    public function listTemplates(Request $request)
    {
        try {
            $campaignId = $request->input('campaign_id');
            if ($campaignId) {
                $templates = EmailTemplate::where('campaign_id', $campaignId);
            } else {
                $templates = EmailTemplate::all();
            }
            $this->json(['templates' => $templates]);
        } catch (\Exception $e) {
            error_log('PhishingController::listTemplates error: ' . $e->getMessage());
            $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function createTemplate(Request $request)
    {
        try {
            $this->validateCsrf($request);
            $data = $request->only(['campaign_id', 'name', 'subject', 'body', 'attachments', 'ab_group']);
            if (empty($data['campaign_id']) || empty($data['name'])) {
                return $this->json(['error' => 'Campaign ID and name required'], 400);
            }
            // Convert empty strings to null for optional fields
            foreach (['subject', 'body', 'attachments', 'ab_group'] as $field) {
                if (isset($data[$field]) && $data[$field] === '') {
                    $data[$field] = null;
                }
            }
            $id = PhishingService::createTemplate($data);
            $this->json(['status' => 'created', 'id' => $id]);
        } catch (\Exception $e) {
            error_log('PhishingController::createTemplate error: ' . $e->getMessage());
            $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function editTemplate(Request $request)
    {
        try {
            $this->validateCsrf($request);
            $id = $request->input('id');
            $data = $request->only(['name', 'subject', 'body', 'attachments', 'ab_group']);
            if (!$id) return $this->json(['error' => 'ID required'], 400);
            PhishingService::updateTemplate($id, $data);
            $this->json(['success' => true]);
        } catch (\Exception $e) {
            error_log('PhishingController::editTemplate error: ' . $e->getMessage());
            $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function deleteTemplate(Request $request)
    {
        try {
            $this->validateCsrf($request);
            $id = $request->input('id');
            if (!$id) return $this->json(['error' => 'ID required'], 400);
            PhishingService::deleteTemplate($id);
            $this->json(['success' => true]);
        } catch (\Exception $e) {
            error_log('PhishingController::deleteTemplate error: ' . $e->getMessage());
            $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    // ---- Social Posts ----
    public function listSocial(Request $request)
    {
        try {
            $campaignId = $request->input('campaign_id');
            if ($campaignId) {
                $posts = SocialPost::where('campaign_id', $campaignId);
            } else {
                $posts = SocialPost::all();
            }
            $this->json(['social_posts' => $posts]);
        } catch (\Exception $e) {
            error_log('PhishingController::listSocial error: ' . $e->getMessage());
            $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function createSocial(Request $request)
    {
        try {
            $this->validateCsrf($request);
            $campaignId = $request->input('campaign_id');
            $platform = $request->input('platform');
            $content = $request->input('content');
            $imageUrl = $request->input('image_url');
            $scheduledAt = $request->input('scheduled_at');
            if (!$campaignId || !$platform || !$content) {
                return $this->json(['error' => 'Campaign ID, platform, and content required'], 400);
            }
            $result = PhishingService::postToSocial($campaignId, $platform, $content, $imageUrl, $scheduledAt);
            $this->json($result);
        } catch (\Exception $e) {
            error_log('PhishingController::createSocial error: ' . $e->getMessage());
            $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function deleteSocial(Request $request)
    {
        try {
            $this->validateCsrf($request);
            $id = $request->input('id');
            if (!$id) return $this->json(['error' => 'ID required'], 400);
            SocialPost::delete($id);
            $this->json(['success' => true]);
        } catch (\Exception $e) {
            error_log('PhishingController::deleteSocial error: ' . $e->getMessage());
            $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    // ---- SMS ----
    public function listSms(Request $request)
    {
        try {
            $campaignId = $request->input('campaign_id');
            if ($campaignId) {
                $sms = SmsLog::where('campaign_id', $campaignId);
            } else {
                $sms = SmsLog::all();
            }
            $this->json(['sms_logs' => $sms]);
        } catch (\Exception $e) {
            error_log('PhishingController::listSms error: ' . $e->getMessage());
            $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function sendSms(Request $request)
    {
        try {
            $this->validateCsrf($request);
            $campaignId = $request->input('campaign_id');
            $phones = $request->input('phones', []);
            $message = $request->input('message');
            if (!$campaignId || !$phones || !$message) {
                return $this->json(['error' => 'Missing parameters: campaign_id, phones, message required'], 400);
            }
            if (is_string($phones)) {
                $phones = array_filter(array_map('trim', explode("\n", $phones)));
            }
            if (empty($phones)) {
                return $this->json(['error' => 'At least one phone number required'], 400);
            }
            $result = PhishingService::sendSms($campaignId, $phones, $message);
            $this->json($result);
        } catch (\Exception $e) {
            error_log('PhishingController::sendSms error: ' . $e->getMessage());
            $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    // ---- Email Sending ----
    public function sendEmails(Request $request)
    {
        try {
            $this->validateCsrf($request);
            $campaignId = $request->input('campaign_id');
            if (!$campaignId) return $this->json(['error' => 'Campaign ID required'], 400);
            $smtp = [
                'host' => $request->input('smtp_host', 'smtp.example.com'),
                'port' => $request->input('smtp_port', 587),
                'user' => $request->input('smtp_user', 'user'),
                'pass' => $request->input('smtp_pass', 'pass')
            ];
            $result = PhishingService::sendEmails($campaignId, $smtp);
            $this->json($result);
        } catch (\Exception $e) {
            error_log('PhishingController::sendEmails error: ' . $e->getMessage());
            $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    // ---- Tracking ----
    public function trackOpen(Request $request)
    {
        try {
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
        } catch (\Exception $e) {
            error_log('PhishingController::trackOpen error: ' . $e->getMessage());
            header('Content-Type: image/gif');
            echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
            exit;
        }
    }

    public function trackClick(Request $request)
    {
        try {
            $campaignId = $request->input('campaign_id');
            $url = $request->input('url', '/');
            if ($campaignId) {
                PhishingService::trackClick($campaignId, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $_SERVER['HTTP_REFERER'] ?? null);
            }
            header("Location: $url");
            exit;
        } catch (\Exception $e) {
            error_log('PhishingController::trackClick error: ' . $e->getMessage());
            header("Location: /");
            exit;
        }
    }

    public function trackConversion(Request $request)
    {
        try {
            $this->validateCsrf($request);
            $campaignId = $request->input('campaign_id');
            $value = (int)$request->input('value', 1);
            if (!$campaignId) return $this->json(['error' => 'campaign_id required'], 400);
            PhishingService::trackConversion($campaignId, $value);
            $this->json(['status' => 'conversion_tracked']);
        } catch (\Exception $e) {
            error_log('PhishingController::trackConversion error: ' . $e->getMessage());
            $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function listTracks(Request $request)
    {
        try {
            $campaignId = $request->input('campaign_id');
            $type = $request->input('type');
            if ($campaignId) {
                $tracks = CampaignTrack::getByCampaign($campaignId, $type);
            } else {
                $tracks = CampaignTrack::all();
            }
            $this->json(['tracks' => $tracks]);
        } catch (\Exception $e) {
            error_log('PhishingController::listTracks error: ' . $e->getMessage());
            $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    // ---- Analytics ----
    public function getStats(Request $request)
    {
        try {
            $campaignId = $request->input('campaign_id');
            if (!$campaignId) {
                $stats = PhishingService::getOverallStats();
                $this->json($stats);
            } else {
                $stats = PhishingService::getCampaignStats($campaignId);
                if (!$stats) return $this->json(['error' => 'Campaign not found'], 404);
                $this->json($stats);
            }
        } catch (\Exception $e) {
            error_log('PhishingController::getStats error: ' . $e->getMessage());
            $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function getMetrics(Request $request)
    {
        try {
            $campaignId = $request->input('campaign_id');
            if (!$campaignId) return $this->json(['error' => 'campaign_id required'], 400);
            $metrics = CampaignMetric::where('campaign_id', $campaignId);
            $this->json(['metrics' => $metrics]);
        } catch (\Exception $e) {
            error_log('PhishingController::getMetrics error: ' . $e->getMessage());
            $this->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
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
