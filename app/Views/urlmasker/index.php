<?php ob_start(); ?>
<div>
    <h1 class="text-3xl font-bold gradient-text mb-6">🔗 Cosmic UrlMasker + Generator</h1>
    <div class="glass p-6 rounded-2xl">
        <input type="url" id="originalUrl" placeholder="Original URL" class="w-full p-2 rounded bg-black/30 mb-2">
        <input type="text" id="maskDomain" placeholder="Mask Domain (e.g., google.com)" class="w-full p-2 rounded bg-black/30 mb-4">
        <button onclick="generateMaskedUrl()" class="glass px-4 py-2 rounded">Generate Masked URL</button>
        <div id="result" class="mt-4 p-3 glass rounded break-all hidden"></div>
    </div>
</div>
<script>
async function generateMaskedUrl() {
    let url = document.getElementById('originalUrl').value;
    let mask = document.getElementById('maskDomain').value;
    let res = await fetch('/urlmasker/generate', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`url=${encodeURIComponent(url)}&mask_domain=${encodeURIComponent(mask)}`});
    let data = await res.json();
    let resultDiv = document.getElementById('result');
    resultDiv.innerHTML = `<strong>Masked URL:</strong> <a href="${data.masked_url}" target="_blank" class="text-purple-400">${data.masked_url}</a>`;
    resultDiv.classList.remove('hidden');
}
</script>
<?php $content = ob_get_clean(); require_once __DIR__ . '/../layout.php'; ?>
