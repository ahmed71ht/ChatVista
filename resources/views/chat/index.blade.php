<x-app-layout>
    <div x-data="chatRooms()" x-init="init()" class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="glass rounded-3xl shadow-2xl overflow-hidden">
                <div class="p-8">
                    <!-- رأس الصفحة + تبويبات -->
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <div class="flex items-center gap-2 glass rounded-full p-1">
                            <a href="{{ route('chat.index') }}" 
                               class="px-5 py-2 rounded-full text-sm font-medium transition-all {{ !request()->has('my') ? 'bg-indigo-600 text-white shadow-lg' : 'text-gray-600 dark:text-gray-300 hover:bg-white/20' }}">
                                <i class="fas fa-compass mr-1"></i> استكشف
                            </a>
                            <a href="{{ route('chat.index', ['my' => 1]) }}" 
                               class="px-5 py-2 rounded-full text-sm font-medium transition-all {{ request()->has('my') ? 'bg-indigo-600 text-white shadow-lg' : 'text-gray-600 dark:text-gray-300 hover:bg-white/20' }}">
                                <i class="fas fa-comment-dots mr-1"></i> محادثاتي
                            </a>
                        </div>
                        
                        @unless(request()->has('my'))
                        <button id="openModalBtn"
                                class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/40 transition-all transform hover:scale-105">
                            ✨ إنشاء غرفة جديدة
                        </button>
                        @endunless
                    </div>

                    <!-- نموذج البحث -->
                    <form method="GET" class="mb-8 flex flex-wrap gap-3 glass rounded-2xl p-4">
                        <div class="flex-1 relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="ابحث بمعرف الغرفة (slug)..."
                                   class="w-full pl-10 pr-4 py-2.5 bg-white/60 dark:bg-gray-800/60 border border-white/20 rounded-xl shadow-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 transition dark:text-white" />
                            <i class="fas fa-search absolute left-3 top-3.5 text-gray-400"></i>
                        </div>
                        <select name="type" class="bg-white/60 dark:bg-gray-800/60 border border-white/20 rounded-xl px-4 py-2.5 shadow-sm focus:ring-2 focus:ring-indigo-300 dark:text-white">
                            <option value="">كل الأنواع</option>
                            <option value="public" {{ request('type') == 'public' ? 'selected' : '' }}>عامة</option>
                            <option value="private" {{ request('type') == 'private' ? 'selected' : '' }}>خاصة</option>
                            <option value="protected" {{ request('type') == 'protected' ? 'selected' : '' }}>محمية</option>
                        </select>
                        @if(request()->has('my'))
                            <input type="hidden" name="my" value="1">
                        @endif
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl shadow-lg transition">
                            بحث
                        </button>
                        @if(request('search') || request('type'))
                            <a href="{{ request()->has('my') ? route('chat.index', ['my' => 1]) : route('chat.index') }}" class="bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-5 py-2.5 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                                مسح البحث
                            </a>
                        @endif
                    </form>

                    <!-- قائمة الغرف -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        <template x-for="room in rooms" :key="room.id">
                            <div class="glass rounded-2xl p-5 transition shadow-sm hover:shadow-2xl hover:scale-[1.02] duration-300 group">
                                <div class="flex justify-between items-start mb-2">
                                    <h3 class="text-xl font-bold text-gray-800 dark:text-white truncate max-w-[70%]" x-text="room.name"></h3>
                                    <span class="text-xs px-2 py-1 rounded-full font-medium"
                                        :class="room.type == 'public' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' : 
                                                room.type == 'private' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300' : 
                                                'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'"
                                        x-text="room.type == 'public' ? 'عامة' : (room.type == 'private' ? 'خاصة' : 'محمية')">
                                    </span>
                                </div>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mb-2" x-text="'@ ' + room.slug"></p>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mb-3 line-clamp-2" x-text="room.description || 'لا يوجد وصف'"></p>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-3 bg-white/80 dark:bg-gray-700/80 rounded p-2 border border-white/20" x-show="room.messages && room.messages.length">
                                    <span class="font-semibold">آخر رسالة:</span>
                                    <span x-text="room.messages[0]?.content?.substring(0, 50) || ''"></span>
                                    <span class="block text-gray-400 dark:text-gray-500 mt-1" x-text="room.messages[0]?.created_at ? new Date(room.messages[0].created_at).toLocaleString() : ''"></span>
                                </div>
                                <div class="flex justify-between items-center mt-auto">
                                    <div class="flex items-center gap-2">
                                        <a :href="'{{ url('chat') }}/' + room.slug + (isMyRooms ? '?ref=my' : '')" 
                                        class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 font-semibold text-sm">
                                            دخول الغرفة →
                                        </a>
                                        
                                        <!-- ✅ عداد الرسائل غير المقروءة -->
                                        <span x-show="room.unread_messages > 0" 
                                            class="bg-indigo-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center"
                                            x-text="room.unread_messages"></span>
                                    </div>
                                    
                                    <div class="flex items-center gap-1">
                                        <!-- ✅ المنشن -->
                                        <span x-show="room.unread_mentions > 0" 
                                            class="text-xs text-blue-500 font-bold">
                                            <i class="fas fa-at"></i> <span x-text="room.unread_mentions"></span>
                                        </span>
                                        
                                        <span class="text-xs text-gray-500 dark:text-gray-400 bg-white/80 dark:bg-gray-700/80 px-2 py-1 rounded-full border border-white/20" 
                                            x-text="room.members_count + ' عضو'"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                        
                        <!-- رسالة عدم وجود غرف -->
                        <div x-show="rooms.length === 0" class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
                            <i class="fas fa-comments text-5xl mb-3 opacity-30"></i>
                            <p class="text-lg" x-text="isMyRooms ? 'لم تنضم إلى أي غرفة بعد.' : 'لا توجد غرف متاحة حاليًا.'"></p>
                            <p class="text-sm">قم بإنشاء غرفة جديدة أو تعديل معايير البحث.</p>
                        </div>
                    </div>

                    <div class="mt-8">
                        {{ $rooms->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal إنشاء غرفة (يظل مفتوحًا إذا كانت هناك أخطاء) -->
    <div id="createRoomModal" class="fixed inset-0 z-50 overflow-y-auto {{ $errors->any() ? '' : 'hidden' }}">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm close-modal"></div>
            <div class="relative glass rounded-3xl shadow-2xl max-w-md w-full p-6 z-10">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white">✨ إنشاء غرفة جديدة</h3>
                    <button class="close-modal text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-2xl">&times;</button>
                </div>
                <form method="POST" action="{{ route('chat.store') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">اسم الغرفة</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-xl bg-white/60 dark:bg-gray-800/60 border border-white/20 px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:text-white">
                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">معرّف الغرفة (slug) - اختياري</label>
                            <input type="text" name="slug" value="{{ old('slug') }}" placeholder="مثلاً: my-chat-room"
                                   class="mt-1 w-full rounded-xl bg-white/60 dark:bg-gray-800/60 border border-white/20 px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:text-white">
                            <p class="text-xs text-gray-400 mt-1">يسمح فقط بالأحرف والشرطات (-)، بدون أرقام أو رموز.</p>
                            @error('slug')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">الوصف (اختياري)</label>
                            <textarea name="description" rows="2" class="mt-1 w-full rounded-xl bg-white/60 dark:bg-gray-800/60 border border-white/20 px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:text-white">{{ old('description') }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">النوع</label>
                            <select id="roomTypeSelect" name="type" class="mt-1 w-full rounded-xl bg-white/60 dark:bg-gray-800/60 border border-white/20 px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:text-white">
                                <option value="public" {{ old('type') == 'public' ? 'selected' : '' }}>عامة</option>
                                <option value="private" {{ old('type') == 'private' ? 'selected' : '' }}>خاصة</option>
                                <option value="protected" {{ old('type') == 'protected' ? 'selected' : '' }}>محمية (كلمة مرور)</option>
                            </select>
                        </div>
                        <div id="passwordField" class="{{ old('type') == 'protected' ? '' : 'hidden' }}">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">كلمة المرور</label>
                            <input type="password" name="password" class="mt-1 w-full rounded-xl bg-white/60 dark:bg-gray-800/60 border border-white/20 px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:text-white">
                            @error('password')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">التصنيف (اختياري)</label>
                            <input type="text" name="category" value="{{ old('category') }}" placeholder="مثال: تقنية، رياضة" class="mt-1 w-full rounded-xl bg-white/60 dark:bg-gray-800/60 border border-white/20 px-3 py-2 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:text-white">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" class="close-modal px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                            إلغاء
                        </button>
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl hover:from-indigo-700 hover:to-purple-700 transition shadow-lg">
                            إنشاء
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('createRoomModal');
            const openBtn = document.getElementById('openModalBtn');
            const closeBtns = document.querySelectorAll('.close-modal');
            const roomTypeSelect = document.getElementById('roomTypeSelect');
            const passwordField = document.getElementById('passwordField');

            if (openBtn && modal) {
                openBtn.addEventListener('click', function () {
                    modal.classList.remove('hidden');
                });
            }

            closeBtns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    modal.classList.add('hidden');
                });
            });

            if (roomTypeSelect && passwordField) {
                roomTypeSelect.addEventListener('change', function () {
                    passwordField.classList.toggle('hidden', this.value !== 'protected');
                });
            }
        });

        document.addEventListener('alpine:init', () => {
            Alpine.data('chatRooms', () => ({
                rooms: @json($rooms->items()),
                isMyRooms: {{ request()->has('my') ? 'true' : 'false' }},
                pagination: {
                    currentPage: {{ $rooms->currentPage() }},
                    lastPage: {{ $rooms->lastPage() }},
                    perPage: {{ $rooms->perPage() }},
                    total: {{ $rooms->total() }},
                },

                init() {
                    // اشترك في قناة الغرف
                    if (window.Echo) {
                        // استمع لإنشاء غرفة جديدة
                        window.Echo.channel('rooms')
                            .listen('.room.created', (e) => {
                                console.log('New room created:', e);
                                // أضف الغرفة الجديدة للقائمة إذا كانت عامة أو محمية
                                if (e.room.type === 'public' || e.room.type === 'protected') {
                                    // تأكد من عدم وجود الغرفة بالفعل
                                    const exists = this.rooms.some(r => r.id === e.room.id);
                                    if (!exists) {
                                        this.rooms.unshift(e.room);
                                        this.pagination.total++;
                                    }
                                }
                            });
                        
                        window.Echo.channel('rooms')
                            .listen('.room.updated', (e) => {
                                const room = this.rooms.find(r => r.id === e.room.id);
                                if (room) {
                                    // ✅ آخر رسالة وعدد الأعضاء عبر WebSocket مباشرة
                                    room.messages = e.room.messages;
                                    room.members_count = e.room.members_count;
                                }
                            });

                        // ✅ عدّادات الرسائل غير المقروءة عبر WebSocket فقط (قناة خاصة بالمستخدم)
                        window.Echo.private(`user.{{ Auth::id() }}`)
                            .listen('.unread.updated', (e) => {
                                const room = this.rooms.find(r => r.id === e.roomId);
                                if (room) {
                                    room.unread_messages = e.unreadMessages;
                                    room.unread_mentions = e.unreadMentions;
                                }
                            });
                        // اشترك في قناة المستخدم الحالي
                        window.Echo.private(`user.{{ Auth::id() }}`)
                            .listen('.room.left', (e) => {
                                console.log('Room left event received:', e);
                                // إذا كنت في صفحة "محادثاتي"، احذف الغرفة من القائمة
                                if (this.isMyRooms) {
                                    this.rooms = this.rooms.filter(room => room.id !== e.roomId);
                                    this.pagination.total--;
                                }
                            });
                    }
                }
            }));
        });
    </script>
    @endpush
</x-app-layout>