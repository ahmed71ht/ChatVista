<x-guest-layout>
    <div class="w-full max-w-md mx-auto">
        <!-- رأس الصفحة -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-blue-400 to-cyan-500 shadow-2xl shadow-blue-500/20 mb-4 animate-float">
                <i class="fas fa-key text-3xl text-white"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-gray-800 to-gray-600 dark:from-white dark:to-gray-300">
                نسيت كلمة المرور؟
            </h2>
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                لا تقلق! أخبرنا بعنوان بريدك الإلكتروني وسنرسل لك رابط إعادة تعيين كلمة المرور.
            </p>
        </div>

        <!-- حالة الجلسة -->
        <x-auth-session-status class="mb-6" :status="session('status')" />

        <!-- بطاقة النموذج -->
        <div class="glass rounded-3xl p-8 shadow-2xl">
            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf

                <!-- البريد الإلكتروني -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                        <i class="fas fa-envelope mr-2 text-indigo-500"></i>البريد الإلكتروني
                    </label>
                    <div class="relative">
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full pl-12 pr-4 py-4 rounded-xl bg-white/60 dark:bg-gray-800/60 border border-white/20 focus:ring-2 focus:ring-indigo-400 dark:text-white transition-all duration-300"
                               placeholder="example@domain.com">
                        <i class="fas fa-at absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- زر الإرسال -->
                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white py-4 rounded-xl shadow-lg shadow-blue-500/20 hover:shadow-blue-500/40 transition-all duration-300 transform hover:scale-[1.02] font-bold text-lg">
                    <i class="fas fa-paper-plane mr-2"></i> إرسال رابط إعادة التعيين
                </button>
            </form>
        </div>

        <!-- رابط العودة -->
        <div class="text-center mt-6">
            <a href="{{ route('login') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                <i class="fas fa-arrow-right ml-1"></i> العودة لتسجيل الدخول
            </a>
        </div>
    </div>
</x-guest-layout>