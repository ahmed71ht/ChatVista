<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\Reaction;
use App\Models\MessageView;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\JoinRequest;
use App\Events\MessageSent;
use App\Events\ReactionToggled;
use App\Events\MessageViewed;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = ChatRoom::with(['creator', 'messages' => function($q) {
            $q->latest()->take(1);
        }])->withCount('members');

        if ($request->has('my')) {
            $query->whereHas('members', function($q) {
                $q->where('user_id', Auth::id());
            });
        } else {
            $query->publicRooms();
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $rooms = $query->latest()->paginate(20);

        // ✅ نجيب كل room IDs
        $roomIds = $rooms->pluck('id')->toArray();
        
        // ✅ نجيب العدادات للمستخدم الحالي مرة واحدة
        $unreadCounts = \App\Models\UnreadCount::where('user_id', Auth::id())
            ->whereIn('room_id', $roomIds)
            ->get()
            ->keyBy('room_id');
        
        // ✅ نضيف العدادات لكل غرفة
        $rooms->getCollection()->transform(function ($room) use ($unreadCounts) {
            $unread = $unreadCounts->get($room->id);
            $room->unread_messages = $unread ? $unread->unread_messages : 0;
            $room->unread_mentions = $unread ? $unread->unread_mentions : 0;
            return $room;
        });

        return view('chat.index', compact('rooms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:chat_rooms,slug|regex:/^[\p{L}-]+$/u',
            'description' => 'nullable|string',
            'type' => 'required|in:public,private,protected',
            'password' => 'required_if:type,protected|string|min:6|nullable',
            'category' => 'nullable|string|max:100',
        ]);

        $room = ChatRoom::create([
            'name' => $request->name,
            'slug' => $request->slug ?: null,
            'description' => $request->description,
            'type' => $request->type,
            'password' => $request->password,
            'category' => $request->category,
            'creator_id' => Auth::id(),
        ]);

        $room->members()->attach(Auth::id(), ['role' => 'admin']);

        // بث حدث إنشاء الغرفة للجميع
        broadcast(new \App\Events\RoomCreated($room))->toOthers();

        return redirect()->route('chat.room', $room->slug);
    }

    public function getUnreadCounts(ChatRoom $room)
    {
        $unread = \App\Models\UnreadCount::where('user_id', Auth::id())
            ->where('room_id', $room->id)
            ->first();
        
        return response()->json([
            'unread_messages' => $unread ? $unread->unread_messages : 0,
            'unread_mentions' => $unread ? $unread->unread_mentions : 0,
        ]);
    }

    public function markLeft(ChatRoom $room)
    {
        if ($room->members->contains(Auth::id())) {
            $room->members()->updateExistingPivot(Auth::id(), ['last_read_at' => null]);
        }
        return response()->json(['success' => true]);
    }

    public function show(Request $request, ChatRoom $room)
    {
        $isNewMember = false;
        
        // ✅ تصفير العداد
        \App\Models\UnreadCount::where('user_id', Auth::id())
            ->where('room_id', $room->id)
            ->update(['unread_messages' => 0, 'unread_mentions' => 0]);
        
        // ✅ تحديث last_read_at فوراً - هذا هو الحل
        if ($room->members->contains(Auth::id())) {
            $room->members()->updateExistingPivot(Auth::id(), ['last_read_at' => now()]);
        }

        // التحقق من نوع الغرفة وإضافة العضو إذا كانت عامة
        if ($room->type === 'public' && !$room->members->contains(Auth::id())) {
            $room->members()->attach(Auth::id(), ['role' => 'member']);
            $isNewMember = true;
        }

        // التحقق من أن المستخدم لديه صلاحية الدخول
        if ($room->creator_id === Auth::id() || $room->approvedMembers->contains(Auth::id())) {
            // تحديث وقت آخر قراءة
            $room->members()->updateExistingPivot(Auth::id(), ['last_read_at' => now()]);

            // إرسال رسالة انضمام فقط إذا كان المستخدم جديد
            if ($isNewMember) {
                // إنشاء رسالة نظام
                \App\Models\SystemMessage::create([
                    'room_id' => $room->id,
                    'type' => 'member_joined',
                    'content' => "لقد انضم " . Auth::user()->name . " إلى غرفة " . $room->name,
                    'data' => [
                        'user_id' => Auth::id(),
                        'user_name' => Auth::user()->name,
                    ],
                ]);

                // بث الحدث للآخرين فقط
                broadcast(new \App\Events\MemberJoined(
                    $room->id,
                    Auth::id(),
                    Auth::user()->name,
                    $room->name
                ))->toOthers();
            }

            // تحميل العلاقات
            $room->load(['members', 'pinnedMessage']);
            
            // جلب الرسائل العادية
            $messages = $room->messages()
                ->with(['user', 'reactions', 'parent.user'])
                ->oldest()
                ->paginate(30);

            // ✅ جلب رسائل النظام - الكود الجديد
            $isCreator = Auth::id() === $room->creator_id;
            
            $systemMessages = \App\Models\SystemMessage::where('room_id', $room->id)
                ->where(function ($query) use ($isCreator) {
                    // join_request: المالك فقط يشوفها
                    if (!$isCreator) {
                        $query->where('type', '!=', 'join_request');
                    }
                    
                    // رسائل الانضمام والمغادرة وطلبات الانضمام: ما نشوف رسائلنا
                    $query->where(function ($q) {
                        $q->whereNotIn('type', ['member_joined', 'member_left', 'join_request'])
                        ->orWhere(function ($inner) {
                            $inner->whereIn('type', ['member_joined', 'member_left', 'join_request'])
                                    ->where('data->user_id', '!=', Auth::id());
                        });
                    });
                })
                ->oldest()
                ->get()
                ->map(function ($msg) {
                    return [
                        'id' => 'system-' . $msg->id,
                        'user_id' => null,
                        'content' => $msg->content,
                        'created_at' => $msg->created_at->toISOString(),
                        'is_system' => true,
                        'type' => $msg->type,
                    ];
                });

            // تسجيل مشاهدة الرسائل
            foreach ($messages as $msg) {
                \App\Models\MessageView::firstOrCreate([
                    'message_id' => $msg->id,
                    'user_id'    => Auth::id(),
                ]);
            }

            // جلب بيانات المشاهدات
            $viewsData = \App\Models\MessageView::whereIn('message_id', $messages->pluck('id'))
                ->with('user:id,name')
                ->get()
                ->groupBy('message_id')
                ->map(fn($views) => $views->map(fn($v) => [
                    'user_id' => $v->user_id,
                    'name'    => $v->user->name,
                ])->values()->toArray());

            // جلب بيانات الأعضاء
            $membersData = $room->members->map(fn($m) => [
                'id'           => $m->id,
                'name'         => $m->name,
                'last_read_at' => $m->pivot->last_read_at,
            ]);

            return view('chat.room', compact('room', 'messages', 'systemMessages', 'membersData', 'viewsData'));
        }

        // معالجة الغرف الخاصة
        if ($room->type === 'private') {
            $existingRequest = $room->joinRequests()->where('user_id', Auth::id())->first();
            if (!$existingRequest) {
                return redirect()->route('chat.join', $room->slug);
            } elseif ($existingRequest->status === 'pending') {
                return view('chat.request-pending', compact('room'));
            } elseif ($existingRequest->status === 'rejected') {
                return view('chat.request-rejected', compact('room'));
            }
        }

        // معالجة الغرف المحمية بكلمة مرور
        if ($room->type === 'protected' && !$room->members->contains(Auth::id())) {
            if (!$request->session()->get('room_password_'.$room->id)) {
                return redirect()->route('chat.join', $room->slug);
            }
        }

        // تحديث وقت آخر قراءة للمستخدم
        $room->members()->updateExistingPivot(Auth::id(), ['last_read_at' => now()]);
        $room->load(['members', 'pinnedMessage']);
        
        // جلب الرسائل العادية
        $messages = $room->messages()
            ->with(['user', 'reactions', 'parent.user'])
            ->oldest()
            ->paginate(30);

        // ✅ جلب رسائل النظام - الكود الجديد (النسخة الثانية)
        $isCreator = Auth::id() === $room->creator_id;
        
        $systemMessages = \App\Models\SystemMessage::where('room_id', $room->id)
            ->where(function ($query) use ($isCreator) {
                // join_request: المالك فقط يشوفها
                if (!$isCreator) {
                    $query->where('type', '!=', 'join_request');
                }
                
                // رسائل الانضمام والمغادرة وطلبات الانضمام: ما نشوف رسائلنا
                $query->where(function ($q) {
                    $q->whereNotIn('type', ['member_joined', 'member_left', 'join_request'])
                    ->orWhere(function ($inner) {
                        $inner->whereIn('type', ['member_joined', 'member_left', 'join_request'])
                                ->where('data->user_id', '!=', Auth::id());
                    });
                });
            })
            ->oldest()
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => 'system-' . $msg->id,
                    'user_id' => null,
                    'content' => $msg->content,
                    'created_at' => $msg->created_at->toISOString(),
                    'is_system' => true,
                    'type' => $msg->type,
                ];
            });

        // تسجيل مشاهدة الرسائل
        foreach ($messages as $msg) {
            \App\Models\MessageView::firstOrCreate([
                'message_id' => $msg->id,
                'user_id'    => Auth::id(),
            ]);
        }

        // جلب بيانات المشاهدات
        $viewsData = \App\Models\MessageView::whereIn('message_id', $messages->pluck('id'))
            ->with('user:id,name')
            ->get()
            ->groupBy('message_id')
            ->map(fn($views) => $views->map(fn($v) => [
                'user_id' => $v->user_id,
                'name'    => $v->user->name,
            ])->values()->toArray());

        // جلب بيانات الأعضاء
        $membersData = $room->members->map(fn($m) => [
            'id'   => $m->id,
            'name' => $m->name,
            'last_read_at' => $m->pivot->last_read_at,
        ]);

        return view('chat.room', compact('room', 'messages', 'systemMessages', 'membersData', 'viewsData'));
    }

    public function joinForm(ChatRoom $room)
    {
        if ($room->type === 'private') {
            return view('chat.request-join', compact('room'));
        }
        if ($room->type === 'protected') {
            return view('chat.join', compact('room'));
        }
        return redirect()->route('chat.room', $room->slug);
    }

    public function requestJoin(Request $request, ChatRoom $room)
    {
        if ($room->type !== 'private') {
            abort(403);
        }

        $existing = $room->joinRequests()->where('user_id', Auth::id())->first();
        if ($existing) {
            if ($existing->status === 'pending') {
                return back()->with('info', 'طلبك قيد المراجعة.');
            } elseif ($existing->status === 'rejected') {
                $existing->update(['status' => 'pending']);
                return back()->with('success', 'تم إعادة إرسال الطلب.');
            } else {
                return redirect()->route('chat.room', $room->slug);
            }
        }

        $room->joinRequests()->create([
            'user_id' => Auth::id(),
            'status' => 'pending',
        ]);

        // ✅ حفظ رسالة نظام جديدة - type: join_request
        \App\Models\SystemMessage::create([
            'room_id' => $room->id,
            'type' => 'join_request',
            'content' => Auth::user()->name . " قدم طلب انضمام إلى غرفة " . $room->name,
            'data' => [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
            ],
        ]);

        // ✅ بث حدث للمالك
        broadcast(new \App\Events\JoinRequestSubmitted(
            $room->id,
            Auth::id(),
            Auth::user()->name,
            $room->name
        ))->toOthers();

        return redirect()->route('chat.room', $room->slug)->with('success', 'تم إرسال طلب الانضمام إلى مدير الغرفة.');
    }

    public function checkRequests(ChatRoom $room)
    {
        if ($room->creator_id !== Auth::id()) {
            abort(403);
        }

        $requests = $room->joinRequests()->with('user')->latest()->get()->map(function ($req) {
            return [
                'id' => $req->id,
                'user' => ['name' => $req->user->name],
                'status' => $req->status,
                'created_at_human' => $req->created_at->diffForHumans(),
            ];
        });
        
        return response()->json(['requests' => $requests]);
    }

    public function checkSystemMessages(ChatRoom $room)
    {
        $isCreator = Auth::id() === $room->creator_id;
        
        $lastCheck = session('last_system_check_' . $room->id) ?? now()->subMinute();
        
        $messages = \App\Models\SystemMessage::where('room_id', $room->id)
            ->where('created_at', '>', $lastCheck)
            ->where(function ($query) use ($isCreator) {
                if (!$isCreator) {
                    $query->where('type', '!=', 'join_request');
                }
                $query->where(function ($q) {
                    $q->whereNotIn('type', ['member_joined', 'member_left', 'join_request'])
                    ->orWhere(function ($inner) {
                        $inner->whereIn('type', ['member_joined', 'member_left', 'join_request'])
                                ->where('data->user_id', '!=', Auth::id());
                    });
                });
            })
            ->oldest()
            ->get()
            ->map(function ($msg) {
                return [
                    'id' => 'system-' . $msg->id,
                    'user_id' => null,
                    'content' => $msg->content,
                    'created_at' => $msg->created_at->toISOString(),
                    'is_system' => true,
                    'type' => $msg->type,
                ];
            });
        
        session(['last_system_check_' . $room->id => now()]);
        
        return response()->json(['messages' => $messages]);
    }

    public function manageRequests(ChatRoom $room)
    {
        if ($room->creator_id !== Auth::id()) {
            abort(403);
        }

        $requests = $room->joinRequests()->with('user')->latest()->get();

        return view('chat.manage-requests', compact('room', 'requests'));
    }

    public function handleRequest(ChatRoom $room, JoinRequest $joinRequest, $action)
    {
        if ($room->creator_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($action, ['approve', 'reject'])) {
            abort(400);
        }

        $joinRequest->update([
            'status' => $action === 'approve' ? 'approved' : 'rejected',
        ]);

        $userName = \App\Models\User::find($joinRequest->user_id)->name;

        if ($action === 'approve') {
            $room->approvedMembers()->syncWithoutDetaching([
                $joinRequest->user_id => ['role' => 'member']
            ]);
            
            // إضافة العضو إلى الغرفة
            $room->members()->syncWithoutDetaching([$joinRequest->user_id => ['role' => 'member']]);
            
            // ✅ حفظ رسالة نظام - تم القبول
            \App\Models\SystemMessage::create([
                'room_id' => $room->id,
                'type' => 'member_joined',
                'content' => "تم قبول دعوى الانضمام، انضم {$userName} إلى غرفة {$room->name}",
                'data' => [
                    'user_id' => $joinRequest->user_id,
                    'user_name' => $userName,
                ],
            ]);
            
            // ✅ بث حدث member.joined للكل
            broadcast(new \App\Events\MemberJoined(
                $room->id,
                $joinRequest->user_id,
                $userName,
                $room->name
            ))->toOthers();
        }

        // ✅ بث نتيجة الطلب للشخص اللي قدمه (سواء قبول أو رفض)
        broadcast(new \App\Events\JoinRequestHandled(
            $room->id,
            $joinRequest->user_id,
            $userName,
            $room->name,
            $action
        ))->toOthers();

        return back()->with('success', $action === 'approve' ? 'تم قبول العضو.' : 'تم رفض الطلب.');
    }

    public function join(Request $request, ChatRoom $room)
    {
        if ($room->type === 'protected') {
            $request->validate(['password' => 'required|string']);
            if ($request->password !== $room->password) {
                return back()->withErrors(['password' => 'كلمة المرور غير صحيحة']);
            }
            session(['room_password_'.$room->id => true]);
        }

        $room->members()->syncWithoutDetaching([Auth::id() => ['role' => 'member']]);

        return redirect()->route('chat.room', $room->slug);
    }

    public function leaveRoom(ChatRoom $room)
    {
        // التحقق من أن المستخدم عضو في الغرفة
        if (!$room->members->contains(Auth::id())) {
            return back()->with('error', 'أنت لست عضواً في هذه الغرفة');
        }

        // منع مغادرة المدير إذا كان الوحيد
        if ($room->creator_id === Auth::id() && $room->members->count() <= 1) {
            return back()->with('error', 'لا يمكنك مغادرة الغرفة لأنك المدير الوحيد');
        }

        $userName = Auth::user()->name;
        $roomName = $room->name;
        $userId = Auth::id();
        $roomId = $room->id;

        // إذا كان المدير يغادر، نقل الملكية لأول عضو
        if ($room->creator_id === Auth::id()) {
            $newAdmin = $room->members()->where('user_id', '!=', Auth::id())->first();
            if ($newAdmin) {
                $room->creator_id = $newAdmin->id;
                $room->save();
                $room->members()->updateExistingPivot($newAdmin->id, ['role' => 'admin']);
            }
        }

        // حفظ رسالة النظام
        \App\Models\SystemMessage::create([
            'room_id' => $room->id,
            'type' => 'member_left',
            'content' => "غادر {$userName} غرفة {$roomName}",
            'data' => [
                'user_id' => $userId,
                'user_name' => $userName,
            ],
        ]);

        // إزالة العضو من الغرفة
        $room->members()->detach(Auth::id());

        // بث حدث المغادرة للآخرين (رسالة النظام)
        broadcast(new \App\Events\MemberLeft(
            $room->id,
            $userId,
            $userName,
            $roomName
        ))->toOthers();

        // بث حدث إزالة الغرفة من قائمة محادثاتي للمستخدم (بدون toOthers عشان يوصل للمستخدم نفسه)
        broadcast(new \App\Events\RoomLeft(
            $room->id,
            Auth::id()
        ));

        return redirect()->route('chat.index', ['my' => 1])
            ->with('success', 'تم مغادرة الغرفة بنجاح');
    }

    public function sendMessage(Request $request, ChatRoom $room)
    {
        $request->validate(['content' => 'required|string']);

        if (!$room->members->contains(Auth::id())) {
            abort(403);
        }

        $message = $room->messages()->create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'parent_id' => $request->parent_id,
        ]);

        // استخراج المنشنات
        preg_match_all('/@(\w+)/', $request->content, $matches);
        $mentionedNames = $matches[1] ?? [];
        $mentionedUserIds = [];
        
        foreach ($mentionedNames as $username) {
            $mentionedUser = \App\Models\User::where('name', $username)->first();
            if ($mentionedUser && $mentionedUser->id !== Auth::id()) {
                \App\Models\Mention::create([
                    'message_id' => $message->id,
                    'mentioned_user_id' => $mentionedUser->id,
                ]);
                $mentionedUserIds[] = $mentionedUser->id;
            }
        }

        // ✅ زيادة عداد الرسائل غير المقروءة للأعضاء الغائبين فقط
        $allMembers = $room->members()->where('user_id', '!=', Auth::id())->get();

        foreach ($allMembers as $member) {
            $isAbsent = !$member->pivot->last_read_at;
            
            if ($isAbsent) {
                $unread = \App\Models\UnreadCount::firstOrCreate(
                    ['user_id' => $member->id, 'room_id' => $room->id],
                    ['unread_messages' => 0, 'unread_mentions' => 0]
                );
                $unread->increment('unread_messages');
                
                if (in_array($member->id, $mentionedUserIds)) {
                    $unread->increment('unread_mentions');
                }
            }
        }

        $message->load(['user', 'reactions', 'parent.user']);
        broadcast(new MessageSent($message))->toOthers();
        
        // ✅ بث تحديث الغرفة
        broadcast(new \App\Events\RoomUpdated($room))->toOthers();

        return response()->json(['message' => $message]);
    }

    public function updateMessage(Request $request, Message $message)
    {
        // التحقق من أن المستخدم هو صاحب الرسالة
        if ($message->user_id !== Auth::id()) {
            return response()->json([
                'error' => 'لا يمكنك تعديل هذه الرسالة'
            ], 403);
        }

        // التحقق من أن الرسالة لم تتجاوز 24 ساعة
        if ($message->created_at->diffInHours(now()) >= 24) {
            return response()->json([
                'error' => 'لا يمكن تعديل رسالة مضى عليها أكثر من 24 ساعة'
            ], 422);
        }

        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        // تحديث المحتوى
        $message->update([
            'content' => $request->content,
            'is_edited' => true,
            'edited_at' => now(),
        ]);

        // تحميل العلاقات
        $message->load(['user', 'reactions', 'parent.user']);

        // بث الحدث للآخرين فقط
        broadcast(new \App\Events\MessageUpdated($message))->toOthers();

        return response()->json([
            'message' => $message,
            'is_edited' => true,
            'edited_at' => $message->edited_at->diffForHumans(),
        ]);
    }

    public function destroyMessage(Request $request, Message $message)
    {
        if ($message->user_id !== Auth::id()) {
            abort(403);
        }

        $deleteType = $request->input('type', 'for_me');

        if ($deleteType === 'for_everyone') {
            $message->delete();
            broadcast(new \App\Events\MessageDeleted($message->id, $message->room_id))->toOthers();
            return response()->json(['success' => true, 'deleted' => true]);
        } else {
            return response()->json(['success' => true, 'deleted' => false]);
        }
    }

    public function loadMessages(ChatRoom $room, Request $request)
    {
        $lastId = $request->input('last_id');
        $afterId = $request->input('after_id');
        $knownIds = $request->input('known_ids', []);
        $lastMembersUpdate = $request->input('last_members_update');

        $messagesQuery = $room->messages()->with(['user', 'reactions', 'parent.user']);

        if ($afterId) {
            $newMessages = $messagesQuery->where('id', '>', $afterId)->oldest()->get();

            foreach ($newMessages as $msg) {
                \App\Models\MessageView::firstOrCreate([
                    'message_id' => $msg->id,
                    'user_id' => Auth::id(),
                ]);
            }

            $updatedReactions = [];
            if (!empty($knownIds)) {
                $changed = $room->messages()
                    ->whereIn('id', $knownIds)
                    ->with('reactions')
                    ->get()
                    ->filter(fn($msg) => $msg->reactions->isNotEmpty())
                    ->mapWithKeys(fn($msg) => [
                        $msg->id => $msg->reactions->map(fn($r) => [
                            'user_id' => $r->user_id,
                            'type' => $r->type
                        ])->values()->toArray()
                    ]);
                $updatedReactions = $changed;
            }

            $updatedMembers = [];
            if ($lastMembersUpdate) {
                $members = $room->members()
                    ->wherePivot('last_read_at', '>', $lastMembersUpdate)
                    ->get()
                    ->map(fn($m) => [
                        'id'           => $m->id,
                        'name'         => $m->name,
                        'last_read_at' => $m->pivot->last_read_at,
                    ]);
                $updatedMembers = $members;
            }

            $allIds = $newMessages->pluck('id')->merge($knownIds)->unique();
            $viewsData = \App\Models\MessageView::whereIn('message_id', $allIds)
                ->with('user:id,name')
                ->get()
                ->groupBy('message_id')
                ->map(fn($views) => $views->map(fn($v) => [
                    'user_id' => $v->user_id,
                    'name' => $v->user->name,
                ])->values()->toArray());

            return response()->json([
                'messages'          => $newMessages,
                'updated_reactions' => $updatedReactions,
                'updated_members'   => $updatedMembers,
                'views_data'        => $viewsData,
                'server_time'       => now()->toISOString(),
            ]);
        } elseif ($lastId) {
            $olderMessages = $messagesQuery->where('id', '<', $lastId)->latest()->take(30)->get()->reverse()->values();

            $olderIds = $olderMessages->pluck('id');
            $viewsData = \App\Models\MessageView::whereIn('message_id', $olderIds)
                ->with('user:id,name')
                ->get()
                ->groupBy('message_id')
                ->map(fn($views) => $views->map(fn($v) => [
                    'user_id' => $v->user_id,
                    'name' => $v->user->name,
                ])->values()->toArray());

            return response()->json([
                'messages'   => $olderMessages,
                'views_data' => $viewsData,
            ]);
        }

        return response()->json(['messages' => []]);
    }

    public function markAsViewed(Message $message)
    {
        \App\Models\MessageView::firstOrCreate([
            'message_id' => $message->id,
            'user_id' => Auth::id(),
        ]);

        broadcast(new MessageViewed($message->id, Auth::id(), Auth::user()->name, $message->room_id))->toOthers();

        return response()->json(['success' => true]);
    }

    public function toggleReaction(Message $message, Request $request)
    {
        $user = Auth::user();
        $type = $request->input('type', 'like');

        $existing = $message->reactions()->where('user_id', $user->id)->first();

        if ($existing) {
            $oldType = $existing->type;
            $existing->delete();

            if ($oldType !== $type) {
                $message->reactions()->create(['user_id' => $user->id, 'type' => $type]);
            }
        } else {
            $message->reactions()->create(['user_id' => $user->id, 'type' => $type]);
        }

        $message->load('reactions');

        broadcast(new ReactionToggled($message))->toOthers();

        return response()->json([
            'reactions' => $message->reactions,
        ]);
    }

    public function searchUsers(Request $request)
    {
        $query = $request->get('q', '');
        
        $users = \App\Models\User::where('id', '!=', Auth::id())
            ->where('name', 'like', "%{$query}%")
            ->limit(8)
            ->get(['id', 'name']);
        
        return response()->json($users);
    }

    public function pinMessage(ChatRoom $room, Message $message)
    {
        if (!$room->isAdmin(Auth::user())) {
            abort(403);
        }

        $room->update(['pinned_message_id' => $message->id]);

        return back()->with('success', 'تم تثبيت الرسالة');
    }

    public function reportMessage(Message $message, Request $request)
    {
        $message->reports()->create([
            'user_id' => Auth::id(),
            'reason' => $request->reason,
        ]);

        return back()->with('success', 'تم الإبلاغ عن الرسالة');
    }
}