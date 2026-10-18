{{-- LEBD-3 MCQ homepage sections, isolated from homepage/index.blade.php --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="{{ asset('web/css/job-preparation-home.css') }}">
@php
    // MCQ Job Preparation uses its own category tables; never mix with course subjects.
    $lebdJobDeptCategories = \Illuminate\Support\Facades\DB::table('job_prep_categories')
        ->where('kind','department')->where('status','Active')
        ->orderBy('sort_order')->orderBy('id')->get();
    $lebdJobNonDeptCategories = \Illuminate\Support\Facades\DB::table('job_prep_categories')
        ->where('kind','non-department')->where('status','Active')
        ->orderBy('sort_order')->orderBy('id')->get();
@endphp
{{-- =========================================================
     MCQ JOB PREPARATION + MCQ STUDY
========================================================= --}}

<section class="mcq-main-wrapper">

    {{-- =====================================================
         MCQ JOB PREPARATION
    ====================================================== --}}
    <div class="mcq-section mcq-job-section">

        {{-- Decorative Left --}}
        <div class="mcq-decoration mcq-left-decoration">
            <span>Small</span>
            <span>Steps</span>
            <span>Big</span>
            <span>Results</span>
        </div>

        {{-- Decorative Right --}}
        <div class="mcq-decoration mcq-right-decoration">
            <span>Prepare</span>
            <span>For a</span>
            <span>Better</span>
            <span>Tomorrow</span>
        </div>

        {{-- Section Heading --}}
        <div class="mcq-heading job-heading">

            <div class="mcq-heading-icon">
                <i class="fas fa-briefcase"></i>
            </div>

            <div>
                <h2>Department MCQ Study
                </h2>
                <p>Practice Today · Build Your Career</p>
            </div>

        </div>


        {{-- Dynamic department cards — admin-managed MCQ Job Preparation --}}
        <div class="mcq-category-grid">
            @forelse($lebdJobDeptCategories as $jpCat)
                @php
                  $jpIcon = preg_match('/^[a-z0-9-]+$/', (string)($jpCat->icon ?? '')) ? 'fa-'.$jpCat->icon : 'fa-book-open';
                  $jpFg = preg_match('/^#[0-9a-fA-F]{6}$/',(string)($jpCat->icon_color ?? '')) ? $jpCat->icon_color : '#1176e9';
                  $jpBg = preg_match('/^#[0-9a-fA-F]{6}$/',(string)($jpCat->card_color ?? '')) ? $jpCat->card_color : '#eef5ff';
                @endphp
                <a href="{{ route('student.jobprep.category',['kind'=>'department','category'=>$jpCat->id]) }}" class="mcq-category-card lebd-jp-dynamic-card" style="background:{{ $jpBg }}" title="{{ $jpCat->name }}">
                    <div class="mcq-card-icon" style="color:{{ $jpFg }}"><i class="fa-solid {{ $jpIcon }}"></i></div>
                    <h3>{{ $jpCat->name }}</h3>
                </a>
            @empty
                <div class="lebd-jp-empty">এখনো কোনো Department Category প্রকাশিত হয়নি।</div>
            @endforelse
        </div>
    </div>


    {{-- =====================================================
         MCQ STUDY
    ====================================================== --}}
    <div class="mcq-section mcq-study-section">

        {{-- Decorative Left --}}
        <div class="mcq-decoration study-left-decoration">
            <span>Small</span>
            <span>Steps Make</span>
            <span>Big Results</span>
        </div>

        {{-- Decorative Right --}}
        <div class="study-books-decoration">
            <i class="fas fa-book"></i>
            <i class="fas fa-book"></i>
            <i class="fas fa-book"></i>
        </div>

        {{-- Section Heading --}}
        <div class="mcq-heading study-heading">

            <div class="mcq-heading-icon">
                <i class="fas fa-book-open"></i>
            </div>

            <div>
                <h2>Non-MCQ Study</h2>
                <p>Learn · Practice · Improve</p>
            </div>

        </div>


        {{-- Dynamic non-department cards — same admin-managed catalogue --}}
        <div class="mcq-study-grid">
            @forelse($lebdJobNonDeptCategories as $jpCat)
                @php
                  $jpIcon = preg_match('/^[a-z0-9-]+$/', (string)($jpCat->icon ?? '')) ? 'fa-'.$jpCat->icon : 'fa-book-open';
                  $jpFg = preg_match('/^#[0-9a-fA-F]{6}$/',(string)($jpCat->icon_color ?? '')) ? $jpCat->icon_color : '#7b3ee8';
                  $jpBg = preg_match('/^#[0-9a-fA-F]{6}$/',(string)($jpCat->card_color ?? '')) ? $jpCat->card_color : '#f3edff';
                @endphp
                <a href="{{ route('student.jobprep.category',['kind'=>'non-department','category'=>$jpCat->id]) }}" class="mcq-study-card lebd-jp-dynamic-card" style="background:{{ $jpBg }}" title="{{ $jpCat->name }}">
                    <div class="study-card-icon" style="color:{{ $jpFg }}"><i class="fa-solid {{ $jpIcon }}"></i></div>
                    <h3>{{ $jpCat->name }}</h3>
                </a>
            @empty
                <div class="lebd-jp-empty">এখনো কোনো Non-Department Category প্রকাশিত হয়নি।</div>
            @endforelse
        </div>
    </div>

</section>



{{-- =========================================================
     FONT AWESOME
========================================================= --}}
