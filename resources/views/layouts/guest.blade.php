<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'ChatVista') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        .dark { color-scheme: dark; }
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .dark .glass {
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .animated-bg {
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
    </style>
</head>
<body class="font-sans antialiased min-h-screen relative overflow-hidden">
    <!-- خلفية متحركة -->
    <div class="fixed inset-0 animated-bg opacity-10 dark:opacity-5"></div>

    <!-- دوائر زخرفية -->
    <div class="fixed -top-40 -right-40 w-96 h-96 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full opacity-20 blur-3xl"></div>
    <div class="fixed -bottom-40 -left-40 w-96 h-96 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-full opacity-20 blur-3xl"></div>

    <!-- المحتوى -->
    <div class="relative min-h-screen flex flex-col items-center justify-center px-4 py-12">
        <!-- الشعار -->
        <div class="mb-8">
            <a href="/" class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400">
                💬 ChatVista
            </a>
        </div>

        <!-- البطاقة الرئيسية -->
        <div class="w-full max-w-lg">
            {{ $slot }}
        </div>

        <!-- زر تبديل الوضع الداكن -->
        <button @click="darkMode = !darkMode" class="fixed bottom-6 right-6 glass rounded-full w-12 h-12 flex items-center justify-center shadow-lg hover:shadow-xl transition z-50">
            <i class="fas text-lg" :class="darkMode ? 'fa-sun text-yellow-500' : 'fa-moon text-indigo-600'"></i>
        </button>
    </div>

        <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('mobileMenuOpen', false);
        });
    </script>
</body>
</html>