<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#000000">
    <title>Keep Training - Suplementação</title>

    <link rel="icon" href="/icon-192x192.png" type="image/png">

    @pwaHead('icon-192x192.png', '#000000')

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            -webkit-tap-highlight-color: transparent;
            -webkit-touch-callout: none;
        }
    </style>
</head>
<body class="bg-background text-on-background font-body-lg overflow-x-hidden select-none">

<div class="fixed inset-0 z-0">
    <div class="absolute inset-0 bg-primary opacity-90 z-10 mix-blend-multiply"></div>
    <div class="absolute inset-0 kinetic-grid z-20"></div>
    <div
        class="absolute inset-0 bg-cover bg-center grayscale contrast-125 z-0"
        data-alt="A powerful monochromatic high-contrast image of an athlete performing a heavy deadlift in a rugged, industrial warehouse gym. Dust particles are caught in dramatic spotlighting from high windows, creating deep shadows and bright highlights. The style is raw, energetic, and minimalist, emphasizing muscle definition and intense focus."
        style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAFzIRoGnXd7fZUbi_iDevjan8y4dka2Ky9E5N4Ryi6i9fqPCQKoRtzJs6bDrW4nYFxqJ0XZ9CuE6W0WIj32PJj_-x3Iv5wWPHsrNloTPm-7H_IOpRRvS8XcxLEXbK6R3muyIweZIZDyzKnoSpptsfNvRpOAZR-Z6Fbj1hXc4cGCug0HpvwxY4DMJeUWUlNVUV3fs-fe9TvSCX66Q95l-WF8SRuxqcQvZu_vA1iEfN8bUt6dvr48zav');"
    ></div>
</div>

