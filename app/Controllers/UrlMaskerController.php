<?php
namespace App\Controllers;

use App\Core\Request;
use App\Models\MaskedUrl;
use App\Services\UrlMaskerService;

class UrlMaskerController extends Controller
{
    public function index()
    {
        $urls = MaskedUrl::all();
        $this->view('urlmasker/index', ['urls' => $urls]);
    }

    public function generate(Request $request)
    {
        $original = $request->input('url');
        $maskDomain = $request->input('mask_domain');
        $campaignId = $request->input('campaign_id');
        $agentId = $request->input('agent_id');
        if (!$original || !$maskDomain) {
            $this->json(['error' => 'URL and mask domain required'], 400);
        }
        $result = UrlMaskerService::generateMaskedUrl($original, $maskDomain, $campaignId, $agentId);
        // Generate QR
        $qrUrl = UrlMaskerService::generateQRCode($result['masked_url']);
        $this->json([
            'masked_url' => $result['masked_url'],
            'id' => $result['id'],
            'qr_code' => $qrUrl
        ]);
    }

    public function editUrl(Request $request)
    {
        $id = $request->input('id');
        $original = $request->input('original_url');
        if (!$id || !$original) $this->json(['error' => 'ID and URL required'], 400);
        $url = MaskedUrl::find($id);
        if (!$url) $this->json(['error' => 'URL not found'], 404);
        MaskedUrl::update($id, ['original_url' => $original]);
        $this->json(['success' => true]);
    }

    public function viewUrl(Request $request)
    {
        $id = $request->input('id');
        if (!$id) $this->json(['error' => 'ID required'], 400);
        $url = MaskedUrl::find($id);
        if (!$url) $this->json(['error' => 'URL not found'], 404);
        $this->json(['url' => $url]);
    }

    public function deleteUrl(Request $request)
    {
        $id = $request->input('id');
        if (!$id) $this->json(['error' => 'ID required'], 400);
        MaskedUrl::delete($id);
        $this->json(['success' => true]);
    }

    public function redirect($token)
    {
        $url = UrlMaskerService::trackClick($token, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
        if ($url) {
            header("Location: " . $url['original_url']);
            exit;
        }
        http_response_code(404);
        $this->view('error/404', ['title' => 'URL Not Found']);
    }

    public function list()
    {
        $urls = MaskedUrl::all();
        $this->json(['urls' => $urls]);
    }

    public function stats()
    {
        $total = count(MaskedUrl::all());
        $totalClicks = array_sum(array_column(MaskedUrl::all(), 'clicks'));
        $this->json(['total_urls' => $total, 'total_clicks' => $totalClicks]);
    }

    public function share(Request $request)
    {
        $id = $request->input('id');
        if (!$id) $this->json(['error' => 'ID required'], 400);
        $url = MaskedUrl::find($id);
        if (!$url) $this->json(['error' => 'URL not found'], 404);
        // Generate share token if not exists
        if (empty($url['share_token'])) {
            $token = bin2hex(random_bytes(8));
            MaskedUrl::update($id, ['share_token' => $token]);
            $url['share_token'] = $token;
        }
        $shareLink = getenv('APP_URL') . '/go/share/' . $url['share_token'];
        $this->json(['share_url' => $shareLink]);
    }
}
