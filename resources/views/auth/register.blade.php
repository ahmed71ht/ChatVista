<x-guest-layout>
    <div class="w-full max-w-md mx-auto">
        <!-- رأس الصفحة -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 shadow-2xl shadow-emerald-500/20 mb-4">
                <i class="fas fa-user-plus text-3xl text-white"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 dark:from-emerald-400 dark:to-teal-400">
                إنشاء حساب جديد
            </h2>
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                انضم إلينا وابدأ المحادثات الآن
            </p>
        </div>

        <!-- بطاقة النموذج -->
        <div class="glass rounded-3xl p-8 shadow-2xl">
            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <!-- الاسم -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                        <i class="fas fa-user mr-2 text-emerald-500"></i>الاسم الكامل
                    </label>
                    <div class="relative">
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                               class="w-full pl-12 pr-4 py-4 rounded-xl bg-white/60 dark:bg-gray-800/60 border border-white/20 focus:ring-2 focus:ring-emerald-400 dark:text-white transition-all duration-300"
                               placeholder="أدخل اسمك الكامل">
                        <i class="fas fa-id-card absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <!-- البريد الإلكتروني -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                        <i class="fas fa-envelope mr-2 text-emerald-500"></i>البريد الإلكتروني
                    </label>
                    <div class="relative">
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                               class="w-full pl-12 pr-4 py-4 rounded-xl bg-white/60 dark:bg-gray-800/60 border border-white/20 focus:ring-2 focus:ring-emerald-400 dark:text-white transition-all duration-300"
                               placeholder="example@domain.com">
                        <i class="fas fa-at absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- كلمة المرور -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                        <i class="fas fa-lock mr-2 text-emerald-500"></i>كلمة المرور
                    </label>
                    <div class="relative">
                        <input id="password" type="password" name="password" required autocomplete="new-password"
                               class="w-full pl-12 pr-12 py-4 rounded-xl bg-white/60 dark:bg-gray-800/60 border border-white/20 focus:ring-2 focus:ring-emerald-400 dark:text-white transition-all duration-300"
                               placeholder="••••••••">
                        <i class="fas fa-key absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <button type="button" onclick="togglePassword('password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-emerald-500 transition">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- تأكيد كلمة المرور -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                        <i class="fas fa-check-double mr-2 text-emerald-500"></i>تأكيد كلمة المرور
                    </label>
                    <div class="relative">
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                               class="w-full pl-12 pr-12 py-4 rounded-xl bg-white/60 dark:bg-gray-800/60 border border-white/20 focus:ring-2 focus:ring-emerald-400 dark:text-white transition-all duration-300"
                               placeholder="••••••••">
                        <i class="fas fa-shield-halved absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <button type="button" onclick="togglePassword('password_confirmation', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-emerald-500 transition">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <!-- زر التسجيل -->
                <button type="submit" class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white py-4 rounded-xl shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/40 transition-all duration-300 transform hover:scale-[1.02] font-bold text-lg">
                    <i class="fas fa-user-check mr-2"></i> إنشاء حساب
                </button>
            </form>

            <!-- رابط الدخول -->
            <div class="text-center mt-6">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    لديك حساب بالفعل؟
                    <a href="{{ route('login') }}" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400 dark:hover:text-emerald-300 font-bold transition">
                        تسجيل الدخول
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