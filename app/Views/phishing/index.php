<?php ob_start(); ?>
<div>
    <h1 class="text-3xl font-bold gradient-text mb-6">🎣 Phishing Campaigns</h1>
    <div class="glass p-4 rounded-2xl">
        <button onclick="showCreateCampaign()" class="glass px-4 py-2 rounded mb-4"><i class="fas fa-plus"></i> New Campaign</button>
        <div id="campaignsList">Loading...</div>
    </div>
</div>
<script>
async function fetchCampaigns() {
    let res = await fetch('/phishing/campaigns');
    let data = await res.json();
    let html = '<div class="space-y-2">';
    (data.campaigns || []).forEach(c => { html += `<div class="glass p-3 rounded">${c.name} - Clicks: ${c.clicks}</div>`; });
    html += '</div>';
    document.getElementById('campaignsList').innerHTML = html;
}
function showCreateCampaign() {
    let name = prompt('Campaign name:');
    if(name) fetch('/phishing/campaign', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`name=${encodeURIComponent(name)}`}).then(()=>fetchCampaigns());
}
fetchCampaigns();
</script>
<?php $content = ob_get_clean(); require_once __DIR__ . '/../layout.php'; ?>
