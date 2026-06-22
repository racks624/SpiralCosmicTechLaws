<?php ob_start(); ?>
<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold cosmic-glow-text">🔗 Cosmic UrlMasker + Generator</h1>
    </div>
    <div class="cosmic-glass p-6 rounded-2xl mb-6">
        <input type="url" id="originalUrl" placeholder="Original URL (e.g., https://example.com)" class="mb-2">
        <input type="text" id="maskDomain" placeholder="Mask Domain (e.g., google.com)" class="mb-2">
        <input type="text" id="campaignId" placeholder="Campaign ID (optional)" class="mb-2">
        <input type="text" id="agentId" placeholder="Agent ID (optional)" class="mb-4">
        <button onclick="generateMaskedUrl()" class="cosmic-btn">Generate Masked URL</button>
        <div id="result" class="mt-4 p-3 cosmic-glass rounded break-all hidden"></div>
    </div>
    <div class="cosmic-glass p-4 rounded-2xl">
        <h3 class="text-lg font-semibold mb-2">Recent URLs</h3>
        <div class="table-wrapper">
            <table class="w-full text-sm cosmic-table">
                <thead><tr><th>Original URL</th><th>Masked URL</th><th>Clicks</th><th>Actions</th></tr></thead>
                <tbody id="urlsTable"><tr><td colspan="4" class="text-gray-400">Loading...</td></tr></tbody>
            </table>
        </div>
    </div>
</div>

<script>
async function generateMaskedUrl() {
    const url = document.getElementById('originalUrl').value;
    const mask = document.getElementById('maskDomain').value;
    const campaignId = document.getElementById('campaignId').value;
    const agentId = document.getElementById('agentId').value;
    if (!url || !mask) { alert('URL and mask domain required'); return; }
    const fd = new FormData();
    fd.append('url', url); fd.append('mask_domain', mask);
    if (campaignId) fd.append('campaign_id', campaignId);
    if (agentId) fd.append('agent_id', agentId);
    const res = await fetch('/urlmasker/generate', { method: 'POST', body: fd });
    const data = await res.json();
    const resultDiv = document.getElementById('result');
    resultDiv.innerHTML = `<strong>Masked URL:</strong> <a href="${data.masked_url}" target="_blank" class="text-green-400">${data.masked_url}</a><br>
        <strong>QR Code:</strong> <img src="${data.qr_code}" class="mt-2">`;
    resultDiv.classList.remove('hidden');
    fetchUrls();
}

async function fetchUrls() {
    const res = await fetch('/urlmasker/list');
    const data = await res.json();
    let html = '';
    if (data.urls && data.urls.length) {
        data.urls.forEach(u => {
            html += `<tr>
                <td class="p-2">${u.original_url}</td>
                <td class="p-2"><a href="${u.masked_url}" target="_blank" class="text-green-400">${u.masked_url}</a></td>
                <td class="p-2">${u.clicks}</td>
                <td class="p-2">
                    <button onclick="shareUrl(${u.id})" class="text-green-400 hover:text-green-300"><i class="fas fa-share-alt"></i></button>
                    <button onclick="editUrl(${u.id})" class="text-yellow-400 hover:text-yellow-300"><i class="fas fa-edit"></i></button>
                    <button onclick="deleteUrl(${u.id})" class="text-red-400 hover:text-red-300"><i class="fas fa-trash"></i></button>
                </td>
            </tr>`;
        });
    } else {
        html = '<tr><td colspan="4" class="text-gray-400">No URLs generated.</td></tr>';
    }
    document.getElementById('urlsTable').innerHTML = html;
}
fetchUrls();

async function shareUrl(id) {
    const res = await fetch(`/urlmasker/share?id=${id}`);
    const data = await res.json();
    alert('Shareable link: ' + data.share_url);
}

async function editUrl(id) {
    const newUrl = prompt('Enter new original URL:');
    if (!newUrl) return;
    const fd = new FormData(); fd.append('id', id); fd.append('original_url', newUrl);
    await fetch('/urlmasker/edit', { method: 'POST', body: fd });
    fetchUrls();
}

async function deleteUrl(id) {
    if (!confirm('Delete this masked URL?')) return;
    const fd = new FormData(); fd.append('id', id);
    await fetch('/urlmasker/delete', { method: 'POST', body: fd });
    fetchUrls();
}
</script>
<?php $content = ob_get_clean(); require_once __DIR__ . '/../layout.php'; ?>
