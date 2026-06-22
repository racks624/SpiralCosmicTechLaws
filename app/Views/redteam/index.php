<?php ob_start(); ?>
<div>
    <div class="flex justify-between items-center mb-6 flex-wrap gap-2">
        <h1 class="text-3xl font-bold cosmic-glow-text">🔴 RedTeam Operations</h1>
        <div class="flex gap-2">
            <button onclick="document.getElementById('addTargetModal').classList.remove('hidden')" class="cosmic-btn">
                <i class="fas fa-plus"></i> Add Target
            </button>
            <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" class="cosmic-btn">
                <i class="fas fa-upload"></i> Upload CSV
            </button>
        </div>
    </div>

    <div class="cosmic-glass p-4 rounded-2xl overflow-auto">
        <div class="table-wrapper">
            <table class="w-full text-sm cosmic-table">
                <thead>
                    <tr><th class="text-left p-2">Name</th><th>Value</th><th>Status</th><th>Findings</th><th class="text-right">Actions</th></tr>
                </thead>
                <tbody id="targetsTable">
                    <?php foreach ($targets as $t): ?>
                    <tr>
                        <td class="p-2"><?= htmlspecialchars($t['name']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($t['target_value']) ?></td>
                        <td class="p-2"><span class="cosmic-badge <?= $t['status']=='completed'?'border-green-400 text-green-400':($t['status']=='scanning'?'border-yellow-400 text-yellow-400':'border-gray-400 text-gray-400') ?>"><?= $t['status'] ?></span></td>
                        <td class="p-2"><span class="bg-green-500/20 px-2 rounded"><?= $t['findings_count'] ?? 0 ?></span></td>
                        <td class="p-2 text-right">
                            <button onclick="viewTarget(<?= $t['id'] ?>)" class="text-blue-400 hover:text-blue-300" title="View"><i class="fas fa-eye"></i></button>
                            <button onclick="editTarget(<?= $t['id'] ?>)" class="text-yellow-400 hover:text-yellow-300" title="Edit"><i class="fas fa-edit"></i></button>
                            <button onclick="runScan(<?= $t['id'] ?>)" class="text-purple-400 hover:text-purple-300" title="Run Scan"><i class="fas fa-play"></i></button>
                            <button onclick="deleteTarget(<?= $t['id'] ?>)" class="text-red-400 hover:text-red-300" title="Delete"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Target Modal -->
<div id="addTargetModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50">
    <div class="cosmic-glass p-6 rounded-2xl w-96 max-w-full">
        <h2 class="text-xl mb-4 cosmic-glow-text">New Target</h2>
        <form id="addTargetForm">
            <input type="text" name="name" placeholder="Name" class="mb-2" required>
            <select name="target_type" class="mb-2">
                <option value="ip">IP</option><option value="domain">Domain</option><option value="url">URL</option>
            </select>
            <input type="text" name="target_value" placeholder="Value" class="mb-2" required>
            <textarea name="description" placeholder="Description" class="mb-4"></textarea>
            <div class="flex gap-2">
                <button type="submit" class="cosmic-btn flex-1">Add</button>
                <button type="button" onclick="document.getElementById('addTargetModal').classList.add('hidden')" class="cosmic-btn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Upload CSV Modal -->
<div id="uploadModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50">
    <div class="cosmic-glass p-6 rounded-2xl w-96 max-w-full">
        <h2 class="text-xl mb-4 cosmic-glow-text">Upload CSV</h2>
        <form id="uploadForm" enctype="multipart/form-data">
            <input type="file" name="csv_file" accept=".csv" class="mb-4" required>
            <div class="flex gap-2">
                <button type="submit" class="cosmic-btn flex-1">Upload</button>
                <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" class="cosmic-btn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- View/Edit Modal -->
<div id="viewEditModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50">
    <div class="cosmic-glass p-6 rounded-2xl w-96 max-w-full">
        <h2 id="veTitle" class="text-xl mb-4 cosmic-glow-text">Target Details</h2>
        <div id="veContent"></div>
        <div class="flex gap-2 mt-4">
            <button onclick="saveEdit()" class="cosmic-btn flex-1">Save</button>
            <button type="button" onclick="document.getElementById('viewEditModal').classList.add('hidden')" class="cosmic-btn">Close</button>
        </div>
    </div>
</div>

<script>
// Add Target
document.getElementById('addTargetForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const res = await fetch('/redteam/add', { method: 'POST', body: fd });
    if (res.ok) location.reload();
    else alert('Error adding target');
});

