<section class="lebd-ebook-group" aria-labelledby="lebd-ebook-general-title">
    <div class="lebd-ebook-header lebd-ebook-header-general">
        <div class="lebd-ebook-heading"><span class="lebd-ebook-heading-icon"><i class="fa fa-book" aria-hidden="true"></i></span><div>
            <h2 id="lebd-ebook-general-title">E-Book / PDF ( General )</h2>
            <p>সাধারণ জ্ঞান ও প্রস্তুতিমূলক ই-বুক</p>
        </div></div>
        <a class="lebd-ebook-see-all" href="{{ route('student.ebook.categories', ['kind' => 'non-department']) }}">সব দেখুন →</a>
    </div>
    <div class="lebd-ebook-grid">
        @forelse($ebookCategories as $cat)
            <a class="lebd-ebook-card" href="{{ route('student.ebook.chapters', ['kind' => 'non-department', 'category' => $cat->id]) }}"
               style="--lebd-book-bg: {{ preg_match('/^#[0-9a-fA-F]{6}$/', $cat->card_color ?? '') ? $cat->card_color : '#f6edff' }}; --lebd-book-icon: {{ preg_match('/^#[0-9a-fA-F]{6}$/', $cat->icon_color ?? '') ? $cat->icon_color : '#7943ca' }};"
               aria-label="{{ $cat->name }} — সূচিপত্র দেখুন">
                <span class="lebd-ebook-icon-circle"><i class="fa {{ preg_match('/^fa-[a-z0-9-]+$/i', $cat->icon ?? '') ? $cat->icon : 'fa-book' }}" aria-hidden="true"></i></span>
                <span class="lebd-ebook-card-name">{{ $cat->name }}</span>
            </a>
        @empty
            <p class="lebd-ebook-empty">এখন কোনো General E-Book প্রকাশিত নেই।</p>
        @endforelse
    </div>
</section>
