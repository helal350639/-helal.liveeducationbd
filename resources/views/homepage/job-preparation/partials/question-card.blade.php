@php
 $letters=['a','b','c','d','e','f'];
 $correctChoices=$choices->filter(fn($choice)=>in_array(strtolower(trim((string)$choice->correct_answer)),['1','yes','true','on'],true));
 $sourceText = strip_tags((string)($question->details ?? ''));
 $shareUrl = route('student.jobprep.share.show',['kind'=>$kind,'level'=>$level,'entity'=>$entity,'question'=>$question->id]);
@endphp
<article class="jp-question" id="question-{{$question->id}}">
 <header class="jp-q-head"><div class="jp-q-title-line"><span class="jp-q-num">Q-{{ str_pad($idx,2,'0',STR_PAD_LEFT) }})</span> <div class="jp-q-title-text">{!!$question->title!!}</div></div>
  @if(!empty($sourceText))<div class="jp-source"><span>প্রশ্ন সোর্স:</span><span class="jp-src-pill">{{ \Illuminate\Support\Str::limit($sourceText,90) }}</span></div>@endif
 </header>
 <div class="jp-q-body">
  @if($question->image)<img loading="lazy" alt="Question image" src="{{preg_match('~^https?://~i',$question->image)?$question->image:asset(ltrim($question->image,'/'))}}">@endif
  <div class="jp-opts">
  @foreach($choices as $i=>$choice)
   @php $isCorrect=in_array(strtolower(trim((string)$choice->correct_answer)),['1','yes','true','on'],true); @endphp
   <div class="jp-opt {{$isCorrect?'correct':''}}"><strong>{{ $letters[$i] ?? ($i+1) }})</strong> {!!$choice->item!!}
    @if($choice->image)<img alt="Option image" loading="lazy" src="{{preg_match('~^https?://~i',(string)$choice->image)?$choice->image:asset(ltrim($choice->image,'/'))}}">@endif
   </div>
  @endforeach
  </div>
  <details class="jp-solution"><summary>সঠিক উত্তর ও ব্যাখ্যা দেখুন</summary>
   <p><strong>সঠিক উত্তর:</strong> @forelse($correctChoices as $ans){!!$ans->item!!} @empty {!!$question->answer ?: 'উত্তর দেওয়া হয়নি'!!} @endforelse</p>
   <p><strong>ব্যাখ্যা:</strong> {!!$question->explanation ?: ($question->solution ?: 'ব্যাখ্যা নেই')!!}</p>
  </details>
 </div>
 <div class="jp-q-actions">
  <button type="button" data-jp-toggle="reaction-{{$question->id}}" aria-expanded="false"><i class="fa fa-heart"></i> Reaction <span class="jp-reaction-count">{{ $reactionCounts->get($question->id,0) }}</span></button>
  <button type="button" data-jp-toggle="comment-{{$question->id}}"><i class="fa fa-comment"></i>Comment</button>
  <button type="button" data-jp-toggle="rating-{{$question->id}}"><i class="fa fa-star"></i>Rating</button>
  <button type="button" data-jp-share="{{$shareUrl}}" data-jp-title="{{strip_tags($question->title)}}" aria-label="Share this question"><i class="fa fa-share-alt"></i>Share</button>
  <button type="button" data-jp-focus="mark-{{$question->id}}"><i class="fa fa-book-open"></i>Reading</button>
 </div>
 <div class="jp-comment-box" id="reaction-{{$question->id}}" hidden>
  <strong>Question Reaction</strong>
  @if($student)
   <form method="POST" action="{{ route('student.jobprep.reaction.save',['kind'=>$kind,'level'=>$level,'entity'=>$entity,'question'=>$question->id]) }}" class="jp-reaction-form">
    @csrf
    <button class="jp-reaction-btn {{ $myReactions->get($question->id) ? 'is-active' : '' }}" type="submit"><i class="fa fa-heart"></i> {{ $myReactions->get($question->id) ? 'Liked · Click to remove' : 'Like this question' }}</button>
   </form>
  @else <a href="{{url('/login')}}">Reaction দিতে Student Login করুন</a> @endif
 </div>
 @php
  $ratingInfo = $ratingStats->get($question->id);
  $averageRating = $ratingInfo ? number_format((float) $ratingInfo->average, 1) : '0.0';
  $totalRatings = $ratingInfo ? (int) $ratingInfo->total : 0;
  $ownRating = (int) $myRatings->get($question->id, 0);
 @endphp
 <div class="jp-comment-box jp-rating-box" id="rating-{{$question->id}}" hidden>
  <div class="jp-rating-head">
   <strong><i class="fa fa-star" aria-hidden="true"></i> Question Rating</strong>
   <span>Average: {{ $averageRating }}/5 · {{ $totalRatings }} Ratings</span>
  </div>
  @if($student)
   <form class="jp-rating-form" method="POST" action="{{route('student.jobprep.rating.save',['kind'=>$kind,'level'=>$level,'entity'=>$entity,'question'=>$question->id])}}">
    @csrf
    <label for="rating-choose-{{$question->id}}">Your Rating:</label>
    <select id="rating-choose-{{$question->id}}" class="jp-rating-select" name="rating" required>
     <option value="" disabled {{$ownRating === 0 ? 'selected' : ''}}>Select stars</option>
     @foreach([1,2,3,4,5] as $star)
      <option value="{{$star}}" {{$ownRating === $star ? 'selected' : ''}}>{{str_repeat('★', $star)}} ({{$star}}/5)</option>
     @endforeach
    </select>
    <button class="jp-filter-btn" type="submit">Save Rating</button>
   </form>
  @else
   <a href="{{url('/login')}}">Rating দিতে Student Login করুন</a>
  @endif
 </div>
 <div class="jp-comment-box" id="comment-{{$question->id}}" hidden>
  <strong>Comments</strong>
  @foreach(($comments->get($question->id)??collect()) as $comment)
   <div class="jp-comment-item"><b>Student #{{$comment->student_id}}:</b> {{ $comment->comment }}</div>
  @endforeach
  @if($student)
   <form action="{{route('student.jobprep.comment.save',['kind'=>$kind,'level'=>$level,'entity'=>$entity,'question'=>$question->id])}}" method="POST">
    @csrf <textarea name="comment" maxlength="1000" required placeholder="আপনার মন্তব্য লিখুন..." aria-label="Comment"></textarea>
    <button class="jp-filter-btn" type="submit">Post Comment</button>
   </form>
  @else
   <a href="{{url('/login')}}">Comment লিখতে Student Login করুন</a>
  @endif
 </div>
 <div class="jp-markbar">
 @if($student)
  <form method="POST" action="{{route('student.jobprep.reading.save',['kind'=>$kind,'level'=>$level,'entity'=>$entity,'question'=>$question->id])}}">
   @csrf <label for="mark-{{$question->id}}">Question Reading:</label>
   <select class="jp-select" id="mark-{{$question->id}}" name="difficulty" onchange="this.form.submit()">
    <option value="clear">Not Marked</option>
    @foreach(['easy'=>'Easy','medium'=>'Medium','difficult'=>'Difficult'] as $key=>$val)
    <option value="{{$key}}" {{$marks->get($question->id)===$key?'selected':''}}>{{$val}}</option>
    @endforeach
   </select>
   <noscript><button type="submit">Save</button></noscript>
  </form>
 @else <a href="{{url('/login')}}">Login to mark reading</a> @endif
 </div>
</article>
