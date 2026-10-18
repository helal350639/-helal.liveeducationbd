{{-- Live Education BD | LIVE Daily Update via Eloquent Models.
     ONE FILE: resources/views/daily-update.blade.php
     Existing GET /daily-update route stays unchanged.
     Category detail URLs use ?section={category.slug}.
     Comments remain demo-only until the comment API is connected.
--}}
@extends('homepage')
@section('title', 'Daily Update | Live Education BD')
@section('content')
@php
    // LIVE READ ONLY: Existing GET /daily-update route returns this Blade.
    // Uses the three Eloquent models created in prior steps. No routes/index.blade.php changes.
    $duBaseUrl = url('/daily-update');
    $duCategories = \App\Models\DailyUpdateCategory::query()->active()->displayOrder()->get();
    $duSections = [];
    foreach ($duCategories as $category) {
        $slug = (string) $category->slug;
        $duSections[$slug] = [
            'name' => (string) $category->name,
            'subtitle' => (string) ($category->description ?: 'এই বিভাগের প্রকাশিত গুরুত্বপূর্ণ প্রশ্ন ও উত্তর'),
            'symbol' => (string) ($category->icon ?: '📰'),
        ];
    }
    $duRequested = request()->query('section');
    $duIsDetail = is_string($duRequested) && $duRequested !== '';
    $duValid = !$duIsDetail || array_key_exists($duRequested, $duSections);
    $duCurrent = $duIsDetail && $duValid ? $duRequested : null;
    $duPageTitle = $duCurrent ? $duSections[$duCurrent]['name'] : 'Daily Update';
    $duPageSubtitle = $duCurrent
        ? $duSections[$duCurrent]['subtitle']
        : 'প্রকাশিত কারেন্ট অ্যাফেয়ার্স, মাসিক আপডেট ও অন্যান্য বিভাগের প্রশ্ন';

    // Drafts, deleted updates, future-scheduled updates and inactive categories never display.
    $duRecords = \App\Models\DailyUpdate::query()
        ->with('category')
        ->published()
        ->whereHas('category', function ($q) {
            $q->where('is_active', 1);
        })
        ->latestUpdates()
        ->get();

    $duItems = []; // Name retained solely for compatibility with existing UI loop.
    foreach ($duRecords as $update) {
        $categorySlug = (string) optional($update->category)->slug;
        if (!isset($duSections[$categorySlug])) {
            continue;
        }
        $answerIndex = array_search(strtoupper((string) $update->correct_option), ['A', 'B', 'C', 'D'], true);
        $duItems[] = [
            'id' => (int) $update->id,
            'section' => $categorySlug,
            'date' => $update->update_date->format('Y-m-d'),
            'topic' => (string) ($update->topic ?: $duSections[$categorySlug]['name']),
            'question' => (string) $update->question,
            'options' => [(string) $update->option_a, (string) $update->option_b, (string) $update->option_c, (string) $update->option_d],
            'answer' => ($answerIndex === false ? null : $answerIndex),
            'explain' => (string) ($update->explanation ?? ''),
            'image' => (string) ($update->featured_image ?? ''),
        ];
    }

    $duBn = ['0'=>'০','1'=>'১','2'=>'২','3'=>'৩','4'=>'৪','5'=>'৫','6'=>'৬','7'=>'৭','8'=>'৮','9'=>'৯'];
    $duMonths = ['01'=>'জানুয়ারি','02'=>'ফেব্রুয়ারি','03'=>'মার্চ','04'=>'এপ্রিল','05'=>'মে','06'=>'জুন','07'=>'জুলাই','08'=>'আগস্ট','09'=>'সেপ্টেম্বর','10'=>'অক্টোবর','11'=>'নভেম্বর','12'=>'ডিসেম্বর'];
    $duLetters = ['ক','খ','গ','ঘ'];
    $duYears = collect($duItems)->pluck('date')->map(function ($date) {
        return substr($date, 0, 4);
    })->unique()->sortDesc()->values();
    $duVisibleItems = array_values(array_filter($duItems, function ($entry) use ($duCurrent) {
        return $duCurrent === null || $entry['section'] === $duCurrent;
    }));
