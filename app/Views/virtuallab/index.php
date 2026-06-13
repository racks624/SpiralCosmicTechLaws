<?php ob_start(); ?>
<div>
    <h1 class="text-3xl font-bold gradient-text mb-6">🧪 VirtualLab</h1>
    <div class="glass p-6 rounded-2xl">
        <select id="os" class="p-2 rounded bg-black/30 mb-4">
            <option value="ubuntu">Ubuntu 22.04</option><option value="windows">Windows 10</option><option value="kali">Kali Linux</option>
        </select>
        <button onclick="spawnVM()" class="glass px-4 py-2 rounded">Spawn Instance</button>
        <div id="vmStatus" class="mt-4"></div>
    </div>
</div>
<script>
async function spawnVM() {
    let os = document.getElementById('os').value;
    let res = await fetch('/virtuallab/spawn', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`os=${os}`});
    let data = await res.json();
    document.getElementById('vmStatus').innerHTML = `<div class="glass p-3 rounded">Spawning ${os}... Machine ID: ${data.machine_id}</div>`;
}
</script>
<?php $content = ob_get_clean(); require_once __DIR__ . '/../layout.php'; ?>
