<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class EmailAccountController extends Controller
{
    public function __construct() { $this->middleware('auth'); }

    public function index()
    {
        abort_unless(Schema::hasTable('email_sender_accounts'), 500, 'email_sender_accounts table পাওয়া যায়নি। Install SQL চালান।');
        $accounts = DB::table('email_sender_accounts')->orderByDesc('id')->get();
        return view('layouts.email.accounts', compact('accounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'account_name' => 'required|string|max:120',
            'from_name' => 'required|string|max:150',
            'email' => 'required|email|max:190',
            'driver' => 'required|in:smtp',
            'host' => 'required|string|max:190',
            'port' => 'required|integer|min:1|max:65535',
            'encryption' => 'nullable|in:tls,ssl,none',
            'username' => 'required|string|max:190',
            'password' => 'required|string|max:500',
        ]);
        $password = $data['password']; unset($data['password']);
        $data['password_encrypted'] = Crypt::encrypt($password);
        $data['is_active'] = $request->boolean('is_active') ? 1 : 0;
        $data['created_by'] = auth()->id();
        $data['created_at'] = now(); $data['updated_at'] = now();
        DB::table('email_sender_accounts')->insert($data);
        return redirect()->route('email.accounts.index')->with('success', 'Email Account যোগ হয়েছে।');
    }

    public function update(Request $request, $id)
    {
        $account = DB::table('email_sender_accounts')->find($id); abort_unless($account, 404);
        $data = $request->validate([
            'account_name' => 'required|string|max:120','from_name' => 'required|string|max:150',
            'email' => 'required|email|max:190','host' => 'required|string|max:190',
            'port' => 'required|integer|min:1|max:65535','encryption' => 'nullable|in:tls,ssl,none',
            'username' => 'required|string|max:190','password' => 'nullable|string|max:500',
        ]);
        if (!empty($data['password'])) { $data['password_encrypted'] = Crypt::encrypt($data['password']); }
        unset($data['password']);
        $data['is_active'] = $request->boolean('is_active') ? 1 : 0; $data['updated_at'] = now();
        DB::table('email_sender_accounts')->where('id',$id)->update($data);
        return redirect()->route('email.accounts.index')->with('success', 'Email Account আপডেট হয়েছে।');
    }

    public function destroy($id)
    {
        DB::table('email_sender_accounts')->where('id',$id)->delete();
        return redirect()->route('email.accounts.index')->with('success', 'Email Account মুছে ফেলা হয়েছে।');
    }

    public function test($id)
    {
        $a = DB::table('email_sender_accounts')->where('id',$id)->first(); abort_unless($a,404);
        try {
            $transport = new \Swift_SmtpTransport($a->host, (int)$a->port, $a->encryption === 'none' ? null : $a->encryption);
            $transport->setUsername($a->username)->setPassword(Crypt::decrypt($a->password_encrypted));
            $mailer = new \Swift_Mailer($transport);
            $message = (new \Swift_Message('Live Education BD - SMTP Test'))
                ->setFrom([$a->email => $a->from_name])->setTo([$a->email])
                ->setBody('Your SMTP connection is working.');
            $mailer->send($message);
            return back()->with('success', 'Test Email সফলভাবে পাঠানো হয়েছে।');
        } catch (\Throwable $e) {
            \Log::error('Email SMTP test failed: '.$e->getMessage());
            return back()->with('error', 'SMTP Connection ব্যর্থ হয়েছে। Host, Port, Username ও Password যাচাই করুন।');
        }
    }
}
