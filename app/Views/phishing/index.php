<?php ob_start(); ?>
<div>
    <div class="flex justify-between items-center mb-6 flex-wrap gap-2">
        <h1 class="text-3xl font-bold cosmic-glow-text">🎣 Phishing Campaigns</h1>
        <div class="flex gap-2">
            <button onclick="fetchCampaigns(1)" class="cosmic-btn"><i class="fas fa-sync"></i> Refresh</button>
            <button onclick="openCreateCampaignModal()" class="cosmic-btn"><i class="fas fa-plus"></i> New Campaign</button>
        </div>
    </div>

    <!-- Search & Pagination -->
    <div class="mb-4 flex flex-wrap gap-2">
        <input type="text" id="searchInput" placeholder="Search campaigns..." class="cosmic-input flex-1 min-w-[200px]">
        <button onclick="fetchCampaigns(1)" class="cosmic-btn"><i class="fas fa-search"></i> Search</button>
    </div>

    <!-- Tabs -->
    <div class="flex flex-wrap gap-2 mb-4">
        <button class="cosmic-btn active-tab" data-tab="campaigns">Campaigns</button>
        <button class="cosmic-btn" data-tab="templates">Templates</button>
        <button class="cosmic-btn" data-tab="social">Social</button>
        <button class="cosmic-btn" data-tab="sms">SMS</button>
        <button class="cosmic-btn" data-tab="tracking">Tracking</button>
        <button class="cosmic-btn" data-tab="analytics">Analytics</button>
    </div>

    <!-- Tab Content -->
    <div id="tab-campaigns" class="tab-content cosmic-glass p-4 rounded-2xl overflow-auto">
        <div id="campaignsContainer"><p class="text-gray-400">Loading campaigns...</p></div>
        <div id="paginationControls" class="mt-4 flex justify-between items-center"></div>
    </div>
    <div id="tab-templates" class="tab-content hidden cosmic-glass p-4 rounded-2xl">
        <div class="mb-2 flex flex-wrap gap-2">
            <input type="text" id="templateCampaignId" placeholder="Campaign ID" class="cosmic-input w-32">
            <button onclick="fetchTemplates()" class="cosmic-btn"><i class="fas fa-search"></i> Load</button>
            <button onclick="openCreateTemplateModal()" class="cosmic-btn"><i class="fas fa-plus"></i> New Template</button>
        </div>
        <div id="templatesList"><p class="text-gray-400">Enter Campaign ID to load templates.</p></div>
    </div>
    <div id="tab-social" class="tab-content hidden cosmic-glass p-4 rounded-2xl">
        <div class="mb-2 flex flex-wrap gap-2">
            <input type="text" id="socialCampaignId" placeholder="Campaign ID" class="cosmic-input w-32">
            <button onclick="fetchSocial()" class="cosmic-btn"><i class="fas fa-search"></i> Load</button>
            <button onclick="openCreateSocialModal()" class="cosmic-btn"><i class="fas fa-plus"></i> New Social Post</button>
        </div>
        <div id="socialList"><p class="text-gray-400">Enter Campaign ID to load posts.</p></div>
    </div>
    <div id="tab-sms" class="tab-content hidden cosmic-glass p-4 rounded-2xl">
        <div class="mb-2 flex flex-wrap gap-2">
            <input type="text" id="smsCampaignId" placeholder="Campaign ID" class="cosmic-input w-32">
            <button onclick="fetchSms()" class="cosmic-btn"><i class="fas fa-search"></i> Load</button>
            <button onclick="openSendSmsModal()" class="cosmic-btn"><i class="fas fa-plus"></i> Send SMS</button>
        </div>
        <div id="smsList"><p class="text-gray-400">Enter Campaign ID to load SMS logs.</p></div>
    </div>
    <div id="tab-tracking" class="tab-content hidden cosmic-glass p-4 rounded-2xl">
        <div class="mb-2 flex flex-wrap gap-2">
            <input type="text" id="trackingCampaignId" placeholder="Campaign ID" class="cosmic-input w-32">
            <select id="trackingType" class="cosmic-input w-32">
                <option value="">All types</option>
                <option value="open">Open</option>
                <option value="click">Click</option>
                <option value="convert">Conversion</option>
            </select>
            <button onclick="fetchTracks()" class="cosmic-btn"><i class="fas fa-search"></i> Load</button>
        </div>
        <div id="trackingData"><p class="text-gray-400">Enter Campaign ID to load tracking events.</p></div>
    </div>
    <div id="tab-analytics" class="tab-content hidden cosmic-glass p-4 rounded-2xl">
        <div class="mb-2 flex flex-wrap gap-2">
            <input type="text" id="analyticsCampaignId" placeholder="Campaign ID (leave empty for overall)" class="cosmic-input w-48">
            <button onclick="fetchAnalytics()" class="cosmic-btn"><i class="fas fa-chart-line"></i> Load</button>
        </div>
        <div id="analyticsData"><p class="text-gray-400">Enter Campaign ID for detailed analytics.</p></div>
    </div>
</div>

<!-- ============================================================ -->
<!-- ====== ALL COSMIC MODALS (SCROLLABLE) ====== -->

