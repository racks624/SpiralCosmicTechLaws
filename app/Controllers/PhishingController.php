<?php
namespace App\Controllers;

use App\Core\Request;
use App\Models\Campaign;

class PhishingController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::all();
        $this->view('phishing/index', ['campaigns' => $campaigns]);
    }

    public function listCampaigns()
    {
        $campaigns = Campaign::all();
        $this->json(['campaigns' => $campaigns]);
    }

    public function createCampaign(Request $request)
    {
        $name = $request->input('name');
        if (!$name) $this->json(['error' => 'Campaign name required'], 400);
        $id = Campaign::createCampaign($name);
        $this->json(['status' => 'created', 'id' => $id]);
    }

    public function trackClick(Request $request)
    {
        $campaignId = $request->input('campaign_id');
        if ($campaignId) {
            Campaign::incrementClicks($campaignId);
        }
        // Redirect to a fake page
        echo "<script>alert('You have been phished!'); window.location='https://google.com';</script>";
    }

    public function deleteCampaign(Request $request)
    {
        $id = $request->input('id');
        if ($id) {
            Campaign::delete($id);
            $this->json(['success' => true]);
        }
        $this->json(['error' => 'ID required'], 400);
    }
}
