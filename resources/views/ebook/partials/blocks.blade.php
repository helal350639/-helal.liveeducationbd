@php
    $ebookLabels = ['ক','খ','গ','ঘ','ঙ','চ','ছ','জ'];
    $ebNum = 0;
    $ebEditor = !empty($isEditor);
    $chapterIndex = isset($chapterIndex) ? (int) $chapterIndex : null;
    $chapterNo = $chapterIndex ? str_pad((string)$chapterIndex,2,'0',STR_PAD_LEFT) : null;
@endphp
<link rel="stylesheet" href="{{ asset('web/css/lebd-ebook-a4-v14.css') }}">
<div class="lebd-a4-reader" data-ebook-reader data-chapter-title="{{ $ch->title }}">
  <div class="lebd-a4-pages" data-ebook-pages></div>
  <div class="lebd-source-items" data-ebook-source>

    <article class="lebd-book-item lebd-book-chapter-intro" data-page-item data-item-type="chapter_intro">
      <div class="lebd-chapter-intro-frame">
        <div class="lebd-chapter-intro-hero">
          <div class="lebd-chapter-intro-left">
            <div class="lebd-chapter-intro-left-top">
              {{ $chapterNo ? 'অধ্যায়-'.$chapterNo : 'অধ্যায়' }}
            </div>
            <div class="lebd-chapter-intro-left-bottom">
              {{ $cat->name }}
            </div>
          </div>
          <div class="lebd-chapter-intro-main">
            <div class="lebd-chapter-intro-main-title">{{ $ch->title }}</div>
            <div class="lebd-chapter-intro-main-subtitle">{{ $cat->name }} — E-Book Chapter</div>
          </div>
        </div>
        <div class="lebd-chapter-intro-strip">এক নজরে এই অধ্যায়ের গুরুত্বপূর্ণ তথ্যাবলি</div>
      </div>
    </article>

  @foreach($blocks as $block)
    @php
      $q = $block->block_type === 'database_mcq' ? $qs->get($block->question_id) : null;
      $choices = $block->block_type === 'database_mcq' ? $opts->get($block->question_id, collect()) : collect();
      $isQuestion = $block->block_type === 'custom_question' || ($block->block_type === 'database_mcq' && $q);
      if ($isQuestion) $ebNum++;
      $answerLabel = '';
      if ($block->block_type === 'database_mcq' && $q) {
        foreach($choices as $j => $choice) {
          if (!empty($choice->correct_answer)) { $answerLabel = $ebookLabels[$j] ?? (string)($j+1); break; }
        }
        if ($answerLabel === '' && !empty($q->answer)) $answerLabel = (string)$q->answer;
      } elseif ($block->block_type === 'custom_question') {
        $answerLabel = (string)($block->answer ?? '');
      }
      $questionText = $block->block_type === 'database_mcq' && $q ? $q->title : $block->title;
      $explanationText = $block->block_type === 'database_mcq' && $q ? ($q->solution ?: $q->explanation) : $block->explanation;
      $customOptions = $block->block_type === 'custom_question' ? (json_decode($block->options_json ?? '[]', true) ?: []) : [];
    @endphp
    <article class="lebd-book-item" data-page-item data-item-type="{{ $block->block_type }}" @if($isQuestion) data-q-number="{{ $ebNum }}" data-q-answer="{{ $answerLabel }}" @endif>
      @if($block->block_type === 'heading')
        <h3 class="lebd-book-subtitle">{{ $block->title }}</h3>
      @elseif($block->block_type === 'text')
        <div class="lebd-book-prose">{!! nl2br(e($block->content ?? '')) !!}</div>
      @elseif($isQuestion)
        <div class="lebd-book-question"><b>প্রশ্ন-{{ str_pad((string)$ebNum,2,'0',STR_PAD_LEFT) }}:</b> <span class="lebd-rich-content">{!! $questionText !!}</span></div>
        @if($block->block_type === 'database_mcq' && $q && !empty($q->image))
          <img class="lebd-book-question-image" src="{{ asset($q->image) }}" alt="Question illustration" loading="lazy">
        @endif
        <div class="lebd-book-options">
          @if($block->block_type === 'database_mcq')
            @foreach($choices as $j => $choice)
              <div class="lebd-book-option"><span>{{ $ebookLabels[$j] ?? ($j+1) }})</span> <span class="lebd-rich-content">{!! $choice->item !!}</span> @if(!empty($choice->image))<img src="{{ asset($choice->image) }}" alt="Option image" loading="lazy">@endif</div>
            @endforeach
          @else
            @foreach($customOptions as $j => $option)
              <div class="lebd-book-option"><span>{{ $ebookLabels[$j] ?? ($j+1) }})</span> <span>{{ $option }}</span></div>
            @endforeach
          @endif
        </div>
        <div class="lebd-book-correct"><strong>সঠিক উত্তর:</strong> {{ $answerLabel !== '' ? $answerLabel : 'প্রদান করা হয়নি' }}</div>
        @if(!empty($explanationText))
          <div class="lebd-book-explanation"><strong>ব্যাখ্যা:</strong> <span class="lebd-rich-content">{!! $explanationText !!}</span></div>
        @endif
      @elseif($block->block_type === 'database_mcq')
        <p class="lebd-missing-question">প্রশ্নটি Main Database-এ পাওয়া যায়নি (ID: {{ $block->question_id }}).</p>
      @endif
      @if($ebEditor)
        <div class="lebd-a4-edit-controls" data-editor-controls>
          <form method="POST" action="{{ route('admin.ebook.blocks.move',['kind'=>$kind,'chapter'=>$ch->id,'block'=>$block->id]) }}">@csrf<input type="hidden" name="direction" value="up"><button class="ebook-btn alt" type="submit">↑ Up</button></form>
          <form method="POST" action="{{ route('admin.ebook.blocks.move',['kind'=>$kind,'chapter'=>$ch->id,'block'=>$block->id]) }}">@csrf<input type="hidden" name="direction" value="down"><button class="ebook-btn alt" type="submit">↓ Down</button></form>
          <form method="POST" action="{{ route('admin.ebook.blocks.delete',['kind'=>$kind,'chapter'=>$ch->id,'block'=>$block->id]) }}" onsubmit="return confirm('Remove this item?')">@csrf @method('DELETE')<button class="ebook-btn danger" type="submit">Delete</button></form>
        </div>
        @if($block->block_type !== 'database_mcq')
          <details class="lebd-a4-edit-details"><summary>Edit Content</summary>
            <form method="POST" action="{{ route('admin.ebook.blocks.update',['kind'=>$kind,'chapter'=>$ch->id,'block'=>$block->id]) }}">@csrf @method('PUT')
              @if($block->block_type !== 'text')<label>Heading / Question<input class="ebook-field" name="title" value="{{ $block->title }}"></label>@endif
              @if($block->block_type === 'text')<label>Text<textarea class="ebook-field" rows="5" name="content">{{ $block->content }}</textarea></label>@endif
              @if($block->block_type === 'custom_question')
                @foreach($customOptions as $option)<input class="ebook-field" name="options[]" value="{{ $option }}">@endforeach
                <label>Answer<input class="ebook-field" name="answer" value="{{ $block->answer }}"></label>
                <label>Explanation<textarea class="ebook-field" name="explanation">{{ $block->explanation }}</textarea></label>
              @endif
              <button type="submit" class="ebook-btn">Save Changes</button>
            </form>
          </details>
        @endif
      @endif
    </article>
  @endforeach
  </div>
  @if($ebEditor)
    <section class="lebd-editor-items"><h3>অধ্যায়ের Content Edit / Up / Down / Delete</h3><div data-editor-list></div></section>
  @endif
  <noscript><p>Please enable JavaScript to display A4 pagination.</p></noscript>
</div>
<script defer src="{{ asset('web/js/lebd-ebook-a4-v14.js') }}"></script>
