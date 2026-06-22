<?php ob_start(); ?>
<div>
    <h1 class="text-3xl font-bold cosmic-glow-text mb-6">MITRE ATT&CK Matrix</h1>
    <div class="cosmic-glass p-4 rounded-2xl">
        <div class="overflow-auto">
            <table class="w-full text-sm cosmic-table">
                <thead>
                    <tr><th class="p-2 text-left">Tactic</th><th class="p-2 text-left">Technique</th><th class="p-2 text-left">Count</th></tr>
                </thead>
                <tbody id="matrixBody">
                    <tr><td colspan="3" class="text-center text-gray-400">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
async function loadMatrix() {
    const res = await fetch('/matrix/data');
    const data = await res.json();
    const matrix = data.matrix || [];
    let html = '';
    if (matrix.length === 0) {
        html = '<tr><td colspan="3" class="text-center text-gray-400">No MITRE data available.</td></tr>';
    } else {
        const tactics = {};
        matrix.forEach(row => {
            const t = row.mitre_tactic || 'Unknown';
            if (!tactics[t]) tactics[t] = [];
            tactics[t].push(row);
        });
        Object.entries(tactics).forEach(([tactic, rows]) => {
            html += `<tr class="bg-green-900/20"><td colspan="3" class="p-2 font-bold text-green-300">${tactic}</td></tr>`;
            rows.forEach(r => {
                html += `<tr>
                    <td class="p-2 pl-4">${r.mitre_technique || ''}</td>
                    <td class="p-2"><span class="bg-green-500/20 px-2 rounded">${r.count}</span></td>
                </tr>`;
            });
        });
    }
    document.getElementById('matrixBody').innerHTML = html;
}
loadMatrix();
</script>
<?php $content = ob_get_clean(); require_once __DIR__ . '/../layout.php'; ?>