<!-- Modal: Campaign (Create/Edit/View) -->
<div id="campaignModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="cosmic-glass-modal w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6">
        <h2 id="campaignModalTitle" class="text-2xl font-bold cosmic-glow-text mb-4">Campaign Details</h2>
        <form id="campaignForm" class="w-full">
            <input type="hidden" id="campaignFormId">
            <div class="mb-3">
                <label class="block text-green-400 font-semibold mb-1">Name</label>
                <input type="text" id="campaignFormName" class="cosmic-input w-full" required>
            </div>
            <div class="mb-3">
                <label class="block text-green-400 font-semibold mb-1">Type</label>
                <select id="campaignFormType" class="cosmic-input w-full">
                    <option value="phishing">Phishing</option>
                    <option value="marketing">Marketing</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="block text-green-400 font-semibold mb-1">Platform</label>
                <select id="campaignFormPlatform" class="cosmic-input w-full">
                    <option value="email">Email</option>
                    <option value="sms">SMS</option>
                    <option value="social">Social</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="block text-green-400 font-semibold mb-1">From Name</label>
                <input type="text" id="campaignFormFromName" class="cosmic-input w-full">
            </div>
            <div class="mb-3">
                <label class="block text-green-400 font-semibold mb-1">From Email</label>
                <input type="email" id="campaignFormFromEmail" class="cosmic-input w-full">
            </div>
            <div class="mb-3">
                <label class="block text-green-400 font-semibold mb-1">Reply-To</label>
                <input type="email" id="campaignFormReplyTo" class="cosmic-input w-full">
            </div>
            <div class="mb-3">
                <label class="block text-green-400 font-semibold mb-1">Template (HTML)</label>
                <textarea id="campaignFormTemplate" class="cosmic-input w-full" rows="4"></textarea>
            </div>
            <div class="mb-3">
                <label class="block text-green-400 font-semibold mb-1">Targets (comma separated)</label>
                <input type="text" id="campaignFormTargets" class="cosmic-input w-full">
            </div>
            <div class="mb-3">
                <label class="block text-green-400 font-semibold mb-1">Status</label>
                <select id="campaignFormStatus" class="cosmic-input w-full">
                    <option value="draft">Draft</option>
                    <option value="active">Active</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
            <div class="flex flex-wrap gap-2 mt-4">
                <button type="submit" id="campaignFormSubmit" class="cosmic-btn flex-1 min-w-[100px]">Save</button>
                <button type="button" onclick="closeModal('campaignModal')" class="cosmic-btn flex-1 min-w-[100px]">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Confirm Delete -->
<div id="confirmModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="cosmic-glass-modal w-full max-w-md max-h-[90vh] overflow-y-auto p-6">
        <h2 id="confirmTitle" class="text-xl font-bold cosmic-glow-text mb-4">Confirm</h2>
        <p id="confirmMessage" class="text-green-300 mb-4">Are you sure?</p>
        <div class="flex flex-wrap gap-2">
            <button id="confirmYes" class="cosmic-btn flex-1 min-w-[100px]">Yes</button>
            <button onclick="closeModal('confirmModal')" class="cosmic-btn flex-1 min-w-[100px]">Cancel</button>
        </div>
    </div>
</div>

<!-- Modal: Share -->
<div id="shareModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="cosmic-glass-modal w-full max-w-md max-h-[90vh] overflow-y-auto p-6">
        <h2 class="text-xl font-bold cosmic-glow-text mb-4">Shareable Link</h2>
        <p class="text-green-300 text-sm mb-2">Copy the link below to share this campaign.</p>
        <div class="flex flex-wrap gap-2">
            <input type="text" id="shareLinkInput" class="cosmic-input flex-1 min-w-[150px]" readonly>
            <button onclick="copyShareLink()" class="cosmic-btn"><i class="fas fa-copy"></i> Copy</button>
        </div>
        <div class="flex flex-wrap gap-2 mt-4">
            <button onclick="closeModal('shareModal')" class="cosmic-btn flex-1 min-w-[100px]">Close</button>
        </div>
    </div>
</div>

<!-- Modal: Send Email -->
<div id="sendEmailModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="cosmic-glass-modal w-full max-w-md max-h-[90vh] overflow-y-auto p-6">
        <h2 class="text-xl font-bold cosmic-glow-text mb-4">Send Emails</h2>
        <form id="sendEmailForm" class="w-full">
            <input type="hidden" id="sendEmailCampaignId">
            <div class="mb-3">
                <label class="block text-green-400 font-semibold mb-1">SMTP Host</label>
                <input type="text" id="sendSmtpHost" placeholder="smtp.example.com" class="cosmic-input w-full">
            </div>
            <div class="mb-3">
                <label class="block text-green-400 font-semibold mb-1">SMTP Port</label>
                <input type="number" id="sendSmtpPort" placeholder="587" class="cosmic-input w-full">
            </div>
            <div class="mb-3">
                <label class="block text-green-400 font-semibold mb-1">SMTP User</label>
                <input type="text" id="sendSmtpUser" placeholder="user@example.com" class="cosmic-input w-full">
            </div>
            <div class="mb-3">
                <label class="block text-green-400 font-semibold mb-1">SMTP Password</label>
                <input type="password" id="sendSmtpPass" class="cosmic-input w-full">
            </div>
            <div class="flex flex-wrap gap-2 mt-4">
                <button type="submit" class="cosmic-btn flex-1 min-w-[100px]">Send</button>
                <button type="button" onclick="closeModal('sendEmailModal')" class="cosmic-btn flex-1 min-w-[100px]">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Template (Create/Edit) -->
