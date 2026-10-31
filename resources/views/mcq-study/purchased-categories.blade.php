<!doctype html>
<html lang="bn"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>MCQ Study Categories — Live Education BD</title>
<style>
:root{--navy:#10366a;--blue:#0868e0;--light:#eaf2ff}*{box-sizing:border-box}body{margin:0;background:#f6f8fc;color:#103164;font-family:Arial,"Noto Sans Bengali",sans-serif}.wrap{max-width:940px;margin:16px auto;padding:10px}.back{display:inline-flex;color:#1765ba;text-decoration:none;font-size:13px;margin:0 0 14px}.group{border-radius:15px;padding:20px 15px 15px;margin:0 0 20px;background:linear-gradient(110deg,#fff5f1,#fff0e6);box-shadow:0 7px 19px #13326012}.group.general{background:linear-gradient(110deg,#f0f2ff,#e9edff)}.group-title{display:flex;justify-content:center;align-items:center;margin:-1px 0 15px;position:relative}.tag{display:inline-flex;align-items:center;justify-content:center;min-height:58px;padding:8px 28px;gap:10px;border-radius:28px;color:#fff;font-weight:800;font-size:20px;line-height:1.15;text-align:center;background:linear-gradient(110deg,#ff9900,#ff3c4b);box-shadow:0 5px 12px #df714b30}.general .tag{background:linear-gradient(110deg,#377fff,#1a51d2);box-shadow:0 5px 12px #1e60c72c}.tag small{display:block;font-size:9px;font-weight:500;margin-top:3px}.tag .symbol{font-size:25px}.motto{position:absolute;color:#e5a797;font-size:11px;line-height:1.2;left:5px}.motto.right{left:auto;right:5px;color:#918a89}.general .motto{color:#adbafa}.category-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px}.category{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:98px;border:1px solid #d9e2f0;border-radius:8px;background:#eaf2ff;box-shadow:0 4px 10px #13386113;gap:7px;color:#0d2b58;text-decoration:none;font-weight:800;font-size:13px}.category .symbol{font-size:28px;color:#1671f3}.category .state{font-size:10px;color:#9b5a36}.category:focus-visible{outline:3px solid #ff9000;outline-offset:2px}.note{font-size:12px;color:#6b7f9c;margin:8px 0 0;text-align:center}.subscription{padding:12px 15px;background:#ecf8ef;border:1px solid #addbbb;border-radius:10px;margin-bottom:15px;font-size:13px}.subscription strong{color:#125936}.subscription small{display:block;color:#4d6757;margin-top:5px}.action{display:inline-block;margin-top:8px;color:#145eab}.hint{padding:10px 15px;border:1px solid #f2d5ac;background:#fffbef;border-radius:8px;font-size:12px;margin-bottom:15px}@media(max-width:650px){.wrap{padding:8px;margin:0 auto}.group{padding:13px 10px}.tag{font-size:15px;min-height:48px;padding:7px 16px}.category-grid{grid-template-columns:repeat(5,minmax(0,1fr));gap:5px}.category{min-height:68px;font-size:9px;gap:4px}.category .symbol{font-size:20px}.motto{font-size:8px}.group-title{margin-bottom:12px}}@media(max-width:380px){.category-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.motto{display:none}}
</style></head><body><main class="wrap">
<a class="back" href="{{ url()->previous() }}">← Student Dashboard-এ ফিরুন</a>
@if(!$isStudent)
<div class="hint">কেনা প্যাকেজ দেখতে Student Account-এ Login করতে হবে।</div>
@elseif($subscriptions->isEmpty())
<div class="hint">আপনার কোনো চলমান MCQ Study Package পাওয়া যায়নি। <a href="{{ route('lebd.packages.index', ['section'=>'mcq']) }}">প্যাকেজ দেখুন</a></div>
@else
<div class="subscription"><strong>আপনার চলমান MCQ Study প্যাকেজ</strong>
@foreach($subscriptions as $subscription)
<small>{{ $subscription->name }} · মেয়াদ শেষ: {{ \Carbon\Carbon::parse($subscription->ends_at)->format('d M Y') }}</small>
@endforeach
<small>সঠিক Category ID ও Question Route যাচাই করে পরের ধাপে নির্দিষ্ট Category Access চালু করা হবে।</small>
</div>
@endif
<section class="group"><div class="group-title"><span class="motto">Small<br>Steps<br>Big<br>Results</span><div class="tag"><span class="symbol">▣</span><span>Department MCQ<br>Study<small>Practice Today · Build Your Career</small></span></div><span class="motto right">Prepare<br>For a<br>Better<br>Tomorrow</span></div><div class="category-grid">
@foreach($departments as $i => $name)
<div class="category" aria-label="{{ $name }} — access not linked"><span class="symbol">{{ ['▰','ϟ','⚙','▣','▤','◉','✿','▦','▥','❧'][$i] }}</span>{{ $name }}<span class="state">Link pending</span></div>
@endforeach
</div></section>
<section class="group general"><div class="group-title"><span class="motto">Small<br>Steps Make<br>Big Results</span><div class="tag"><span class="symbol">▣</span><span>Non-MCQ Study<small>Learn · Practice · Improve</small></span></div></div><div class="category-grid">
@foreach($general as $i => $name)
<div class="category" aria-label="{{ $name }} — access not linked"><span class="symbol">{{ ['▤','▣','◎','◉','▦','▰'][$i] }}</span>{{ $name }}<span class="state">Link pending</span></div>
@endforeach
</div></section>
<p class="note">এই ধাপে কেনা প্যাকেজ ও মেয়াদ Laravel থেকে দেখানো হচ্ছে; Question Link ও Category Access mapping যাচাই সাপেক্ষে যুক্ত হবে।</p>
</main></body></html>
