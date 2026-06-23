<x-guest-layout>
    <div class="w-full max-w-md mx-auto">
        <!-- رأس الصفحة -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-violet-400 to-purple-500 shadow-2xl shadow-violet-500/20 mb-4 animate-float">
                <i class="fas fa-envelope-circle-check text-3xl text-white"></i>
            </div>
            <h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-violet-600 to-purple-600 dark:from-violet-400 dark:to-purple-400">
                تأكيد البريد الإلكتروني
            </h2>
            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                شكراً لتسجيلك! يرجى التحقق من بريدك الإلكتروني والنقر على رابط التأكيد الذي أرسلناه.
                إذا لم تستلم البريد، يمكننا إرسال رابط آخر.
            </p>
        </div>

        <!-- بطاقة المحتوى -->
        <div class="glass rounded-3xl p-8 shadow-2xl">
            @if (session('status') == 'verification-link-sent')
                <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 rounded-xl">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-check-circle text-green-500 text-xl"></i>
                        <span class="text-sm text-green-700 dark:text-green-300">
                            تم إرسال رابط تأكيد جديد إلى بريدك الإلكتروني.
                        </span>
                    </div>
                </div>
            @endif

            <div class="space-y-4">
                <!-- زر إعادة الإرسال -->
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="w-full bg-gradient-to-r from-violet-600 to-purple-600 hover:from-violet-700 hover:to-purple-700 text-white py-4 rounded-xl shadow-lg shadow-violet-500/20 hover:shadow-violet-500/40 transition-all duration-300 transform hover:scale-[1.02] font-bold">
                        <i class="fas fa-paper-plane mr-2"></i> إعادة إرسال رابط التأكيد
                    </button>
                </form>

                <!-- فاصل -->
                <div class="relative my-4">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white dark:bg-gray-800 text-gray-500">أو</span>
                    </div>
                </div>

                <!-- تسجيل الخروج -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 py-3 rounded-xl transition-all duration-300 font-medium">
                        <i class="fas fa-sign-out-alt mr-2"></i> تسجيل الخروج
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>