<x-app-layout>
    <div x-data="chatRoom('{{ $room->slug }}')" x-init="init()" 
     class="h-[calc(100vh-8rem)] flex flex-col max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- رأس الغرفة -->
        <div class="glass rounded-2xl p-4 mb-4 flex items-center gap-4 shadow-lg">
            <a href="{{ request()->has('ref') ? route('chat.index', ['my' => 1]) : route('chat.index') }}" 
            class="text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                <i class="fas fa-arrow-right text-xl"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">{{ $room->name }}</h2>
                @if($room->creator_id === Auth::id() && $room->type === 'private')
                    <a href="{{ route('chat.manage-requests', $room->slug) }}" class="text-sm bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400 px-3 py-1 rounded-full hover:bg-indigo-200 transition">
                        <i class="fas fa-tasks mr-1"></i> طلبات الانضمام
                    </a>
                @endif
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    <span x-text="members.length"></span> أعضاء
                </span>
            </div>
            <div class="mr-auto flex gap-2">
                <button @click="showMembers = !showMembers" class="glass rounded-full w-10 h-10 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:shadow-md transition">
                    <i class="fas fa-users"></i>
                </button>
                <button @click="leaveRoom()" 
                        class="glass rounded-full w-10 h-10 flex items-center justify-center text-red-500 hover:text-red-600 hover:shadow-md transition"
                        title="مغادرة الغرفة">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </div>
        </div>

        <div class="flex flex-1 gap-4 overflow-hidden">
            <!-- منطقة الرسائل -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- رسالة مثبتة -->
                <div x-show="pinnedMessage" x-transition class="bg-yellow-50/90 dark:bg-yellow-900/30 border border-yellow-300 dark:border-yellow-700 rounded-xl p-3 mb-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-bold text-yellow-800 dark:text-yellow-300">📌 مثبتة</span>
                        <button @click="pinMessage(null)" class="text-xs text-red-500 hover:underline">إلغاء</button>
                    </div>
                    <p class="text-sm mt-1" x-text="pinnedMessage?.content"></p>
                </div>

                <!-- حاوية الرسائل -->
                <div class="flex-1 overflow-y-auto space-y-4 pr-2 custom-scrollbar" id="messagesContainer" @scroll="onScroll">
                    <template x-for="(message, index) in allMessages" :key="message.id">
                        <div class="message-item flex group"
                            :data-message-id="message.id"
                            :class="message.is_system ? 'justify-center' : (message.user_id == {{ Auth::id() }} ? 'justify-end' : 'justify-start')">
                            
                        <div x-show="message.is_system" 
                            class="message-system text-xs rounded-full px-4 py-1.5 text-center"
                            :class="message.type === 'member_left' ? 'message-system-leave' : 'message-system-join'">
                            <i class="fas" :class="message.type === 'member_left' ? 'fa-sign-out-alt' : 'fa-sign-in-alt' ml-1"></i>
                            <span x-text="message.content"></span>
                        </div>
                            
                        <div x-show="!message.is_system" class="max-w-[70%]">
                            <div x-show="!editingMessage || editingMessage.id !== message.id"
                                class="message-bubble rounded-2xl p-3 relative cursor-pointer margin-3 transition-all duration-200 ease-out hover:shadow-xl hover:-translate-y-0.5 active:scale-95"
                                :class="message.user_id == {{ Auth::id() }} ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600'"
                                @dblclick="heartReaction(message)"
                                @contextmenu.prevent="openReactionBar($event, message)">
                                
                                <p class="text-sm rtl" x-html="highlightMentions(message.content)"></p>
                                
                                <div class="flex justify-between items-center mt-1 text-xs" :class="message.user_id == {{ Auth::id() }} ? 'color' : 'text-gray-500'">
                                    <span dir="ltr" style="text-align: left; display: inline-block; unicode-bidi: plaintext;" x-text="formatTime(message.is_edited ? message.edited_at : message.created_at)"></span>
                                    <div style="margin-left: 10px;">
                                        <span x-show="message.is_edited">تم تعديلها</span>
                                    </div>
                                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200 mr-auto">
                                        <button @click="startReply(message)" class="p-1 rounded-full hover:bg-white/20 transition">
                                            <i class="fas fa-reply text-xs"></i>
                                        </button>
                                        <button @click="openReactionBar($event, message)" class="p-1 rounded-full hover:bg-white/20 transition">
                                            <i class="fas fa-smile text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div x-show="message.reactions_summary && Object.keys(message.reactions_summary).length"
                                    class="absolute -bottom-2 -left-2 bg-white dark:bg-gray-600 rounded-full px-2 py-0.5 text-xs shadow flex gap-1" x-transition>
                                    <template x-for="(count, emoji) in message.reactions_summary" :key="emoji">
                                        <span @click.stop="openReactionDetail($event, message, emoji)"
                                            class="flex items-center gap-0.5 px-1 py-0.5 cursor-pointer rounded-full hover:bg-gray-100 dark:hover:bg-gray-500 transition">
                                            <span x-text="emoji"></span>
                                            <span x-text="count > 1 ? count : ''" class="text-gray-500"></span>
                                        </span>
                                    </template>
                                </div>
                                
                                <div x-show="message.user_id == {{ Auth::id() }}" 
                                    class="absolute -bottom-5 -right-2 flex items-center gap-1 text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    <i class="fas fa-eye"></i>
                                    <span x-text="(views[message.id] && views[message.id].length > 0) ? views[message.id].length : '0'"></span>
                                    <div class="relative group">
                                        <div class="hidden group-hover:block absolute bottom-full mb-1 right-0 bg-gray-800 text-white text-xs rounded-lg px-3 py-1 w-max max-w-[200px] shadow-lg z-10">
                                            <ul class="list-none" x-show="views[message.id] && views[message.id].length > 0">
                                                <template x-for="member in (views[message.id] || []).slice(0, 5)" :key="member.user_id">
                                                    <li x-text="member.name"></li>
                                                </template>
                                                <li x-show="views[message.id] && views[message.id].length > 5">وآخرون...</li>
                                            </ul>
                                            <span x-show="!views[message.id] || views[message.id].length === 0">لا يوجد مشاهدين</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div x-show="editingMessage && editingMessage.id === message.id" 
                                class="message-bubble rounded-2xl p-3"
                                :class="message.user_id == {{ Auth::id() }} ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 shadow-sm'">
                                <div class="flex flex-col gap-2">
                                    <textarea x-model="editContent" 
                                            :data-edit-textarea="message.id"
                                            @keydown.enter.prevent="saveEdit()"
                                            @keydown.escape.prevent="cancelEdit()"
                                            rows="2"
                                            class="w-full rounded-lg border border-indigo-300 dark:border-indigo-700 bg-white dark:bg-gray-600 p-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none dark:text-white resize-none"
                                            style="min-height: 50px; max-height: 150px;"></textarea>
                                    <div class="flex gap-2 items-center flex-wrap">
                                        <button @click="saveEdit()" class="px-3 py-1.5 bg-indigo-500 text-white rounded-lg text-sm hover:bg-indigo-600 transition flex items-center gap-1">
                                            <i class="fas fa-save text-xs"></i> حفظ
                                        </button>
                                        <button @click="cancelEdit()" class="px-3 py-1.5 bg-gray-200 dark:bg-gray-500 text-gray-700 dark:text-white rounded-lg text-sm hover:bg-gray-300 dark:hover:bg-gray-400 transition">إلغاء</button>
                                        <span x-show="editError" class="text-red-400 text-sm" x-text="editError"></span>
                                        <span class="text-[10px] opacity-60 mr-auto" x-show="!editError">
                                            <kbd class="px-1 py-0.5 bg-white/20 rounded text-[10px]">Enter</kbd> حفظ · 
                                            <kbd class="px-1 py-0.5 bg-white/20 rounded text-[10px]">Esc</kbd> إلغاء
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </template>
                </div>

                <div x-show="hiddenMentionCount > 0" @click="goToNextHiddenMention()" class="mention-float-btn">
                    <i class="fas fa-at"></i>
                    <span x-text="hiddenMentionCount"></span>
                </div>

                <div x-show="replyToMessage" class="glass rounded-xl p-3 mb-2 flex items-start gap-3">
                    <div class="flex-1">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            رد على <span class="font-bold" x-text="replyToMessage?.user?.name || ''"></span>
                        </p>
                        <p class="text-sm text-gray-700 dark:text-gray-200 line-clamp-1" x-text="replyToMessage?.content || ''"></p>
                    </div>
                    <button @click="replyToMessage = null" class="text-gray-400 hover:text-red-500 transition">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="mt-4 glass rounded-2xl p-2 flex items-end gap-2 relative">
                    <button @click="showEmojiPicker = !showEmojiPicker" class="p-2 rounded-full hover:bg-white/20 transition">
                        <i class="fas fa-smile text-gray-600 dark:text-gray-300"></i>
                    </button>
                    
                    <div class="flex-1 relative">
                        <div x-show="showMentionList && filteredUsers.length > 0" @click.outside="showMentionList = false"
                            class="absolute bottom-full left-0 right-0 mb-2 bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-h-48 overflow-y-auto z-50 border border-gray-200 dark:border-gray-700" x-transition>
                            <template x-for="(user, index) in filteredUsers" :key="user.id">
                                <div @click="selectMention(user)" @mouseenter="selectedMentionIndex = index"
                                    class="flex items-center gap-3 px-4 py-2.5 cursor-pointer transition hover:bg-indigo-50 dark:hover:bg-indigo-900/30"
                                    :class="selectedMentionIndex === index ? 'bg-indigo-50 dark:bg-indigo-900/30' : ''">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-sm font-bold">
                                        <span x-text="user.name.charAt(0).toUpperCase()"></span>
                                    </div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white" x-text="user.name"></p>
                                </div>
                            </template>
                        </div>
                        
                        <textarea x-model="newMessage" @keydown.enter.prevent="sendMessage()" @keydown.escape="showMentionList = false"
                            @input="handleMentionInput($event)" placeholder="اكتب رسالتك... @ للمنشن" rows="1" 
                            class="w-full bg-transparent border-none resize-none focus:ring-0 dark:text-white py-2 max-h-32" x-ref="messageInput"></textarea>
                    </div>
                    
                    <button @click="sendMessage()" :disabled="!newMessage.trim() || sendCooldown || sending"
                            class="p-2 rounded-full bg-indigo-600 text-white hover:bg-indigo-700 transition disabled:opacity-50">
                        <i class="fas fa-spinner fa-spin" x-show="sending"></i>
                        <i class="fas fa-paper-plane" x-show="!sending"></i>
                    </button>
                </div>

                <div x-show="showEmojiPicker" @click.outside="showEmojiPicker = false" class="glass rounded-2xl p-2 mt-2 grid grid-cols-8 gap-2 max-w-xs">
                    <template x-for="emoji in ['😀','😂','😍','😢','😡','👍','👎','❤️','🔥','🎉','🤔','🙏','💪','👀','✨','💯']">
                        <button @click="insertEmoji(emoji)" class="text-xl hover:scale-125 transition transform" x-text="emoji"></button>
                    </template>
                </div>
            </div>

            <div x-show="showMembers" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-full" x-transition:enter-end="opacity-100 translate-x-0" class="w-72 glass rounded-2xl p-4 overflow-y-auto hidden lg:block">
                <h3 class="font-bold text-gray-800 dark:text-white mb-4">الأعضاء</h3>
                <ul class="space-y-2">
                    <template x-for="member in members" :key="member.id">
                        <li class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-300" :class="member.animationClass || ''" x-transition>
                            <span class="w-2 h-2 rounded-full" :class="isOnline(member) ? 'bg-green-500' : 'bg-gray-400'"></span>
                            <span class="truncate" x-text="member.name"></span>
                            <span x-show="member.id == {{ $room->creator_id }}" class="mr-auto text-xs bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400 px-2 rounded-full">مدير</span>
                        </li>
                    </template>
                </ul>
            </div>
        </div>

        <div x-show="reactionBar.open && reactionBar.message" @click.outside="reactionBar.open = false" x-cloak
             class="fixed z-50 w-72 glass rounded-2xl shadow-2xl overflow-hidden"
             :style="'top: '+reactionBar.y+'px; left: '+reactionBar.x+'px;'"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform scale-90"
             x-transition:enter-end="opacity-100 transform scale-100">
            <div class="flex justify-center gap-1 p-3 border-b border-gray-200 dark:border-gray-700 flex-wrap">
                <template x-for="emoji in baseEmojis" :key="emoji">
                    <button @click="quickReact(reactionBar.message, emoji)"
                            class="relative w-10 h-10 flex items-center justify-center text-xl hover:scale-125 transition transform rounded-full hover:bg-gray-100 dark:hover:bg-gray-700"
                            :class="{ 'border-b-2 border-indigo-500': reactionBar.message?.my_reactions && reactionBar.message.my_reactions.includes(emoji) }">
                        <span x-text="emoji"></span>
                    </button>
                </template>
                <template x-for="emoji in getTempEmojis(reactionBar.message)" :key="emoji">
                    <button @click="quickReact(reactionBar.message, emoji)"
                            class="relative w-10 h-10 flex items-center justify-center text-xl hover:scale-125 transition transform rounded-full hover:bg-gray-100 dark:hover:bg-gray-700"
                            :class="{ 'border-b-2 border-indigo-500': reactionBar.message?.my_reactions && reactionBar.message.my_reactions.includes(emoji) }">
                        <span x-text="emoji"></span>
                    </button>
                </template>
                <button @click="openFullEmojiPicker(reactionBar.message)" class="w-10 h-10 flex items-center justify-center text-lg hover:scale-125 transition transform rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500">+</button>
            </div>
            
            <button x-show="reactionBar.message && reactionBar.message.user_id == {{ Auth::id() }} && canEditMessage(reactionBar.message)"
                    @click="startEditing(reactionBar.message); reactionBar.open = false"
                    class="w-full text-left px-4 py-3 hover:bg-white/20 dark:hover:bg-white/10 transition flex items-center gap-2 text-indigo-600 dark:text-indigo-400">
                <i class="fas fa-edit"></i> تعديل
            </button>
            
            <button @click="startReply(reactionBar.message); reactionBar.open = false" class="w-full text-left px-4 py-3 hover:bg-white/20 dark:hover:bg-white/10 transition flex items-center gap-2">
                <i class="fas fa-reply"></i> رد
            </button>
            <button @click="copyMessage(reactionBar.message); reactionBar.open = false" class="w-full text-left px-4 py-3 hover:bg-white/20 dark:hover:bg-white/10 transition flex items-center gap-2">
                <i class="fas fa-copy"></i> نسخ
            </button>
            <button @click="reportMessage(reactionBar.message); reactionBar.open = false" class="w-full text-left px-4 py-3 hover:bg-white/20 dark:hover:bg-white/10 transition flex items-center gap-2 text-red-500">
                <i class="fas fa-flag"></i> إبلاغ
            </button>
            
            <div x-show="reactionBar.message && reactionBar.message.user_id == {{ Auth::id() }}" class="border-t border-gray-200 dark:border-gray-700">
                <button @click="deleteMessage(reactionBar.message, 'for_everyone'); reactionBar.open = false" 
                        class="w-full text-left px-4 py-3 hover:bg-white/20 dark:hover:bg-white/10 transition flex items-center gap-2 text-red-600">
                    <i class="fas fa-trash"></i> حذف للجميع
                </button>
            </div>
            
            <div class="border-t border-gray-200 dark:border-gray-700" x-show="reactionBar.message">
                <button @click="deleteMessage(reactionBar.message, 'for_me'); reactionBar.open = false" 
                        class="w-full text-left px-4 py-3 hover:bg-white/20 dark:hover:bg-white/10 transition flex items-center gap-2 text-red-500">
                    <i class="fas fa-trash-alt"></i> حذف لدي
                </button>
            </div>
        </div>

        <div x-show="reactionDetail.open" @click.outside="reactionDetail.open = false" x-cloak
             class="fixed z-50 w-48 glass rounded-xl shadow-2xl p-3"
             :style="'top: '+reactionDetail.y+'px; left: '+reactionDetail.x+'px;'" x-transition>
            <div class="text-center mb-2">
                <span class="text-3xl" x-text="reactionDetail.emoji"></span>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-1"><span x-text="reactionDetail.count"></span> تفاعل</p>
            </div>
            <button x-show="reactionDetail.isMine" @click="removeReaction(reactionDetail.message, reactionDetail.emoji)"
                    class="w-full text-center px-3 py-2 bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-200 transition text-sm">
                <i class="fas fa-trash-alt mr-1"></i> إزالة تفاعلي
            </button>
        </div>

        <div x-show="fullEmojiPicker.open" @click.outside="fullEmojiPicker.open = false" x-cloak
             class="fixed inset-0 z-50 flex items-end justify-center pb-4 sm:pb-8 bg-black/20 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-full"
             x-transition:enter-end="opacity-100 translate-y-0">
            <div class="glass rounded-3xl shadow-2xl w-full max-w-lg max-h-[70vh] overflow-hidden flex flex-col">
                <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-bold text-gray-800 dark:text-white">اختر رمزًا تعبيريًا</h3>
                    <button @click="fullEmojiPicker.open = false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                </div>
                <div class="flex overflow-x-auto gap-1 p-2 border-b border-gray-200 dark:border-gray-700">
                    <template x-for="cat in emojiCategories" :key="cat.key">
                        <button @click="currentEmojiCategory = cat.key"
                                :class="currentEmojiCategory === cat.key ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-600' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700'"
                                class="px-3 py-1.5 rounded-full text-sm whitespace-nowrap transition">
                            <span x-text="cat.icon + ' ' + cat.name"></span>
                        </button>
                    </template>
                </div>
                <div class="flex-1 overflow-y-auto p-3 grid grid-cols-6 sm:grid-cols-8 gap-2">
                    <template x-for="emoji in filteredEmojis" :key="emoji">
                        <button @click="selectEmoji(emoji, fullEmojiPicker.message)"
                                class="text-2xl hover:scale-125 transition transform rounded-lg hover:bg-white/20 p-1" x-text="emoji"></button>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <div id="undoToast" class="undo-toast" x-data>
        <i class="fas fa-trash-alt text-red-400"></i>
        <span>تم حذف الرسالة</span>
        <button @click="$dispatch('undo-delete')">تراجع</button>
    </div>

    <style>
        @keyframes fadeOut { 0% { opacity: 1; max-height: 200px; margin-bottom: 16px; } 100% { opacity: 0; max-height: 0; margin-bottom: 0; } }
        .message-fading { animation: fadeOut 0.5s ease-out forwards; overflow: hidden; }
        @keyframes slideUpToFill { from { transform: translateY(20px); } to { transform: translateY(0); } }
        .message-slide-up { animation: slideUpToFill 0.4s ease-out forwards; }
        @keyframes memberJoin { from { opacity: 0; transform: translateX(20px); max-height: 0; } to { opacity: 1; transform: translateX(0); max-height: 50px; } }
        .member-join { animation: memberJoin 0.5s ease-out; }
        @keyframes memberLeave { from { opacity: 1; transform: translateX(0); max-height: 50px; } to { opacity: 0; transform: translateX(-20px); max-height: 0; margin: 0; padding: 0; } }
        .member-leave { animation: memberLeave 0.5s ease-in forwards; overflow: hidden; }
        @keyframes countPulse { 0% { transform: scale(1); } 50% { transform: scale(1.3); color: #6366f1; } 100% { transform: scale(1); } }
        .count-update { animation: countPulse 0.4s ease-in-out; }
        @keyframes fadeInRestore { 0% { opacity: 0; max-height: 0; margin-bottom: 0; } 100% { opacity: 1; max-height: 200px; margin-bottom: 16px; } }
        .message-restore { animation: fadeInRestore 0.5s ease-out forwards; overflow: hidden; }
        .undo-toast { position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%) translateY(100px); background: #1f2937; color: white; padding: 12px 24px; border-radius: 50px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); z-index: 9999; display: flex; align-items: center; gap: 12px; font-size: 14px; transition: transform 0.3s ease, opacity 0.3s ease; font-family: 'Tajawal', sans-serif; opacity: 0; pointer-events: none; }
        .undo-toast.show { transform: translateX(-50%) translateY(0); opacity: 1; pointer-events: all; }
        .undo-toast.hide { transform: translateX(-50%) translateY(100px); opacity: 0; pointer-events: none; }
        .undo-toast button { background: #4f46e5; color: white; border: none; padding: 8px 16px; border-radius: 25px; cursor: pointer; font-weight: bold; font-family: 'Tajawal', sans-serif; }
        .undo-toast button:hover { background: #6366f1; }
        .message-system { background: linear-gradient(135deg, #e8f5e9, #f1f8e9); color: #4a7c3f; border: 1px solid #c8e6c9; }
        .dark .message-system { background: linear-gradient(135deg, #1b3a1b, #2d4a2d); color: #a5d6a7; border: 1px solid #3e5c3e; }
        .mention-highlight { color: #2563eb; font-weight: 600; cursor: pointer; transition: all 0.15s; }
        .mention-highlight:hover { text-decoration: underline; opacity: 0.85; }
        .dark .mention-highlight { color: #60a5fa; }
        .mention-float-btn { position: absolute; right: 16px; bottom: 80px; background: #2563eb; color: white; padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; cursor: pointer; z-index: 40; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4); transition: all 0.2s; animation: mentionFloatIn 0.3s ease-out; display: flex; align-items: center; gap: 6px; }
        .mention-float-btn:hover { background: #1d4ed8; transform: scale(1.05); box-shadow: 0 6px 16px rgba(37, 99, 235, 0.5); }
        @keyframes mentionFloatIn { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .dark .mention-float-btn { background: #3b82f6; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4); }
        .message-editing textarea { min-height: 50px; resize: vertical; }
        kbd { font-family: inherit; background: rgba(0,0,0,0.05); padding: 1px 6px; border-radius: 4px; font-size: 10px; }
        .dark kbd { background: rgba(255,255,255,0.1); }
        .margin-3 { margin: 3px; }
        .rtl { direction: rtl; }
        .color { color: rgb(163 178 239); }
        .message-system-join { background: linear-gradient(135deg, #e8f5e9, #f1f8e9); color: #4a7c3f; border: 1px solid #c8e6c9; }
        .dark .message-system-join { background: linear-gradient(135deg, #1b3a1b, #2d4a2d); color: #a5d6a7; border: 1px solid #3e5c3e; }
        .message-system-leave { background: linear-gradient(135deg, #fde8e8, #fce4e4); color: #c62828; border: 1px solid #ffcdd2; }
        .dark .message-system-leave { background: linear-gradient(135deg, #3d1a1a, #4d1a1a); color: #ef9a9a; border: 1px solid #5c2a2a; }
    </style>
        @push('scripts')
    <script>
document.addEventListener('alpine:init', () => {
    Alpine.data('chatRoom', (roomId) => ({
        messages: @json(isset($messages) ? $messages->items() : []),
        systemMessages: @json($systemMessages ?? []),
        newMessage: '',
        loading: false,
        lastMessageId: null,
        firstMessageId: null,
        replyTo: null,
        pinnedMessage: @json($room->pinnedMessage),
        showMembers: false,
        showEmojiPicker: false,
        typingUsers: [],
        reactionBar: { open: false, x: 0, y: 0, message: null },
        reactionDetail: { open: false, x: 0, y: 0, emoji: '', count: 0, message: null, isMine: false },
        fullEmojiPicker: { open: false, message: null },
        currentEmojiCategory: 'Smileys & Emotion',
        baseEmojis: ['👍','❤️','😂','😮','😢','😡'],
        tempEmojisMap: {},
        emojiCategories: [
            { key: 'Smileys & Emotion', name: 'ابتسامات', icon: '😃' },
            { key: 'People & Body', name: 'أشخاص', icon: '👋' },
            { key: 'Symbols', name: 'رموز', icon: '❤️' },
        ],
        emojiData: {
            'Smileys & Emotion': ['😀','😃','😄','😁','😅','😂','🤣','😊','😇','🙂','🙃','😉','😌','😍','🥰','😘','😗','😙','😚','😋','😛','😜','🤪','😝','🤑','🤗','🤔','😐','😑','😶','😏','😒','🙄','😮','😯','😲','😳','🥺','😢','😭','😱','😡','😠','🤬','💀','👻','👽','🤖','💩'],
            'People & Body': ['👋','🤚','✋','👌','🤞','🤟','🤘','👈','👉','👆','👇','👍','👎','👏','🙌','🤝','🙏','💪','🦵','🦶','👂','👀','👅','👄'],
            'Symbols': ['❤️','💔','💕','💖','💗','💘','💝','💟','💯','💢','💥','💫','💬','🗨','🗯','💭','🕳','👁️‍🗨️','❤️‍🔥','❤️‍🩹']
        },
        sendCooldown: false,
        sending: false,
        members: @json($membersData ?? []),
        replyToMessage: null,
        lastMembersUpdate: null,
        views: @json($viewsData ?? []),
        deletedMessage: null,
        undoTimeout: null,
        STORAGE_KEY: 'deleted_messages_' + '{{ $room->slug }}',
        allMessages: [],
        joinMessagesAdded: new Set(),
        countAnimation: false,
        showMentionList: false,
        mentionQuery: '',
        filteredUsers: [],
        selectedMentionIndex: -1,
        mentionSearchTimeout: null,
        mentionCheckTimeout: null,
        hiddenMentions: [],
        hiddenMentionIndex: 0,
        hiddenMentionCount: 0,
        seenMentions: [],
        editingMessage: null,
        editContent: '',
        editError: null,

        updateAllMessages() {
            const allMessagesMap = new Map();
            this.systemMessages.forEach(msg => {
                if (!msg.type) msg.type = 'member_joined';
                allMessagesMap.set(msg.id, msg);
            });
            this.messages.forEach(msg => allMessagesMap.set(msg.id, msg));
            this.allMessages = Array.from(allMessagesMap.values())
                .sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
        },

        async leaveRoom() {
            if (!confirm('هل أنت متأكد من مغادرة الغرفة؟')) return;
            try {
                const response = await fetch(`/chat/${roomId}/leave`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                });
                if (response.redirected) window.location.href = response.url;
            } catch (error) {
                console.error('Leave room error:', error);
                this.$dispatch('notify', { message: 'حدث خطأ أثناء مغادرة الغرفة', type: 'error' });
            }
        },

        init() {
            sessionStorage.removeItem('seen_mentions_' + roomId);
            this.joinMessagesAdded.clear();
            const regularMessages = this.messages.filter(m => !m.is_system);
            if (regularMessages.length > 0) {
                this.lastMessageId = regularMessages[regularMessages.length - 1].id;
                this.firstMessageId = regularMessages[0].id;
            }
            this.filterDeletedMessages();
            this.messages.forEach(msg => {
                if (msg.user_id == {{ Auth::id() }}) {
                    if (this.views[msg.id]) {
                        this.views[msg.id] = this.views[msg.id].filter(v => v.user_id != msg.user_id);
                    } else {
                        this.views[msg.id] = [];
                    }
                } else {
                    delete this.views[msg.id];
                }
            });
            this.lastMembersUpdate = new Date().toISOString();
            this.updateAllMessages();
            this.$nextTick(() => {
                this.scrollToBottom();
                this.checkHiddenMentions();
                this.messages.forEach(msg => {
                    if (msg.user_id != {{ Auth::id() }} && !msg.is_system) this.markAsViewed(msg.id);
                });
            });
            this.messages.forEach(msg => this.updateReactionsSummary(msg));

            window.Echo.channel(`room.{{ $room->id }}`)
                .listen('.message.sent', (e) => {
                    const existingIds = new Set(this.messages.map(m => m.id));
                    if (!existingIds.has(e.message.id)) {
                        this.messages.push(e.message);
                        this.lastMessageId = e.message.id;
                        this.updateReactionsSummary(e.message);
                        this.updateAllMessages();
                        this.$nextTick(() => {
                            this.scrollToBottom();
                            this.checkHiddenMentions();
                            this.messages.forEach(msg => {
                                if (msg.user_id != {{ Auth::id() }} && !msg.is_system) this.markAsViewed(msg.id);
                            });
                        });
                        this.markAsViewed(e.message.id);
                    }
                })
                .listen('.reaction.toggled', (e) => {
                    const msg = this.messages.find(m => m.id === e.messageId);
                    if (msg) { msg.reactions = e.reactions; this.updateReactionsSummary(msg); }
                })
                .listen('.message.viewed', (e) => {
                    const msg = this.messages.find(m => m.id === e.messageId);
                    if (msg && msg.user_id == {{ Auth::id() }} && e.userId != {{ Auth::id() }}) {
                        if (!this.views[msg.id]) this.views[msg.id] = [];
                        if (!this.views[msg.id].some(v => v.user_id === e.userId)) {
                            this.views[msg.id].push({ user_id: e.userId, name: e.userName });
                        }
                    }
                })
                .listen('.member.joined', (e) => {
                    if (e.userId == {{ Auth::id() }}) return;
                    const recentDuplicate = this.systemMessages.some(msg => 
                        msg.type === 'member_joined' && msg.content.includes(e.userName) && 
                        (Date.now() - new Date(msg.created_at).getTime()) < 2000
                    );
                    if (!recentDuplicate) {
                        this.systemMessages.push({
                            id: 'system-' + Date.now() + '-' + Math.random().toString(36).substr(2, 5),
                            user_id: null, content: `لقد انضم ${e.userName} إلى غرفة ${e.roomName}`,
                            created_at: new Date().toISOString(), is_system: true, type: 'member_joined',
                        });
                        this.updateAllMessages();
                        this.$nextTick(() => this.scrollToBottom());
                    }
                    if (!this.members.find(m => m.id === e.userId)) {
                        const newMember = { id: e.userId, name: e.userName, last_read_at: new Date().toISOString(), animationClass: 'member-join' };
                        this.members.push(newMember);
                        setTimeout(() => { const m = this.members.find(m => m.id === e.userId); if (m) m.animationClass = ''; }, 600);
                        this.countAnimation = true;
                        setTimeout(() => { this.countAnimation = false; }, 500);
                    }
                })
                .listen('.member.left', (e) => {
                    if (e.userId == {{ Auth::id() }}) return;
                    const recentDuplicate = this.systemMessages.some(msg => 
                        msg.type === 'member_left' && msg.content.includes(e.userName) && 
                        (Date.now() - new Date(msg.created_at).getTime()) < 2000
                    );
                    if (!recentDuplicate) {
                        this.systemMessages.push({
                            id: 'system-' + Date.now() + '-' + Math.random().toString(36).substr(2, 5),
                            user_id: null, content: `غادر ${e.userName} غرفة ${e.roomName}`,
                            created_at: new Date().toISOString(), is_system: true, type: 'member_left',
                        });
                        this.updateAllMessages();
                        this.$nextTick(() => this.scrollToBottom());
                    }
                    const leavingMember = this.members.find(m => m.id === e.userId);
                    if (leavingMember) {
                        leavingMember.animationClass = 'member-leave';
                        setTimeout(() => {
                            this.members = this.members.filter(m => m.id !== e.userId);
                            this.countAnimation = true;
                            setTimeout(() => { this.countAnimation = false; }, 500);
                        }, 400);
                    }
                })
                .listen('.message.deleted', (e) => {
                    this.messages = this.messages.filter(m => m.id !== e.messageId);
                    delete this.views[e.messageId];
                    this.updateAllMessages();
                })
                .listen('.message.updated', (e) => {
                    const msg = this.messages.find(m => m.id === e.messageId);
                    if (msg) { msg.content = e.content; msg.is_edited = true; msg.edited_at = e.updatedAt; this.updateAllMessages(); }
                    this.$dispatch('notify', { message: 'تم تعديل رسالة', type: 'info' });
                });

            window.addEventListener('undo-delete', () => { if (this.deletedMessage) this.undoDelete(); });
            window.addEventListener('beforeunload', () => {
                navigator.sendBeacon(`/chat/${roomId}/mark-left`, new URLSearchParams({ _token: '{{ csrf_token() }}' }));
            });
        },

        loadDeletedMessages() {
            try { const stored = localStorage.getItem(this.STORAGE_KEY); return stored ? JSON.parse(stored) : []; } 
            catch (e) { return []; }
        },
        addToDeletedMessages(messageId) {
            const deleted = this.loadDeletedMessages();
            if (!deleted.includes(messageId)) { deleted.push(messageId); localStorage.setItem(this.STORAGE_KEY, JSON.stringify(deleted)); }
        },
        removeFromDeletedMessages(messageId) {
            let deleted = this.loadDeletedMessages();
            deleted = deleted.filter(id => id !== messageId);
            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(deleted));
        },
        filterDeletedMessages() {
            const deletedIds = this.loadDeletedMessages();
            if (deletedIds.length > 0) { this.messages = this.messages.filter(m => !deletedIds.includes(m.id)); }
        },

        getTempEmojis(message) { return (message && this.tempEmojisMap[message.id]) || []; },
        updateTempEmojis(message, addedEmoji = null, removedEmoji = null) {
            if (!message) return;
            if (!this.tempEmojisMap[message.id]) this.tempEmojisMap[message.id] = [];
            const baseSet = new Set(this.baseEmojis);
            if (addedEmoji && !baseSet.has(addedEmoji) && !this.tempEmojisMap[message.id].includes(addedEmoji)) 
                this.tempEmojisMap[message.id].push(addedEmoji);
            if (removedEmoji && !baseSet.has(removedEmoji)) {
                const idx = this.tempEmojisMap[message.id].indexOf(removedEmoji);
                if (idx > -1) this.tempEmojisMap[message.id].splice(idx, 1);
            }
        },
        updateReactionsSummary(message) {
            if (!message || !message.reactions) return;
            const summary = {};
            message.reactions.forEach(r => { summary[r.type] = (summary[r.type] || 0) + 1; });
            message.reactions_summary = summary;
            message.my_reactions = message.reactions.filter(r => r.user_id == {{ Auth::id() }}).map(r => r.type);
        },
        get filteredEmojis() { return this.emojiData[this.currentEmojiCategory] || []; },
        isOnline(member) {
            if (!member || !member.last_read_at) return false;
            return new Date(member.last_read_at).getTime() >= Date.now() - (5 * 60 * 1000);
        },
        formatTime(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            let hours = date.getHours();
            const minutes = date.getMinutes().toString().padStart(2, '0');
            const ampm = hours >= 12 ? 'م' : 'ص';
            hours = hours % 12 || 12;
            return hours + ':' + minutes + ' ' + ampm;
        },

        checkHiddenMentions() {
            const container = document.getElementById('messagesContainer');
            if (!container) return;
            const containerTop = container.scrollTop;
            const myName = '{{ Auth::user()->name }}';
            this.seenMentions = JSON.parse(sessionStorage.getItem('seen_mentions_' + roomId) || '[]');
            this.hiddenMentions = [];
            this.allMessages.forEach(msg => {
                if (!msg.is_system && msg.content.includes(myName) && msg.user_id != {{ Auth::id() }} && !this.seenMentions.includes(msg.id)) {
                    const msgElement = document.querySelector(`[data-message-id="${msg.id}"]`);
                    if (msgElement) {
                        const msgTop = msgElement.offsetTop;
                        if (msgTop < containerTop - 10) {
                            this.hiddenMentions.push({ id: msg.id, top: msgTop });
                        } else {
                            if (!this.seenMentions.includes(msg.id)) this.seenMentions.push(msg.id);
                        }
                    }
                }
            });
            sessionStorage.setItem('seen_mentions_' + roomId, JSON.stringify(this.seenMentions));
            this.hiddenMentions.sort((a, b) => b.top - a.top);
            this.hiddenMentionCount = this.hiddenMentions.length;
        },

        goToNextHiddenMention() {
            if (this.hiddenMentions.length === 0) return;
            const target = this.hiddenMentions[0];
            const container = document.getElementById('messagesContainer');
            const msgElement = document.querySelector(`[data-message-id="${target.id}"]`);
            if (msgElement && container) {
                this.seenMentions.push(target.id);
                sessionStorage.setItem('seen_mentions_' + roomId, JSON.stringify(this.seenMentions));
                const msgRect = msgElement.getBoundingClientRect();
                const containerRect = container.getBoundingClientRect();
                const scrollTo = container.scrollTop + (msgRect.top - containerRect.top) - 100;
                container.scrollTo({ top: scrollTo, behavior: 'smooth' });
                msgElement.classList.add('ring-2', 'ring-blue-400', 'ring-offset-2');
                setTimeout(() => { msgElement.classList.remove('ring-2', 'ring-blue-400', 'ring-offset-2'); }, 2000);
            }
            this.hiddenMentions.shift();
            this.hiddenMentionCount = this.hiddenMentions.length;
        },

        resetMentionCheck() { this.$nextTick(() => this.checkHiddenMentions()); },

        handleMentionInput(event) {
            const textarea = event.target;
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
            const cursorPos = textarea.selectionStart;
            const textBeforeCursor = this.newMessage.substring(0, cursorPos);
            const lastAtIndex = textBeforeCursor.lastIndexOf('@');
            if (lastAtIndex !== -1) {
                const textAfterAt = textBeforeCursor.substring(lastAtIndex + 1);
                if (!textAfterAt.includes(' ') && !textAfterAt.includes('@') && !textAfterAt.includes('\n')) {
                    this.mentionQuery = textAfterAt;
                    this.showMentionList = true;
                    this.selectedMentionIndex = -1;
                    clearTimeout(this.mentionSearchTimeout);
                    this.mentionSearchTimeout = setTimeout(() => { this.searchUsers(this.mentionQuery); }, 100);
                    return;
                }
            }
            this.showMentionList = false;
        },

        async searchUsers(query) {
            try {
                const url = query ? `/api/users/search?q=${encodeURIComponent(query)}` : '/api/users/search?q=';
                const response = await fetch(url);
                this.filteredUsers = await response.json();
            } catch (error) { console.error('Search error:', error); }
        },

        selectMention(user) {
            const textarea = this.$refs.messageInput;
            const cursorPos = textarea.selectionStart;
            const textBeforeCursor = this.newMessage.substring(0, cursorPos);
            const lastAtIndex = textBeforeCursor.lastIndexOf('@');
            let beforeMention = '';
            if (lastAtIndex !== -1) {
                beforeMention = this.newMessage.substring(0, lastAtIndex);
                const afterMention = this.newMessage.substring(cursorPos);
                this.newMessage = beforeMention + user.name + ' ' + afterMention;
            }
            this.showMentionList = false;
            this.filteredUsers = [];
            this.mentionQuery = '';
            this.$nextTick(() => {
                const ta = this.$refs.messageInput;
                ta.focus();
                const newPos = beforeMention.length + user.name.length + 1;
                ta.setSelectionRange(newPos, newPos);
            });
        },

        highlightMentions(text) {
            if (!text) return '';
            const memberNames = this.members.map(m => m.name);
            const escapedNames = memberNames.map(name => name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
            const regex = new RegExp(`(${escapedNames.join('|')})`, 'g');
            return text.replace(regex, (match) => `<span class="mention-highlight">${match}</span>`);
        },

        animateCount() { this.countAnimation = true; setTimeout(() => { this.countAnimation = false; }, 500); },

        async markAsViewed(messageId) {
            const msg = this.messages.find(m => m.id === messageId);
            if (msg && msg.user_id == {{ Auth::id() }}) return;
            try { 
                await fetch(`/messages/${messageId}/mark-view`, { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' } 
                }); 
            } catch (error) {}
        },
        
        scrollToBottom() { 
            const container = document.getElementById('messagesContainer'); 
            if (container) container.scrollTop = container.scrollHeight; 
        },
        
        onScroll(e) { 
            if (e.target.scrollTop === 0 && this.firstMessageId && !this.loading) this.loadMoreMessages();
            clearTimeout(this.mentionCheckTimeout);
            this.mentionCheckTimeout = setTimeout(() => { this.checkHiddenMentions(); }, 300);
        },
        
        async loadMoreMessages() {
            this.loading = true;
            try {
                const response = await fetch(`/chat/${roomId}/messages/load?last_id=${this.firstMessageId}`);
                if (!response.ok) return;
                const data = await response.json();
                const olderMessages = data.messages || [];
                if (olderMessages.length > 0) {
                    const existingIds = new Set(this.messages.map(m => m.id));
                    const trulyNew = olderMessages.filter(m => !existingIds.has(m.id));
                    if (trulyNew.length > 0) { 
                        this.messages = [...trulyNew, ...this.messages]; 
                        this.firstMessageId = this.messages[0].id; 
                        trulyNew.forEach(msg => this.updateReactionsSummary(msg)); 
                    }
                }
                if (data.views_data) {
                    Object.keys(data.views_data).forEach(msgId => {
                        const msg = this.messages.find(m => m.id == msgId);
                        if (msg && msg.user_id == {{ Auth::id() }}) { 
                            let viewers = data.views_data[msgId] || []; 
                            viewers = viewers.filter(v => v.user_id != msg.user_id); 
                            this.views[msgId] = viewers; 
                        }
                    });
                }
                this.updateAllMessages();
            } catch (error) { console.error('Load more error:', error); } 
            finally { this.loading = false; }
        },

        async sendMessage() {
            if (!this.newMessage.trim() || this.sendCooldown || this.sending) return;
            this.sending = true;
            const payload = { content: this.newMessage, parent_id: this.replyToMessage?.id || null };
            try {
                const response = await fetch(`/chat/${roomId}/messages`, { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, 
                    body: JSON.stringify(payload) 
                });
                if (!response.ok) throw new Error('فشل الإرسال');
                const data = await response.json();
                const existingIds = new Set(this.messages.map(m => m.id));
                if (!existingIds.has(data.message.id)) {
                    this.messages.push(data.message);
                    this.lastMessageId = data.message.id;
                    if (this.messages.length === 1) this.firstMessageId = data.message.id;
                    this.updateReactionsSummary(data.message);
                    this.updateAllMessages();
                    this.views[data.message.id] = [];
                    const myMember = this.members.find(m => m.id == {{ Auth::id() }});
                    if (myMember) myMember.last_read_at = new Date().toISOString();
                    this.$nextTick(() => this.scrollToBottom());
                    this.markAsViewed(data.message.id);
                }
                this.newMessage = '';
                this.replyToMessage = null;
                this.sendCooldown = true;
                setTimeout(() => { this.sendCooldown = false; }, 3000);
            } catch (error) { console.error('Send error:', error); alert('تعذر إرسال الرسالة.'); } 
            finally { this.sending = false; }
        },

        startReply(message) { this.replyToMessage = message; this.$nextTick(() => document.querySelector('textarea')?.focus()); },
        
        openReactionBar(event, message) {
            event.preventDefault();
            const rect = event.target.getBoundingClientRect();
            let x = Math.min(event.clientX, window.innerWidth - 300);
            let y = rect.top - 10;
            if (y < 100) y = rect.bottom + 10;
            this.reactionBar = { open: true, x, y, message };
        },
        
        quickReact(message, emoji) { this.toggleReaction(message, emoji); this.reactionBar.open = false; },
        
        openFullEmojiPicker(message) { 
            this.reactionBar.open = false; 
            if (this.emojiCategories.length < 6) this.loadMoreEmojiCategories(); 
            this.fullEmojiPicker = { open: true, message }; 
        },
        
        loadMoreEmojiCategories() { 
            this.emojiCategories = [ 
                { key: 'Smileys & Emotion', name: 'ابتسامات', icon: '😃' }, 
                { key: 'People & Body', name: 'أشخاص', icon: '👋' }, 
                { key: 'Animals & Nature', name: 'حيوانات وطبيعة', icon: '🐶' }, 
                { key: 'Food & Drink', name: 'طعام وشراب', icon: '🍔' }, 
                { key: 'Travel & Places', name: 'سفر وأماكن', icon: '🚗' }, 
                { key: 'Activities', name: 'أنشطة', icon: '⚽' }, 
                { key: 'Objects', name: 'أشياء', icon: '💡' }, 
                { key: 'Symbols', name: 'رموز', icon: '❤️' }, 
                { key: 'Flags', name: 'أعلام', icon: '🏁' } 
            ]; 
        },
        
        selectEmoji(emoji, message) { this.toggleReaction(message, emoji); this.fullEmojiPicker.open = false; },
        
        async toggleReaction(message, type) {
            try {
                const res = await fetch(`/messages/${message.id}/reaction`, { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, 
                    body: JSON.stringify({ type }) 
                });
                if (!res.ok) return;
                const data = await res.json();
                message.reactions = data.reactions;
                this.updateReactionsSummary(message);
                const myReaction = message.my_reactions[0] || null;
                if (myReaction) { this.updateTempEmojis(message, myReaction, null); } 
                else {
                    const baseSet = new Set(this.baseEmojis); 
                    this.tempEmojisMap[message.id] = (this.tempEmojisMap[message.id] || [])
                        .filter(e => baseSet.has(e) || message.reactions.some(r => r.type === e && r.user_id != {{ Auth::id() }}));
                }
            } catch (error) { console.error('Reaction error:', error); }
        },
        
        openReactionDetail(event, message, emoji) { 
            event.stopPropagation(); 
            const count = message.reactions_summary[emoji] || 0; 
            const isMine = message.my_reactions && message.my_reactions.includes(emoji); 
            this.reactionDetail = { open: true, x: event.clientX, y: event.clientY, emoji, count, message, isMine }; 
        },
        
        removeReaction(message, emoji) { this.toggleReaction(message, emoji); this.reactionDetail.open = false; },
        heartReaction(message) { this.toggleReaction(message, '❤️'); },
        
        async deleteMessage(message, type) {
            if (type === 'for_everyone' && !confirm('هل أنت متأكد من حذف هذه الرسالة للجميع؟')) return;
            try {
                const messageElement = document.querySelector(`[data-message-id="${message.id}"]`);
                if (messageElement) messageElement.classList.add('message-fading');
                if (type === 'for_everyone') {
                    const response = await fetch(`/messages/${message.id}`, { 
                        method: 'DELETE', 
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, 
                        body: JSON.stringify({ type: 'for_everyone' }) 
                    });
                    if (!response.ok) throw new Error('فشل الحذف');
                    setTimeout(() => {
                        const deletedIndex = this.messages.findIndex(m => m.id === message.id);
                        this.messages = this.messages.filter(m => m.id !== message.id);
                        delete this.views[message.id];
                        this.updateAllMessages();
                        this.$nextTick(() => { 
                            const allMessages = document.querySelectorAll('.message-item'); 
                            allMessages.forEach((el, idx) => { if (idx >= deletedIndex) el.classList.add('message-slide-up'); }); 
                        });
                        this.$dispatch('notify', { message: 'تم حذف الرسالة للجميع', type: 'success' });
                    }, 500);
                } else {
                    this.addToDeletedMessages(message.id);
                    setTimeout(() => {
                        const deletedIndex = this.messages.findIndex(m => m.id === message.id);
                        this.messages = this.messages.filter(m => m.id !== message.id);
                        delete this.views[message.id];
                        this.updateAllMessages();
                        this.$nextTick(() => { 
                            const allMessages = document.querySelectorAll('.message-item'); 
                            allMessages.forEach((el, idx) => { if (idx >= deletedIndex) el.classList.add('message-slide-up'); }); 
                        });
                        this.showUndoToast(message);
                    }, 500);
                }
            } catch (error) { console.error('Delete error:', error); alert('تعذر حذف الرسالة.'); }
        },
        
        showUndoToast(message) {
            this.deletedMessage = message;
            const toast = document.getElementById('undoToast');
            if (toast) {
                if (this.undoTimeout) clearTimeout(this.undoTimeout);
                toast.classList.remove('hide');
                toast.classList.add('show');
                this.undoTimeout = setTimeout(() => { this.hideUndoToast(); }, 4000);
            }
        },
        
        hideUndoToast() {
            const toast = document.getElementById('undoToast');
            if (toast) { toast.classList.remove('show'); toast.classList.add('hide'); setTimeout(() => { this.deletedMessage = null; }, 300); }
            if (this.undoTimeout) { clearTimeout(this.undoTimeout); this.undoTimeout = null; }
        },
        
        undoDelete() {
            if (this.deletedMessage) {
                this.removeFromDeletedMessages(this.deletedMessage.id);
                const insertIndex = this.messages.findIndex(m => m.id > this.deletedMessage.id);
                if (insertIndex === -1) this.messages.push(this.deletedMessage);
                else this.messages.splice(insertIndex, 0, this.deletedMessage);
                this.hideUndoToast();
                const restoredMessage = this.deletedMessage;
                this.deletedMessage = null;
                this.updateAllMessages();
                this.$nextTick(() => {
                    const restoredEl = document.querySelector(`[data-message-id="${restoredMessage.id}"]`);
                    if (restoredEl) restoredEl.classList.add('message-restore');
                });
                this.$dispatch('notify', { message: 'تم استعادة الرسالة', type: 'success' });
            }
        },
        
        canEditMessage(message) {
            if (!message || message.is_system) return false;
            if (message.user_id != {{ Auth::id() }}) return false;
            const createdAt = new Date(message.created_at);
            const now = new Date();
            const hoursDiff = (now - createdAt) / (1000 * 60 * 60);
            return hoursDiff < 24;
        },

        startEditing(message) {
            if (!this.canEditMessage(message)) {
                const createdAt = new Date(message.created_at);
                const now = new Date();
                const hoursDiff = (now - createdAt) / (1000 * 60 * 60);
                const remaining = Math.round(24 - hoursDiff);
                this.$dispatch('notify', { message: remaining > 0 ? `يمكن التعديل خلال ${remaining} ساعة` : 'انتهى وقت التعديل', type: 'error' });
                return;
            }
            this.editingMessage = message;
            this.editContent = message.content;
            this.editError = null;
            this.reactionBar.open = false;
            this.$nextTick(() => {
                const textarea = document.querySelector(`[data-edit-textarea="${message.id}"]`);
                if (textarea) { textarea.focus(); textarea.setSelectionRange(textarea.value.length, textarea.value.length); }
            });
        },

        cancelEdit() { this.editingMessage = null; this.editContent = ''; this.editError = null; },

        async saveEdit() {
            if (!this.editingMessage) return;
            const content = this.editContent.trim();
            if (!content) { this.editError = 'الرسالة لا يمكن أن تكون فارغة'; return; }
            if (content === this.editingMessage.content) { this.cancelEdit(); return; }
            try {
                const response = await fetch(`/messages/${this.editingMessage.id}/update`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ content })
                });
                if (!response.ok) { const error = await response.json(); throw new Error(error.error || 'فشل التعديل'); }
                const data = await response.json();
                const msg = this.messages.find(m => m.id === data.message.id);
                if (msg) { msg.content = data.message.content; msg.is_edited = data.message.is_edited; msg.edited_at = data.message.edited_at; this.updateAllMessages(); }
                this.cancelEdit();
                this.$dispatch('notify', { message: 'تم تعديل الرسالة بنجاح', type: 'success' });
            } catch (error) { console.error('Edit error:', error); this.editError = error.message || 'حدث خطأ أثناء التعديل'; }
        },
        
        insertEmoji(emoji) { this.newMessage += emoji; this.showEmojiPicker = false; },
        
        copyMessage(message) { 
            navigator.clipboard.writeText(message.content); 
            this.$dispatch('notify', { message: 'تم النسخ!', type: 'success' }); 
        },
        
        reportMessage(message) { 
            const reason = prompt('سبب الإبلاغ:'); 
            if (reason) {
                fetch(`/messages/${message.id}/report`, { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, 
                    body: JSON.stringify({ reason }) 
                }).then(() => this.$dispatch('notify', { message: 'تم الإبلاغ', type: 'success' }));
            }
        },
        
        pinMessage(message) { 
            fetch(`/chat/${roomId}/pin/${message?.id || ''}`, { 
                method: 'POST', 
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } 
            }).then(() => location.reload()); 
        },
        
        destroy() { 
            if (this.undoTimeout) clearTimeout(this.undoTimeout);
            fetch(`/chat/${roomId}/mark-left`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            });
        }
    }));
});
    </script>
    @endpush
</x-app-layout>