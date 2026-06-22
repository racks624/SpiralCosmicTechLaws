<?php ob_start(); ?>
<div class="flex flex-col items-center justify-center text-center py-20">
    <i class="fas fa-cosmic text-8xl text-green-400 animate-pulse mb-8"></i>
    <h1 class="text-6xl font-bold cosmic-glow-text mb-4">404</h1>
    <h2 class="text-2xl text-gray-300 mb-6">Cosmic Drift – Page Not Found</h2>
    <p class="text-gray-400 max-w-md">The path you seek has vanished into the nebula. Return to the dashboard to continue your mission.</p>
    <a href="/" class="cosmic-btn px-6 py-3 rounded-xl mt-6 inline-block">
        <i class="fas fa-rocket"></i> Return to Dashboard
    </a>
</div>
<?php $content = ob_get_clean(); require_once __DIR__ . '/../layout.php'; ?>