<div class="relative z-30 min-h-screen flex flex-col md:flex-row w-full max-w-md md:max-w-none mx-auto bg-white/95 shadow-2xl">

    <nav class="fixed bottom-0 left-0 right-0 w-full max-w-md mx-auto md:static md:w-64 bg-white border-t-2 md:border-t-0 md:border-r-2 border-[#000000] flex md:flex-col justify-around md:justify-start py-3 md:py-10 px-2 md:px-6 z-40 md:z-0 md:gap-4 md:min-h-screen">

        <div class="hidden md:flex items-center gap-3 mb-8 border-b-2 border-[#000000] pb-6">
            <img src="{{ asset('logo.png') }}" alt="Keep Training Logo" class="w-10 h-10 object-contain">
            <h1 class="text-3xl font-black tracking-tighter text-[#000000] leading-none">KEEP<br>TRAINING</h1>
        </div>

        <button class="tab-btn flex flex-col md:flex-row items-center md:justify-start md:gap-4 text-gray-400 active:scale-95 transition-transform md:hover:bg-gray-100 md:p-3 md:border-2 md:border-transparent md:hover:border-[#000000]" data-target="treino">
            <svg class="w-6 h-6 mb-1 md:mb-0 md:w-7 md:h-7" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17.596 12.768a2 2 0 1 0 2.829-2.829l-1.768-1.767a2 2 0 0 0 2.828-2.829l-2.828-2.828a2 2 0 0 0-2.829 2.828l-1.767-1.768a2 2 0 1 0-2.829 2.829z" />
              <path d="m2.5 21.5 1.4-1.4" />
              <path d="m20.1 3.9 1.4-1.4" />
              <path d="M5.343 21.485a2 2 0 1 0 2.829-2.828l1.767 1.768a2 2 0 1 0 2.829-2.829l-6.364-6.364a2 2 0 1 0-2.829 2.829l1.768 1.767a2 2 0 0 0-2.828 2.829z" />
              <path d="m9.6 14.4 4.8-4.8" />
            </svg>
            <span class="text-[10px] md:text-sm uppercase font-bold tracking-wider">Treino</span>
        </button>
        <button class="tab-btn flex flex-col md:flex-row items-center md:justify-start md:gap-4 text-gray-400 active:scale-95 transition-transform md:hover:bg-gray-100 md:p-3 md:border-2 md:border-transparent md:hover:border-[#000000]" data-target="dieta">
            <svg class="w-6 h-6 mb-1 md:mb-0 md:w-7 md:h-7" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="m16 2-2.3 2.3a3 3 0 0 0 0 4.2l1.8 1.8a3 3 0 0 0 4.2 0L22 8" />
              <path d="M15 15 3.3 3.3a4.2 4.2 0 0 0 0 6l7.3 7.3c.7.7 2 .7 2.8 0L15 15Zm0 0 7 7" />
              <path d="m2.1 21.8 6.4-6.3" />
              <path d="m19 5-7 7" />
            </svg>
            <span class="text-[10px] md:text-sm uppercase font-bold tracking-wider">Dieta</span>
        </button>
        <button class="tab-btn flex flex-col md:flex-row items-center md:justify-start md:gap-4 text-[#92400e] active:scale-95 transition-transform md:hover:bg-gray-100 md:p-3 md:border-2 md:border-transparent md:hover:border-[#000000]" data-target="suplementacao">
            <svg class="w-6 h-6 mb-1 md:mb-0 md:w-7 md:h-7" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M18 11h-4a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h4" />
              <path d="M6 7v13a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7" />
              <rect width="16" height="5" x="4" y="2" rx="1" />
            </svg>
            <span class="text-[10px] md:text-sm uppercase font-bold tracking-wider">Suplementos</span>
        </button>
        <button class="tab-btn flex flex-col md:flex-row items-center md:justify-start md:gap-4 text-gray-400 active:scale-95 transition-transform md:hover:bg-gray-100 md:p-3 md:border-2 md:border-transparent md:hover:border-[#000000]" data-target="saude">
            <svg class="w-6 h-6 mb-1 md:mb-0 md:w-7 md:h-7" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2" />
            </svg>
            <span class="text-[10px] md:text-sm uppercase font-bold tracking-wider">Saúde</span>
        </button>
        <button class="tab-btn flex flex-col md:flex-row items-center md:justify-start md:gap-4 text-gray-400 active:scale-95 transition-transform md:hover:bg-gray-100 md:p-3 md:border-2 md:border-transparent md:hover:border-[#000000]" data-target="perfil">
            <svg class="w-6 h-6 mb-1 md:mb-0 md:w-7 md:h-7" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
              <circle cx="12" cy="7" r="4" />
            </svg>
            <span class="text-[10px] md:text-sm uppercase font-bold tracking-wider">Perfil</span>
        </button>
    </nav>

    <div class="flex-1 flex flex-col w-full pb-20 md:pb-0">
        <header class="md:hidden flex items-center justify-between px-4 py-5 border-b-2 border-[#000000] sticky top-0 z-40 bg-white">
            <button class="p-1 active:scale-95 transition-transform">
                <svg class="w-7 h-7 text-[#000000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            <div class="flex items-center gap-2">
                <img src="{{ asset('logo.png') }}" alt="Keep Training Logo" class="w-6 h-6 object-contain">
                <h1 class="text-xl font-black tracking-tighter text-[#000000]">KEEP TRAINING</h1>
            </div>
            <button class="p-1 active:scale-95 transition-transform">
                <svg class="w-7 h-7 text-[#000000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4" stroke-width="2"></circle></svg>
            </button>
        </header>

        <header class="hidden md:flex items-center justify-end px-8 py-5 border-b-2 border-[#000000] bg-white sticky top-0 z-30">
            <button class="flex items-center gap-3 active:scale-95 transition-transform hover:bg-gray-100 p-2 border-2 border-transparent hover:border-[#000000]">
                <span class="font-bold tracking-widest uppercase text-sm text-[#000000]">{{ auth()->user()->name }}</span>
                <svg class="w-8 h-8 text-[#000000]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4" stroke-width="2"></circle></svg>
            </button>
        </header>

        <main class="flex-1 px-5 md:px-10 py-8 md:py-10">
            <div id="tab-content-treino" class="hidden tab-panel">
                <x-tabs.treino />
            </div>
            <div id="tab-content-dieta" class="hidden tab-panel">
                <x-tabs.dieta />
            </div>
            <div id="tab-content-suplementacao" class="tab-panel">
                <x-tabs.suplementacao />
            </div>
            <div id="tab-content-saude" class="hidden tab-panel">
                <x-tabs.saude />
            </div>
            <div id="tab-content-perfil" class="hidden tab-panel">
                <x-tabs.perfil />
            </div>
        </main>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const grid = document.querySelector('.kinetic-grid');
        if (grid) {
            document.addEventListener('mousemove', (e) => {
                const x = e.clientX / window.innerWidth;
                const y = e.clientY / window.innerHeight;
                grid.style.transform = `translate(${x * 10}px, ${y * 10}px)`;
            });
        }

        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabPanels = document.querySelectorAll('.tab-panel');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                tabBtns.forEach(b => {
                    b.classList.remove('text-[#92400e]');
                    b.classList.add('text-gray-400');
                });
                btn.classList.remove('text-gray-400');
                btn.classList.add('text-[#92400e]');

                tabPanels.forEach(panel => {
                    panel.classList.add('hidden');
                });

                const targetId = btn.getAttribute('data-target');
                document.getElementById('tab-content-' + targetId).classList.remove('hidden');
            });
        });
    });
</script>
</body>
</html>