<div id="templateModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="cosmic-glass-modal w-full max-w-lg max-h-[90vh] overflow-y-auto p-6">
        <h2 id="templateModalTitle" class="text-xl font-bold cosmic-glow-text mb-4">Template</h2>
        <form id="templateForm" class="w-full">
            <input type="hidden" id="templateFormId">
            <input type="hidden" id="templateFormCampaignId">
            <div class="mb-3">
                <label class="block text-green-400 font-semibold mb-1">Name</label>
                <input type="text" id="templateFormName" class="cosmic-input w-full" required>
            </div>
            <div class="mb-3">
                <label class="block text-green-400 font-semibold mb-1">Subject</label>
                <input type="text" id="templateFormSubject" class="cosmic-input w-full">
            </div>
            <div class="mb-3">
                <label class="block text-green-400 font-semibold mb-1">Body (HTML)</label>
                <textarea id="templateFormBody" class="cosmic-input w-full" rows="5"></textarea>
            </div>
            <div class="mb-3">
                <label class="block text-green-400 font-semibold mb-1">A/B Group</label>
                <select id="templateFormAbGroup" class="cosmic-input w-full">
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                </select>
            </div>
            <div class="flex flex-wrap gap-2 mt-4">
                <button type="submit" class="cosmic-btn flex-1 min-w-[100px]">Save</button>
                <button type="button" onclick="closeModal('templateModal')" class="cosmic-btn flex-1 min-w-[100px]">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Social Post -->
<div id="socialModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="cosmic-glass-modal w-full max-w-lg max-h-[90vh] overflow-y-auto p-6">
        <h2 class="text-xl font-bold cosmic-glow-text mb-4">New Social Post</h2>
        <form id="socialForm" class="w-full">
            <input type="hidden" id="socialFormCampaignId">
            <div class="mb-3">
                <label class="block text-green-400 font-semibold mb-1">Platform</label>
                <select id="socialFormPlatform" class="cosmic-input w-full">
                    <option value="facebook">Facebook</option>
                    <option value="instagram">Instagram</option>
                    <option value="twitter">Twitter / X</option>
                    <option value="telegram">Telegram</option>
                    <option value="tiktok">TikTok</option>
                    <option value="whatsapp">WhatsApp</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="block text-green-400 font-semibold mb-1">Content</label>
                <textarea id="socialFormContent" class="cosmic-input w-full" rows="4" required></textarea>
            </div>
            <div class="mb-3">
                <label class="block text-green-400 font-semibold mb-1">Image URL (optional)</label>
                <input type="text" id="socialFormImage" class="cosmic-input w-full">
            </div>
            <div class="mb-3">
                <label class="block text-green-400 font-semibold mb-1">Schedule (YYYY-MM-DD HH:MM:SS)</label>
                <input type="text" id="socialFormSchedule" class="cosmic-input w-full" placeholder="e.g. 2026-07-01 14:30:00">
            </div>
            <div class="flex flex-wrap gap-2 mt-4">
                <button type="submit" class="cosmic-btn flex-1 min-w-[100px]">Create</button>
                <button type="button" onclick="closeModal('socialModal')" class="cosmic-btn flex-1 min-w-[100px]">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Send SMS -->
<div id="smsModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
    <div class="cosmic-glass-modal w-full max-w-lg max-h-[90vh] overflow-y-auto p-6">
        <h2 class="text-xl font-bold cosmic-glow-text mb-4">Send SMS</h2>
        <form id="smsForm" class="w-full">
            <input type="hidden" id="smsFormCampaignId">
            <div class="mb-3">
                <label class="block text-green-400 font-semibold mb-1">Phone Numbers (one per line)</label>
                <textarea id="smsFormPhones" class="cosmic-input w-full" rows="4" required></textarea>
            </div>
            <div class="mb-3">
                <label class="block text-green-400 font-semibold mb-1">Message</label>
                <textarea id="smsFormMessage" class="cosmic-input w-full" rows="3" required></textarea>
            </div>
            <div class="flex flex-wrap gap-2 mt-4">
                <button type="submit" class="cosmic-btn flex-1 min-w-[100px]">Send</button>
                <button type="button" onclick="closeModal('smsModal')" class="cosmic-btn flex-1 min-w-[100px]">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Toast notification -->
<div id="toast" class="fixed bottom-6 right-6 cosmic-glass-modal p-4 rounded-xl hidden z-50"></div>

<script>
// ============================================================
// ===== HELPERS =====
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = 'fixed bottom-6 right-6 cosmic-glass-modal p-4 rounded-xl z-50';
    if (type === 'error') toast.style.borderColor = '#ef4444';
    else toast.style.borderColor = '#00ff88';
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 4000);
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

// ============================================================
// ===== TAB SWITCHING =====
document.querySelectorAll('[data-tab]').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('[data-tab]').forEach(b => b.classList.remove('active-tab'));
        this.classList.add('active-tab');
        const tab = this.dataset.tab;
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.getElementById('tab-' + tab).classList.remove('hidden');
        if (tab === 'templates') fetchTemplates();
        if (tab === 'social') fetchSocial();
        if (tab === 'sms') fetchSms();
        if (tab === 'tracking') fetchTracks();
        if (tab === 'analytics') fetchAnalytics();
    });
});

// ============================================================
// ===== CAMPAIGNS =====
let currentPage = 1;
let currentSearch = '';

