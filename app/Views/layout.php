<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Spiral Cosmic Tech Laws' ?> | Enterprise RedTeam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    backdropBlur: {
                        xs: '2px',
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom glassmorphism */
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .dark .glass {
            background: rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.1);
        }
        .glass-card {
            transition: all 0.3s ease;
        }
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 30px -10px rgba(0,0,0,0.3);
            border-color: rgba(255,255,255,0.4);
        }
        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, #a855f7, #3b82f6);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #1e1e2f; }
        ::-webkit-scrollbar-thumb { background: #a855f7; border-radius: 4px; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-purple-900 to-gray-900 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 min-h-screen text-white transition-all duration-300">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="glass w-72 h-screen sticky top-0 m-3 shadow-2xl">
            <div class="p-6">
                <div class="flex items-center gap-3 mb-10">
                    <i class="fas fa-cosmic text-3xl text-purple-400"></i>
                    <span class="text-xl font-bold gradient-text">Spiral Cosmic</span>
                </div>
                <nav class="space-y-2">
                    <a href="/" class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/10 transition-all group">
                        <i class="fas fa-tachometer-alt w-5"></i> <span>Dashboard</span>
                    </a>
                    <a href="/redteam" class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/10 transition-all">
                        <i class="fas fa-skull-crossbones w-5"></i> <span>RedTeam Ops</span>
                    </a>
                    <a href="/c2" class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/10 transition-all">
                        <i class="fas fa-satellite-dish w-5"></i> <span>Global C2</span>
                    </a>
                    <a href="/phishing" class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/10 transition-all">
                        <i class="fas fa-fish w-5"></i> <span>Phishing</span>
                    </a>
                    <a href="/urlmasker" class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/10 transition-all">
                        <i class="fas fa-link w-5"></i> <span>UrlMasker</span>
                    </a>
                    <a href="/virtuallab" class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/10 transition-all">
                        <i class="fas fa-flask w-5"></i> <span>VirtualLab</span>
                    </a>
                    <a href="/payload" class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/10 transition-all">
                        <i class="fas fa-biohazard w-5"></i> <span>Payload Gen</span>
                    </a>
                </nav>
            </div>
            <div class="absolute bottom-6 left-6 right-6">
                <button id="themeToggle" class="glass w-full p-2 rounded-xl flex items-center justify-center gap-2">
                    <i class="fas fa-moon"></i> <span>Dark / Light</span>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-6">
            <div class="glass p-6 rounded-2xl min-h-[90vh]">
                <?= $content ?? '' ?>
            </div>
        </main>
    </div>

    <script>
        // Theme toggle
        const html = document.documentElement;
        const themeBtn = document.getElementById('themeToggle');
        if (localStorage.theme === 'light' || (!localStorage.theme && window.matchMedia('(prefers-color-scheme: light)').matches)) {
            html.classList.remove('dark');
        } else {
            html.classList.add('dark');
        }
        themeBtn.addEventListener('click', () => {
            html.classList.toggle('dark');
            localStorage.theme = html.classList.contains('dark') ? 'dark' : 'light';
        });
    </script>
</body>
</html>
