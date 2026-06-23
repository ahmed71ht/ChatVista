<x-app-layout>
    <div class="py-12">
        <div class="max-w-xl mx-auto">
            <div class="glass rounded-3xl p-8 shadow-2xl text-center">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-400 to-cyan-500 mx-auto flex items-center justify-center text-3xl text-white mb-6 animate-float">
                    ⏳
                </div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">طلبك قيد المراجعة</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">لقد أرسلت طلب انضمام إلى "{{ $room->name }}". بمجرد قبول المدير، ستتمكن من الدخول والمشاركة.</p>
                <a href="{{ route('chat.index') }}" class="inline-block bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-3 rounded-xl shadow-lg hover:shadow-xl transition font-bold">
                    العودة للقائمة
                </a>
            </div>
        </div>
    </div>
</x-app-layout>