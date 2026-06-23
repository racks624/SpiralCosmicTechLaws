<?php ob_start(); ?>
<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold cosmic-glow-text">📁 Files Management</h1>
    </div>

    <!-- Category Selector -->
    <div class="mb-4 flex flex-wrap gap-2">
        <select id="categorySelect" class="cosmic-input w-48">
            <option value="payloads">Payloads</option>
            <option value="templates">Email Templates</option>
            <option value="sms">SMS Logs</option>
            <option value="urls">Masked URLs</option>
            <option value="c2">C2 Agents</option>
            <option value="virtuallab">Virtual Machines</option>
            <option value="targets">RedTeam Targets</option>
            <option value="findings">RedTeam Findings</option>
        </select>
        <button onclick="loadItems()" class="cosmic-btn"><i class="fas fa-search"></i> Load</button>
        <button onclick="loadItems()" class="cosmic-btn"><i class="fas fa-sync"></i> Refresh</button>
    </div>

    <!-- Items Table -->
    <div class="cosmic-glass p-4 rounded-2xl overflow-auto">
        <div id="itemsContainer"><p class="text-gray-400">Select a category and click Load.</p></div>
    </div>
</div>

<!-- Modal: View/Edit -->
<div id="viewEditModal" class="modal-overlay">
    <div class="modal-content">
        <h2 id="veTitle" class="text-xl mb-4 cosmic-glow-text">Item Details</h2>
        <div id="veContent"></div>
        <div class="flex gap-2 mt-4">
            <button onclick="saveEdit()" class="cosmic-btn flex-1">Save</button>
            <button type="button" onclick="closeModal('viewEditModal')" class="cosmic-btn flex-1">Close</button>
        </div>
    </div>
</div>

<!-- Modal: Share -->
<div id="shareModal" class="modal-overlay">
    <div class="modal-content">
        <h2 class="text-xl font-bold cosmic-glow-text mb-4">Shareable Link</h2>
        <p class="text-green-300 text-sm mb-2">Copy the link below to share this item.</p>
        <div class="flex flex-wrap gap-2">
            <input type="text" id="shareLinkInput" class="cosmic-input flex-1" readonly>
            <button onclick="copyShareLink()" class="cosmic-btn"><i class="fas fa-copy"></i> Copy</button>
        </div>
        <div class="flex flex-wrap gap-2 mt-4">
            <button onclick="closeModal('shareModal')" class="cosmic-btn flex-1">Close</button>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toast" class="fixed bottom-6 right-6 cosmic-glass p-4 rounded-xl hidden z-50"></div>

<script>
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

let currentCategory = '';
let currentItemId = null;

async function loadItems() {
    const category = document.getElementById('categorySelect').value;
    currentCategory = category;
    const res = await fetch(`/files/list?category=${category}`);
    const data = await res.json();
    let html = '<div class="table-wrapper"><table class="w-full text-sm cosmic-table"><thead><tr>';
    // Determine columns based on category
    let cols = [];
    if (category === 'payloads') cols = ['id', 'os', 'lhost', 'lport', 'filename', 'status'];
    else if (category === 'templates') cols = ['id', 'name', 'subject', 'ab_group'];
    else if (category === 'sms') cols = ['id', 'phone', 'message', 'status'];
    else if (category === 'urls') cols = ['id', 'original_url', 'masked_url', 'clicks'];
    else if (category === 'c2') cols = ['id', 'hostname', 'os', 'ip_address', 'status'];
    else if (category === 'virtuallab') cols = ['id', 'machine_id', 'os', 'status'];
    else if (category === 'targets') cols = ['id', 'name', 'target_value', 'status'];
    else if (category === 'findings') cols = ['id', 'severity', 'title', 'cve_id'];
    cols.forEach(c => html += `<th>${c.replace('_',' ').toUpperCase()}</th>`);
    html += '<th>Actions</th></tr></thead><tbody>';
    if (data.items && data.items.length) {
        data.items.forEach(item => {
            html += '<tr>';
            cols.forEach(c => {
                html += `<td class="p-2">${item[c] || 'N/A'}</td>`;
            });
            html += `<td class="p-2">
                <button onclick="viewItem('${category}', ${item.id})" class="text-blue-400 hover:text-blue-300"><i class="fas fa-eye"></i></button>
                <button onclick="editItem('${category}', ${item.id})" class="text-yellow-400 hover:text-yellow-300"><i class="fas fa-edit"></i></button>
                <button onclick="shareItem('${category}', ${item.id})" class="text-green-400 hover:text-green-300"><i class="fas fa-share-alt"></i></button>
                <button onclick="downloadItem('${category}', ${item.id})" class="text-purple-400 hover:text-purple-300"><i class="fas fa-download"></i></button>
                <button onclick="deleteItem('${category}', ${item.id})" class="text-red-400 hover:text-red-300"><i class="fas fa-trash"></i></button>
            </td>`;
            html += '</tr>';
        });
    } else {
        html += `<tr><td colspan="${cols.length+1}" class="text-center text-gray-400">No items found.</td></tr>`;
    }
    html += '</tbody></table></div>';
    document.getElementById('itemsContainer').innerHTML = html;
}