async function fetchCampaigns(page = 1) {
    currentPage = page;
    currentSearch = document.getElementById('searchInput').value.trim();
    const url = `/phishing/campaigns?page=${page}&limit=10&search=${encodeURIComponent(currentSearch)}`;
    try {
        const res = await fetch(url);
        const data = await res.json();
        let html = '<div class="space-y-2">';
        if (!data.campaigns || data.campaigns.length === 0) {
            html += '<p class="text-gray-400">No campaigns. Click "New Campaign" to create one.</p>';
        } else {
            data.campaigns.forEach(c => {
                html += `<div class="cosmic-glass p-3 rounded flex justify-between items-center flex-wrap gap-2">
                    <span><span class="text-green-400 font-bold">#${c.id}</span> <strong class="text-green-300">${c.name}</strong> (${c.type || 'phishing'}) - Platform: ${c.platform || 'email'} | Sent: ${c.sent_count || 0} | Opens: ${c.opened_count || 0} | Clicks: ${c.clicked_count || 0} | Conversions: ${c.converted_count || 0}</span>
                    <span>
                        <button onclick="viewCampaign(${c.id})" class="text-blue-400 hover:text-blue-300" title="View"><i class="fas fa-eye"></i></button>
                        <button onclick="editCampaign(${c.id})" class="text-yellow-400 hover:text-yellow-300" title="Edit"><i class="fas fa-edit"></i></button>
                        <button onclick="duplicateCampaign(${c.id})" class="text-green-400 hover:text-green-300" title="Duplicate"><i class="fas fa-copy"></i></button>
                        <button onclick="exportCampaign(${c.id})" class="text-purple-400 hover:text-purple-300" title="Export"><i class="fas fa-download"></i></button>
                        <button onclick="shareCampaign(${c.id})" class="text-indigo-400 hover:text-indigo-300" title="Share"><i class="fas fa-share-alt"></i></button>
                        <button onclick="sendEmails(${c.id})" class="text-cyan-400 hover:text-cyan-300" title="Send Emails"><i class="fas fa-envelope"></i></button>
                        <button onclick="confirmDeleteCampaign(${c.id})" class="text-red-400 hover:text-red-300" title="Delete"><i class="fas fa-trash"></i></button>
                    </span>
                </div>`;
            });
        }
        html += '</div>';
        document.getElementById('campaignsContainer').innerHTML = html;

        const totalPages = data.pages || 0;
        let pagHtml = '';
        if (totalPages > 1) {
            pagHtml += '<div class="flex gap-2">';
            for (let i = 1; i <= totalPages; i++) {
                pagHtml += `<button onclick="fetchCampaigns(${i})" class="cosmic-btn ${i === page ? 'active-tab' : ''}">${i}</button>`;
            }
            pagHtml += '</div>';
            pagHtml += `<span class="text-green-300">Total: ${data.total} campaigns</span>`;
        }
        document.getElementById('paginationControls').innerHTML = pagHtml;
    } catch (e) {
        showToast('Error loading campaigns', 'error');
    }
}
document.getElementById('searchInput').addEventListener('keyup', function(e) { if (e.key === 'Enter') fetchCampaigns(1); });
fetchCampaigns(1);
setInterval(() => fetchCampaigns(currentPage), 30000);

// ============================================================
// ===== CAMPAIGN MODAL (Create / Edit / View) =====
function openCreateCampaignModal() {
    document.getElementById('campaignForm').reset();
    document.getElementById('campaignFormId').value = '';
    document.getElementById('campaignModalTitle').innerText = '✨ New Campaign';
    document.getElementById('campaignFormSubmit').innerText = 'Create';
    document.getElementById('campaignForm').style.display = 'block';
    document.getElementById('campaignFormSubmit').style.display = 'block';
    openModal('campaignModal');
}

async function editCampaign(id) {
    const res = await fetch(`/phishing/view?id=${id}`);
    const data = await res.json();
    const c = data.campaign;
    document.getElementById('campaignModalTitle').innerText = `✏️ Edit Campaign #${c.id}`;
    document.getElementById('campaignFormId').value = c.id;
    document.getElementById('campaignFormName').value = c.name;
    document.getElementById('campaignFormType').value = c.type || 'phishing';
    document.getElementById('campaignFormPlatform').value = c.platform || 'email';
    document.getElementById('campaignFormFromName').value = c.from_name || '';
    document.getElementById('campaignFormFromEmail').value = c.from_email || '';
    document.getElementById('campaignFormReplyTo').value = c.reply_to || '';
    document.getElementById('campaignFormTemplate').value = c.template || '';
    document.getElementById('campaignFormTargets').value = c.targets || '';
    document.getElementById('campaignFormStatus').value = c.status || 'draft';
    document.getElementById('campaignFormSubmit').innerText = 'Save';
    document.getElementById('campaignForm').style.display = 'block';
    document.getElementById('campaignFormSubmit').style.display = 'block';
    openModal('campaignModal');
}

