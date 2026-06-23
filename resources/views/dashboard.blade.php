<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400">
                📊 لوحة التحكم
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- بطاقة الترحيب -->
            <div class="glass rounded-3xl p-8 shadow-2xl mb-8">
                <div class="flex items-center gap-6">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-3xl text-white shadow-xl">
                        👋
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800 dark:text-white">
                            مرحباً بك، {{ Auth::user()->name }}!
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">
                            أنت مسجل الدخول بنجاح. ابدأ بالمحادثات الآن!
                        </p>
                    </div>
                </div>
            </div>

            <!-- بطاقات سريعة -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- المحادثات -->
                <a href="{{ route('chat.index') }}" class="glass rounded-2xl p-6 hover:shadow-2xl hover:scale-[1.02] transition-all duration-300 group">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-2xl text-white mb-4 group-hover:scale-110 transition">
                        💬
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">المحادثات</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">تصفح وانضم للغرف</p>
                </a>

                <!-- الملف الشخصي -->
                <a href="{{ route('profile.edit') }}" class="glass rounded-2xl p-6 hover:shadow-2xl hover:scale-[1.02] transition-all duration-300 group">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-2xl text-white mb-4 group-hover:scale-110 transition">
                        👤
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">الملف الشخصي</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">تحديث معلومات حسابك</p>
                </a>

                <!-- إنشاء غرفة -->
                <a href="{{ route('chat.index') }}" onclick="event.preventDefault(); window.dispatchEvent(new CustomEvent('open-create-room'))" class="glass rounded-2xl p-6 hover:shadow-2xl hover:scale-[1.02] transition-all duration-300 group cursor-pointer">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-2xl text-white mb-4 group-hover:scale-110 transition">
                        ✨
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">إنشاء غرفة</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">ابدأ محادثة جديدة</p>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>