<?php ob_start(); ?>
<div>
    <h1 class="text-3xl font-bold gradient-text mb-6">💀 Trojan/Payload Generator</h1>
    <div class="glass p-6 rounded-2xl">
        <select id="payloadType" class="w-full p-2 rounded bg-black/30 mb-2">
            <option value="reverse_shell">Reverse Shell (PHP)</option><option value="windows_exe">Windows EXE Stub</option><option value="linux_bin">Linux Binary</option>
        </select>
        <input type="text" id="lhost" placeholder="LHOST (your IP)" class="w-full p-2 rounded bg-black/30 mb-2">
        <input type="number" id="lport" placeholder="LPORT" class="w-full p-2 rounded bg-black/30 mb-4">
        <button onclick="generatePayload()" class="glass px-4 py-2 rounded">Generate</button>
        <div id="payloadOutput" class="mt-4 p-3 glass rounded hidden"></div>
    </div>
</div>
<script>
async function generatePayload() {
    let type = document.getElementById('payloadType').value;
    let lhost = document.getElementById('lhost').value;
    let lport = document.getElementById('lport').value;
    let res = await fetch('/payload/generate', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`type=${type}&lhost=${lhost}&lport=${lport}`});
    let data = await res.json();
    let out = document.getElementById('payloadOutput');
    out.innerHTML = `<strong>Payload (base64):</strong><br><textarea class="w-full h-32 bg-black/30 rounded p-2">${data.payload}</textarea><br><a href="/payload/download/${data.filename}" class="text-purple-400">Download</a>`;
    out.classList.remove('hidden');
}
</script>
<?php $content = ob_get_clean(); require_once __DIR__ . '/../layout.php'; ?>
