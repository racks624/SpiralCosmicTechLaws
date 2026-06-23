<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Spiral Cosmic Tech Laws' ?> | Divine Cosmic</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Exo+2:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { orbitron: ['Orbitron', 'sans-serif'], exo: ['Exo 2', 'sans-serif'] },
                    colors: {
                        cosmic: { indigo: '#1a0b3a', pink: '#7c2d8e', green: '#00ff88', gold: '#fbbf24' }
                    }
                }
            }
        }
    </script>
    
    <style>
        /* ---- Background ---- */
        body {
            background: radial-gradient(ellipse at 30% 20%, #4a1a6b, #1a0b3a 70%, #0a0520);
            min-height: 100vh;
            font-family: 'Exo 2', sans-serif;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
            width: 100%;
        }
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background: radial-gradient(ellipse at 70% 80%, rgba(217,70,239,0.15), transparent 70%),
                        radial-gradient(ellipse at 20% 50%, rgba(0,255,136,0.08), transparent 50%);
            pointer-events: none;
            z-index: 0;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: radial-gradient(2px 2px at 20% 30%, #fff, rgba(0,0,0,0)), radial-gradient(2px 2px at 40% 70%, #fff, rgba(0,0,0,0)), radial-gradient(1px 1px at 10% 90%, #fff, rgba(0,0,0,0)), radial-gradient(1px 1px at 80% 40%, #fff, rgba(0,0,0,0)), radial-gradient(1px 1px at 90% 80%, #fff, rgba(0,0,0,0)), radial-gradient(2px 2px at 60% 10%, #fff, rgba(0,0,0,0)), radial-gradient(1px 1px at 30% 50%, #fff, rgba(0,0,0,0));
            background-size: 300px 300px, 400px 400px, 200px 200px, 250px 250px, 350px 350px, 500px 500px, 150px 150px;
            background-repeat: repeat;
            opacity: 0.5;
            pointer-events: none;
            z-index: 0;
            animation: twinkle 10s ease-in-out infinite alternate;
        }
        @keyframes twinkle { 0%{opacity:0.3}100%{opacity:0.8} }
        /* ---- Glass ---- */
        .cosmic-glass {
            background: rgba(10,5,32,0.55);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(0,255,136,0.15);
            box-shadow: 0 8px 32px 0 rgba(0,0,0,0.7), inset 0 0 40px rgba(0,255,136,0.05);
            border-radius: 1.5rem;
            transition: all 0.4s ease;
            position: relative;
            z-index: 1;
            overflow: hidden;
            max-width: 100%;
        }
        .cosmic-glass:hover { border-color: rgba(251,191,36,0.3); box-shadow: 0 8px 48px 0 rgba(0,255,136,0.15); }
        .cosmic-glow-text { background: linear-gradient(135deg, #00ff88, #fbbf24); -webkit-background-clip: text; background-clip: text; color: transparent; text-shadow: 0 0 40px rgba(0,255,136,0.3); font-family: 'Orbitron', sans-serif; }
        .cosmic-sidebar { background: rgba(26,11,58,0.6); backdrop-filter: blur(16px); border-right: 1px solid rgba(0,255,136,0.1); border-left: 1px solid rgba(0,255,136,0.1); box-shadow: 0 0 60px rgba(0,255,136,0.05); flex-shrink: 0; width: 280px; }
        .cosmic-nav-link { position: relative; overflow: hidden; transition: all 0.3s ease; border-left: 3px solid transparent; color: #c4b5d4; }
        .cosmic-nav-link:hover { background: rgba(0,255,136,0.06); border-left-color: #00ff88; transform: translateX(4px); color: #fff; }
        .cosmic-nav-link i { color: #4ade80; transition: color 0.3s; }
        .cosmic-nav-link:hover i { color: #fbbf24; text-shadow: 0 0 20px rgba(251,191,36,0.4); }
        .cosmic-btn { background: linear-gradient(135deg, rgba(0,255,136,0.15), rgba(251,191,36,0.1)); border: 1px solid rgba(0,255,136,0.2); backdrop-filter: blur(8px); transition: all 0.3s ease; font-weight: 600; color: #d1d5db; }
        .cosmic-btn:hover { background: linear-gradient(135deg, rgba(0,255,136,0.25), rgba(251,191,36,0.2)); border-color: #fbbf24; box-shadow: 0 0 30px rgba(0,255,136,0.15); transform: scale(1.02); color: #fff; }
        .cosmic-table th { background: rgba(0,255,136,0.08); border-bottom: 1px solid rgba(0,255,136,0.2); color: #a7f3d0; }
        .cosmic-table td { border-bottom: 1px solid rgba(255,255,255,0.04); }
        .cosmic-badge { background: rgba(0,255,136,0.15); border: 1px solid rgba(251,191,36,0.2); border-radius: 9999px; padding: 0.1rem 0.6rem; font-size: 0.65rem; text-transform: uppercase; color: #fbbf24; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #1a0b3a; }
        ::-webkit-scrollbar-thumb { background: #00ff88; border-radius: 8px; }
        .particle { position: fixed; border-radius: 50%; pointer-events: none; background: radial-gradient(circle at center, rgba(0,255,136,0.3), transparent); animation: floatParticle linear infinite; z-index: 0; }
        @keyframes floatParticle { 0%{transform:translateY(100vh) scale(0);opacity:0}10%{opacity:1}90%{opacity:1}100%{transform:translateY(-10vh) scale(0.5);opacity:0} }
        /* ---- Main layout ---- */
        .main-wrapper {
            display: flex;
            width: 100%;
            max-width: 100vw;
            overflow-x: hidden;
            padding: 0.75rem;
            gap: 0.75rem;
            min-height: 100vh;
        }
        .content-area {
            flex: 1;
            min-width: 0;
            overflow-x: auto;
        }
        .sidebar-wrapper {
            flex-shrink: 0;
            width: 280px;
            transition: transform 0.3s ease;
            position: sticky;
            top: 0.75rem;
            height: calc(100vh - 1.5rem);
        }
        /* ---- Responsive sidebar ---- */
        @media (max-width: 768px) {
            .sidebar-wrapper {
                position: fixed;
                left: 0;
                top: 0;
                height: 100vh;
                transform: translateX(-100%);
                z-index: 50;
                width: 80%;
                max-width: 280px;
                margin: 0;
                border-radius: 0 1.5rem 1.5rem 0;
            }
            .sidebar-wrapper.open {
                transform: translateX(0);
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                z-index: 40;
            }
            .sidebar-overlay.active {
                display: block;
            }
            .mobile-toggle {
                display: inline-block !important;
            }
            .main-wrapper {
                padding: 0.5rem;
                gap: 0.5rem;
            }
            .content-area {
                padding: 0.5rem;
            }
        }
        .mobile-toggle {
            display: none;
        }
        /* ---- Form elements ---- */
        input, select, textarea {
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(0,255,136,0.2);
            color: #e5e7eb;
            border-radius: 0.75rem;
            padding: 0.5rem 0.75rem;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #00ff88;
            box-shadow: 0 0 20px rgba(0,255,136,0.1);
        }
        /* ---- Modal fixes ---- */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 1rem;
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal-content {
            background: rgba(10,5,32,0.9);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(0,255,136,0.2);
            border-radius: 1.5rem;
            box-shadow: 0 8px 32px 0 rgba(0,0,0,0.8), inset 0 0 40px rgba(0,255,136,0.05);
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            padding: 1.5rem;
            box-sizing: border-box;
        }
        @media (max-width: 640px) {
            .modal-content {
                max-width: 98%;
                padding: 1rem;
                max-height: 95vh;
            }
        }
        .modal-content label {
            font-weight: 600;
            color: #00ff88;
            text-shadow: 0 0 20px rgba(0,255,136,0.2);
            display: block;
            margin-bottom: 0.25rem;
        }
        .modal-content h2 {
            text-shadow: 0 0 30px rgba(0,255,136,0.3);
        }
        .modal-content .cosmic-btn {
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
        }
        .modal-content .cosmic-btn:hover {
            background: linear-gradient(135deg, rgba(0,255,136,0.25), rgba(251,191,36,0.2));
            border-color: #fbbf24;
            box-shadow: 0 0 30px rgba(0,255,136,0.15);
            transform: scale(1.02);
            color: #fff;
        }
        .modal-content .cosmic-btn:active {
            transform: scale(0.98);
        }
        .modal-content .flex {
            flex-wrap: wrap;
        }
        .modal-content .cosmic-input {
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
        .modal-content .cosmic-input:focus {
            border-color: #fbbf24;
            box-shadow: 0 0 20px rgba(0,255,136,0.15);
        }
        .modal-content .mb-3 {
            margin-bottom: 0.75rem;
        }
        .modal-content .gap-2 {
            gap: 0.5rem;
        }
    </style>
</head>
<body>
    <div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="main-wrapper">
        <!-- Sidebar -->
        <aside id="sidebar" class="cosmic-glass cosmic-sidebar sidebar-wrapper flex flex-col">
            <div class="p-6 flex-1 overflow-y-auto">
                <div class="flex items-center gap-3 mb-10">
                    <i class="fas fa-cosmic text-3xl text-green-400 animate-pulse"></i>
                    <span class="text-xl font-bold cosmic-glow-text">Spiral Cosmic</span>
                </div>
                <nav class="space-y-1">
                    <a href="/" class="cosmic-nav-link flex items-center gap-3 p-3 rounded-xl"><i class="fas fa-tachometer-alt w-5"></i> <span>Dashboard</span></a>
                    <a href="/redteam" class="cosmic-nav-link flex items-center gap-3 p-3 rounded-xl"><i class="fas fa-skull-crossbones w-5"></i> <span>RedTeam Ops</span></a>
                    <a href="/c2" class="cosmic-nav-link flex items-center gap-3 p-3 rounded-xl"><i class="fas fa-satellite-dish w-5"></i> <span>Global C2</span></a>
                    <a href="/phishing" class="cosmic-nav-link flex items-center gap-3 p-3 rounded-xl"><i class="fas fa-fish w-5"></i> <span>Phishing</span></a>
                    <a href="/urlmasker" class="cosmic-nav-link flex items-center gap-3 p-3 rounded-xl"><i class="fas fa-link w-5"></i> <span>UrlMasker</span></a>
                    <a href="/virtuallab" class="cosmic-nav-link flex items-center gap-3 p-3 rounded-xl"><i class="fas fa-flask w-5"></i> <span>VirtualLab</span></a>
                    <a href="/payload" class="cosmic-nav-link flex items-center gap-3 p-3 rounded-xl"><i class="fas fa-biohazard w-5"></i> <span>Payload Gen</span></a>
                    <a href="/matrix" class="cosmic-nav-link flex items-center gap-3 p-3 rounded-xl"><i class="fas fa-braille w-5"></i> <span>MITRE Matrix</span></a>
                </nav>
            </div>
            <div class="p-6 border-t border-white/10">
                <button id="themeToggle" class="cosmic-btn w-full p-2 rounded-xl flex items-center justify-center gap-2">
                    <i class="fas fa-moon"></i> <span>Dark / Light</span>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="content-area">
            <!-- Top bar -->
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <button class="mobile-toggle cosmic-btn p-2 rounded-xl" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="relative flex-1 min-w-[180px] max-w-md">
                    <input type="text" id="globalSearch" placeholder="Search across modules..." class="w-full p-2 pl-10 rounded-xl bg-black/30 border border-green-500/20 focus:border-green-400 transition">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    <div id="searchResults" class="absolute mt-1 w-full bg-black/80 backdrop-blur rounded-xl hidden z-50 max-h-80 overflow-y-auto"></div>
                </div>
                <div class="flex items-center gap-2">
                    <button class="cosmic-btn p-2 rounded-xl relative" title="Notifications">
                        <i class="fas fa-bell"></i>
                        <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full text-xs flex items-center justify-center">3</span>
                    </button>
                    <button class="cosmic-btn p-2 rounded-xl" title="Quick Actions">
                        <i class="fas fa-plus-circle"></i>
                    </button>
                </div>
            </div>

            <!-- Main glass panel -->
            <div class="cosmic-glass p-4 md:p-6 rounded-2xl min-h-[70vh] overflow-hidden">
                <?= $content ?? '' ?>
            </div>
        </main>
    </div>

    <div id="particles"></div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        }

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

        (function() {
            const container = document.getElementById('particles');
            for (let i = 0; i < 25; i++) {
                const p = document.createElement('div');
                p.className = 'particle';
                const size = Math.random() * 10 + 4;
                p.style.width = size + 'px';
                p.style.height = size + 'px';
                p.style.left = Math.random() * 100 + '%';
                p.style.animationDuration = (Math.random() * 25 + 15) + 's';
                p.style.animationDelay = (Math.random() * 20) + 's';
                if (Math.random() > 0.5) p.style.background = 'radial-gradient(circle at center, rgba(251,191,36,0.25), transparent)';
                container.appendChild(p);
            }
        })();

        // Global Search
        const searchInput = document.getElementById('globalSearch');
        const searchResults = document.getElementById('searchResults');
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const q = this.value.trim();
            if (q.length < 2) {
                searchResults.classList.add('hidden');
                return;
            }
            searchTimeout = setTimeout(() => {
                fetch(`/api/search?q=${encodeURIComponent(q)}`)
                    .then(res => res.json())
                    .then(data => {
                        let html = '';
                        const sections = [
                            { key: 'targets', label: 'Targets' },
                            { key: 'agents', label: 'Agents' },
                            { key: 'campaigns', label: 'Campaigns' },
                            { key: 'payloads', label: 'Payloads' },
                            { key: 'urls', label: 'Masked URLs' }
                        ];
                        let hasResults = false;
                        sections.forEach(s => {
                            const items = data[s.key] || [];
                            if (items.length) {
                                hasResults = true;
                                html += `<div class="p-2 border-b border-white/10"><strong class="text-green-400">${s.label}</strong>`;
                                items.slice(0, 5).forEach(item => {
                                    const name = item.name || item.hostname || item.filename || item.original_url || 'Item';
                                    html += `<div class="py-1 px-2 hover:bg-white/5 rounded">${name}</div>`;
                                });
                                html += '</div>';
                            }
                        });
                        if (!hasResults) html = '<div class="p-2 text-gray-400">No results found</div>';
                        searchResults.innerHTML = html;
                        searchResults.classList.remove('hidden');
                    });
            }, 300);
        });
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.relative')) searchResults.classList.add('hidden');
        });
    </script>
</body>
</html>
