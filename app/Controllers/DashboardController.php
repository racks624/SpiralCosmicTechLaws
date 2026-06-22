<?php
namespace App\Controllers;

use App\Models\Target;
use App\Models\Finding;
use App\Models\Agent;
use App\Models\Campaign;
use App\Models\Scan;
use App\Models\Payload;
use App\Models\MaskedUrl;
use App\Models\VirtualMachine;
use App\Models\EmailLog;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = $this->getStats();
        $this->view('dashboard', ['stats' => $stats]);
    }

    public function stats()
    {
        $stats = $this->getStats();
        $this->json($stats);
    }

    private function getStats()
    {
        // Counts
        $totalTargets = count(Target::all());
        $totalFindings = count(Finding::all());
        $totalAgents = count(Agent::all());
        $totalCampaigns = count(Campaign::all());
        $totalScans = count(Scan::all());
        $totalPayloads = count(Payload::all());
        $totalMaskedUrls = count(MaskedUrl::all());
        $totalVMs = count(VirtualMachine::all());
        $totalEmails = count(EmailLog::all());

        // Severity breakdown
        $severitySql = "SELECT severity, COUNT(*) as count FROM redteam_findings GROUP BY severity";
        $stmt = \App\Core\Model::query($severitySql);
        $severityData = $stmt->fetchAll();

        // Agent status
        $agentStatusSql = "SELECT status, COUNT(*) as count FROM agents GROUP BY status";
        $stmt = \App\Core\Model::query($agentStatusSql);
        $agentStatus = $stmt->fetchAll();

        // Recent findings (last 5)
        $recentFindingsSql = "SELECT * FROM redteam_findings ORDER BY discovered_at DESC LIMIT 5";
        $stmt = \App\Core\Model::query($recentFindingsSql);
        $recentFindings = $stmt->fetchAll();

        // Recent agents (last 5 heartbeats)
        $recentAgentsSql = "SELECT * FROM agents ORDER BY last_seen DESC LIMIT 5";
        $stmt = \App\Core\Model::query($recentAgentsSql);
        $recentAgents = $stmt->fetchAll();

        return [
            'total_targets' => $totalTargets,
            'total_findings' => $totalFindings,
            'total_agents' => $totalAgents,
            'total_campaigns' => $totalCampaigns,
            'total_scans' => $totalScans,
            'total_payloads' => $totalPayloads,
            'total_masked_urls' => $totalMaskedUrls,
            'total_vms' => $totalVMs,
            'total_emails' => $totalEmails,
            'severity_breakdown' => $severityData,
            'agent_status' => $agentStatus,
            'recent_findings' => $recentFindings,
            'recent_agents' => $recentAgents,
        ];
    }
}
