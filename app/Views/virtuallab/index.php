<?php ob_start(); ?>
<div>
    <h1 class="text-3xl font-bold cosmic-glow-text mb-6">🧪 VirtualLab</h1>
    <div class="cosmic-glass p-6 rounded-2xl mb-6">
        <select id="os" class="cosmic-input mb-4">
            <option value="ubuntu">Ubuntu 22.04</option>
            <option value="windows">Windows 10 (Pro)</option>
            <option value="kali">Kali Linux</option>
            <option value="centos">CentOS 8</option>
        </select>
        <button onclick="spawnVM()" class="cosmic-btn"><i class="fas fa-play"></i> Spawn Instance</button>
        <div id="vmStatus" class="mt-4"></div>
    </div>
    <div class="cosmic-glass p-4 rounded-2xl">
        <h3 class="text-lg font-semibold mb-2">Active Machines</h3>
        <div class="table-wrapper">
            <table class="w-full text-sm cosmic-table">
                <thead><tr><th>ID</th><th>OS</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody id="vmsTable"><tr><td colspan="4" class="text-gray-400">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: View/Edit VM -->
<div id="viewEditModal" class="modal-overlay">
    <div class="modal-content">
        <h2 id="veTitle" class="text-xl mb-4 cosmic-glow-text">VM Details</h2>
        <div id="veContent"></div>
        <div class="flex gap-2 mt-4">
            <button onclick="saveEditVM()" class="cosmic-btn flex-1">Save</button>
            <button type="button" onclick="closeModal('viewEditModal')" class="cosmic-btn flex-1">Close</button>
        </div>
    </div>
</div>

<script>
function closeModal(id) { document.getElementById(id).classList.remove('active'); }
function openModal(id) { document.getElementById(id).classList.add('active'); }

async function spawnVM() {
    const os = document.getElementById('os').value;
    const fd = new FormData(); fd.append('os', os);
    const res = await fetch('/virtuallab/spawn', { method: 'POST', body: fd });
    const data = await res.json();
    document.getElementById('vmStatus').innerHTML = `<div class="cosmic-glass p-3 rounded">Spawning ${os}... Machine ID: ${data.machine_id}</div>`;
    fetchVMs();
}

async function fetchVMs() {
    const res = await fetch('/virtuallab/list');
    const data = await res.json();
    let html = '';
    if (data.machines && data.machines.length) {
        data.machines.forEach(m => {
            html += `<tr>
                <td class="p-2">${m.machine_id}</td>
                <td class="p-2">${m.os}</td>
                <td class="p-2"><span class="cosmic-badge ${m.status==='running'?'border-green-400 text-green-400':'border-red-400 text-red-400'}">${m.status}</span></td>
                <td class="p-2">
                    <button onclick="viewVM(${m.id})" class="text-blue-400 hover:text-blue-300"><i class="fas fa-eye"></i></button>
                    <button onclick="editVM(${m.id})" class="text-yellow-400 hover:text-yellow-300"><i class="fas fa-edit"></i></button>
                    <button onclick="stopVM('${m.machine_id}')" class="text-orange-400 hover:text-orange-300"><i class="fas fa-stop"></i></button>
                    <button onclick="deleteVM(${m.id})" class="text-red-400 hover:text-red-300"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
    } else {
        html = '<tr><td colspan="4" class="text-gray-400">No active machines.</td></tr>';
    }
    document.getElementById('vmsTable').innerHTML = html;
}
fetchVMs();
setInterval(fetchVMs, 10000);

async function viewVM(id) {
    const res = await fetch(`/virtuallab/view?id=${id}`);
    const data = await res.json();
    const m = data.machine;
    let html = `<div class="space-y-2 text-sm">
        <p><strong>ID:</strong> ${m.machine_id}</p>
        <p><strong>OS:</strong> ${m.os}</p>
        <p><strong>Status:</strong> ${m.status}</p>
        <p><strong>IP:</strong> ${m.ip || 'N/A'}</p>
        <p><strong>Config:</strong> ${m.config || 'N/A'}</p>
    </div>`;
    document.getElementById('veContent').innerHTML = html;
    document.getElementById('veTitle').innerText = 'View VM';
    openModal('viewEditModal');
    window._editId = null;
}

async function editVM(id) {
    const res = await fetch(`/virtuallab/view?id=${id}`);
    const data = await res.json();
    const m = data.machine;
    let html = `
        <input type="hidden" id="editId" value="${m.id}">
        <div class="mb-3"><label>OS</label><input type="text" id="editOs" value="${m.os}" class="cosmic-input"></div>
        <div class="mb-3"><label>Status</label><select id="editStatus" class="cosmic-input">
            <option value="running" ${m.status=='running'?'selected':''}>Running</option>
            <option value="stopped" ${m.status=='stopped'?'selected':''}>Stopped</option>
        </select></div>
        <div class="mb-3"><label>IP</label><input type="text" id="editIp" value="${m.ip || ''}" class="cosmic-input"></div>
        <div class="mb-3"><label>Config</label><textarea id="editConfig" class="cosmic-input">${m.config || ''}</textarea></div>
    `;
    document.getElementById('veContent').innerHTML = html;
    document.getElementById('veTitle').innerText = 'Edit VM';
    openModal('viewEditModal');
    window._editId = id;
}

async function saveEditVM() {
    const id = window._editId || document.getElementById('editId').value;
    if (!id) return;
    const data = {
        id: id,
        os: document.getElementById('editOs').value,
        status: document.getElementById('editStatus').value,
        ip: document.getElementById('editIp').value,
        config: document.getElementById('editConfig').value
    };
    const res = await fetch('/virtuallab/edit', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) });
    if (res.ok) { closeModal('viewEditModal'); fetchVMs(); } else { alert('Error updating VM'); }
}

async function stopVM(machineId) {
    const fd = new FormData(); fd.append('machine_id', machineId);
    await fetch('/virtuallab/stop', { method: 'POST', body: fd });
    fetchVMs();
}

async function deleteVM(id) {
    if (!confirm('Delete this VM?')) return;
    const fd = new FormData(); fd.append('id', id);
    await fetch('/virtuallab/delete', { method: 'POST', body: fd });
    fetchVMs();
}
</script>
<?php $content = ob_get_clean(); require_once __DIR__ . '/../layout.php'; ?>
