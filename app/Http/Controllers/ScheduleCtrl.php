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
    $source = new SourceCtrl;

    $now = now();
    if(App::environment('production'))
    {
      $past = new DateTime();
      // $past->modify('+6 hours 11 minutes');
      $past->modify('+11 minutes');
      $now = $past->format('Y-m-d H:i:s');
    }
    
    $smses = Sms::where('status', 'Scheduled')
    ->where('datetime', '<=', $now)
    ->get();

    if($smses)
    {
      foreach($smses as $sms)
      {
        //string to array that have value only string
        $student_array = json_decode($sms->student_id);

        // array numbers/actual array
        $student_ids = array_map('intval', $student_array);
        $student_contacts = Student::whereIn('id', $student_ids)
        ->pluck('contact')->toArray();

        $contact = array_map(function($num)
        {
          return '+88'.$num;
        }, $student_contacts);

        $smsable_contact = implode(', ', $contact);
        
        /** ------------- send sms by gateway --------------- */ 
        $source->sms_send($smsable_contact, $sms->details);

        Sms::where('id', $sms->id)->update(['status' => 'Sent']);
      }

      \Log::info('Schedule SMS Test ' . now());
    }
  }

  static function scheduleEmail()
  {
    $source = new SourceCtrl;

    $now = now();
    if(App::environment('production'))
    {
      $past = new DateTime();
      // $past->modify('+6 hours 11 minutes');
      $past->modify('+11 minutes');
      $now = $past->format('Y-m-d H:i:s');
    }
    
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