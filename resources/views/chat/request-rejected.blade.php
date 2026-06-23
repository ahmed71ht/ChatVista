<x-app-layout>
    <div class="py-12">
        <div class="max-w-xl mx-auto">
            <div class="glass rounded-3xl p-8 shadow-2xl text-center">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-red-400 to-rose-500 mx-auto flex items-center justify-center text-3xl text-white mb-6">
                    ❌
                </div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">تم رفض طلبك</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">للأسف، رفض مدير غرفة "{{ $room->name }}" طلب انضمامك. يمكنك محاولة تقديم طلب جديد.</p>
                <form method="POST" action="{{ route('chat.request-join', $room->slug) }}">
                    @csrf
                    <button type="submit" class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-3 rounded-xl shadow-lg hover:shadow-xl transition font-bold">
                        إعادة تقديم الطلب
                    </button>
                </form>
                <a href="{{ route('chat.index') }}" class="block mt-4 text-sm text-gray-500 hover:text-indigo-600">العودة للقائمة</a>
            </div>
        </div>
    </div>
</x-app-layout>