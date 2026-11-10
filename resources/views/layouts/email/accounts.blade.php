@extends('dashboard')
@section('title','Manage Email Accounts')
@section('content')
<section class="content-header"><h1>Manage Email Accounts <small>Email Management</small></h1><ol class="breadcrumb"><li><a href="{{ url('/home') }}">Dashboard</a></li><li><a href="{{ route('email.index') }}">Email</a></li><li class="active">Manage Email Accounts</li></ol></section>
<section class="content">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="box box-primary" style="border-top:3px solid #1685c5"><div class="box-header with-border">
<h3 class="box-title">Add Multiple Sender Email Accounts</h3>
<button type="button" class="btn btn-info btn-sm pull-right" data-toggle="modal" data-target="#emailUserGuide"><i class="fa fa-book"></i> User Guide</button>
</div><div class="box-body">
<form method="POST" action="{{ route('email.accounts.store') }}">@csrf
<div class="row">
<div class="row">
<div class="col-md-4"><div class="form-group"><label>Account Name</label><input class="form-control" name="account_name" type="text" placeholder="উদাহরণ: Main Gmail / Support Email" required></div></div>
<div class="col-md-4"><div class="form-group"><label>Sender Name</label><input class="form-control" name="from_name" type="text" placeholder="উদাহরণ: Live Education BD" required></div></div>
<div class="col-md-4"><div class="form-group"><label>Email Address</label><input class="form-control" name="email" type="email" placeholder="উদাহরণ: info@yourdomain.com" required></div></div>
<div class="col-md-4"><div class="form-group"><label>SMTP Host</label><input class="form-control" name="host" type="text" placeholder="Gmail: smtp.gmail.com" required></div></div>
<div class="col-md-4"><div class="form-group"><label>SMTP Port</label><input class="form-control" name="port" type="number" min="1" max="65535" placeholder="TLS: 587 / SSL: 465" required></div></div>
<div class="col-md-4"><div class="form-group"><label>SMTP Username</label><input class="form-control" name="username" type="text" placeholder="সাধারণত সম্পূর্ণ Email Address" required></div></div>
<div class="col-md-4"><div class="form-group"><label>SMTP Password / App Password</label><div class="input-group"><input id="new_smtp_password" class="form-control" name="password" type="password" placeholder="আপনার SMTP/App Password" required autocomplete="new-password"><span class="input-group-btn"><button class="btn btn-default toggle-password" type="button" data-target="new_smtp_password" aria-label="Show password"><i class="fa fa-eye"></i></button></span></div></div></div>
<div class="col-md-4"><div class="form-group"><label>Encryption</label><select name="encryption" class="form-control"><option value="tls">TLS</option><option value="ssl">SSL</option><option value="none">None</option></select></div></div>
<div class="col-md-4"><div class="form-group"><label>Driver</label><input class="form-control" value="SMTP" readonly><input type="hidden" name="driver" value="smtp"></div></div>
<div class="col-md-4"><div class="form-group"><label><input type="checkbox" name="is_active" value="1" checked> Active রাখুন</label></div></div>
</div><button class="btn btn-success"><i class="fa fa-plus"></i> Add Email Account</button></form></div></div>
<div class="box box-primary"><div class="box-header with-border"><h3 class="box-title">Saved Email Accounts</h3><span class="pull-right label label-primary">{{ $accounts->count() }} Accounts</span></div><div class="box-body table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>SL</th><th>Account Name</th><th>Sender Email</th><th>SMTP Host</th><th>Port</th><th>Status</th><th>Actions</th></tr></thead><tbody>
@forelse($accounts as $i=>$a)<tr><td>{{ $i+1 }}</td><td>{{ $a->account_name }}</td><td>{{ $a->email }}</td><td>{{ $a->host }}</td><td>{{ $a->port }}</td><td>{!! $a->is_active ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>' !!}</td><td><button type="button" class="btn btn-warning btn-xs" data-toggle="modal" data-target="#editEmail{{ $a->id }}">Edit</button> <form method="POST" action="{{ route('email.accounts.test',$a->id) }}" style="display:inline">@csrf<button class="btn btn-info btn-xs">Test Connection</button></form> <form method="POST" action="{{ route('email.accounts.delete',$a->id) }}" style="display:inline" onsubmit="return confirm('এই Email Account মুছে ফেলবেন?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form></td></tr>
<div class="modal fade" id="editEmail{{ $a->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="POST" action="{{ route('email.accounts.update',$a->id) }}">@csrf @method('PUT')<div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4>Edit {{ $a->account_name }}</h4></div><div class="modal-body">
<div class="form-group"><label>Account Name</label><input class="form-control" name="account_name" value="{{ $a->account_name }}" required></div><div class="form-group"><label>Sender Name</label><input class="form-control" name="from_name" value="{{ $a->from_name }}" required></div><div class="form-group"><label>Email</label><input class="form-control" type="email" name="email" value="{{ $a->email }}" required></div><div class="form-group"><label>SMTP Host</label><input class="form-control" name="host" value="{{ $a->host }}" required></div><div class="form-group"><label>Port</label><input class="form-control" type="number" name="port" value="{{ $a->port }}" required></div><div class="form-group"><label>Encryption</label><select class="form-control" name="encryption"><option value="tls" {{ $a->encryption==='tls'?'selected':'' }}>TLS</option><option value="ssl" {{ $a->encryption==='ssl'?'selected':'' }}>SSL</option><option value="none" {{ $a->encryption==='none'?'selected':'' }}>None</option></select></div><div class="form-group"><label>Username</label><input class="form-control" name="username" value="{{ $a->username }}" required></div><div class="form-group"><label>New SMTP Password / App Password (খালি রাখলে আগেরটি থাকবে)</label><div class="input-group"><input id="edit_smtp_password_{{ $a->id }}" class="form-control" type="password" name="password" placeholder="নতুন App Password দিতে চাইলে লিখুন" autocomplete="new-password"><span class="input-group-btn"><button class="btn btn-default toggle-password" type="button" data-target="edit_smtp_password_{{ $a->id }}" aria-label="Show password"><i class="fa fa-eye"></i></button></span></div></div><label><input type="checkbox" name="is_active" value="1" {{ $a->is_active?'checked':'' }}> Active</label></div><div class="modal-footer"><button class="btn btn-primary">Save Changes</button></div></form></div></div></div>
@empty<tr><td colspan="7" class="text-center">কোনো Email Account যোগ করা হয়নি।</td></tr>@endforelse
</tbody></table></div></div>
</section>
<div class="modal fade" id="emailUserGuide" tabindex="-1" role="dialog" aria-labelledby="emailUserGuideLabel">
 <div class="modal-dialog modal-lg" role="document" style="width:94%;max-width:1050px">
  <div class="modal-content">
   <div class="modal-header" style="background:#eef6ff;border-bottom:2px solid #1685c5">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
    <h4 class="modal-title" id="emailUserGuideLabel"><i class="fa fa-book"></i> Email Module – User Guide</h4>
    <p class="text-muted" style="margin:5px 0 0">Live Education BD | Email Management System</p>
   </div>
   <div class="modal-body" style="max-height:72vh;overflow-y:auto">
    <div class="alert alert-info"><strong>Email Module কী?</strong><br>এই মডিউলের মাধ্যমে Admin Panel থেকে শিক্ষার্থীদের কাছে Email পাঠানো, Draft সংরক্ষণ, Schedule তৈরি এবং একাধিক Sender Email Account পরিচালনা করা যাবে। প্রতিটি Batch বা Exam Section-এর জন্য প্রয়োজন অনুযায়ী Sender Account নির্বাচন করতে পারবেন।</div>
    <div class="panel-group" id="emailGuideAccordion">
     <div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#emailGuideAccordion" href="#eg1">১. Email Account যোগ করার নিয়ম</a></h4></div><div id="eg1" class="panel-collapse collapse in"><div class="panel-body">
      <p><strong>Account Name:</strong> Admin Panel-এ Account চেনার জন্য একটি নাম। যেমন: Main Gmail, HSC Email, Support Email।</p>
      <p><strong>Sender Name:</strong> শিক্ষার্থীর Inbox-এ প্রেরকের নাম হিসেবে দেখা যাবে। যেমন: Live Education BD বা HSC Exam Team।</p>
      <p><strong>Email Address:</strong> যে Email Address থেকে মেইল পাঠাবেন। যেমন: info@yourdomain.com অথবা liveeducationbd@gmail.com।</p>
      <p><strong>SMTP Host:</strong> Email Provider-এর outgoing mail server address। Gmail-এর জন্য smtp.gmail.com; অন্য Provider-এর Host তাদের কাছ থেকে সংগ্রহ করুন।</p>
      <p><strong>SMTP Port:</strong> Server connection-এর Port। TLS-এর সঙ্গে সাধারণত 587 এবং SSL-এর সঙ্গে সাধারণত 465 ব্যবহার হয়।</p>
      <p><strong>SMTP Username:</strong> সাধারণত সম্পূর্ণ Email Address লিখতে হয়।</p>
      <p><strong>SMTP Password / App Password:</strong> SMTP login-এর Password। Gmail-এর ক্ষেত্রে সাধারণ Gmail Password নয়, Google App Password ব্যবহার করুন।</p>
      <p><strong>Encryption:</strong> Port 587 হলে TLS এবং Port 465 হলে SSL নির্বাচন করুন। Provider-এর নির্দেশনা অনুসরণ করুন।</p>
      <p><strong>Driver:</strong> SMTP নির্বাচন করা থাকবে। <strong>Active:</strong> Account ব্যবহারযোগ্য রাখতে Checkbox চালু রাখুন।</p>
      <p>সব তথ্য পূরণ করে <strong>Add Email Account</strong> চাপুন। Account যোগ হলে Saved Email Accounts তালিকায় দেখা যাবে। এরপর Test Connection দিয়ে সংযোগ পরীক্ষা করুন।</p>
     </div></div></div>
     <div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#emailGuideAccordion" href="#eg2">২. Gmail-এর Recommended Configuration</a></h4></div><div id="eg2" class="panel-collapse collapse"><div class="panel-body">
      <table class="table table-bordered table-condensed"><tbody><tr><th>SMTP Host</th><td><code>smtp.gmail.com</code></td></tr><tr><th>SMTP Port</th><td><code>587</code></td></tr><tr><th>Username</th><td>সম্পূর্ণ Gmail Address</td></tr><tr><th>Password</th><td>Google App Password</td></tr><tr><th>Encryption</th><td>TLS</td></tr><tr><th>Driver</th><td>SMTP</td></tr></tbody></table>
      <p><strong>Gmail App Password তৈরির ধাপ:</strong></p><ol><li>Google Account-এ Sign in করুন।</li><li>Security থেকে 2-Step Verification চালু করুন।</li><li>App Password অপশন পাওয়া গেলে নতুন App Password তৈরি করুন।</li><li>তৈরি করা App Password SMTP Password ঘরে দিন।</li></ol>
      <div class="alert alert-warning">App Password অপশন সব Google Account বা সব ধরনের Account Policy-তে নাও দেখা যেতে পারে। Google-এর বর্তমান Security নির্দেশনা অনুসরণ করুন। App Password কারও সঙ্গে শেয়ার করবেন না।</div>
     </div></div></div>
     <div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#emailGuideAccordion" href="#eg3">৩. Domain Email / অন্য Provider-এর Configuration</a></h4></div><div id="eg3" class="panel-collapse collapse"><div class="panel-body">
      <p>Domain Email যেমন info@yourdomain.com ব্যবহার করলে আপনার Hosting বা Email Provider-এর Control Panel/Documentation থেকে SMTP Host, Port, Username, Password এবং Encryption জেনে নিন। সব Provider-এর Host বা Port এক নয়।</p>
      <p>Outlook, Zoho বা অন্য Provider ব্যবহার করলে তাদের নির্দিষ্ট SMTP server settings ব্যবহার করবেন। অনুমান করে Host বা Port বসাবেন না।</p>
     </div></div></div>
     <div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#emailGuideAccordion" href="#eg4">৪. একাধিক Email Account পরিচালনা</a></h4></div><div id="eg4" class="panel-collapse collapse"><div class="panel-body">
      <p>প্রয়োজন অনুযায়ী একাধিক Sender Email Account যোগ করতে পারবেন। উদাহরণ: Main Email, HSC Email, BCS Email, Support Email এবং Notification Email।</p>
      <ul><li><strong>Edit:</strong> Account-এর তথ্য পরিবর্তন করুন। Password ঘর ফাঁকা রাখলে আগের Password অপরিবর্তিত থাকবে—যদি Update Form-এর নিয়ম সেটি সমর্থন করে।</li><li><strong>Test Connection:</strong> SMTP সংযোগ পরীক্ষা করুন।</li><li><strong>Active/Inactive:</strong> Account ব্যবহারযোগ্য বা নিষ্ক্রিয় রাখুন।</li><li><strong>Delete:</strong> মুছে ফেলার আগে Account কোনো Batch বা Schedule-এ ব্যবহৃত হচ্ছে কি না যাচাই করুন।</li></ul>
      <p>একই Sender Account একাধিক Batch-এ ব্যবহার করা যেতে পারে, তবে প্রতিটি Batch-এর জন্য কোন Account ব্যবহার হবে তা Email তৈরি করার সময় নির্বাচন করুন।</p>
     </div></div></div>
     <div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#emailGuideAccordion" href="#eg5">৫. Create Batch Name ও Sender নির্বাচন</a></h4></div><div id="eg5" class="panel-collapse collapse"><div class="panel-body">
      <p><strong>Batch List:</strong> Select Category থেকে Batch List নির্বাচন করুন, এরপর Course এবং সংশ্লিষ্ট Batch নির্বাচন করে Create করুন।</p>
      <p><strong>Exam Section List:</strong> Exam Section List নির্বাচন করে Exam Section Dashboard-এর Category নির্বাচন করুন। Category-এর অধীনে Sub-category থাকলে সেটি নির্বাচন করুন।</p>
      <p>তৈরি করা Batch/Category/Sub-category Email Management তালিকায় যুক্ত হবে। Email তৈরি করার সময় সংশ্লিষ্ট Batch বা Exam Section-এর শিক্ষার্থীদের প্রাপক হিসেবে নির্বাচন/লোড হবে এবং উপযুক্ত Sender Email Account নির্বাচন করতে হবে।</p>
     </div></div></div>
     <div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#emailGuideAccordion" href="#eg6">৬. Email তৈরি, Draft ও Schedule</a></h4></div><div id="eg6" class="panel-collapse collapse"><div class="panel-body">
      <ol><li>সঠিক Batch বা Exam Section খুলুন।</li><li>প্রাপকের Email Address সঠিক আছে কি না নিশ্চিত করুন।</li><li>Sender Email Account নির্বাচন করুন।</li><li>Email Subject এবং Message লিখুন।</li><li>প্রয়োজনে Save Draft করুন, পরে Edit করতে পারবেন।</li><li>তাৎক্ষণিক পাঠাতে Send অথবা ভবিষ্যৎ সময়ে পাঠাতে Schedule ব্যবহার করুন।</li></ol>
      <p>Exam Alert, Exam Link, Routine Update এবং Academic Announcement-এর মতো বার্তা পাঠানোর আগে তারিখ, সময়, লিংক ও প্রাপকের তালিকা যাচাই করুন।</p>
      <div class="alert alert-warning">Scheduled Email স্বয়ংক্রিয়ভাবে পাঠাতে Server Cron Job/Queue Worker সচল এবং সঠিকভাবে Configure থাকতে হবে। শুধু Schedule Save করলেই Delivery নিশ্চিত হয় না।</div>
     </div></div></div>
     <div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#emailGuideAccordion" href="#eg7">৭. সাধারণ সমস্যা ও সমাধান</a></h4></div><div id="eg7" class="panel-collapse collapse"><div class="panel-body">
      <table class="table table-bordered table-striped"><thead><tr><th>সমস্যা</th><th>যা পরীক্ষা করবেন</th></tr></thead><tbody><tr><td>Authentication Failed</td><td>SMTP Username ও Password/App Password যাচাই করুন।</td></tr><tr><td>Connection Failed</td><td>SMTP Host, Port, Server Firewall এবং Outbound Connection পরীক্ষা করুন।</td></tr><tr><td>SSL/TLS Error</td><td>Port ও Encryption-এর মিল পরীক্ষা করুন: 587/TLS বা 465/SSL।</td></tr><tr><td>Email যাচ্ছে না</td><td>Test Connection, Server Log এবং Provider-এর Sending Limit পরীক্ষা করুন।</td></tr><tr><td>Email Spam-এ যাচ্ছে</td><td>SPF, DKIM, DMARC এবং Sender Domain Reputation পরীক্ষা করুন।</td></tr><tr><td>Invalid Recipient</td><td>শিক্ষার্থীর Email Address সঠিক কি না যাচাই করুন।</td></tr><tr><td>Schedule কাজ করছে না</td><td>Cron Job, Queue Worker এবং Application Log পরীক্ষা করুন।</td></tr></tbody></table>
     </div></div></div>
     <div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#emailGuideAccordion" href="#eg8">৮. Security ও Best Practices</a></h4></div><div id="eg8" class="panel-collapse collapse"><div class="panel-body">
      <ul><li>Gmail-এর মূল Password ব্যবহার করবেন না; App Password বা Provider অনুমোদিত Authentication ব্যবহার করুন।</li><li>SMTP Password নিরাপদভাবে সংরক্ষণ করুন; Source Code, Screenshot বা Public Document-এ প্রকাশ করবেন না।</li><li>শুধু বিশ্বস্ত Admin-কে Email Account পরিচালনার অনুমতি দিন।</li><li>Domain Email-এর জন্য SPF, DKIM এবং DMARC Configure করুন।</li><li>Provider-এর দৈনিক/প্রতি ঘণ্টার Sending Limit মেনে চলুন।</li><li>অনুমতি ছাড়া Bulk Email পাঠাবেন না; Unsubscribe ও প্রযোজ্য Email Policy মেনে চলুন।</li><li>Failed/Delivered Email Log পর্যবেক্ষণ করুন এবং Duplicate Sending প্রতিরোধ করুন।</li><li>একটি Account-এ সমস্যা হলে বিকল্প Account ব্যবহার করার আগে সেটির SMTP Connection পরীক্ষা করুন।</li></ul>
     </div></div></div>
     <div class="panel panel-success"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" data-parent="#emailGuideAccordion" href="#eg9">৯. Live Education BD-এর জন্য প্রস্তাবিত সেটআপ</a></h4></div><div id="eg9" class="panel-collapse collapse"><div class="panel-body">
      <p>নিয়মিত শিক্ষার্থী ও Bulk Email পাঠানোর ক্ষেত্রে Domain-based বা নির্ভরযোগ্য Transactional Email Provider ব্যবহার করা ভালো। Gmail ছোট পরিসরের পরীক্ষা বা সীমিত ব্যবহারে সুবিধাজনক হতে পারে, তবে Provider-এর Sending Limit মেনে চলতে হবে।</p>
      <ul><li>Primary Sender: Verified Domain Email</li><li>Backup Sender: দ্বিতীয় SMTP Account/Provider</li><li>Connection: SMTP with TLS (Provider সমর্থন করলে)</li><li>Monitoring: Delivery/Failure Log</li><li>Automation: Queue Worker + Cron Job</li></ul>
      <p class="text-muted">সঠিক Host, Port, Sending Limit ও Authentication Provider অনুযায়ী পরিবর্তিত হয়। তাই Provider-এর অফিসিয়াল সেটিংসই চূড়ান্ত হিসেবে অনুসরণ করবেন।</p>
     </div></div></div>
    </div>
   </div>
   <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
  </div>
 </div>
</div>

<script>
document.addEventListener('click', function (event) {
    var button = event.target.closest('.toggle-password');
    if (!button) return;
    var input = document.getElementById(button.getAttribute('data-target'));
    if (!input) return;
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    var icon = button.querySelector('i');
    if (icon) {
        icon.classList.toggle('fa-eye', !show);
        icon.classList.toggle('fa-eye-slash', show);
    }
    button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
});
</script>
@endsection