document.getElementById('campaignForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('campaignFormId').value;
    const isEdit = id !== '';
    const targetsRaw = document.getElementById('campaignFormTargets').value;
    const targets = targetsRaw ? targetsRaw.split(',').map(s => s.trim()).filter(s => s) : [];
    const data = {
        id: id,
        name: document.getElementById('campaignFormName').value.trim(),
        type: document.getElementById('campaignFormType').value,
        platform: document.getElementById('campaignFormPlatform').value,
        from_name: document.getElementById('campaignFormFromName').value.trim(),
        from_email: document.getElementById('campaignFormFromEmail').value.trim(),
        reply_to: document.getElementById('campaignFormReplyTo').value.trim(),
        template: document.getElementById('campaignFormTemplate').value,
        targets: targets,
        status: document.getElementById('campaignFormStatus').value
    };
    if (!data.name) { showToast('Campaign name is required', 'error'); return; }
    const url = isEdit ? '/phishing/edit' : '/phishing/campaign';
    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (res.ok) {
            const campaignId = result.id || id || 'N/A';
            showToast(isEdit ? `✅ Campaign #${campaignId} updated` : `✅ Campaign #${campaignId} created`);
            closeModal('campaignModal');
            fetchCampaigns(currentPage);
        } else {
            showToast(result.error || 'Error saving campaign', 'error');
        }
    } catch (e) {
        showToast('Network error', 'error');
    }
});

async function viewCampaign(id) {
    const res = await fetch(`/phishing/view?id=${id}`);
    const data = await res.json();
    const c = data.campaign;
    let html = `<div class="space-y-2 text-green-200 break-words">
        <p><span class="text-green-400 font-semibold">ID:</span> #${c.id}</p>
        <p><span class="text-green-400 font-semibold">Name:</span> ${c.name}</p>
        <p><span class="text-green-400 font-semibold">Type:</span> ${c.type || 'phishing'}</p>
        <p><span class="text-green-400 font-semibold">Platform:</span> ${c.platform}</p>
        <p><span class="text-green-400 font-semibold">Status:</span> ${c.status}</p>
        <p><span class="text-green-400 font-semibold">From Name:</span> ${c.from_name || 'N/A'}</p>
        <p><span class="text-green-400 font-semibold">From Email:</span> ${c.from_email || 'N/A'}</p>
        <p><span class="text-green-400 font-semibold">Reply-To:</span> ${c.reply_to || 'N/A'}</p>
        <p><span class="text-green-400 font-semibold">Targets:</span> ${c.targets}</p>
        <hr class="border-green-500/30 my-2">
        <p><span class="text-green-400 font-semibold">Sent:</span> ${c.sent_count || 0}</p>
        <p><span class="text-green-400 font-semibold">Opens:</span> ${c.opened_count || 0}</p>
        <p><span class="text-green-400 font-semibold">Clicks:</span> ${c.clicked_count || 0}</p>
        <p><span class="text-green-400 font-semibold">Conversions:</span> ${c.converted_count || 0}</p>
        <p><span class="text-green-400 font-semibold">Open Rate:</span> ${data.open_rate}%</p>
        <p><span class="text-green-400 font-semibold">Click Rate:</span> ${data.click_rate}%</p>
        <p><span class="text-green-400 font-semibold">Conversion Rate:</span> ${data.conversion_rate}%</p>
        <hr class="border-green-500/30 my-2">
        <h4 class="text-green-400 font-semibold">Tracking Events (last 5)</h4>`;
    if (data.tracks && data.tracks.length) {
        data.tracks.slice(0, 5).forEach(t => {
            html += `<div class="border-b border-green-500/20 py-1">${t.track_type} - ${t.device_type} - ${t.location}</div>`;
        });
    } else {
        html += '<p>No tracking events.</p>';
    }
    html += '</div>';
    document.getElementById('campaignModalTitle').innerText = `👁️ View Campaign #${c.id}`;
    document.getElementById('campaignForm').style.display = 'none';
    document.getElementById('campaignFormSubmit').style.display = 'none';
    document.getElementById('campaignForm').innerHTML = html;
    openModal('campaignModal');
}

// ============================================================
// ===== DUPLICATE, EXPORT, SHARE, SEND =====
async function duplicateCampaign(id) {
    if (!confirm(`Duplicate campaign #${id}?`)) return;
    const fd = new FormData(); fd.append('id', id);
    const res = await fetch('/phishing/duplicate', { method: 'POST', body: fd });
    if (res.ok) {
        showToast(`✅ Campaign #${id} duplicated`);
        fetchCampaigns(currentPage);
    } else {
        showToast('Error duplicating', 'error');
    }
}

function exportCampaign(id) {
    window.location.href = `/phishing/export?id=${id}`;
}

async function shareCampaign(id) {
    const token = 'share_' + btoa(id + '_' + Date.now());
    const link = window.location.origin + '/phishing/share/' + token;
    document.getElementById('shareLinkInput').value = link;
    openModal('shareModal');
    showToast(`📋 Share link generated for campaign #${id}`);
}

function copyShareLink() {
    const input = document.getElementById('shareLinkInput');
    input.select();
    document.execCommand('copy');
    showToast('📋 Link copied to clipboard');
}

async function sendEmails(id) {
    document.getElementById('sendEmailCampaignId').value = id;
    document.getElementById('sendEmailForm').reset();
    openModal('sendEmailModal');
}

