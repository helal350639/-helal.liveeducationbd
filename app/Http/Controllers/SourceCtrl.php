<?php
namespace App\Http\Controllers;

use Image;
use File;
use Mail;
use DateTime;

class SourceCtrl extends Controller
{
  public function sms_send($number, $sms)
  {
    //number/numbers like "01xxxxxx.., 01xxxxxx.."
    $url = "http://bulksmsbd.net/api/smsapi";
    $api_key = env('SMS_API_KEY');
    $senderid = env('SMS_ID');
    $number = $number;
    $message = $sms;
  
    $data = [
        "api_key" => $api_key,
        "senderid" => $senderid,
        "number" => $number,
        "message" => $message
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
  }

  public function uploadImage($file, $path, $size = null)
  {
    $file_name = uniqid().'.'.$file->getClientOriginalExtension();
    $path = 'uploads/'.$path;
    $file->move(public_path($path), $file_name);
    $fileUrl = '/'.$path.$file_name;
    return $fileUrl;
  }

  public function dformat($date)
  {
    if($date)
    {
      return date('d M Y', strtotime($date));
    }
    return '';
  }

  public function tformat($date)
  {
    if($date)
    {
      return date('h:i:s A', strtotime($date));
    }
    return '';
  }

  public function dtformat($date)
  {
    if($date)
    {
      return date('d M Y h:i:s A', strtotime($date));
    }
    return '';
  }

  public function dtcformat($date)
  {
    if($date)
    {
      return date('M d, Y H:i:s', strtotime($date));
    }
    return '';
  }

  public function mcqlist()
  {
    return [
      ['ক.', 'খ.', 'গ.', 'ঘ.', 'ঙ.'],
      ['ক)', 'খ)', 'গ)', 'ঘ)', 'ঙ)'],
      ['(ক)', '(খ)', '(গ)', '(ঘ)', '(ঙ)'],
      ['1.', '2.', '3.', '4.', '5.'],
      ['1)', '2)', '3)', '4)', '5)'],
      ['(1)', '(2)', '(3)', '(4)', '(5)'],
      ['a.', 'b.', 'c.', 'd.', 'e.'],
      ['a)', 'b)', 'c)', 'd)', 'e)'],
      ['(a)', '(b)', '(c)', '(d)', '(e)'],
      ['A.', 'B.', 'C.', 'D.', 'E.'],
      ['A)', 'B)', 'C)', 'D)', 'E)'],
      ['(A)', '(B)', '(C)', '(D)', '(E)'],
      ['i)', 'ii)', 'iii)', 'iv)', 'v)'],
      ['i.', 'ii.', 'iii.', 'iv.', 'v.'],
      ['(i)', '(ii)', '(iii)', '(iv)', '(v)'],
    ];
  }

  public function sendMail(array $data)
  {
    $data1 = [
      'email_from' => 'liveeducationbd24@gmail.com',
      'from_name' => 'Live Education BD'
    ];

    $data = array_merge($data, $data1);

    Mail::send('mail.default', $data, function($message) use ($data)
    {
      $message->to($data['email_to']);

      if(isset($data['email_bcc']))
      {
        $message->bcc($data['email_bcc']);
      }

      $message->subject($data['subject']);
      $message->from($data['email_from'], $data['from_name']);
    });
  }

  public function host()
  {    
    $protocol = isset($_SERVER['HTTPS'])?'https://':'http://';
    $host = $protocol.$_SERVER['HTTP_HOST'];
    if(empty($_SERVER['HTTP_HOST']))
    {
        $host = 'http://liveeducationbd.com';
    }
    return $host;
  }
  
  public function point0($data)
  {
    return number_format($data, 0);
  }

  public function reminder($date)
  {
    $y = $m = $d = $h = $i = $s = '';
    $returnable = '';
    $datetime1 = new DateTime($date);
    $datetime2 = new DateTime(date('Y-m-d H:i:s'));
    $interval  = $datetime1->diff($datetime2);
    // return $interval->format('%y years %m months and %d days');

    if($interval->format('%y'))
    {
      $y = $interval->format('%y Year ');
      $returnable = $returnable.$y;
    }

    if($interval->format('%m'))
    {
      $m = $interval->format('%m Month ');
      $returnable = $returnable.$m;
    }

    if($interval->format('%d'))
    {
      $d = $interval->format('%d Day ');
      $returnable = $returnable.$d;
    }

    if($interval->format('%h'))
    {
      $h = $interval->format('%h Hour ');
      $returnable = $returnable.$h;
    }

    if($interval->format('%i'))
    {
      $i = $interval->format('%i Minute ');
      $returnable = $returnable.$i;
    }

    return $returnable;
  }

  public function numset($num, $count = null)
  {
    return str_pad($num, $count, '0', STR_PAD_LEFT);
  }
}