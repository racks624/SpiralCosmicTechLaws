<?php ob_start(); ?>
<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold cosmic-glow-text">🚀 Enterprise Dashboard</h1>
        <div class="text-sm text-gray-400 flex items-center gap-3">
            <span id="live-indicator" class="inline-block w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
            <span>Live</span>
            <span id="last-updated"><?= date('H:i:s') ?></span>
        </div>
    </div>

    <!-- Stats Cards (8 cards) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="cosmic-glass p-4 rounded-2xl text-center hover:border-green-400 transition">
            <i class="fas fa-bullseye text-2xl text-green-400"></i>
            <h3 class="text-2xl font-bold" id="stat-targets"><?= $stats['total_targets'] ?></h3>
            <p class="text-xs text-gray-400">Targets</p>
        </div>
        <div class="cosmic-glass p-4 rounded-2xl text-center hover:border-green-400 transition">
            <i class="fas fa-bug text-2xl text-yellow-400"></i>
            <h3 class="text-2xl font-bold" id="stat-findings"><?= $stats['total_findings'] ?></h3>
            <p class="text-xs text-gray-400">Findings</p>
        </div>
        <div class="cosmic-glass p-4 rounded-2xl text-center hover:border-green-400 transition">
            <i class="fas fa-microchip text-2xl text-blue-400"></i>
            <h3 class="text-2xl font-bold" id="stat-agents"><?= $stats['total_agents'] ?></h3>
            <p class="text-xs text-gray-400">Agents</p>
        </div>
        <div class="cosmic-glass p-4 rounded-2xl text-center hover:border-green-400 transition">
            <i class="fas fa-fish text-2xl text-green-300"></i>
            <h3 class="text-2xl font-bold" id="stat-campaigns"><?= $stats['total_campaigns'] ?></h3>
            <p class="text-xs text-gray-400">Campaigns</p>
        </div>
        <div class="cosmic-glass p-4 rounded-2xl text-center hover:border-green-400 transition">
            <i class="fas fa-scan text-2xl text-purple-400"></i>
            <h3 class="text-2xl font-bold" id="stat-scans"><?= $stats['total_scans'] ?></h3>
            <p class="text-xs text-gray-400">Scans</p>
        </div>
        <div class="cosmic-glass p-4 rounded-2xl text-center hover:border-green-400 transition">
            <i class="fas fa-biohazard text-2xl text-red-400"></i>
            <h3 class="text-2xl font-bold" id="stat-payloads"><?= $stats['total_payloads'] ?></h3>
            <p class="text-xs text-gray-400">Payloads</p>
        </div>
        <div class="cosmic-glass p-4 rounded-2xl text-center hover:border-green-400 transition">
            <i class="fas fa-link text-2xl text-cyan-400"></i>
            <h3 class="text-2xl font-bold" id="stat-masked"><?= $stats['total_masked_urls'] ?></h3>
            <p class="text-xs text-gray-400">Masked URLs</p>
        </div>
        <div class="cosmic-glass p-4 rounded-2xl text-center hover:border-green-400 transition">
            <i class="fas fa-flask text-2xl text-indigo-400"></i>
            <h3 class="text-2xl font-bold" id="stat-vms"><?= $stats['total_vms'] ?></h3>
            <p class="text-xs text-gray-400">Virtual Machines</p>
        </div>
    </div>

    <!-- Charts Row (same as before) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="cosmic-glass p-4 rounded-2xl">
            <h3 class="text-lg font-semibold mb-2">Findings by Severity</h3>
            <canvas id="severityChart" height="150"></canvas>
        </div>
        <div class="cosmic-glass p-4 rounded-2xl">
            <h3 class="text-lg font-semibold mb-2">Agent Status</h3>
            <canvas id="agentStatusChart" height="150"></canvas>
        </div>
    </div>

    <!-- MITRE Summary & Recent Activity (same) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 cosmic-glass p-4 rounded-2xl">
            <h3 class="text-lg font-semibold mb-2">MITRE ATT&CK Summary</h3>
            <div id="mitre-summary" class="space-y-2"><p class="text-sm text-gray-400">Loading...</p></div>
            <a href="/matrix" class="text-green-400 text-sm hover:underline">View full matrix →</a>
        </div>
        <div class="lg:col-span-2 cosmic-glass p-4 rounded-2xl">
            <h3 class="text-lg font-semibold mb-2">Recent Activity</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm cosmic-table">
                    <thead><tr><th>Time</th><th>Type</th><th>Detail</th></tr></thead>
                    <tbody id="recent-activity"><tr><td colspan="3" class="text-gray-400">Loading...</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const severityData = <?= json_encode($stats['severity_breakdown']) ?>;
    const agentStatusData = <?= json_encode($stats['agent_status']) ?>;
    const recentFindings = <?= json_encode($stats['recent_findings']) ?>;
    const recentAgents = <?= json_encode($stats['recent_agents']) ?>;

    new Chart(document.getElementById('severityChart').getContext('2d'), {
        type: 'pie',
        data: {
            labels: severityData.map(d => d.severity),
            datasets: [{ data: severityData.map(d => d.count), backgroundColor: ['#a855f7','#f97316','#eab308','#22c55e','#ef4444'], borderWidth: 1 }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });

    new Chart(document.getElementById('agentStatusChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: agentStatusData.map(d => d.status),
            datasets: [{ label: 'Agents', data: agentStatusData.map(d => d.count), backgroundColor: ['#3b82f6','#f97316','#22c55e'], borderRadius: 4 }]
        },
        options: { responsive: true, plugins: { legend: { display: false } } }
    });

    function renderActivity() {
        let html = '';
        const activities = [];
        recentFindings.forEach(f => activities.push({ time: f.discovered_at || f.created_at, type: 'Finding', detail: f.title }));
        recentAgents.forEach(a => activities.push({ time: a.last_seen, type: 'Heartbeat', detail: `Agent ${a.agent_id}` }));
        activities.sort((a,b) => new Date(b.time) - new Date(a.time));
        const top = activities.slice(0, 5);
        if (top.length === 0) html = '<tr><td colspan="3" class="text-gray-400">No recent activity</td></tr>';
        else top.forEach(a => { html += `<tr><td>${new Date(a.time).toLocaleTimeString()}</td><td>${a.type}</td><td>${a.detail}</td></tr>`; });
        document.getElementById('recent-activity').innerHTML = html;
    }
    renderActivity();

    fetch('/matrix/data').then(res => res.json()).then(data => {
        const matrix = data.matrix || [];
        let html = '';
        if (matrix.length === 0) html = '<p class="text-sm text-gray-400">No MITRE data yet.</p>';
        else {
            const tactics = {};
            matrix.forEach(row => { const t = row.mitre_tactic || 'Unknown'; tactics[t] = (tactics[t] || 0) + row.count; });
            Object.entries(tactics).sort((a,b) => b[1] - a[1]).slice(0, 3).forEach(([tactic, count]) => {
                html += `<div class="flex justify-between border-b border-white/10 py-1"><span>${tactic}</span><span class="bg-green-500/30 px-2 rounded">${count}</span></div>`;
            });
        }
        document.getElementById('mitre-summary').innerHTML = html;
    }).catch(() => document.getElementById('mitre-summary').innerHTML = '<p class="text-sm text-red-400">Failed to load MITRE data.</p>');

    function updateStats() {
        fetch('/api/stats').then(res => res.json()).then(data => {
            document.getElementById('stat-targets').innerText = data.total_targets;
            document.getElementById('stat-findings').innerText = data.total_findings;
            document.getElementById('stat-agents').innerText = data.total_agents;
            document.getElementById('stat-campaigns').innerText = data.total_campaigns;
            document.getElementById('stat-scans').innerText = data.total_scans;
            document.getElementById('stat-payloads').innerText = data.total_payloads || 0;
            document.getElementById('stat-masked').innerText = data.total_masked_urls || 0;
            document.getElementById('stat-vms').innerText = data.total_vms || 0;
            document.getElementById('last-updated').innerText = new Date().toLocaleTimeString();
        });
    }
    setInterval(updateStats, 10000);
});
</script>
<?php $content = ob_get_clean(); require_once __DIR__ . '/layout.php'; ?>
