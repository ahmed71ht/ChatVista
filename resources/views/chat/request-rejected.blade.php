<x-app-layout>
    <div class="py-12" x-data="rejectedRequest('{{ $room->slug }}', {{ $room->id }})">
        <div class="max-w-md mx-auto">
            <div class="glass rounded-3xl p-8 shadow-2xl text-center">
                <div class="text-6xl mb-4">❌</div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">تم رفض طلبك</h2>
                <p class="text-gray-500 dark:text-gray-400">يمكنك إعادة تقديم الطلب مرة أخرى.</p>
                
                <a href="{{ route('chat.index') }}" class="block mt-6 text-indigo-600 hover:text-indigo-800">العودة للغرف</a>
            </div>
        </div>
    </div>
</x-app-layout>