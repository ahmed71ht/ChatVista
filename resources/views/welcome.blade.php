<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ChatVista') }}</title>

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
            background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #f5576c);
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
    <div class="relative min-h-screen flex flex-col">
        <!-- الهيدر -->
        <header class="w-full max-w-4xl mx-auto px-4 py-6">
            <nav class="flex items-center justify-between glass rounded-2xl px-6 py-4">
                <a href="/" class="text-2xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400">
                    💬 ChatVista
                </a>
                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl shadow-lg hover:shadow-indigo-500/30 transition font-medium">
                            لوحة التحكم
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-5 py-2.5 glass rounded-xl text-gray-700 dark:text-gray-200 hover:shadow-md transition font-medium">
                            تسجيل الدخول
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl shadow-lg hover:shadow-indigo-500/30 transition font-medium">
                                إنشاء حساب
                            </a>
                        @endif
                    @endauth
                    <button @click="darkMode = !darkMode" class="glass rounded-full w-10 h-10 flex items-center justify-center">
                        <i class="fas text-sm" :class="darkMode ? 'fa-sun text-yellow-500' : 'fa-moon text-indigo-600'"></i>
                    </button>
                </div>
            </nav>
        </header>

        <!-- المحتوى الرئيسي -->
        <main class="flex-1 flex items-center justify-center px-4">
            <div class="text-center max-w-2xl">
                <!-- شعار كبير -->
                <div class="inline-flex items-center justify-center w-28 h-28 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 shadow-2xl shadow-indigo-500/30 mb-8 animate-float">
                    <i class="fas fa-comments text-5xl text-white"></i>
                </div>

                <h1 class="text-5xl md:text-7xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 mb-6">
                    ChatVista
                </h1>

                <p class="text-xl text-gray-600 dark:text-gray-300 mb-10 leading-relaxed">
                    منصة محادثات عصرية تجمع الناس معاً. أنشئ غرفاً، انضم للمحادثات، وشارك أفكارك مع العالم.
                </p>

                <div class="flex flex-wrap justify-center gap-4">
                    @auth
                        <a href="{{ route('chat.index') }}" class="px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-2xl shadow-xl shadow-indigo-500/20 hover:shadow-indigo-500/40 transition-all duration-300 transform hover:scale-105 font-bold text-lg">
                            <i class="fas fa-comments mr-2"></i> ابدأ المحادثة
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-2xl shadow-xl shadow-indigo-500/20 hover:shadow-indigo-500/40 transition-all duration-300 transform hover:scale-105 font-bold text-lg">
                            <i class="fas fa-user-plus mr-2"></i> انضم إلينا الآن
                        </a>
                        <a href="{{ route('login') }}" class="px-8 py-4 glass rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 font-bold text-lg text-gray-700 dark:text-gray-200">
                            <i class="fas fa-sign-in-alt mr-2"></i> تسجيل الدخول
                        </a>
                    @endauth
                </div>

                <!-- مميزات -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-16">
                    <div class="glass rounded-2xl p-6">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-xl text-white mb-3">
                            💬
                        </div>
                        <h3 class="font-bold text-gray-800 dark:text-white">غرف متعددة</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">أنشئ وانضم للغرف بسهولة</p>
                    </div>
                    <div class="glass rounded-2xl p-6">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-xl text-white mb-3">
                            🔒
                        </div>
                        <h3 class="font-bold text-gray-800 dark:text-white">خصوصية تامة</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">غرف عامة وخاصة ومحمية</p>
                    </div>
                    <div class="glass rounded-2xl p-6">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-xl text-white mb-3">
                            ⚡
                        </div>
                        <h3 class="font-bold text-gray-800 dark:text-white">سرعة فائقة</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">تجربة محادثة سلسة وفورية</p>
                    </div>
                </div>
            </div>
        </main>

        <!-- الفوتر -->
        <footer class="text-center py-6 text-sm text-gray-500 dark:text-gray-400">
            © {{ date('Y') }} ChatVista. جميع الحقوق محفوظة.
        </footer>
    </div>
</body>
</html>