@endphp

<style>
/* Every selector begins with lebd-daily; no changes to Homepage stylesheet */
.lebd-daily{--d-navy:#0b2b64;--d-blue:#0b64df;--d-line:#dae6f8;--d-muted:#627897;--d-soft:#edf5ff;max-width:1200px;margin:0 auto;padding:16px 16px 45px;color:#172c4a;font-family:inherit;font-size:14px;line-height:1.55}
.lebd-daily,.lebd-daily *,.lebd-daily *:before,.lebd-daily *:after{box-sizing:border-box}
.lebd-daily button,.lebd-daily input,.lebd-daily select,.lebd-daily textarea{font:inherit}
.lebd-daily button{cursor:pointer}.lebd-daily a{text-decoration:none}
.lebd-daily [hidden]{display:none!important}
.lebd-daily .d-crumb{display:flex;align-items:center;flex-wrap:wrap;gap:9px;color:#6a80a5;font-size:12px;margin:0 0 13px}
.lebd-daily .d-crumb a{color:#2658a0}.lebd-daily .d-crumb a:hover{text-decoration:underline}
.lebd-daily .d-hero{display:flex;align-items:center;justify-content:space-between;gap:15px;padding:22px 24px;border-radius:19px;background:linear-gradient(115deg,#e6f0ff,#deedff 63%,#eaf4ff);border:1px solid #dbe8f9;box-shadow:0 7px 21px rgba(30,75,140,.06)}
.lebd-daily .d-hero-main{display:flex;gap:17px;align-items:center;min-width:0}
.lebd-daily .d-hero-icon{display:grid;place-items:center;width:70px;height:70px;flex:0 0 70px;font-size:32px;border:2px solid #bfdaff;background:#f5faff;border-radius:50%;box-shadow:0 5px 18px rgba(32,89,161,.08)}
.lebd-daily h1{margin:0;color:#0a306e;font-size:27px;line-height:1.2;font-weight:850}.lebd-daily .d-hero p{margin:7px 0 0;color:#5c7397;font-size:13px}
.lebd-daily .d-back{white-space:nowrap;display:inline-flex;align-items:center;gap:8px;padding:11px 18px;border-radius:11px;border:1px solid #dce7f5;background:#fff;color:#183967;font-weight:800;box-shadow:0 2px 8px rgba(0,0,0,.04)}.lebd-daily .d-back:hover{color:#055acc;background:#f9fcff}
.lebd-daily .d-filters{display:grid;grid-template-columns:1.2fr 1fr 1fr 1fr auto;align-items:end;gap:11px;margin:16px 0;background:#fff;padding:17px;border:1px solid #e0e9f4;border-radius:16px;box-shadow:0 5px 22px rgba(34,67,117,.045)}
.lebd-daily .d-field{min-width:0}.lebd-daily .d-field label{display:block;margin-bottom:7px;color:#193b70;font-weight:800;font-size:12px}
.lebd-daily .d-field input,.lebd-daily .d-field select{height:44px;width:100%;border:1px solid #d0dff2;border-radius:10px;background:#fbfdff;color:#1d3b68;padding:0 11px;outline:none;min-width:0;font-size:13px}
.lebd-daily .d-field input:focus,.lebd-daily .d-field select:focus,.lebd-daily .d-comment textarea:focus{border-color:#2578ef;box-shadow:0 0 0 3px rgba(20,105,228,.1)}
.lebd-daily .d-actions{display:flex;align-items:center;gap:8px}.lebd-daily .d-action{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:10px 16px;border:1px solid #dae5f3;background:#fff;border-radius:10px;color:#1b4d90;font-weight:800;white-space:nowrap;font-size:13px}
.lebd-daily .d-share{color:#fff;border-color:#069a66;background:#079f69}.lebd-daily .d-print{color:#fff;border-color:#0659ce;background:#0964dc}.lebd-daily .d-action:hover{filter:brightness(.96)}
.lebd-daily .d-categories{display:flex;gap:10px;flex-wrap:wrap;margin:17px 0 0;align-items:center}.lebd-daily .d-category{padding:12px 18px;border-radius:12px 12px 0 0;border:1px solid #d5e4f7;background:#e8f2ff;color:#14447e;font-weight:850;display:inline-flex;align-items:center;gap:9px;transition:.16s}
.lebd-daily .d-category:hover{background:#cee3ff;transform:translateY(-1px)}.lebd-daily .d-category.d-active{background:linear-gradient(100deg,#126be5,#0750b6);border-color:#1665d2;color:#fff}
.lebd-daily .d-panel{padding:18px;background:#fff;border:1px solid #dde7f4;border-radius:0 17px 17px 17px;box-shadow:0 8px 24px rgba(29,70,124,.045)}
.lebd-daily .d-panel-top{padding:16px 18px;border-radius:13px;border:1px solid #dce9f8;background:linear-gradient(105deg,#edf5ff,#e8f2ff);display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}
.lebd-daily .d-panel-top strong{display:block;color:#0c3375;font-size:18px}.lebd-daily .d-panel-top p{margin:3px 0 0;font-size:12px;color:#6c7fa0}
.lebd-daily .d-count{border-radius:30px;padding:7px 12px;border:1px solid #dbe6f5;background:#fff;color:#1d5bb3;font-weight:800;font-size:12px;white-space:nowrap}
.lebd-daily .d-notice{padding:9px 12px;border-radius:10px;border:1px solid #f6e4ae;background:#fffaf0;color:#816022;font-size:12px;margin-bottom:12px}
.lebd-daily .d-card{border:1px solid #cdddff;border-radius:13px;padding:14px;margin-bottom:13px;background:#fff;break-inside:avoid}.lebd-daily .d-card:last-child{margin-bottom:0}
.lebd-daily .d-card-head{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:10px}.lebd-daily .d-badge{border-radius:99px;padding:5px 10px;background:#e6f2ff;color:#22599f;font-size:11px;font-weight:800}.lebd-daily .d-date{color:#6b80a1;font-size:11px}
.lebd-daily .d-question{background:#ebf4ff;border-radius:9px;padding:11px 13px;color:#082c66;margin:0 0 10px;font-size:16px;font-weight:850;line-height:1.5}
.lebd-daily .d-image{padding:7px 0 12px}.lebd-daily .d-image img{max-width:100%;max-height:350px;border:1px solid #dce8f7;border-radius:10px;object-fit:contain}
.lebd-daily .d-options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.lebd-daily .d-option{min-height:42px;padding:10px 12px;background:#f9fbff;border:1px solid #e0e8f4;border-radius:9px;font-size:14px}
.lebd-daily .d-correct{margin-top:11px;background:#e9f8ee;border:1px solid #bde6ca;border-radius:9px;padding:10px 12px;color:#0b7b42;font-weight:850}.lebd-daily .d-explain{margin-top:8px;color:#526987;font-size:12px}
.lebd-daily .d-card-bottom{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:9px;padding-top:11px;margin-top:12px;border-top:1px dashed #dbe5f3}
.lebd-daily .d-card-bottom .d-action{min-height:34px;padding:7px 12px;font-size:12px}.lebd-daily .d-comment-toggle{color:#0c5ac5;background:none;border:0;padding:6px 0;font-weight:850;font-size:12px}
.lebd-daily .d-open{display:inline-flex;align-items:center;gap:7px;color:#0d62d8;font-size:12px;font-weight:850}
.lebd-daily .d-comment{padding:14px;background:#fff1f4;border:1px solid #f7dce3;border-radius:10px;margin-top:13px}.lebd-daily .d-comment label{display:block;margin-bottom:7px;color:#c12338;font-size:13px;font-weight:800}
.lebd-daily .d-comment textarea{width:100%;min-height:80px;padding:11px 12px;resize:vertical;border-radius:9px;background:#fff;border:1px solid #e5d9e2;outline:none}.lebd-daily .d-comment-send{margin-top:9px;border:0;border-radius:9px;padding:9px 15px;background:#0b66df;color:#fff;font-weight:800}
.lebd-daily .d-comment-list{margin-top:9px}.lebd-daily .d-comment-entry{margin-top:6px;padding:8px 10px;border:1px solid #efdce3;border-radius:8px;background:#fff;white-space:pre-wrap;overflow-wrap:anywhere;font-size:12px}
.lebd-daily .d-empty{padding:32px 12px;border:1px dashed #cbdcf0;background:#f9fcff;border-radius:12px;text-align:center;color:#647a98}
.lebd-daily .d-toast{position:fixed;z-index:99999;right:22px;bottom:22px;pointer-events:none;opacity:0;transform:translateY(8px);padding:11px 17px;border-radius:11px;background:#133568;color:white;font-size:13px;box-shadow:0 10px 26px rgba(8,29,67,.22);transition:opacity .2s,transform .2s}.lebd-daily .d-toast.d-show{opacity:1;transform:translateY(0)}
@media(max-width:1060px){.lebd-daily .d-filters{grid-template-columns:repeat(4,minmax(0,1fr))}.lebd-daily .d-actions{grid-column:1 / -1;justify-content:flex-end}}
@media(max-width:730px){.lebd-daily{padding:12px 10px 38px}.lebd-daily .d-hero{padding:15px}.lebd-daily h1{font-size:21px}.lebd-daily .d-hero-icon{width:54px;height:54px;flex-basis:54px;font-size:26px}.lebd-daily .d-filters{grid-template-columns:repeat(2,minmax(0,1fr));padding:13px}.lebd-daily .d-panel{padding:12px}.lebd-daily .d-categories{gap:6px}.lebd-daily .d-category{font-size:12px;padding:10px 12px}.lebd-daily .d-options{grid-template-columns:1fr}}
@media(max-width:460px){.lebd-daily{padding:9px 8px 28px}.lebd-daily .d-hero{padding:12px;gap:8px}.lebd-daily .d-hero-main{gap:10px}.lebd-daily .d-hero-icon{width:42px;height:42px;flex-basis:42px;font-size:21px}.lebd-daily h1{font-size:17px}.lebd-daily .d-hero p{font-size:11px;margin-top:4px}.lebd-daily .d-back{font-size:11px;padding:7px 9px}.lebd-daily .d-filters{gap:9px}.lebd-daily .d-field input,.lebd-daily .d-field select{height:40px}.lebd-daily .d-panel-top{padding:12px;align-items:flex-start}.lebd-daily .d-panel-top strong{font-size:15px}.lebd-daily .d-card{padding:10px}.lebd-daily .d-question{font-size:14px}.lebd-daily .d-option{padding:9px;font-size:13px}.lebd-daily .d-actions{justify-content:flex-start}}
@media print{body{background:#fff!important}.lebd-daily{max-width:none!important;padding:0!important}.lebd-daily .d-crumb,.lebd-daily .d-back,.lebd-daily .d-filters,.lebd-daily .d-categories,.lebd-daily .d-card-bottom,.lebd-daily .d-comment,.lebd-daily .d-notice,.lebd-daily .d-toast,.lebd-daily .d-open{display:none!important}.lebd-daily .d-hero,.lebd-daily .d-panel{border:0;box-shadow:none;padding:6px 0;background:#fff}.lebd-daily .d-panel-top{background:#fff;border:0}.lebd-daily .d-card{border-color:#b8c8dc}.lebd-daily .d-card[hidden]{display:none!important}body.d-print-one .lebd-daily .d-card:not(.d-print-target){display:none!important}}
</style>

<div class="lebd-daily" id="lebdDailyPage">
    <nav class="d-crumb" aria-label="Breadcrumb">
        <a href="{{ url('/') }}">⌂ Home</a><span>›</span>
        <a href="{{ $duBaseUrl }}">দৈনিক আপডেট</a>
        @if($duIsDetail && $duValid)<span>›</span><span>{{ $duPageTitle }}</span>@endif
    </nav>

    <header class="d-hero">
        <div class="d-hero-main">
            <span class="d-hero-icon" aria-hidden="true">{{ $duCurrent ? $duSections[$duCurrent]['symbol'] : '📰' }}</span>
            <div>
                <h1>{{ $duPageTitle }}</h1>
                <p>{{ $duPageSubtitle }}</p>
            </div>
        </div>
        <a class="d-back" href="{{ $duIsDetail ? $duBaseUrl : url('/') }}" id="dBack">← Back</a>
    </header>

    @if(!$duValid)
        <div class="d-empty" style="margin-top:18px">এই বিভাগটি পাওয়া যায়নি। <a href="{{ $duBaseUrl }}">Daily Update-এ ফিরে যান</a></div>
    @else
        <section class="d-filters" aria-label="Daily Update Filters">
            <div class="d-field"><label for="dSearch">Search</label><input type="search" id="dSearch" placeholder="প্রশ্ন বা বিষয় লিখুন..." autocomplete="off"></div>
            <div class="d-field"><label for="dYear">Select Year</label><select id="dYear"><option value="">সব বছর</option>@foreach($duYears as $year)<option value="{{ $year }}">{{ strtr((string) $year, $duBn) }}</option>@endforeach</select></div>
            <div class="d-field"><label for="dMonth">Select Month</label><select id="dMonth"><option value="">সব মাস</option>@foreach($duMonths as $no=>$name)<option value="{{ $no }}">{{ $name }}</option>@endforeach</select></div>
            <div class="d-field"><label for="dDate">Date</label><input type="date" id="dDate"></div>
            <div class="d-actions"><button class="d-action d-share" id="dShareAll" type="button">↗ Share</button><button class="d-action d-print" id="dPrintAll" type="button">▣ Print</button></div>
        </section>

        {{-- Every LIVE category uses this same reusable detail design. --}}
        <nav class="d-categories" aria-label="Daily Update Categories">
            @foreach($duSections as $slug=>$section)
                <a class="d-category {{ $duCurrent === $slug ? 'd-active' : '' }}" href="{{ $duBaseUrl }}?section={{ urlencode($slug) }}">{{ $section['symbol'] }} {{ $section['name'] }} →</a>
            @endforeach
            @if(empty($duSections))
                <span class="d-badge">এখনো কোনো Active Category নেই</span>
            @endif
        </nav>

        <main class="d-panel" id="dMainPanel">
            <div class="d-panel-top">
                <div>
                    <strong>{{ $duCurrent ? 'Daily Update (' . $duSections[$duCurrent]['name'] . ')' : '📰 Daily Update — সকল বিভাগ' }}</strong>
                    <p>{{ $duCurrent ? 'এই বিভাগের প্রশ্ন, সঠিক উত্তর ও মন্তব্য' : 'যেকোনো Category Button-এ ক্লিক করলে একই ডিজাইনে আলাদা পেজ খুলবে' }}</p>
                </div>
                <span class="d-count" id="dCounter">{{ strtr((string)count($duVisibleItems),$duBn) }}টি প্রশ্ন</span>
            </div>
            <p class="d-notice">✓ Live Database: এখানে শুধু Active Category-এর Published প্রশ্ন দেখানো হয়। মন্তব্য অংশ এখনো Demo—Database-এ Save হবে না।</p>

            <section id="dQuestionList" aria-label="Published MCQ Questions">
                @foreach($duVisibleItems as $item)
                    @php
                        $dateParts = explode('-', $item['date']);
                        $dateLabel = strtr(ltrim($dateParts[2], '0'), $duBn).' '.$duMonths[$dateParts[1]].' '.strtr($dateParts[0],$duBn);
                        $sectionInfo = $duSections[$item['section']];
                    @endphp
                    <article class="d-card" id="question-{{ $item['id'] }}"
                             data-date="{{ $item['date'] }}"
                             data-search="{{ mb_strtolower($item['question'].' '.$item['topic'].' '.implode(' ', $item['options']), 'UTF-8') }}">
                        <div class="d-card-head">
                            <span class="d-badge">{{ $item['topic'] }}</span>
                            <span class="d-date">▦ {{ $dateLabel }}</span>
                        </div>
                        <h2 class="d-question">Q. {{ $item['question'] }}</h2>
                        @if($item['image'])
                            <div class="d-image"><img src="{{ asset('storage/' . ltrim($item['image'], '/')) }}" alt="প্রশ্নের সঙ্গে সংযুক্ত ছবি" loading="lazy"></div>
                        @endif
                        <div class="d-options">
                            @foreach($item['options'] as $i=>$text)
                                <div class="d-option">{{ $duLetters[$i] }}) {{ $text }}</div>
                            @endforeach
                        </div>
                        @if($item['answer'] !== null)
                            <div class="d-correct">✓ সঠিক উত্তর: {{ $duLetters[$item['answer']] }}) {{ $item['options'][$item['answer']] }}</div>
                        @endif
                        @if($item['explain'] !== '')
                            <div class="d-explain">{{ $item['explain'] }}</div>
                        @endif
                        <div class="d-card-bottom">
                            @if($duIsDetail)
                                <button class="d-comment-toggle" type="button" aria-expanded="false">💬 প্রশ্ন সম্পর্কে মন্তব্য করুন</button>
                            @else
                                <a class="d-open" href="{{ $duBaseUrl }}?section={{ urlencode($item['section']) }}#question-{{ $item['id'] }}">{{ $sectionInfo['name'] }} — বিস্তারিত দেখুন →</a>
                            @endif
                            <div class="d-actions">
                                <button class="d-action d-share-one" type="button">↗ Share</button>
                                <button class="d-action d-print-one" type="button">▣ Print</button>
                            </div>
                        </div>
                        @if($duIsDetail)
                            <div class="d-comment" hidden>
                                <label for="dComment{{ $item['id'] }}">প্রশ্ন সম্পর্কে মন্তব্য করার ব্যবস্থা</label>
                                <textarea id="dComment{{ $item['id'] }}" maxlength="500" placeholder="আপনার মন্তব্য এখানে লিখুন..."></textarea>
                                <button type="button" class="d-comment-send">➤ মন্তব্য করুন (Demo)</button>
                                <div class="d-comment-list" aria-live="polite"></div>
                            </div>
                        @endif
                    </article>
                @endforeach
            </section>
            <div class="d-empty" id="dEmpty" hidden>🔎 এখন কোনো Published প্রশ্ন পাওয়া যায়নি। Filter পরিবর্তন করে দেখুন।</div>
        </main>
    @endif
    <div class="d-toast" id="dToast" role="status" aria-live="polite"></div>
</div>
<script>
(function(){
    'use strict';
    var root=document.getElementById('lebdDailyPage');
    if(!root)return;
    var cards=Array.prototype.slice.call(root.querySelectorAll('.d-card'));
    var search=root.querySelector('#dSearch');
    var year=root.querySelector('#dYear');
    var month=root.querySelector('#dMonth');
    var date=root.querySelector('#dDate');
    var counter=root.querySelector('#dCounter');
    var empty=root.querySelector('#dEmpty');
    var toast=root.querySelector('#dToast');
    var toastId;
    function message(t){toast.textContent=t;toast.classList.add('d-show');clearTimeout(toastId);toastId=setTimeout(function(){toast.classList.remove('d-show')},2600)}
    function bn(n){return String(n).replace(/[0-9]/g,function(d){return '০১২৩৪৫৬৭৮৯'[Number(d)]})}
    function filter(){if(!search)return;var q=search.value.trim().toLocaleLowerCase(),seen=0;
        cards.forEach(function(c){var v=c.getAttribute('data-date');var ok=(!q||c.getAttribute('data-search').toLocaleLowerCase().includes(q))&&(!year.value||v.substring(0,4)===year.value)&&(!month.value||v.substring(5,7)===month.value)&&(!date.value||v===date.value);c.hidden=!ok;if(ok)seen++});
        counter.textContent=bn(seen)+'টি প্রশ্ন';empty.hidden=seen>0;
    }
    [search,year,month,date].forEach(function(i){if(i)i.addEventListener(i===search?'input':'change',filter)});
    async function share(title,text,url){
        if(navigator.share){try{await navigator.share({title:title,text:text,url:url});return}catch(e){if(e&&e.name==='AbortError')return}}
        var value=text+'\n'+url;
        if(navigator.clipboard && window.isSecureContext){try{await navigator.clipboard.writeText(value);message('✓ Link Copied Successfully');return}catch(e){}}
        var tmp=document.createElement('textarea');tmp.value=value;tmp.style.position='fixed';tmp.style.opacity='0';document.body.appendChild(tmp);tmp.select();var good=document.execCommand('copy');tmp.remove();message(good?'✓ Link Copied Successfully':'এই Browser-এ Share সম্ভব হচ্ছে না');
    }
    function questionShare(btn){var card=btn.closest('.d-card');share('Daily Update | Live Education BD',card.querySelector('.d-question').textContent+'\n'+(card.querySelector('.d-correct') ? card.querySelector('.d-correct').textContent : ''),window.location.href.split('#')[0]+'#'+card.id)}
    root.querySelectorAll('.d-share-one').forEach(function(btn){btn.addEventListener('click',function(){questionShare(btn)})});
    var shareAll=root.querySelector('#dShareAll');if(shareAll)shareAll.addEventListener('click',function(){share(document.title,'Daily Update দেখুন',window.location.href)});
    function printOne(card){document.body.classList.add('d-print-one');cards.forEach(function(c){c.classList.toggle('d-print-target',c===card)});window.print()}
    root.querySelectorAll('.d-print-one').forEach(function(btn){btn.addEventListener('click',function(){printOne(btn.closest('.d-card'))})});
    var printAll=root.querySelector('#dPrintAll');if(printAll)printAll.addEventListener('click',function(){document.body.classList.remove('d-print-one');cards.forEach(function(c){c.classList.remove('d-print-target')});window.print()});
    window.addEventListener('afterprint',function(){document.body.classList.remove('d-print-one');cards.forEach(function(c){c.classList.remove('d-print-target')})});
    root.querySelectorAll('.d-comment-toggle').forEach(function(btn){btn.addEventListener('click',function(){var box=btn.closest('.d-card').querySelector('.d-comment');box.hidden=!box.hidden;btn.setAttribute('aria-expanded',String(!box.hidden));if(!box.hidden)box.querySelector('textarea').focus()})});
    root.querySelectorAll('.d-comment-send').forEach(function(btn){btn.addEventListener('click',function(){var box=btn.closest('.d-comment'),area=box.querySelector('textarea'),val=area.value.trim();if(!val){message('প্রথমে মন্তব্য লিখুন');area.focus();return}var div=document.createElement('div');div.className='d-comment-entry';div.textContent=val;box.querySelector('.d-comment-list').prepend(div);area.value='';message('Demo Comment দেখানো হয়েছে; Save হয়নি')})});
    var back=root.querySelector('#dBack');if(back)back.addEventListener('click',function(e){try{var ref=document.referrer;if(ref&&new URL(ref).origin===window.location.origin){e.preventDefault();window.history.back()}}catch(err){}});
    filter();
})();
</script>
@endsection