document.getElementById('sendEmailForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const campaignId = document.getElementById('sendEmailCampaignId').value;
    const data = {
        campaign_id: campaignId,
        smtp_host: document.getElementById('sendSmtpHost').value,
        smtp_port: document.getElementById('sendSmtpPort').value,
        smtp_user: document.getElementById('sendSmtpUser').value,
        smtp_pass: document.getElementById('sendSmtpPass').value
    };
    try {
        const res = await fetch('/phishing/send-email', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (res.ok) {
            showToast(`📨 Emails sent for campaign #${campaignId}: ${result.count}`);
            closeModal('sendEmailModal');
            fetchCampaigns(currentPage);
        } else {
            showToast(result.error || 'Error sending emails', 'error');
        }
    } catch (e) {
        showToast('Network error', 'error');
    }
});

// ============================================================
// ===== DELETE =====
let deleteTargetId = null;
function confirmDeleteCampaign(id) {
    deleteTargetId = id;
    document.getElementById('confirmTitle').innerText = '⚠️ Confirm Delete';
    document.getElementById('confirmMessage').innerText = `Are you sure you want to delete campaign #${id}? This action cannot be undone.`;
    document.getElementById('confirmYes').onclick = async function() {
        const fd = new FormData(); fd.append('id', deleteTargetId);
        const res = await fetch('/phishing/delete', { method: 'POST', body: fd });
        if (res.ok) {
            showToast(`🗑️ Campaign #${deleteTargetId} deleted`);
            fetchCampaigns(currentPage);
        } else {
            showToast('Error deleting', 'error');
        }
        closeModal('confirmModal');
    };
    openModal('confirmModal');
}

// ============================================================
// ===== TEMPLATES =====
async function fetchTemplates() {
    const campaignId = document.getElementById('templateCampaignId').value;
    if (!campaignId) { showToast('Enter Campaign ID', 'error'); return; }
    const res = await fetch(`/phishing/templates?campaign_id=${campaignId}`);
    const data = await res.json();
    let html = '<div class="space-y-2">';
    if (!data.templates || data.templates.length === 0) {
        html += '<p class="text-gray-400">No templates.</p>';
    } else {
        data.templates.forEach(t => {
            html += `<div class="cosmic-glass p-3 rounded flex justify-between items-center">
                <span><strong class="text-green-300">${t.name}</strong> (Group ${t.ab_group || 'A'})</span>
                <span>
                    <button onclick="editTemplate(${t.id})" class="text-yellow-400"><i class="fas fa-edit"></i></button>
                    <button onclick="deleteTemplate(${t.id})" class="text-red-400"><i class="fas fa-trash"></i></button>
                </span>
            </div>`;
        });
    }
    html += '</div>';
    document.getElementById('templatesList').innerHTML = html;
}

function openCreateTemplateModal() {
    const campaignId = document.getElementById('templateCampaignId').value;
    if (!campaignId) { showToast('Enter Campaign ID first', 'error'); return; }
    document.getElementById('templateForm').reset();
    document.getElementById('templateFormId').value = '';
    document.getElementById('templateFormCampaignId').value = campaignId;
    document.getElementById('templateModalTitle').innerText = `📝 New Template for Campaign #${campaignId}`;
    document.getElementById('templateForm').style.display = 'block';
    openModal('templateModal');
}

async function editTemplate(id) {
    const subject = prompt('New subject:');
    if (subject === null) return;
    const body = prompt('New body:');
    const fd = new FormData();
    fd.append('id', id);
    fd.append('subject', subject);
    fd.append('body', body);
    const res = await fetch('/phishing/template/edit', { method: 'POST', body: fd });
    if (res.ok) { showToast(`✅ Template #${id} updated`); fetchTemplates(); }
    else showToast('Error updating', 'error');
}

async function deleteTemplate(id) {
    if (!confirm(`Delete template #${id}?`)) return;
    const fd = new FormData(); fd.append('id', id);
    const res = await fetch('/phishing/template/delete', { method: 'POST', body: fd });
    if (res.ok) { showToast(`🗑️ Template #${id} deleted`); fetchTemplates(); }
    else showToast('Error deleting', 'error');
}

document.getElementById('templateForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('templateFormId').value;
    const isEdit = id !== '';
    const data = {
        id: id,
        campaign_id: document.getElementById('templateFormCampaignId').value,
        name: document.getElementById('templateFormName').value.trim(),
        subject: document.getElementById('templateFormSubject').value,
        body: document.getElementById('templateFormBody').value,
        ab_group: document.getElementById('templateFormAbGroup').value
    };
    if (!data.name) { showToast('Template name required', 'error'); return; }
    const url = isEdit ? '/phishing/template/edit' : '/phishing/template/create';
    const fd = new FormData();
    Object.keys(data).forEach(key => fd.append(key, data[key]));
    const res = await fetch(url, { method: 'POST', body: fd });
    const result = await res.json();
    if (res.ok) {
        showToast(isEdit ? `✅ Template #${id} updated` : `✅ Template created for campaign #${data.campaign_id}`);
        closeModal('templateModal');
        fetchTemplates();
    } else {
        showToast(result.error || 'Error saving template', 'error');
    }
});

// ============================================================
// ===== SOCIAL =====
async function fetchSocial() {
    const campaignId = document.getElementById('socialCampaignId').value;
    if (!campaignId) { showToast('Enter Campaign ID', 'error'); return; }
    const res = await fetch(`/phishing/social?campaign_id=${campaignId}`);
    const data = await res.json();
    let html = '<div class="space-y-2">';
    if (!data.social_posts || data.social_posts.length === 0) {
        html += '<p class="text-gray-400">No social posts.</p>';
    } else {
        data.social_posts.forEach(p => {
            html += `<div class="cosmic-glass p-3 rounded flex justify-between items-center">
                <span><strong class="text-green-300">${p.platform}</strong> - ${p.content.substring(0, 50)}... (${p.status})</span>
                <span>
                    <button onclick="deleteSocial(${p.id})" class="text-red-400"><i class="fas fa-trash"></i></button>
                </span>
            </div>`;
        });
    }
    html += '</div>';
    document.getElementById('socialList').innerHTML = html;
}

