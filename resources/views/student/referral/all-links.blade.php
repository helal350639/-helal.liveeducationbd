@php
    $refhubStudent = \Illuminate\Support\Facades\Auth::guard('student')->user();
@endphp
<!doctype html>
<html lang="bn">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#0b2a5c">
<title>All Referral Link | Live Education BD</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
<style>
html,body{margin:0;min-height:100%;background:#f3f7fe;color:#0b2a5c}
.lebd-refhub-top{box-sizing:border-box;display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px max(18px,calc((100vw - 1390px)/2));background:#fff;border-bottom:1px solid #d7e6fb;box-shadow:0 2px 12px #153c7610;font-family:system-ui,'Noto Sans Bengali',sans-serif}
.lebd-refhub-brand{display:flex;align-items:center;gap:10px;color:#0b2a5c;font-weight:800;font-size:19px;text-decoration:none}
.lebd-refhub-brand .mark{background:#0967dd;color:white;padding:10px;border-radius:11px}
.lebd-refhub-topright{display:flex;align-items:center;gap:15px;font-size:13px}
.lebd-refhub-topright a{color:#095bd0;text-decoration:none;font-weight:700}
@media(max-width:650px){.lebd-refhub-top{padding:12px}.lebd-refhub-brand{font-size:15px}.lebd-refhub-topright .username{display:none}}
</style>
</head>
<body>
<header class="lebd-refhub-top">
 <a class="lebd-refhub-brand" href="{{ url('/students') }}"><span class="mark"><i class="fa-solid fa-book-open" aria-hidden="true"></i></span> LIVE EDUCATION BD</a>
 <nav class="lebd-refhub-topright" aria-label="Student navigation"><span class="username">{{ $refhubStudent?->name ?? 'Student' }}</span><a href="{{ url('/students') }}"><i class="fa-solid fa-house" aria-hidden="true"></i> Dashboard</a></nav>
</header>
<style>
/* LEBD-05: Entirely scoped to referral hub; never style global homepage. */
.lebd-refhub{--navy:#0b2a5c;--blue:#0663db;--line:#d7e6fb;--light:#f4f8ff;max-width:1370px;margin:auto;padding:22px 20px 55px;color:var(--navy);font-family:inherit}
.lebd-refhub *{box-sizing:border-box}.lebd-refhub .back{display:inline-flex;align-items:center;gap:8px;text-decoration:none;color:var(--blue);font-weight:700;margin:0 0 15px;padding:9px 13px;background:#edf5ff;border:1px solid var(--line);border-radius:12px}
.lebd-refhub .intro,.lebd-refhub .section{border:1px solid var(--line);border-radius:18px;background:white;box-shadow:0 5px 20px #1c4a8810}
.lebd-refhub .intro{display:flex;gap:15px;align-items:center;padding:20px;margin-bottom:16px}.lebd-refhub .tile-icon{display:grid;place-items:center;flex:none;width:52px;height:52px;color:#075ed4;font-size:22px;border-radius:14px;background:#e8f2ff}
.lebd-refhub h1{font-size:26px;line-height:1.25;font-weight:800;margin:0 0 6px}.lebd-refhub .intro p{margin:0;color:#597296;line-height:1.6}
.lebd-refhub .section{display:grid;grid-template-columns:minmax(225px,255px) minmax(0,1fr);gap:16px;padding:16px;margin-bottom:13px;align-items:start}.lebd-refhub .section-label{display:flex;align-items:flex-start;gap:12px;padding:5px}.lebd-refhub .section-label .tile-icon{width:45px;height:45px}
.lebd-refhub h2{font-size:17px;font-weight:800;line-height:1.4;margin:3px 0 6px}.lebd-refhub .section-label p{font-size:12px;color:#687e9c;line-height:1.55;margin:0}
.lebd-refhub .entries{display:grid;gap:9px;min-width:0}.lebd-refhub .entry{display:grid;grid-template-columns:minmax(160px,1.1fr) minmax(150px,1fr) minmax(190px,1.4fr);align-items:center;gap:12px;border:1px solid #dae7f9;border-radius:13px;padding:12px;background:#fff}
.lebd-refhub .entry-title{font-weight:800;font-size:14px;overflow-wrap:anywhere}.lebd-refhub .tag{display:inline-block;padding:4px 9px;border-radius:20px;background:#e5f9e9;color:#08723a;font-size:11px;font-weight:750;margin-top:6px}.lebd-refhub .metadata{font-size:12px;color:#557096;line-height:1.75}.lebd-refhub .metadata strong{color:#1455b2}
.lebd-refhub .actions{display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap}.lebd-refhub .action{border:1px solid #b8d6ff;border-radius:9px;padding:10px 12px;text-decoration:none;white-space:nowrap;background:#f1f7ff;color:#065bd0;font-weight:800;font-size:12px;cursor:pointer}
.lebd-refhub .action.primary{color:white;background:#0863dc;border-color:#0863dc}.lebd-refhub .action:disabled{cursor:not-allowed;opacity:.55}.lebd-refhub .empty{background:#f2f7ff;border:1px dashed #d5e4f9;color:#607798;text-align:center;padding:23px;border-radius:12px;font-size:13px;line-height:1.7}
.lebd-refhub .toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:9999;border-radius:12px;background:#0c3268;color:white;font-weight:700;box-shadow:0 10px 24px #0b2a5c30;padding:12px 20px;max-width:calc(100vw - 28px);text-align:center;font-size:13px}.lebd-refhub .toast[hidden]{display:none!important}.lebd-refhub .notice{padding:13px 16px;margin:12px 0;border:1px solid #fae0ac;background:#fff8e8;border-radius:12px;color:#805708;font-size:13px;line-height:1.65}.lebd-refhub .mobile-tabs{display:none}
@media(max-width:940px){.lebd-refhub .section{grid-template-columns:1fr}.lebd-refhub .entry{grid-template-columns:1fr 1fr}.lebd-refhub .actions{grid-column:1/-1;justify-content:flex-start}}
@media(max-width:650px){.lebd-refhub{padding:12px 10px 80px;background:#f3f7fe}.lebd-refhub .intro{padding:15px;border-radius:17px}.lebd-refhub h1{font-size:20px}.lebd-refhub .intro p{font-size:12px}.lebd-refhub .section{padding:12px;gap:9px;border-radius:17px}.lebd-refhub .section-label{align-items:center}.lebd-refhub h2{font-size:15px}.lebd-refhub .entry{display:flex;flex-direction:column;align-items:stretch;gap:8px;padding:13px}.lebd-refhub .metadata{border-top:1px solid #edf3fd;padding-top:7px}.lebd-refhub .actions{display:grid;grid-template-columns:1fr 1fr}.lebd-refhub .action{text-align:center;padding:12px 8px}.lebd-refhub .mobile-tabs{display:flex;overflow-x:auto;gap:7px;margin:0 0 14px;padding-bottom:4px;scrollbar-width:none}.lebd-refhub .mobile-tabs a{flex:none;font-weight:750;text-decoration:none;color:#075ac0;border-radius:22px;background:white;border:1px solid #c9defa;padding:9px 13px;font-size:12px}.lebd-refhub .section{scroll-margin-top:16px}}
</style>
<main class="lebd-refhub">
  <a href="{{ url('/students') }}" class="back"><i class="fa fa-arrow-left" aria-hidden="true"></i> Student Dashboard</a>
  <div class="intro"><span class="tile-icon"><i class="fa fa-link" aria-hidden="true"></i></span><div><h1>All Referral Link</h1><p>আপনার সক্রিয় প্যাকেজগুলোর রেফারেল তথ্য ও লিংক এখানে পাওয়া যাবে।</p></div></div>
  <nav class="mobile-tabs" aria-label="Referral sections">
    <a href="#lebd-ref-batch">Batch</a><a href="#lebd-ref-exam">Exam</a><a href="#lebd-ref-mcq">MCQ Study</a><a href="#lebd-ref-video">Video</a><a href="#lebd-ref-ebook">E-Book</a>
  </nav>
  <div class="notice" role="status">MCQ-এর বৈধ Referral Link-এ নির্ধারিত Discount প্রযোজ্য হয়। E-Book-এর Referral Link নির্দিষ্ট একটি Package দেখাবে; E-Book Referral Discount Checkout ও Bonus Credit এখনো সম্পন্ন হয়নি। MCQ-এর বিদ্যমান Checkout অপরিবর্তিত রয়েছে।</div>
  @php
    // E-Book section bridge: manual grants + paid package orders, DISPLAY ONLY.
    // No new checkout link is generated until E-Book referral settlement is verified.
    $ebookViewer = \Illuminate\Support\Facades\Auth::guard('student')->user();
    if ($ebookViewer && isset($sections['ebook']) && \Illuminate\Support\Facades\Schema::hasTable('lebd_ebook_packages')
        && \Illuminate\Support\Facades\Schema::hasTable('lebd_ebook_package_categories')
        && \Illuminate\Support\Facades\Schema::hasTable('lebd_ebook_entitlements')) {
        $ebookIds = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('lebd_ebook_manual_grants')) {
            $ebookIds = $ebookIds->merge(\Illuminate\Support\Facades\DB::table('lebd_ebook_manual_grants')
                ->where('student_id',$ebookViewer->id)->where('starts_at','<=',now())->where('ends_at','>',now())->pluck('package_id'));
        }
        if (\Illuminate\Support\Facades\Schema::hasTable('lebd_ebook_payment_orders')) {
            $ebookIds = $ebookIds->merge(\Illuminate\Support\Facades\DB::table('lebd_ebook_payment_orders')
                ->where('student_id',$ebookViewer->id)->where('status','Complete')->pluck('package_id'));
        }
        $ebookIds = $ebookIds->filter()->unique()->values();
        if ($ebookIds->isNotEmpty()) {
            $ebookRecords = \Illuminate\Support\Facades\DB::table('lebd_ebook_packages as p')
                ->join('lebd_ebook_package_categories as pc','pc.package_id','=','p.id')
                ->join('lebd_ebook_categories as c','c.id','=','pc.category_id')
                ->join('lebd_ebook_entitlements as e',function($j)use($ebookViewer){
                    $j->on('e.category_id','=','c.id')->where('e.user_id','=',$ebookViewer->id);
                })
                ->whereIn('p.id',$ebookIds)->where('e.status','Active')->where('e.expires_at','>',now())
                ->where('c.status','Active')
                ->select('p.id','p.name','p.referral_discount','p.referrer_bonus','c.id as category_id','c.kind')
                ->orderBy('p.id')->orderBy('c.id')->get()->unique('id');
            $ebookRows = [];
            foreach ($ebookRecords as $ep) {
                $ebookRows[] = [
                    'id'=>(int)$ep->id,'name'=>(string)$ep->name,
                    'discount'=>(float)($ep->referral_discount ?? 0),'bonus'=>(float)($ep->referrer_bonus ?? 0),
                    'url'=>!empty($ebookViewer->referral_id) ? route('lebd.ebook.referral.package',['package'=>$ep->id,'ref'=>$ebookViewer->referral_id]) : null,
                    'ready'=>!empty($ebookViewer->referral_id),'source'=>'Active E-Book package',
                ];
            }
            // Retain any already-supplied E-Book items, avoid duplicate package rows.
            $existingEbookIds = array_map('intval', array_column($sections['ebook']['items'] ?? [], 'id'));
            foreach ($ebookRows as $ebookRow) {
                if (!in_array($ebookRow['id'],$existingEbookIds,true)) $sections['ebook']['items'][]=$ebookRow;
            }
        }
    }
  @endphp
  @foreach($sections as $key => $section)
  <section class="section" id="lebd-ref-{{ $key }}" aria-labelledby="lebd-ref-title-{{ $key }}">
    <div class="section-label"><span class="tile-icon"><i class="fa {{ $section['icon'] }}" aria-hidden="true"></i></span><div><h2 id="lebd-ref-title-{{ $key }}">{{ $section['title'] }}</h2><p>শুধু Active Package-এর তথ্য এখানে দেখানো হয়।</p></div></div>
    <div class="entries">
      @forelse($section['items'] as $item)
      <article class="entry">
        <div><div class="entry-title">{{ $item['name'] }}</div><span class="tag"><i class="fa fa-check-circle" aria-hidden="true"></i> Active</span></div>
        <div class="metadata">@if($item['discount'] !== null) Referral Discount: <strong>৳{{ number_format($item['discount'],2) }}</strong><br>Referrer Bonus: <strong>৳{{ number_format($item['bonus'],2) }}</strong>@else Referral Bonus: সংযোগ বাকি @endif</div>
        <div class="actions">
          @if(!empty($item['url']))
            @php
              // Only label links as referrals when the existing URL is explicitly referral-attributed.
              // A plain catalog URL must NEVER be represented as discount-bearing.
              $lebdIsReferral = $key === 'mcq' || ($key === 'batch' && !empty($item['ready']) && str_contains((string)$item['url'], '/batch-reference/student/')) || ($key === 'video' && str_contains((string)$item['url'], '/premium-recordings/referral/') && str_contains((string)$item['url'], 'ref=')) || ($key === 'ebook' && !empty($item['ready']) && str_contains((string)$item['url'], '/e-books/referral-package/') && str_contains((string)$item['url'], 'ref='));
              $lebdLinkLabel = $lebdIsReferral ? 'Referral Link' : 'Package Link';
            @endphp
            <button type="button" class="action primary" data-ref-copy data-link="{{ $item['url'] }}" aria-label="{{ $item['name'] }} লিংক কপি করুন"><i class="fa fa-copy" aria-hidden="true"></i> Copy {{ $lebdLinkLabel }}</button>
            <button type="button" class="action" data-ref-share data-link="{{ $item['url'] }}" data-title="{{ $item['name'] }}" aria-label="{{ $item['name'] }} লিংক শেয়ার করুন"><i class="fa fa-share-nodes" aria-hidden="true"></i> Share {{ $lebdLinkLabel }}</button>
            <a class="action" href="{{ $item['url'] }}"><i class="fa fa-external-link" aria-hidden="true"></i> View Package</a>
          @else
            <button type="button" class="action primary" disabled>Copy Package Link</button>
            <button type="button" class="action" disabled>Share Package Link</button>
            <button type="button" class="action" disabled>View Package</button>
          @endif
        </div>
      </article>
      @empty
      <div class="empty"><i class="fa fa-folder-open" aria-hidden="true"></i><br>এই সেকশনে কোনো যাচাইকৃত Active Package পাওয়া যায়নি।<br><small>@if($key === 'ebook')এই সেকশনের Access Mapping যাচাই সাপেক্ষ।@else Active Package থাকলে এখানে দেখাবে।@endif</small></div>
      @endforelse
    </div>
  </section>
  @endforeach
  <div class="toast" id="lebdRefToast" role="status" aria-live="polite" hidden></div>
</main>
<script>
(function(){
  'use strict';
  const root=document.querySelector('.lebd-refhub');
  if(!root) return;
  const toast=root.querySelector('#lebdRefToast');
  let toastTimer;
  function notice(message){
    if(!toast) return;
    toast.textContent=message;
    toast.hidden=false;
    clearTimeout(toastTimer);
    toastTimer=setTimeout(()=>{toast.hidden=true;},3200);
  }
  async function copyText(value){
    if(navigator.clipboard && window.isSecureContext){
      await navigator.clipboard.writeText(value); return;
    }
    const el=document.createElement('textarea');
    el.value=value;el.style.position='fixed';el.style.left='-9999px';
    document.body.appendChild(el);el.focus();el.select();
    let ok=false;
    try{ok=document.execCommand('copy');}finally{el.remove();}
    if(!ok) throw new Error('Clipboard unavailable');
  }
  root.addEventListener('click', async function(event){
    const btn=event.target.closest('button[data-ref-copy],button[data-ref-share]');
    if(!btn || btn.disabled) return;
    const link=btn.getAttribute('data-link');
    if(!link || !/^https?:\/\//i.test(link)){
      notice('এই প্যাকেজের লিংক পাওয়া যায়নি।'); return;
    }
    if(btn.hasAttribute('data-ref-copy')){
      try{await copyText(link);notice('প্যাকেজের লিংক কপি হয়েছে!');}
      catch(e){notice('কপি করা যায়নি। View Package থেকে লিংক কপি করুন।');}
      return;
    }
    const title=btn.getAttribute('data-title')||'Package';
    if(navigator.share){
      try{await navigator.share({title,text:title+' — Live Education BD',url:link});}
      catch(e){if(e.name!=='AbortError')notice('শেয়ার করা যায়নি। আবার চেষ্টা করুন।');}
    }else{
      try{await copyText(link);notice('Share সাপোর্ট নেই; লিংক কপি হয়েছে। এখন পেস্ট করুন।');}
      catch(e){notice('Share সাপোর্ট নেই। View Package থেকে লিংক কপি করুন।');}
    }
  });
})();
</script>
</body>
</html>
