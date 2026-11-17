<?php
namespace App\Http\Controllers\LiveChat;

use App\Http\Controllers\Controller;

use App\Models\LiveChatConversation;
use App\Models\LiveChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LiveChatVisitorController extends Controller
{
    private function conversation(Request $request, bool $create): ?LiveChatConversation
    {
        $key = $request->session()->get('lebd_live_chat_key');
        $chat = $key ? LiveChatConversation::where('visitor_key', $key)->first() : null;
        if (!$chat && $create) {
            $key = Str::random(64);
            $request->session()->put('lebd_live_chat_key', $key);
            $student = Auth::guard('student')->user();
            $chat = LiveChatConversation::create([
                'visitor_key' => $key,
                'student_id' => $student ? $student->id : null,
                'visitor_name' => $student ? (string)($student->name ?? 'Student') : 'Website Visitor',
                'visitor_email' => $student ? ($student->email ?? null) : null,
                'status' => 'open',
            ]);
        }
        return $chat;
    }

    public function messages(Request $request)
    {
        $chat = $this->conversation($request, false);
        if (!$chat) return response()->json(['messages' => [], 'conversation_id' => null]);
        $rows = $chat->messages()->orderBy('id')->limit(250)->get(['id', 'sender', 'body', 'created_at']);
        $chat->messages()->where('sender', 'admin')->whereNull('read_by_visitor_at')->update(['read_by_visitor_at' => now()]);
        return response()->json(['conversation_id' => $chat->id, 'messages' => $rows]);
    }

    public function send(Request $request)
    {
        $data = $request->validate(['message' => ['required', 'string', 'max:2000']]);
        $chat = $this->conversation($request, true);
        DB::transaction(function () use ($chat, $data) {
            LiveChatMessage::create(['conversation_id' => $chat->id, 'sender' => 'visitor', 'body' => trim($data['message'])]);
            $chat->update(['status' => 'open', 'last_message_at' => now()]);
        });
        return response()->json(['ok' => true]);
    }
}
