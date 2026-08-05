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

<main class="relative z-30 min-h-screen flex flex-col items-center justify-between px-gutter py-12 md:py-24 max-w-7xl mx-auto w-full">


    <div id="welcome-view" class="w-full flex flex-col items-center justify-between flex-1 transition-opacity duration-300 opacity-100">
        <x-welcome-content />
    </div>


    <div id="login-view" class="w-full flex-1 flex flex-col items-center justify-center hidden opacity-0 transition-opacity duration-300">
        <x-welcome-login />
    </div>
</main>

<script>
    function toggleView(view) {
        const welcomeView = document.getElementById('welcome-view');
        const loginView = document.getElementById('login-view');
        
        if (view === 'login') {
            welcomeView.classList.remove('opacity-100');
            welcomeView.classList.add('opacity-0');
            
            setTimeout(() => {
                welcomeView.classList.add('hidden');
                welcomeView.classList.remove('flex');
                
                loginView.classList.remove('hidden');
                loginView.classList.add('flex');
                
                setTimeout(() => {
                    loginView.classList.remove('opacity-0');
                    loginView.classList.add('opacity-100');
                }, 50);
            }, 300);
        } else {
            loginView.classList.remove('opacity-100');
            loginView.classList.add('opacity-0');
            
            setTimeout(() => {
                loginView.classList.add('hidden');
                loginView.classList.remove('flex');
                
                welcomeView.classList.remove('hidden');
                welcomeView.classList.add('flex');
                
                setTimeout(() => {
                    welcomeView.classList.remove('opacity-0');
                    welcomeView.classList.add('opacity-100');
                }, 50);
            }, 300);
        }
    }

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