function openCreateSocialModal() {
    const campaignId = document.getElementById('socialCampaignId').value;
    if (!campaignId) { showToast('Enter Campaign ID first', 'error'); return; }
    document.getElementById('socialForm').reset();
    document.getElementById('socialFormCampaignId').value = campaignId;
    openModal('socialModal');
}

document.getElementById('socialForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    const res = await fetch('/phishing/social/create', { method: 'POST', body: fd });
    const result = await res.json();
    if (res.ok) {
        showToast(`✅ Social post created for campaign #${fd.get('campaign_id')}`);
        closeModal('socialModal');
        fetchSocial();
    } else {
        showToast(result.error || 'Error creating post', 'error');
    }
});

async function deleteSocial(id) {
    if (!confirm(`Delete social post #${id}?`)) return;
    const fd = new FormData(); fd.append('id', id);
    const res = await fetch('/phishing/social/delete', { method: 'POST', body: fd });
    if (res.ok) { showToast(`🗑️ Social post #${id} deleted`); fetchSocial(); }
    else showToast('Error deleting', 'error');
}

// ============================================================
// ===== SMS =====
async function fetchSms() {
    const campaignId = document.getElementById('smsCampaignId').value;
    if (!campaignId) { showToast('Enter Campaign ID', 'error'); return; }
    const res = await fetch(`/phishing/sms?campaign_id=${campaignId}`);
    const data = await res.json();
    let html = '<div class="space-y-2">';
    if (!data.sms_logs || data.sms_logs.length === 0) {
        html += '<p class="text-gray-400">No SMS logs.</p>';
    } else {
        data.sms_logs.forEach(s => {
            html += `<div class="cosmic-glass p-3 rounded flex justify-between items-center">
                <span>${s.phone} - ${s.message.substring(0, 30)}... (${s.status})</span>
            </div>`;
        });
    }
    html += '</div>';
    document.getElementById('smsList').innerHTML = html;
}

function openSendSmsModal() {
    const campaignId = document.getElementById('smsCampaignId').value;
    if (!campaignId) { showToast('Enter Campaign ID first', 'error'); return; }
    document.getElementById('smsForm').reset();
    document.getElementById('smsFormCampaignId').value = campaignId;
    openModal('smsModal');
}

document.getElementById('smsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const fd = new FormData(this);
    const res = await fetch('/phishing/send-sms', { method: 'POST', body: fd });
    const result = await res.json();
    if (res.ok) {
        showToast(`📱 SMS sent for campaign #${fd.get('campaign_id')}: ${result.count}`);
        closeModal('smsModal');
        fetchSms();
    } else {
        showToast(result.error || 'Error sending SMS', 'error');
    }
});

// ============================================================
// ===== TRACKING =====
async function fetchTracks() {
    const campaignId = document.getElementById('trackingCampaignId').value;
    const type = document.getElementById('trackingType').value;
    if (!campaignId) { showToast('Enter Campaign ID', 'error'); return; }
    let url = `/phishing/tracks?campaign_id=${campaignId}`;
    if (type) url += `&type=${type}`;
    const res = await fetch(url);
    const data = await res.json();
    let html = '<div class="space-y-2">';
    if (!data.tracks || data.tracks.length === 0) {
        html += '<p class="text-gray-400">No tracking events.</p>';
    } else {
        data.tracks.forEach(t => {
            html += `<div class="cosmic-glass p-2 rounded text-xs flex justify-between">
                <span class="text-green-300">${t.track_type} - ${t.device_type} - ${t.location}</span>
                <span class="text-gray-400">${new Date(t.created_at).toLocaleString()}</span>
            </div>`;
        });
    }
    html += '</div>';
    document.getElementById('trackingData').innerHTML = html;
}

