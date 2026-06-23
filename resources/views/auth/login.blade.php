<x-guest-layout>
    <div class="w-full max-w-md mx-auto">
        <!-- رأس الصفحة -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 shadow-2xl shadow-indigo-500/30 mb-4">
                <i class="fas fa-right-to-bracket text-3xl text-white"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400">
                مرحباً بعودتك!
            </h2>
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                سجل دخولك للمتابعة إلى المحادثات
            </p>
        </div>

        <!-- حالة الجلسة -->
        <x-auth-session-status class="mb-6" :status="session('status')" />

        <!-- بطاقة النموذج -->
        <div class="glass rounded-3xl p-8 shadow-2xl">
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- البريد الإلكتروني -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                        <i class="fas fa-envelope mr-2 text-indigo-500"></i>البريد الإلكتروني
                    </label>
                    <div class="relative">
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                               class="w-full pl-12 pr-4 py-4 rounded-xl bg-white/60 dark:bg-gray-800/60 border border-white/20 focus:ring-2 focus:ring-indigo-400 dark:text-white transition-all duration-300"
                               placeholder="example@domain.com">
                        <i class="fas fa-at absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- كلمة المرور -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                        <i class="fas fa-lock mr-2 text-indigo-500"></i>كلمة المرور
                    </label>
                    <div class="relative">
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                               class="w-full pl-12 pr-12 py-4 rounded-xl bg-white/60 dark:bg-gray-800/60 border border-white/20 focus:ring-2 focus:ring-indigo-400 dark:text-white transition-all duration-300"
                               placeholder="••••••••">
                        <i class="fas fa-key absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <button type="button" onclick="togglePassword('password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-indigo-500 transition">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- تذكرني ونسيت كلمة المرور -->
                <div class="flex items-center justify-between">
                    <label for="remember_me" class="flex items-center gap-2 cursor-pointer">
                        <input id="remember_me" type="checkbox" name="remember"
                               class="w-4 h-4 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                        <span class="text-sm text-gray-600 dark:text-gray-400 select-none">تذكرني</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition font-medium">
                            نسيت كلمة المرور؟
                        </a>
                    @endif
                </div>

                <!-- زر الدخول -->
                <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white py-4 rounded-xl shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 transition-all duration-300 transform hover:scale-[1.02] font-bold text-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i> تسجيل الدخول
                </button>
            </form>

            <!-- فاصل -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white dark:bg-gray-800 text-gray-500">أو</span>
                </div>
            </div>

            <!-- رابط التسجيل -->
            <div class="text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    ليس لديك حساب؟
                    <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-bold transition">
                        إنشاء حساب جديد
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(id, btn) {
            const input = document.getElementById(id);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
</x-guest-layout>