<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\SourceCtrl;

class SendScheduledEmails extends Command
{
    protected $signature = 'email:send-scheduled';
    protected $description = 'Send active scheduled student emails when their scheduled time arrives';

    public function handle()
    {
        $now = now('Asia/Dhaka')->format('Y-m-d H:i:s');
        $rows = DB::table('emails')
            ->where('status', 'Scheduled')
            ->where('is_active', 1)
            ->whereNotNull('datetime')
            ->where('datetime', '<=', $now)
            ->orderBy('id')
            ->limit(50)
            ->get();

        $source = new SourceCtrl();
        foreach ($rows as $row) {
            try {
                $studentIds = json_decode($row->student_id ?: '[]', true) ?: [];
                $recipients = DB::table('students')
                    ->whereIn('id', $studentIds)
                    ->whereNotNull('email')
                    ->where('email', '<>', '')
                    ->pluck('email')
                    ->unique()
                    ->values()
                    ->all();

                if (!$recipients) {
                    throw new \RuntimeException('No valid student email addresses found.');
                }

                $source->sendMail([
                    'email_to' => 'liveeducationbd24@gmail.com',
                    'email_bcc' => $recipients,
                    'subject' => $row->title ?: 'Live Education BD',
                    'comments' => $row->details ?: '',
                    'sender_account_ids' => json_decode($row->sender_account_ids ?? '[]', true) ?: [],
                ]);

                DB::table('emails')->where('id', $row->id)->update([
                    'status' => 'Sent',
                    'updated_at' => now('Asia/Dhaka'),
                ]);
            } catch (\Throwable $e) {
                \Log::error('Scheduled Email #' . $row->id . ' failed: ' . $e->getMessage());
            }
        }

        $this->info('Processed ' . $rows->count() . ' scheduled email(s).');
        return 0;
    }
}
