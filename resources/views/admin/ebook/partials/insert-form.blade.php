<details class="eb15-insert" @if(old('position_after')!==null && (int)old('position_after')===(int)$insertAfter) open @endif>
 <summary>＋ {{ $insertLabel }}</summary>
 <form method="POST" action="{{route('admin.ebook.blocks.store',['kind'=>$kind,'chapter'=>$ch->id])}}">@csrf
 <input type="hidden" name="position_after" value="{{$insertAfter}}">
 <label>Content Type <select name="block_type" class="eb15-type">
  <option value="heading">Heading / উপশিরোনাম</option><option value="text">Short Description / Note</option><option value="custom_question">New Chapter-only MCQ</option>
 </select></label>
 <div class="eb15-titlefield"><label>Heading / Question Title<input name="title" placeholder="নতুন Heading লিখুন"></label></div>
 <div class="eb15-textfield" hidden><label>Short Description<textarea name="content" rows="4" placeholder="যেকোনো ব্যাখ্যা / নোট লিখুন"></textarea></label></div>
 <div class="eb15-mcqfields" hidden>
  <div class="eb15-edit-options">@foreach(['ক','খ','গ','ঘ'] as $letter)<label>{{$letter}})<input name="options[]" placeholder="অপশন {{$letter}}"></label>@endforeach</div>
  <label>সঠিক উত্তর <select name="answer"><option value="">Select Answer</option>@foreach(['ক','খ','গ','ঘ'] as $letter)<option value="{{$letter}}">{{$letter}}</option>@endforeach</select></label>
  <label>ব্যাখ্যা<textarea name="explanation" rows="3"></textarea></label>
 </div>
 <button class="eb15-btn" type="submit">＋ এখানে যোগ করুন</button>
 <a class="eb15-btn eb15-light" href="{{route('admin.ebook.picker',['kind'=>$kind,'chapter'=>$ch->id])}}">Main DB Question Picker</a>
 <p class="eb15-note">নতুন Chapter-only MCQ এখানে সংরক্ষিত হবে। Main DB থেকে একাধিক Question যোগ করতে উপরের Picker ব্যবহার করুন।</p>
 </form>
</details>
