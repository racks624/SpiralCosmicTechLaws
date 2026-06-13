<?php ob_start(); ?>
<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold gradient-text">🔴 RedTeam Operations</h1>
        <button onclick="document.getElementById('addTargetModal').classList.remove('hidden')" class="glass px-4 py-2 rounded-xl">
            <i class="fas fa-plus"></i> Add Target
        </button>
    </div>

    <!-- Targets Table (glass style) -->
    <div class="glass p-4 rounded-2xl overflow-auto">
        <table class="w-full">
            <thead class="border-b border-white/20">
                <tr><th class="text-left p-2">Name</th><th>Value</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody id="targetsTable">
                <?php foreach ($targets as $t): ?>
                <tr class="border-b border-white/10">
                    <td class="p-2"><?= htmlspecialchars($t['name']) ?></td>
                    <td class="p-2"><?= htmlspecialchars($t['target_value']) ?></td>
                    <td class="p-2"><span class="px-2 py-1 rounded-full text-xs <?= $t['status']=='completed'?'bg-green-600':($t['status']=='scanning'?'bg-yellow-600':'bg-gray-600') ?>"><?= $t['status'] ?></span></td>
                    <td class="p-2">
                        <button onclick="runScan(<?= $t['id'] ?>)" class="text-purple-400 hover:text-purple-300"><i class="fas fa-play"></i></button>
                        <button onclick="viewFindings(<?= $t['id'] ?>)" class="ml-2 text-blue-400"><i class="fas fa-eye"></i></button>
                        <button onclick="deleteTarget(<?= $t['id'] ?>)" class="ml-2 text-red-400"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Target Modal (glass) -->
<div id="addTargetModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50">
    <div class="glass p-6 rounded-2xl w-96">
        <h2 class="text-xl mb-4">New Target</h2>
        <form id="addTargetForm">
            <input type="text" name="name" placeholder="Name" class="w-full p-2 rounded bg-black/30 border border-white/20 mb-2" required>
            <select name="type" class="w-full p-2 rounded bg-black/30 border border-white/20 mb-2">
                <option value="ip">IP</option><option value="domain">Domain</option><option value="url">URL</option>
            </select>
            <input type="text" name="value" placeholder="Value" class="w-full p-2 rounded bg-black/30 border border-white/20 mb-2" required>
            <textarea name="description" placeholder="Description" class="w-full p-2 rounded bg-black/30 border border-white/20 mb-4"></textarea>
            <div class="flex gap-2">
                <button type="submit" class="bg-purple-600 px-4 py-2 rounded flex-1">Add</button>
                <button type="button" onclick="document.getElementById('addTargetModal').classList.add('hidden')" class="glass px-4 py-2 rounded">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('addTargetForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);
    await fetch('/redteam/add', {method:'POST', body:fd});
    location.reload();
});
async function runScan(id) { let fd = new FormData(); fd.append('target_id', id); await fetch('/redteam/scan', {method:'POST', body:fd}); location.reload(); }
async function viewFindings(id) { let res = await fetch(`/redteam/findings?target_id=${id}`); let data = await res.json(); let html = '<ul>'; data.findings.forEach(f=>{html+=`<li><strong>[${f.severity}] ${f.title}</strong><br>${f.description}</li>`}); html+='</ul>'; alert(html); }
async function deleteTarget(id) { if(confirm('Delete?')) { let fd = new FormData(); fd.append('target_id', id); await fetch('/redteam/delete', {method:'POST', body:fd}); location.reload(); } }
</script>
<?php $content = ob_get_clean(); require_once __DIR__ . '/../layout.php'; ?>
