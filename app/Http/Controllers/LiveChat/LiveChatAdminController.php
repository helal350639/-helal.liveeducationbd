<?php
namespace App\Http\Controllers\LiveChat;

use App\Http\Controllers\Controller;
use App\Models\LiveChatConversation;
use App\Models\LiveChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LiveChatAdminController extends Controller
{
    public function index() { return view('live-chat.admin.index'); }

    public function conversations()
    {
        if (!Schema::hasTable('lebd_live_chat_conversations') || !Schema::hasTable('lebd_live_chat_messages')) {
            return response()->json([
                'message' => 'Live Chat database tables are missing. Import LEBD_LIVE_CHAT_TABLES.sql or run the Live Chat migration.',
                'required_tables' => ['lebd_live_chat_conversations', 'lebd_live_chat_messages'],
            ], 503);
        }

        $chats = LiveChatConversation::query()
            ->withCount(['messages as unread_count' => fn ($q) => $q->where('sender','visitor')->whereNull('read_by_admin_at')])
            ->orderByRaw('CASE WHEN last_message_at IS NULL THEN 1 ELSE 0 END ASC')
            ->orderByDesc('last_message_at')->orderByDesc('id')->limit(150)->get();
        return response()->json(['conversations' => $chats]);
    }

    public function messages(LiveChatConversation $conversation)
    {
        $rows = $conversation->messages()->orderBy('id')->limit(300)->get(['id','sender','body','created_at']);
        $conversation->messages()->where('sender','visitor')->whereNull('read_by_admin_at')->update(['read_by_admin_at' => now()]);
        $conversation->update(['admin_last_read_at' => now()]);
        return response()->json(['messages' => $rows, 'status' => $conversation->status]);
    }

    public function reply(Request $request, LiveChatConversation $conversation)
    {
        $data = $request->validate(['message' => ['required','string','max:2000']]);
        DB::transaction(function () use ($conversation, $data) {
            LiveChatMessage::create(['conversation_id' => $conversation->id, 'sender' => 'admin', 'body' => trim($data['message'])]);
            $conversation->update(['status' => 'open', 'last_message_at' => now()]);
        });
        return response()->json(['ok' => true]);
    }

    public function status(Request $request, LiveChatConversation $conversation)
    {
        $data = $request->validate(['status' => ['required','in:open,closed']]);
        $conversation->update(['status' => $data['status']]);
        return response()->json(['ok' => true]);
    }
}
