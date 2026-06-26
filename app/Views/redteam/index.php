<?php ob_start(); ?>
<div>
    <div class="flex justify-between items-center mb-6 flex-wrap gap-2">
        <h1 class="text-3xl font-bold cosmic-glow-text">🔴 RedTeam Operations</h1>
        <div class="flex gap-2">
            <button onclick="openAddTargetModal()" class="cosmic-btn"><i class="fas fa-plus"></i> Add Target</button>
            <button onclick="openUploadModal()" class="cosmic-btn"><i class="fas fa-upload"></i> Upload CSV</button>
            <button onclick="openNmapModal()" class="cosmic-btn"><i class="fas fa-file-import"></i> Import Nmap</button>
            <button onclick="openReportModal()" class="cosmic-btn"><i class="fas fa-file-alt"></i> Report</button>
            <button onclick="fetchTargets()" class="cosmic-btn"><i class="fas fa-sync"></i> Refresh</button>
        </div>
    </div>

    <!-- Targets Table -->
    <div class="cosmic-glass p-4 rounded-2xl overflow-auto">
        <div class="table-wrapper">
            <table class="w-full text-sm cosmic-table" id="targetsTable">
                <thead>
                    <tr><th class="text-left p-2">Name</th><th>Value</th><th>Status</th><th>Findings</th><th class="text-right">Actions</th></tr>
                </thead>
                <tbody id="targetsBody">
                    <tr><td colspan="5" class="text-center text-gray-400">Loading targets...</td></tr>
                </tbody>
            </table>
        </div>
        <div id="paginationControls" class="mt-4 flex justify-between items-center"></div>
    </div>
</div>

<!-- ============================================================ -->
<!-- ====== MODALS ====== -->

<!-- Modal: Add Target -->
<div id="addTargetModal" class="modal-overlay">
    <div class="modal-content">
        <h2 class="text-xl mb-4 cosmic-glow-text">New Target</h2>
        <form id="addTargetForm">
            <div class="mb-3"><label>Name</label><input type="text" name="name" class="cosmic-input" required></div>
            <div class="mb-3"><label>Type</label><select name="target_type" class="cosmic-input"><option value="ip">IP</option><option value="domain">Domain</option><option value="url">URL</option></select></div>
            <div class="mb-3"><label>Value</label><input type="text" name="target_value" class="cosmic-input" required></div>
            <div class="mb-3"><label>Description</label><textarea name="description" class="cosmic-input" rows="3"></textarea></div>
            <div class="flex gap-2 mt-4">
                <button type="submit" class="cosmic-btn flex-1">Add</button>
                <button type="button" onclick="closeModal('addTargetModal')" class="cosmic-btn flex-1">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Upload CSV -->
<div id="uploadModal" class="modal-overlay">
    <div class="modal-content">
        <h2 class="text-xl mb-4 cosmic-glow-text">Upload CSV</h2>
        <form id="uploadForm" enctype="multipart/form-data">
            <div class="mb-3"><label>CSV File</label><input type="file" name="csv_file" accept=".csv" class="cosmic-input" required></div>
            <div class="flex gap-2 mt-4">
                <button type="submit" class="cosmic-btn flex-1">Upload</button>
                <button type="button" onclick="closeModal('uploadModal')" class="cosmic-btn flex-1">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Import Nmap -->
<div id="nmapModal" class="modal-overlay">
    <div class="modal-content">
        <h2 class="text-xl mb-4 cosmic-glow-text">Import Nmap XML</h2>
        <form id="nmapForm" enctype="multipart/form-data">
            <div class="mb-3"><label>Nmap XML File</label><input type="file" name="nmap_xml" accept=".xml" class="cosmic-input" required></div>
            <div class="mb-3"><label>Target ID (optional – auto‑create if empty)</label><input type="text" name="target_id" class="cosmic-input" placeholder="Leave empty to auto‑create"></div>
            <div class="flex gap-2 mt-4">
                <button type="submit" class="cosmic-btn flex-1">Import</button>
                <button type="button" onclick="closeModal('nmapModal')" class="cosmic-btn flex-1">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Generate Report -->