// Upload CSV
document.getElementById('uploadForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    const res = await fetch('/redteam/upload', { method: 'POST', body: fd });
    if (res.ok) location.reload();
    else alert('Upload failed');
});

// Run Scan
async function runScan(id) {
    const fd = new FormData(); fd.append('target_id', id);
    const res = await fetch('/redteam/scan', { method: 'POST', body: fd });
    const data = await res.json();
    alert(data.status || data.error);
    if (data.status) location.reload();
}

// View Target
async function viewTarget(id) {
    const res = await fetch(`/redteam/view?id=${id}`);
    const data = await res.json();
    let html = `<div class="space-y-2 text-sm">
        <p><strong>Name:</strong> ${data.target.name}</p>
        <p><strong>Value:</strong> ${data.target.target_value}</p>
        <p><strong>Status:</strong> ${data.target.status}</p>
        <p><strong>Description:</strong> ${data.target.description || 'N/A'}</p>
        <hr><h4>Findings:</h4>`;
    if (data.findings && data.findings.length) {
        data.findings.forEach(f => {
            html += `<div class="border-b border-white/10 py-1">[${f.severity}] ${f.title}</div>`;
        });
    } else {
        html += '<p>No findings.</p>';
    }
    html += '</div>';
    document.getElementById('veContent').innerHTML = html;
    document.getElementById('veTitle').innerText = 'View Target';
    document.getElementById('viewEditModal').classList.remove('hidden');
    window._editId = null;
}

// Edit Target
async function editTarget(id) {
    const res = await fetch(`/redteam/view?id=${id}`);
    const data = await res.json();
    const t = data.target;
    let html = `
        <input type="hidden" id="editId" value="${t.id}">
        <div><label>Name</label><input type="text" id="editName" value="${t.name}" class="mb-2"></div>
        <div><label>Type</label><select id="editType" class="mb-2">
            <option value="ip" ${t.target_type=='ip'?'selected':''}>IP</option>
            <option value="domain" ${t.target_type=='domain'?'selected':''}>Domain</option>
            <option value="url" ${t.target_type=='url'?'selected':''}>URL</option>
        </select></div>
        <div><label>Value</label><input type="text" id="editValue" value="${t.target_value}" class="mb-2"></div>
        <div><label>Description</label><textarea id="editDesc" class="mb-2">${t.description || ''}</textarea></div>
        <div><label>Status</label><select id="editStatus" class="mb-2">
            <option value="pending" ${t.status=='pending'?'selected':''}>Pending</option>
            <option value="scanning" ${t.status=='scanning'?'selected':''}>Scanning</option>
            <option value="completed" ${t.status=='completed'?'selected':''}>Completed</option>
            <option value="failed" ${t.status=='failed'?'selected':''}>Failed</option>
        </select></div>
    `;
    document.getElementById('veContent').innerHTML = html;
    document.getElementById('veTitle').innerText = 'Edit Target';
    document.getElementById('viewEditModal').classList.remove('hidden');
    window._editId = id;
}

// Save Edit
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
    if (res.ok) location.reload();
    else alert('Error updating target');
}

// Delete Target
async function deleteTarget(id) {
    if (!confirm('Delete target and all associated data?')) return;
    const fd = new FormData(); fd.append('target_id', id);
    await fetch('/redteam/delete', { method: 'POST', body: fd });
    location.reload();
}
</script>
<?php $content = ob_get_clean(); require_once __DIR__ . '/../layout.php'; ?>