// ---- View ----
async function viewItem(category, id) {
    const res = await fetch(`/files/show?category=${category}&id=${id}`);
    const data = await res.json();
    const item = data.item;
    let html = '<div class="space-y-2 text-sm">';
    for (let key in item) {
        if (key === 'id') continue;
        html += `<p><strong>${key.replace('_',' ').toUpperCase()}:</strong> ${item[key] || 'N/A'}</p>`;
    }
    html += '</div>';
    document.getElementById('veContent').innerHTML = html;
    document.getElementById('veTitle').innerText = 'View Item';
    openModal('viewEditModal');
    currentItemId = null;
}

// ---- Edit ----
async function editItem(category, id) {
    const res = await fetch(`/files/show?category=${category}&id=${id}`);
    const data = await res.json();
    const item = data.item;
    let html = `<input type="hidden" id="editCategory" value="${category}"><input type="hidden" id="editId" value="${id}">`;
    for (let key in item) {
        if (key === 'id') continue;
        if (key === 'created_at' || key === 'updated_at') continue;
        html += `<div class="mb-3"><label>${key.replace('_',' ').toUpperCase()}</label>`;
        if (typeof item[key] === 'string' && item[key].length > 100) {
            html += `<textarea id="edit_${key}" class="cosmic-input" rows="3">${item[key] || ''}</textarea>`;
        } else {
            html += `<input type="text" id="edit_${key}" class="cosmic-input" value="${item[key] || ''}">`;
        }
        html += '</div>';
    }
    document.getElementById('veContent').innerHTML = html;
    document.getElementById('veTitle').innerText = 'Edit Item';
    openModal('viewEditModal');
    currentItemId = id;
}

// ---- Save Edit ----
async function saveEdit() {
    const id = document.getElementById('editId').value;
    const category = document.getElementById('editCategory').value;
    if (!id) return;
    const form = document.getElementById('veContent');
    const inputs = form.querySelectorAll('input, textarea');
    const data = { id: id, category: category };
    inputs.forEach(input => {
        const key = input.id.replace('edit_', '');
        data[key] = input.value;
    });
    const res = await fetch('/files/edit', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });
    if (res.ok) {
        showToast('✅ Item updated');
        closeModal('viewEditModal');
        loadItems();
    } else {
        showToast('Error updating item', 'error');
    }
}

// ---- Share ----
async function shareItem(category, id) {
    const res = await fetch(`/files/share?category=${category}&id=${id}`);
    const data = await res.json();
    document.getElementById('shareLinkInput').value = data.share_url;
    openModal('shareModal');
}

function copyShareLink() {
    const input = document.getElementById('shareLinkInput');
    input.select();
    document.execCommand('copy');
    showToast('📋 Link copied to clipboard');
}

// ---- Download ----
function downloadItem(category, id) {
    window.location.href = `/files/download?category=${category}&id=${id}`;
}

// ---- Delete ----
async function deleteItem(category, id) {
    if (!confirm('Delete this item?')) return;
    const fd = new FormData();
    fd.append('category', category);
    fd.append('id', id);
    const res = await fetch('/files/delete', { method: 'POST', body: fd });
    if (res.ok) {
        showToast('🗑️ Item deleted');
        loadItems();
    } else {
        showToast('Error deleting', 'error');
    }
}

// ---- Load on category change ----
document.getElementById('categorySelect').addEventListener('change', loadItems);
</script>
<?php $content = ob_get_clean(); require_once __DIR__ . '/../layout.php'; ?>