<div id="reportModal" class="modal-overlay">
    <div class="modal-content">
        <h2 class="text-xl mb-4 cosmic-glow-text">Generate Report</h2>
        <form id="reportForm">
            <div class="mb-3"><label>Campaign Name</label><input type="text" name="campaign_name" class="cosmic-input" placeholder="RedTeam Assessment"></div>
            <div class="mb-3"><label>Target ID (optional)</label><input type="text" name="target_id" class="cosmic-input" placeholder="Leave empty for all targets"></div>
            <div class="mb-3"><label>Format</label><select name="format" class="cosmic-input"><option value="html">HTML</option><option value="pdf">PDF</option></select></div>
            <div class="flex gap-2 mt-4">
                <button type="submit" class="cosmic-btn flex-1">Generate</button>
                <button type="button" onclick="closeModal('reportModal')" class="cosmic-btn flex-1">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: View/Edit -->
<div id="viewEditModal" class="modal-overlay">
    <div class="modal-content">
        <h2 id="veTitle" class="text-xl mb-4 cosmic-glow-text">Target Details</h2>
        <div id="veContent"></div>
        <div class="flex gap-2 mt-4">
            <button onclick="saveEdit()" class="cosmic-btn flex-1">Save</button>
            <button type="button" onclick="closeModal('viewEditModal')" class="cosmic-btn flex-1">Close</button>
        </div>
    </div>
</div>

<!-- Modal: Live Scan Log -->
<div id="liveLogModal" class="modal-overlay">
    <div class="modal-content">
        <h2 class="text-xl mb-4 cosmic-glow-text">Scan Log</h2>
        <div id="liveLogContent" class="bg-black/50 p-4 rounded-xl h-64 overflow-y-auto font-mono text-sm text-green-300"></div>
        <div class="flex gap-2 mt-4">
            <button onclick="closeModal('liveLogModal')" class="cosmic-btn flex-1">Close</button>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toast" class="fixed bottom-6 right-6 cosmic-glass p-4 rounded-xl hidden z-50"></div>

<script>
// ============================================================
// ===== HELPERS =====
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = 'fixed bottom-6 right-6 cosmic-glass p-4 rounded-xl z-50';
    if (type === 'error') toast.style.borderColor = '#ef4444';
    else toast.style.borderColor = '#00ff88';
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 4000);
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

function openModal(id) {
    document.getElementById(id).classList.add('active');
}

function openAddTargetModal() { openModal('addTargetModal'); }
function openUploadModal() { openModal('uploadModal'); }
function openNmapModal() { openModal('nmapModal'); }
function openReportModal() { openModal('reportModal'); }

// ============================================================
// ===== FETCH TARGETS =====
let currentPage = 1;

