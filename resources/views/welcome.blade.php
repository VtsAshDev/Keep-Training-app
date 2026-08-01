<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#000000">
    <title>Keep Training - Welcome</title>

    @pwaHead

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

<main class="relative z-30 min-h-screen flex flex-col items-center justify-between px-gutter py-12 md:py-24 max-w-7xl mx-auto">

    <header class="w-full flex shrink-0 justify-center mb-12 text-center">
        <span class="text-white font-display-lg text-headline-lg md:text-[48px] tracking-tighter leading-none italic uppercase">
            Keep
            <span class="block text-secondary-container">Training</span>
        </span>
    </header>

    <div class="flex-1 flex flex-col items-center justify-center text-center space-y-6 max-w-3xl">
        <div class="space-y-2">
            <h1 class="text-white font-display-lg text-display-lg md:text-[64px] tracking-tighter leading-none italic uppercase">
                Seja
                <div><span class="text-secondary-container">CONSISTENTE</span></div>
            </h1>
            <p class="text-white font-body-lg text-body-lg md:text-[20px] max-w-md mx-auto opacity-90 leading-relaxed font-medium">
                A jornada para a sua melhor versão começa agora. Sem desculpas, apenas progresso.
            </p>
        </div>
    </div>

    <div class="w-full max-w-md space-y-4 md:space-y-6">

        <a
            href="#"
            class="group w-full bg-secondary-container hover:bg-secondary text-on-secondary-container font-headline-lg-mobile text-headline-lg-mobile flex items-center justify-between p-6 border-4 border-primary block-shadow-primary transition-all press-effect"
        >
            <span class="font-black uppercase tracking-tight">COMEÇAR AGORA</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="size-10 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
        </a>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a
                href="#"
                class="w-full bg-surface text-primary font-label-bold text-label-bold border-2 border-primary p-4 flex items-center justify-center gap-2 block-shadow-primary hover:bg-surface-variant transition-colors press-effect"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                <span class="uppercase">ENTRAR</span>
            </a>
            <a
                href="#sobre"
                class="w-full bg-primary text-white font-label-bold text-label-bold border-2 border-secondary p-4 flex items-center justify-center gap-2 block-shadow-secondary hover:bg-on-surface-variant transition-colors press-effect"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="uppercase">SOBRE NÓS</span>
            </a>
        </div>
    </div>

    <footer class="mt-16 md:mt-24 w-full flex flex-col md:flex-row items-center justify-between text-white border-t-2 border-white/20 pt-8 gap-6">
        <div class="flex items-center gap-4">
            <div class="h-12 w-12 border-2 border-white flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                </svg>
            </div>
            <div class="text-left">
                <p class="font-label-bold text-label-bold uppercase opacity-60">Status do Servidor</p>
                <p class="font-mono-data text-mono-data flex items-center">
                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                    ONLINE: BRASIL_01
                </p>
            </div>
        </div>
        <div class="flex gap-8">
            <a class="font-label-bold text-label-bold uppercase hover:text-secondary-container transition-colors" href="#">Instagram</a>
            <a class="font-label-bold text-label-bold uppercase hover:text-secondary-container transition-colors" href="#">Comunidade</a>
            <a class="font-label-bold text-label-bold uppercase hover:text-secondary-container transition-colors" href="#">Ajuda</a>
        </div>
    </footer>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const grid = document.querySelector('.kinetic-grid');
        if (!grid) return;

        document.addEventListener('mousemove', (e) => {
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            grid.style.transform = `translate(${x * 10}px, ${y * 10}px)`;
        });
    });
</script>

</body>
</html>
