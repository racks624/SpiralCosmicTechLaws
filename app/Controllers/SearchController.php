<?php
namespace App\Controllers;

use App\Core\Request;
use App\Models\Target;
use App\Models\Agent;
use App\Models\Campaign;
use App\Models\Payload;
use App\Models\MaskedUrl;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');
        if (strlen($query) < 2) {
            $this->json(['error' => 'Query too short'], 400);
        }
        $results = [
            'targets' => Target::where('name', 'LIKE', "%$query%") ?: [],
            'agents' => Agent::where('hostname', 'LIKE', "%$query%") ?: [],
            'campaigns' => Campaign::where('name', 'LIKE', "%$query%") ?: [],
            'payloads' => Payload::where('filename', 'LIKE', "%$query%") ?: [],
            'urls' => MaskedUrl::where('original_url', 'LIKE', "%$query%") ?: []
        ];
        $this->json($results);
    }
}
