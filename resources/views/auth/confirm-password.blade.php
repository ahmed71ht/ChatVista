<x-guest-layout>
    <div class="w-full max-w-md mx-auto">
        <!-- رأس الصفحة -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 shadow-2xl shadow-amber-500/20 mb-4">
                <i class="fas fa-shield-halved text-3xl text-white"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-gray-800 to-gray-600 dark:from-white dark:to-gray-300">
                تأكيد كلمة المرور
            </h2>
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                هذه منطقة آمنة من التطبيق. يرجى تأكيد كلمة المرور الخاصة بك قبل المتابعة.
            </p>
        </div>

        <!-- بطاقة النموذج -->
        <div class="glass rounded-3xl p-8 shadow-2xl">
            <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6">
                @csrf

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

                <!-- زر التأكيد -->
                <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white py-4 rounded-xl shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 transition-all duration-300 transform hover:scale-[1.02] font-bold text-lg">
                    <i class="fas fa-check-circle mr-2"></i> تأكيد
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