async function fetchTargets(page = 1) {
    currentPage = page;
    const res = await fetch(`/redteam/targets?page=${page}`);
    const data = await res.json();
    let html = '';
    if (!data.targets || data.targets.length === 0) {
        html = '<tr><td colspan="5" class="text-center text-gray-400">No targets found.</td></tr>';
    } else {
        data.targets.forEach(t => {
            html += `<tr>
                <td class="p-2">${t.name}</td>
                <td class="p-2">${t.target_value}</td>
                <td class="p-2"><span class="cosmic-badge ${t.status=='completed'?'border-green-400 text-green-400':(t.status=='scanning'?'border-yellow-400 text-yellow-400':'border-gray-400 text-gray-400')}">${t.status}</span></td>
                <td class="p-2"><span class="bg-green-500/20 px-2 rounded">${t.findings_count || 0}</span></td>
                <td class="p-2 text-right">
                    <button onclick="viewTarget(${t.id})" class="text-blue-400 hover:text-blue-300" title="View"><i class="fas fa-eye"></i></button>
                    <button onclick="editTarget(${t.id})" class="text-yellow-400 hover:text-yellow-300" title="Edit"><i class="fas fa-edit"></i></button>
                    <button onclick="runScan(${t.id})" class="text-purple-400 hover:text-purple-300" title="Run Scan"><i class="fas fa-play"></i></button>
                    <button onclick="liveLog(${t.id})" class="text-indigo-400 hover:text-indigo-300" title="Live Log"><i class="fas fa-terminal"></i></button>
                    <button onclick="deleteTarget(${t.id})" class="text-red-400 hover:text-red-300" title="Delete"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
    }
    document.getElementById('targetsBody').innerHTML = html;
    // Pagination
    const totalPages = data.pages || 0;
    let pagHtml = '';
    if (totalPages > 1) {
        pagHtml += '<div class="flex gap-2">';
        for (let i = 1; i <= totalPages; i++) {
            pagHtml += `<button onclick="fetchTargets(${i})" class="cosmic-btn ${i === page ? 'active-tab' : ''}">${i}</button>`;
        }
        pagHtml += '</div>';
        pagHtml += `<span class="text-green-300">Total: ${data.total} targets</span>`;
    }
    document.getElementById('paginationControls').innerHTML = pagHtml;
}
fetchTargets(1);

// ============================================================
// ===== ADD TARGET =====
document.getElementById('addTargetForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const res = await fetch('/redteam/add', { method: 'POST', body: fd });
    if (res.ok) { closeModal('addTargetModal'); fetchTargets(currentPage); showToast('Target added'); }
    else { const data = await res.json(); showToast(data.error || 'Error', 'error'); }
});

// ============================================================
// ===== UPLOAD CSV =====
document.getElementById('uploadForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const res = await fetch('/redteam/upload', { method: 'POST', body: fd });
    if (res.ok) { closeModal('uploadModal'); fetchTargets(currentPage); showToast('CSV imported'); }
    else { const data = await res.json(); showToast(data.error || 'Error', 'error'); }
});

// ============================================================
// ===== IMPORT Nmap =====
document.getElementById('nmapForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const res = await fetch('/redteam/import-nmap', { method: 'POST', body: fd });
    if (res.ok) { closeModal('nmapModal'); fetchTargets(currentPage); showToast('Nmap imported'); }
    else { const data = await res.json(); showToast(data.error || 'Error', 'error'); }
});

// ============================================================
// ===== REPORT =====
document.getElementById('reportForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const format = fd.get('format');
    if (format === 'pdf') {
        // PDF will download via new window
        const url = '/redteam/report?' + new URLSearchParams(fd);
        window.open(url, '_blank');
    } else {
        window.open('/redteam/report?' + new URLSearchParams(fd), '_blank');
    }
    closeModal('reportModal');
});

// ============================================================
// ===== VIEW / EDIT =====
async function viewTarget(id) {
    const res = await fetch(`/redteam/view?id=${id}`);
    const data = await res.json();
    const t = data.target;
    let html = `<div class="space-y-2 text-sm">
        <p><strong>Name:</strong> ${t.name}</p>
        <p><strong>Value:</strong> ${t.target_value}</p>
        <p><strong>Status:</strong> ${t.status}</p>
        <p><strong>Description:</strong> ${t.description || 'N/A'}</p>
        <hr><h4>Findings:</h4>`;
    if (data.findings && data.findings.length) {
        data.findings.forEach(f => { html += `<div class="border-b border-white/10 py-1">[${f.severity}] ${f.title}</div>`; });
    } else { html += '<p>No findings.</p>'; }
    html += '</div>';
    document.getElementById('veContent').innerHTML = html;
    document.getElementById('veTitle').innerText = 'View Target';
    openModal('viewEditModal');
    window._editId = null;
}

async function editTarget(id) {
    const res = await fetch(`/redteam/view?id=${id}`);
    const data = await res.json();
    const t = data.target;
    let html = `
        <input type="hidden" id="editId" value="${t.id}">
        <div class="mb-3"><label>Name</label><input type="text" id="editName" value="${t.name}" class="cosmic-input"></div>
        <div class="mb-3"><label>Type</label><select id="editType" class="cosmic-input">
            <option value="ip" ${t.target_type=='ip'?'selected':''}>IP</option>
            <option value="domain" ${t.target_type=='domain'?'selected':''}>Domain</option>
            <option value="url" ${t.target_type=='url'?'selected':''}>URL</option>
        </select></div>
        <div class="mb-3"><label>Value</label><input type="text" id="editValue" value="${t.target_value}" class="cosmic-input"></div>
        <div class="mb-3"><label>Description</label><textarea id="editDesc" class="cosmic-input">${t.description || ''}</textarea></div>
        <div class="mb-3"><label>Status</label><select id="editStatus" class="cosmic-input">
            <option value="pending" ${t.status=='pending'?'selected':''}>Pending</option>
            <option value="scanning" ${t.status=='scanning'?'selected':''}>Scanning</option>
            <option value="completed" ${t.status=='completed'?'selected':''}>Completed</option>
            <option value="failed" ${t.status=='failed'?'selected':''}>Failed</option>
        </select></div>
    `;
    document.getElementById('veContent').innerHTML = html;
    document.getElementById('veTitle').innerText = 'Edit Target';
    openModal('viewEditModal');
    window._editId = id;
}

async function saveEdit() {
    const id = window._editId || document.getElementById('editId').value;
    if (!id) return;
    const data = {
        id: id,
        name: document.getElementById('editName').value,
        target_type: document.getElementById('editType').value,
        target_value: document.getElementById('editValue').value,
        description: document.getElementById('editDesc').value,
        status: document.getElementById('editStatus').value
    };
    const res = await fetch('/redteam/edit', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    if (res.ok) { closeModal('viewEditModal'); fetchTargets(currentPage); showToast('Target updated'); }
    else { const err = await res.json(); showToast(err.error || 'Error', 'error'); }
}

// ============================================================
// ===== RUN SCAN =====
async function runScan(id) {
    const fd = new FormData(); fd.append('target_id', id);
    const res = await fetch('/redteam/scan', { method: 'POST', body: fd });
    const data = await res.json();
    if (res.ok) {
        showToast(`Scan started (ID: ${data.scan_id})`);
        // Auto open live log
        liveLog(id, data.scan_id);
    } else {
        showToast(data.error || 'Error starting scan', 'error');
    }
}

// ============================================================
// ===== LIVE LOG (SSE) =====
let eventSource = null;

function liveLog(targetId, scanId = null) {
    // First, find the latest scan for this target if scanId not provided
    if (!scanId) {
        fetch(`/redteam/scans?target_id=${targetId}`)
            .then(res => res.json())
            .then(data => {
                if (data.scans && data.scans.length > 0) {
                    const lastScan = data.scans[0];
                    openLiveLog(lastScan.id);
                } else {
                    showToast('No scans found for this target.', 'error');
                }
            });
        return;
    }
    openLiveLog(scanId);
}

function openLiveLog(scanId) {
    const content = document.getElementById('liveLogContent');
    content.innerHTML = '<div class="text-gray-400">Connecting to scan log...</div>';
    openModal('liveLogModal');

    if (eventSource) {
        eventSource.close();
    }

    eventSource = new EventSource(`/redteam/scan-log/${scanId}`);
    eventSource.onmessage = function(event) {
        const data = JSON.parse(event.data);
        if (data.type === 'done') {
            content.innerHTML += '<div class="text-yellow-400">[Scan completed]</div>';
            eventSource.close();
            return;
        }
        const color = data.type === 'error' ? 'text-red-400' : 'text-green-300';
        content.innerHTML += `<div class="${color}">[${data.time}] ${data.message}</div>`;
        content.scrollTop = content.scrollHeight;
    };
    eventSource.onerror = function() {
        content.innerHTML += '<div class="text-red-400">[Disconnected]</div>';
        eventSource.close();
    };
}

// When closing the modal, close the SSE connection
document.getElementById('liveLogModal').addEventListener('click', function(e) {
    if (e.target === this) {
        if (eventSource) { eventSource.close(); eventSource = null; }
        closeModal('liveLogModal');
    }
});

// ============================================================
// ===== DELETE =====
async function deleteTarget(id) {
    if (!confirm('Delete target and all associated data?')) return;
    const fd = new FormData(); fd.append('target_id', id);
    const res = await fetch('/redteam/delete', { method: 'POST', body: fd });
    if (res.ok) { fetchTargets(currentPage); showToast('Target deleted'); }
    else { const data = await res.json(); showToast(data.error || 'Error', 'error'); }
}
</script>

<style>
.modal-overlay .modal-content { max-width: 800px; }
.modal-overlay .modal-content textarea { resize: vertical; }
.active-tab { background: linear-gradient(135deg, rgba(0,255,136,0.2), rgba(251,191,36,0.2)) !important; border-color: #00ff88 !important; }
</style>
<?php $content = ob_get_clean(); require_once __DIR__ . '/../layout.php'; ?>
