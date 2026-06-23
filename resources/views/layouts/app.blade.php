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
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        .dark .glass {
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .some-element {
            left: -123%;
        }
    </style>
</head>
<body x-data="{ mobileMenuOpen: false }" class="font-sans antialiased bg-gradient-to-br from-indigo-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 min-h-screen transition-colors duration-500">

    <!-- شريط التنقل العلوي -->
    <nav class="glass sticky top-0 z-50 px-6 py-3 flex justify-between items-center shadow-lg dark:shadow-gray-900/20">
        <!-- الشعار -->
        <a href="{{ route('chat.index') }}" class="text-2xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 tracking-tight">
            💬 ChatVista
        </a>

        <!-- روابط التنقل -->
        <div class="hidden sm:flex items-center gap-6">
            <x-nav-link :href="route('chat.index')" :active="request()->routeIs('chat.*')" class="text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                <i class="fas fa-comments mr-1"></i> المحادثات
            </x-nav-link>
            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                <i class="fas fa-gauge mr-1"></i> لوحة التحكم
            </x-nav-link>
        </div>

        <!-- الجانب الأيمن -->
        <div class="flex items-center gap-4">
            <!-- زر تبديل الوضع الداكن -->
            <button @click="darkMode = !darkMode" class="relative w-14 h-7 rounded-full bg-gray-300 dark:bg-gray-600 transition-colors duration-500">
                <span class="absolute top-0.5 left-0.5 w-6 h-6 rounded-full bg-white shadow-md transform transition-transform duration-500" :class="{ 'translate-x-7': darkMode }">
                    <i class="fas text-xs absolute inset-0 flex items-center justify-center" :class="darkMode ? 'fa-moon text-indigo-600' : 'fa-sun text-yellow-500'"></i>
                </span>
            </button>

            <!-- قائمة المستخدم -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center gap-2 glass rounded-full px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:shadow-md transition">
                    <span>{{ Auth::user()->name }}</span>
                    <i class="fas fa-chevron-down text-xs"></i>
                </button>
                <div x-show="open" @click.outside="open = false" x-cloak class="absolute some-element left-0 mt-2 w-56 glass rounded-xl shadow-2xl overflow-hidden z-50">
                    <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                        <p class="text-sm font-medium text-gray-800 dark:text-white">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ Auth::user()->email }}</p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-3 hover:bg-white/20 dark:hover:bg-white/10 transition text-sm text-gray-700 dark:text-gray-200">
                        <i class="fas fa-user-circle mr-2"></i> الملف الشخصي
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-3 hover:bg-white/20 dark:hover:bg-white/10 transition text-sm text-red-600 dark:text-red-400">
                            <i class="fas fa-sign-out-alt mr-2"></i> تسجيل الخروج
                        </button>
                    </form>
                </div>
            </div>

            <!-- زر القائمة للجوال -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="sm:hidden glass rounded-full w-10 h-10 flex items-center justify-center text-gray-600 dark:text-gray-300">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- قائمة الجوال -->
    <div x-show="mobileMenuOpen" x-cloak @click.outside="mobileMenuOpen = false" class="sm:hidden glass mx-4 mt-2 rounded-2xl p-4 shadow-2xl">
        <div class="space-y-2">
            <x-responsive-nav-link :href="route('chat.index')" :active="request()->routeIs('chat.*')">
                <i class="fas fa-comments mr-2"></i> المحادثات
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                <i class="fas fa-gauge mr-2"></i> لوحة التحكم
            </x-responsive-nav-link>
        </div>
    </div>

    <!-- محتوى الصفحة -->
    <main class="py-4">
        {{ $slot }}
    </main>

    <!-- حاوية إشعارات Toast -->
    <div x-data="toast" @notify.window="show($event.detail.message, $event.detail.type)" class="fixed bottom-6 right-6 z-50 space-y-3">
        <template x-for="(toast, index) in toasts" :key="index">
            <div x-show="toast.show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-10" x-transition:enter-end="opacity-100 translate-y-0" class="glass rounded-xl px-6 py-3 shadow-2xl flex items-center gap-3" :class="toast.type === 'success' ? 'border-l-4 border-green-500' : 'border-l-4 border-red-500'">
                <i class="fas" :class="toast.type === 'success' ? 'fa-check-circle text-green-500' : 'fa-exclamation-circle text-red-500'"></i>
                <span x-text="toast.message" class="text-sm font-medium dark:text-gray-200"></span>
            </div>
        </template>
    </div>

    @stack('scripts')
</body>
</html>