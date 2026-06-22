<?php ob_start(); ?>
<div>
    <h1 class="text-3xl font-bold cosmic-glow-text mb-6">💀 Trojan/Payload Generator</h1>
    <div class="cosmic-glass p-6 rounded-2xl mb-6">
        <select id="payloadType" class="mb-2">
            <option value="windows">Windows (.bat)</option>
            <option value="linux">Linux (.sh)</option>
            <option value="android">Android (.java)</option>
        </select>
        <input type="text" id="lhost" placeholder="LHOST (your IP)" class="mb-2">
        <input type="number" id="lport" placeholder="LPORT" class="mb-2">
        <textarea id="notes" placeholder="Notes (optional)" class="mb-4"></textarea>
        <button onclick="generatePayload()" class="cosmic-btn"><i class="fas fa-bolt"></i> Generate</button>
        <div id="payloadOutput" class="mt-4 p-3 cosmic-glass rounded hidden">
            <strong>Payload (Base64):</strong><br>
            <textarea id="payloadText" class="w-full h-32 bg-black/30 rounded p-2 text-sm"></textarea><br>
            <button onclick="downloadPayload()" class="mt-2 cosmic-btn"><i class="fas fa-download"></i> Download</button>
        </div>
    </div>
    <div class="cosmic-glass p-4 rounded-2xl">
        <h3 class="text-lg font-semibold mb-2">Generated Payloads</h3>
        <div class="table-wrapper">
            <table class="w-full text-sm cosmic-table">
                <thead><tr><th>OS</th><th>LHOST</th><th>LPORT</th><th>Downloads</th><th>Actions</th></tr></thead>
                <tbody id="payloadsTable"><tr><td colspan="5" class="text-gray-400">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>
</div>

<script>
async function generatePayload() {
    const type = document.getElementById('payloadType').value;
    const lhost = document.getElementById('lhost').value;
    const lport = document.getElementById('lport').value;
    const notes = document.getElementById('notes').value;
    if (!lhost || !lport) { alert('LHOST and LPORT required'); return; }
    const fd = new FormData();
    fd.append('type', type); fd.append('lhost', lhost); fd.append('lport', lport); fd.append('notes', notes);
    const res = await fetch('/payload/generate', { method: 'POST', body: fd });
    const data = await res.json();
    document.getElementById('payloadText').value = data.payload;
    document.getElementById('payloadOutput').classList.remove('hidden');
    fetchPayloads();
}
function downloadPayload() {
    const payload = document.getElementById('payloadText').value;
    const blob = new Blob([atob(payload)], { type: 'text/plain' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'payload.bin';
    a.click();
}

async function fetchPayloads() {
    const res = await fetch('/payload/list');
    const data = await res.json();
    let html = '';
    if (data.payloads && data.payloads.length) {
        data.payloads.forEach(p => {
            html += `<tr>
                <td class="p-2">${p.os}</td>
                <td class="p-2">${p.lhost}</td>
                <td class="p-2">${p.lport}</td>
                <td class="p-2">${p.downloads || 0}</td>
                <td class="p-2">
                    <button onclick="viewPayload(${p.id})" class="text-blue-400 hover:text-blue-300"><i class="fas fa-eye"></i></button>
                    <button onclick="editPayload(${p.id})" class="text-yellow-400 hover:text-yellow-300"><i class="fas fa-edit"></i></button>
                    <button onclick="deletePayload(${p.id})" class="text-red-400 hover:text-red-300"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
    } else {
        html = '<tr><td colspan="5" class="text-gray-400">No payloads generated.</td></tr>';
    }
    document.getElementById('payloadsTable').innerHTML = html;
}
fetchPayloads();

async function viewPayload(id) {
    const res = await fetch(`/payload/view?id=${id}`);
    const data = await res.json();
    const p = data.payload;
    alert(`OS: ${p.os}\nLHOST: ${p.lhost}\nLPORT: ${p.lport}\nDownloads: ${p.downloads}\nNotes: ${p.notes || 'N/A'}\nStatus: ${p.status}`);
}

async function editPayload(id) {
    const notes = prompt('Enter new notes:');
    if (notes === null) return;
    const fd = new FormData(); fd.append('id', id); fd.append('notes', notes);
    await fetch('/payload/edit', { method: 'POST', body: fd });
    fetchPayloads();
}

async function deletePayload(id) {
    if (!confirm('Delete this payload?')) return;
    const fd = new FormData(); fd.append('id', id);
    await fetch('/payload/delete', { method: 'POST', body: fd });
    fetchPayloads();
}
</script>
<?php $content = ob_get_clean(); require_once __DIR__ . '/../layout.php'; ?>
