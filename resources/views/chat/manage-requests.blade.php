<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto">
            <div class="glass rounded-3xl p-8 shadow-2xl">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">طلبات الانضمام لـ {{ $room->name }}</h2>
                
                @if($requests->isEmpty())
                    <p class="text-gray-500 text-center py-8">لا توجد طلبات حالياً.</p>
                @else
                    <div class="space-y-4" id="requestsList">
                        @foreach($requests as $req)
                            <div class="glass rounded-2xl p-4 flex items-center justify-between request-item" data-user="{{ $req->user->name }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold">
                                        {{ substr($req->user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-800 dark:text-white">{{ $req->user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $req->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    @if($req->status === 'pending')
                                        <form method="POST" action="{{ route('chat.handle-request', [$room->slug, $req->id, 'approve']) }}" class="inline">
                                            @csrf
                                            <button class="px-4 py-2 bg-green-500 text-white rounded-xl hover:bg-green-600 transition">قبول</button>
                                        </form>
                                        <form method="POST" action="{{ route('chat.handle-request', [$room->slug, $req->id, 'reject']) }}" class="inline">
                                            @csrf
                                            <button class="px-4 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600 transition">رفض</button>
                                        </form>
                                    @elseif($req->status === 'approved')
                                        <span class="text-green-600 font-bold">✓ مقبول</span>
                                    @else
                                        <span class="text-red-600 font-bold">✗ مرفوض</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                
                <a href="{{ route('chat.room', $room->slug) }}" class="block mt-6 text-center text-indigo-600 hover:text-indigo-800">العودة للغرفة</a>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
// ✅ Auto-refresh كل 3 ثواني
setInterval(function() {
    location.reload();
}, 3000);
</script>