// ============================================================
// ===== ANALYTICS =====
async function fetchAnalytics() {
    const campaignId = document.getElementById('analyticsCampaignId').value;
    let url = '/phishing/stats';
    if (campaignId) url += `?campaign_id=${campaignId}`;
    const res = await fetch(url);
    const data = await res.json();
    let html = `<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-green-200 break-words">`;
    if (campaignId) {
        html += `
            <div><span class="text-green-400 font-semibold">ID:</span> #${data.campaign.id}</div>
            <div><span class="text-green-400 font-semibold">Name:</span> ${data.campaign.name}</div>
            <div><span class="text-green-400 font-semibold">Type:</span> ${data.campaign.type}</div>
            <div><span class="text-green-400 font-semibold">Platform:</span> ${data.campaign.platform}</div>
            <div><span class="text-green-400 font-semibold">Status:</span> ${data.campaign.status}</div>
            <div><span class="text-green-400 font-semibold">Sent:</span> ${data.campaign.sent_count}</div>
            <div><span class="text-green-400 font-semibold">Opens:</span> ${data.campaign.opened_count}</div>
            <div><span class="text-green-400 font-semibold">Clicks:</span> ${data.campaign.clicked_count}</div>
            <div><span class="text-green-400 font-semibold">Conversions:</span> ${data.campaign.converted_count}</div>
            <div><span class="text-green-400 font-semibold">Open Rate:</span> ${data.open_rate}%</div>
            <div><span class="text-green-400 font-semibold">Click Rate:</span> ${data.click_rate}%</div>
            <div><span class="text-green-400 font-semibold">Conversion Rate:</span> ${data.conversion_rate}%</div>
        `;
    } else {
        html += `
            <div><span class="text-green-400 font-semibold">Total Campaigns:</span> ${data.total_campaigns}</div>
            <div><span class="text-green-400 font-semibold">Total Sent:</span> ${data.total_sent}</div>
            <div><span class="text-green-400 font-semibold">Total Opens:</span> ${data.total_opens}</div>
            <div><span class="text-green-400 font-semibold">Total Clicks:</span> ${data.total_clicks}</div>
            <div><span class="text-green-400 font-semibold">Total Conversions:</span> ${data.total_conversions}</div>
            <div><span class="text-green-400 font-semibold">Overall Open Rate:</span> ${data.overall_open_rate}%</div>
            <div><span class="text-green-400 font-semibold">Overall Click Rate:</span> ${data.overall_click_rate}%</div>
            <div><span class="text-green-400 font-semibold">Overall Conversion Rate:</span> ${data.overall_conversion_rate}%</div>
        `;
    }
    html += '</div>';
    document.getElementById('analyticsData').innerHTML = html;
}

// ============================================================
// ===== MODAL CLOSE ON OVERLAY =====
document.querySelectorAll('.fixed.inset-0').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.add('hidden');
        }
    });
});

// ============================================================
// ===== COSMIC INPUT & MODAL STYLES =====
const style = document.createElement('style');
style.textContent = `
    /* Base cosmic input */
    .cosmic-input {
        background: rgba(0,0,0,0.6);
        border: 1px solid #00ff88;
        color: #a7f3d0;
        border-radius: 0.75rem;
        padding: 0.5rem 0.75rem;
        width: 100%;
        outline: none;
        transition: all 0.3s;
        box-sizing: border-box;
        max-width: 100%;
    }
    .cosmic-input:focus {
        border-color: #fbbf24;
        box-shadow: 0 0 20px rgba(0,255,136,0.15);
    }
    /* Modal container */
    .cosmic-glass-modal {
        background: rgba(10,5,32,0.85);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(0,255,136,0.2);
        border-radius: 1.5rem;
        box-shadow: 0 8px 32px 0 rgba(0,0,0,0.8), inset 0 0 40px rgba(0,255,136,0.05);
        overflow-x: hidden;
        word-wrap: break-word;
        width: 95%;
        max-width: 800px;
        margin: 0 auto;
        max-height: 90vh;
        overflow-y: auto;
    }
    .cosmic-glass-modal label {
        font-weight: 600;
        color: #00ff88;
        text-shadow: 0 0 20px rgba(0,255,136,0.2);
        display: block;
        margin-bottom: 0.25rem;
    }
    .cosmic-glass-modal h2 {
        text-shadow: 0 0 30px rgba(0,255,136,0.3);
    }
    .cosmic-glass-modal .cosmic-btn {
        background: linear-gradient(135deg, rgba(0,255,136,0.15), rgba(251,191,36,0.1));
        border: 1px solid rgba(0,255,136,0.2);
        backdrop-filter: blur(8px);
        transition: all 0.3s ease;
        font-weight: 600;
        color: #d1d5db;
        padding: 0.5rem 1.5rem;
        border-radius: 0.75rem;
        cursor: pointer;
        font-family: 'Exo 2', sans-serif;
        box-sizing: border-box;
        text-align: center;
        white-space: nowrap;
    }
    .cosmic-glass-modal .cosmic-btn:hover {
        background: linear-gradient(135deg, rgba(0,255,136,0.25), rgba(251,191,36,0.2));
        border-color: #fbbf24;
        box-shadow: 0 0 30px rgba(0,255,136,0.15);
        transform: scale(1.02);
        color: #fff;
    }
    .cosmic-glass-modal .cosmic-btn:active {
        transform: scale(0.98);
    }
    .active-tab {
        background: linear-gradient(135deg, rgba(0,255,136,0.2), rgba(251,191,36,0.2)) !important;
        border-color: #00ff88 !important;
    }
    .cosmic-glass-modal form {
        width: 100%;
    }
    .cosmic-glass-modal .flex {
        flex-wrap: wrap;
    }
    /* Responsive tweaks */
    @media (max-width: 640px) {
        .cosmic-glass-modal {
            width: 98%;
            max-width: 100%;
            padding: 1rem;
            max-height: 95vh;
        }
        .cosmic-glass-modal .cosmic-btn {
            width: 100%;
            white-space: normal;
            margin-bottom: 0.5rem;
        }
        .cosmic-glass-modal .flex.gap-2 {
            flex-direction: column;
            gap: 0.5rem;
        }
        .cosmic-glass-modal .flex.gap-2 .cosmic-btn {
            flex: none;
            width: 100%;
        }
        .cosmic-glass-modal .min-w-\\[100px\\] {
            min-width: auto;
        }
    }
`;
document.head.appendChild(style);
</script>
<?php $content = ob_get_clean(); require_once __DIR__ . '/../layout.php'; ?>
