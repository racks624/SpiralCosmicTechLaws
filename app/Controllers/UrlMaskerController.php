<?php
namespace App\Controllers;

use App\Core\Request;
use App\Models\MaskedUrl;

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
        if (!$original || !$maskDomain) {
            $this->json(['error' => 'URL and mask domain required'], 400);
        }
        $token = bin2hex(random_bytes(8));
        $masked = "https://{$maskDomain}/go/{$token}";
        $id = MaskedUrl::create([
            'original_url' => $original,
            'masked_url' => $masked,
            'token' => $token,
            'clicks' => 0
        ]);
        $this->json(['masked_url' => $masked, 'id' => $id]);
    }

    public function redirect($token)
    {
        $url = MaskedUrl::where('token', $token);
        if ($url) {
            MaskedUrl::incrementClicks($url[0]['id']);
            header("Location: " . $url[0]['original_url']);
            exit;
        }
        http_response_code(404);
        echo "URL not found";
    }
}
