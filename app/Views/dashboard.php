<?php ob_start(); ?>
<div>
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold gradient-text">Enterprise Dashboard</h1>
        <div class="text-sm text-gray-400"><i class="far fa-clock"></i> <?= date('F j, Y, g:i a') ?></div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="glass-card glass p-6 rounded-2xl">
            <i class="fas fa-microchip text-3xl text-purple-400 mb-2"></i>
            <h3 class="text-2xl font-bold" id="agentCount">0</h3>
            <p class="text-gray-300">Active Agents</p>
        </div>
        <div class="glass-card glass p-6 rounded-2xl">
            <i class="fas fa-bug text-3xl text-red-400 mb-2"></i>
            <h3 class="text-2xl font-bold" id="findingCount">0</h3>
            <p class="text-gray-300">Vulnerabilities</p>
        </div>
        <div class="glass-card glass p-6 rounded-2xl">
            <i class="fas fa-chart-line text-3xl text-green-400 mb-2"></i>
            <h3 class="text-2xl font-bold" id="campaignCount">0</h3>
            <p class="text-gray-300">Active Campaigns</p>
        </div>
    </div>

    <!-- Module Quick Access Grid -->
    <h2 class="text-2xl font-semibold mb-4">Core Modules</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php
        $modules = [
            ['redteam', 'RedTeam Ops', 'skull-crossbones', 'Industrial-grade scanning & exploitation'],
            ['c2', 'Global C2', 'satellite-dish', 'Multi-protocol command & control'],
            ['phishing', 'Phishing Campaigns', 'fish', 'Advanced email/sms lure campaigns'],
            ['urlmasker', 'Cosmic UrlMasker', 'link', 'URL obfuscation & tracking'],
            ['virtuallab', 'VirtualLab', 'flask', 'On-demand isolated environments'],
            ['payload', 'Payload Generator', 'biohazard', 'Custom trojan & shellcode builder']
        ];
        foreach ($modules as $m): ?>
        <a href="/<?= $m[0] ?>" class="glass-card glass p-5 rounded-2xl block hover:border-purple-500 transition">
            <i class="fas fa-<?= $m[2] ?> text-3xl text-purple-400 mb-3"></i>
            <h3 class="text-xl font-bold"><?= $m[1] ?></h3>
            <p class="text-gray-300 text-sm mt-2"><?= $m[3] ?></p>
            <span class="inline-block mt-3 text-purple-400">Launch →</span>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<script>
    // Simulate live stats (replace with AJAX calls)
    async function fetchStats() {
        try {
            const [agents, findings, campaigns] = await Promise.all([
                fetch('/c2/agents').then(res => res.json()).catch(() => ({agents: []})),
                fetch('/redteam/findings?target_id=all').then(res => res.json()).catch(() => ({findings: []})),
                fetch('/phishing/campaigns').then(res => res.json()).catch(() => ({campaigns: []}))
            ]);
            document.getElementById('agentCount').innerText = agents.agents?.length || 0;
            document.getElementById('findingCount').innerText = findings.findings?.length || 0;
            document.getElementById('campaignCount').innerText = campaigns.campaigns?.length || 0;
        } catch(e) { console.error(e); }
    }
    fetchStats();
    setInterval(fetchStats, 30000);
</script>
<?php $content = ob_get_clean(); require_once __DIR__ . '/layout.php'; ?>
