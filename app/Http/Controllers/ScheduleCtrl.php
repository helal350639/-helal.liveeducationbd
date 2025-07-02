<?php

namespace App\Http\Controllers;

use App\Http\Controllers\SourceCtrl;
use Illuminate\Http\Request;
use App\Models\Batch;
use App\Models\Sms;
use App\Models\Email;
use App\Models\Role;
use App\Models\Student;
use Auth;
use Image;
use Toastr;
use File;
use Session;
use DateTime;
use App;

class ScheduleCtrl extends Controller
{
  static function scheduleSms()
  {
    $sms = Sms::all();
    dd($sms);
    return 'schedule sms';
  }

  static function scheduleEmail()
  {
    $source = new SourceCtrl;

    $now = now();
    if(App::environment('production'))
    {
      $past = new DateTime();
      $past->modify('+6 hours 11 minutes');
      $now = $past->format('Y-m-d H:i:s');
    }
    // dd($now);
    
    $emails = Email::where('status', 'Scheduled')
    ->where('datetime', '<=', $now)
    ->get();

    if($emails)
    {
      foreach($emails as $email)
      {
        $student_array = json_decode($email->student_id);
        $student_ids = array_map('intval', $student_array);
        $student_emails = Student::whereIn('id', $student_ids)
        ->pluck('email')->toArray();
        $emails = array_map('trim', $student_emails);

        // create email data
        $emailData = [
          'email_to' => 'liveeducationbd24@gmail.com',
          'email_bcc' => $emails,
          'subject'  => $email->title,
          'comments' => $email->details
        ];

        // send email to the students
        $source->sendMail($emailData);
        Email::where('id', $email->id)->update(['status' => 'Sent']);
      }

      \Log::info('Schedule Test ' . now());
    }
  }
}