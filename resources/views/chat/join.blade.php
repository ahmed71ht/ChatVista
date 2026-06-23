<x-app-layout>
    <div class="max-w-xl mx-auto px-4 py-20">
        <div class="glass rounded-3xl p-8 shadow-2xl animate-float">
            <h2 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 mb-6">
                🔐 {{ $room->name }}
            </h2>
            <p class="text-gray-600 dark:text-gray-300 mb-8">{{ $room->description }}</p>

            @if($room->type == 'protected')
                <form method="POST" action="{{ route('chat.join', $room->slug) }}">
                    @csrf
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">كلمة المرور</label>
                        <input type="password" name="password" required class="w-full rounded-xl bg-white/60 dark:bg-gray-800/60 border border-white/20 px-4 py-3 dark:text-white focus:ring-2 focus:ring-indigo-400">
                        @error('password')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-xl shadow-lg hover:shadow-indigo-500/30 transition transform hover:scale-105">
                        دخول
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('chat.join', $room->slug) }}">
                    @csrf
                    <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-xl shadow-lg hover:shadow-indigo-500/30 transition transform hover:scale-105">
                        انضم الآن
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>