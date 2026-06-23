<x-guest-layout>
    <div class="w-full max-w-md mx-auto">
        <!-- رأس الصفحة -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-rose-400 to-pink-500 shadow-2xl shadow-rose-500/20 mb-4">
                <i class="fas fa-rotate text-3xl text-white"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-rose-600 to-pink-600 dark:from-rose-400 dark:to-pink-400">
                إعادة تعيين كلمة المرور
            </h2>
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                أدخل كلمة المرور الجديدة لتحديث حسابك
            </p>
        </div>

        <!-- بطاقة النموذج -->
        <div class="glass rounded-3xl p-8 shadow-2xl">
            <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
                @csrf

                <!-- رمز إعادة التعيين -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- البريد الإلكتروني -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                        <i class="fas fa-envelope mr-2 text-rose-500"></i>البريد الإلكتروني
                    </label>
                    <div class="relative">
                        <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                               class="w-full pl-12 pr-4 py-4 rounded-xl bg-white/60 dark:bg-gray-800/60 border border-white/20 focus:ring-2 focus:ring-rose-400 dark:text-white transition-all duration-300"
                               placeholder="example@domain.com">
                        <i class="fas fa-at absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- كلمة المرور الجديدة -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                        <i class="fas fa-lock mr-2 text-rose-500"></i>كلمة المرور الجديدة
                    </label>
                    <div class="relative">
                        <input id="password" type="password" name="password" required autocomplete="new-password"
                               class="w-full pl-12 pr-12 py-4 rounded-xl bg-white/60 dark:bg-gray-800/60 border border-white/20 focus:ring-2 focus:ring-rose-400 dark:text-white transition-all duration-300"
                               placeholder="••••••••">
                        <i class="fas fa-key absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <button type="button" onclick="togglePassword('password', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-rose-500 transition">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- تأكيد كلمة المرور -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                        <i class="fas fa-check-double mr-2 text-rose-500"></i>تأكيد كلمة المرور
                    </label>
                    <div class="relative">
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                               class="w-full pl-12 pr-12 py-4 rounded-xl bg-white/60 dark:bg-gray-800/60 border border-white/20 focus:ring-2 focus:ring-rose-400 dark:text-white transition-all duration-300"
                               placeholder="••••••••">
                        <i class="fas fa-shield-halved absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <button type="button" onclick="togglePassword('password_confirmation', this)" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-rose-500 transition">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <!-- زر إعادة التعيين -->
                <button type="submit" class="w-full bg-gradient-to-r from-rose-600 to-pink-600 hover:from-rose-700 hover:to-pink-700 text-white py-4 rounded-xl shadow-lg shadow-rose-500/20 hover:shadow-rose-500/40 transition-all duration-300 transform hover:scale-[1.02] font-bold text-lg">
                    <i class="fas fa-save mr-2"></i> إعادة تعيين كلمة المرور
                </button>
            </form>
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