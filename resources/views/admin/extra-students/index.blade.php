@extends('dashboard')
@section('title', 'Extra Students Accounts')
@section('content')
<style>
.lebd-extra-list{padding:15px;min-height:75vh}.lebd-extra-list h1{font-size:24px;font-weight:400;margin:0 0 13px}.lebd-extra-list .lebd-list-card{background:#fff;border:1px solid #e1e5eb;border-top:3px solid #3c8dbc;border-radius:3px}.lebd-extra-list .lebd-list-head{padding:12px 13px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;border-bottom:1px solid #eee;color:#a00;font-size:16px}.lebd-extra-list .lebd-list-body{padding:13px}.lebd-extra-list .btn-extra{border:0;border-radius:3px;background:#00a65a;color:white;padding:8px 12px;text-decoration:none}.lebd-extra-list .table-responsive{overflow-x:auto}.lebd-extra-list table{width:100%;border-collapse:collapse}.lebd-extra-list th,.lebd-extra-list td{border:1px solid #eef0f2;padding:10px;text-align:left;font-size:13px}.lebd-extra-list th{background:#f8fafc}.lebd-extra-list .lebd-search{display:flex;gap:5px;flex-wrap:wrap;margin-bottom:12px}.lebd-extra-list input[type=search]{border:1px solid #ccd3dd;border-radius:3px;padding:7px 9px}.lebd-extra-list button{background:#3c8dbc;color:#fff;border:0;border-radius:3px;padding:7px 12px;cursor:pointer}.lebd-extra-list .department-buttons{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:9px;margin:0 0 16px}.lebd-extra-list .department-buttons a{display:block;text-align:center;padding:11px 7px;border:1px solid #cbdcf3;border-radius:5px;background:#f2f7fd;color:#08447c;text-decoration:none;font-weight:600}.lebd-extra-list .department-buttons a.active{background:#1464ac;border-color:#1464ac;color:#fff}.lebd-extra-list .department-buttons a:hover{background:#dbeafa}.lebd-extra-list .department-buttons a.active:hover{background:#115694}.lebd-extra-list .label-active{background:#00a65a;color:white;padding:3px 6px;border-radius:2px}
.lebd-extra-list .pickbar{display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:11px;background:#f0f6ff;border:1px solid #d7e5fa;border-radius:6px;margin-bottom:12px}.lebd-extra-list .pickbar label{display:inline-flex;gap:6px;align-items:center;margin:0;font-weight:650}.lebd-extra-list .pickbar .btn-shuffle{background:#7653b7}.lebd-extra-list .pickbar .pick-note{font-size:12px;color:#526b86}.lebd-extra-list .local-pages{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin:10px 0}.lebd-extra-list .local-pages button:disabled{opacity:.45;cursor:not-allowed}.lebd-extra-list .extra-edit{display:inline-block;padding:6px 10px;background:#ef9c00;color:#fff;border-radius:4px;text-decoration:none;font-weight:700;white-space:nowrap}.lebd-extra-list .extra-pick{width:16px;height:16px;cursor:pointer}
</style>
<div class="lebd-extra-list">
  <h1>Extra Students Accounts</h1>
  <div class="lebd-list-card">
    <div class="lebd-list-head"><span>List of Extra Students</span><a class="btn-extra" href="{{ route('admin.extra-students.create', $selectedDepartment ? ['department' => $selectedDepartment] : []) }}"><i class="fa fa-plus"></i> Add Student</a></div>
    <div class="lebd-list-body">
      @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
      <nav class="department-buttons" aria-label="Extra Student Departments">
        @foreach($departments as $key => $label)
          <a class="{{ $selectedDepartment === $key ? 'active' : '' }}" href="{{ route('admin.extra-students.index', ['department' => $key] + (request()->filled('exam_category') ? ['exam_category'=>request('exam_category')] : []) + (request()->filled('exam_subcategory') ? ['exam_subcategory'=>request('exam_subcategory')] : [])) }}">{{ $label }} <small>({{ $departmentCounts[$key] ?? 0 }})</small></a>
        @endforeach
      </nav>
      <h3 style="font-size:17px;margin:0 0 12px">{{ $selectedDepartment ? $departments[$selectedDepartment] . ' — Students' : 'Choose a Department to view students' }}</h3>
      <form class="lebd-search" method="GET" action="{{ route('admin.extra-students.index') }}">@if($selectedDepartment)<input type="hidden" name="department" value="{{ $selectedDepartment }}">@endif @if(request()->filled('exam_category'))<input type="hidden" name="exam_category" value="{{ request('exam_category') }}">@endif @if(request()->filled('exam_subcategory'))<input type="hidden" name="exam_subcategory" value="{{ request('exam_subcategory') }}">@endif<input type="search" name="search" value="{{ request('search') }}" placeholder="Search name, email, contact" aria-label="Search extra students"><button type="submit"><i class="fa fa-search"></i> Search</button></form>
      @if($selectedDepartment)
      <div class="local-pages lebd-page-top" id="lebd-extra-top-pages"><button type="button" class="lebd-prev-button">Previous 25</button><span class="lebd-page-indicator"></span><button type="button" class="lebd-next-button">Next 25</button></div>
      <div class="pickbar" id="lebd-extra-pickbar">
        <label><input type="checkbox" id="lebd-extra-select-all"> Select All (This Page)</label>
        <button type="button" class="btn-shuffle" id="lebd-extra-shuffle"><i class="fa fa-random"></i> Shuffle</button>
        <strong class="pick-note" id="lebd-extra-selection-count" aria-live="polite">0 selected across pages</strong><button type="button" id="lebd-extra-clear" style="background:#65758c">Clear Selection</button>
        <button type="button" id="lebd-extra-done" style="background:#0b9958;font-weight:700" disabled>✓ Done (0)</button>
      </div>
      <div class="table-responsive"><table id="lebd-extra-table"><thead><tr><th>Select</th><th>ID</th><th>Department</th><th>Name</th><th>Email</th><th>Contact</th><th>Status</th><th>Created On</th><th>Action</th></tr></thead><tbody>
      @forelse($students as $student)
      <tr class="lebd-extra-student-row"><td><input class="extra-pick" type="checkbox" value="{{ $student->id }}" aria-label="Select {{ $student->name }}"></td><td>{{ $student->shared_student_id ?? $student->id }}</td><td>{{ $departments[$student->department] ?? ucfirst($student->department) }}</td><td>{{ $student->name }}</td><td>{{ $student->email ?: '-' }}</td><td>{{ $student->contact }}</td><td><span class="{{ $student->status === 'Active' ? 'label-active' : '' }}">{{ $student->status }}</span></td><td>{{ $student->created_at }}</td><td><a class="extra-edit" href="{{ route('admin.extra-students.edit', ['student' => $student->id]) }}"><i class="fa fa-pencil"></i> Edit</a></td></tr>
      @empty<tr><td colspan="9" style="text-align:center">No extra students found.</td></tr>@endforelse
      </tbody></table></div>
      <div class="local-pages" id="lebd-extra-local-pages"><button type="button" class="lebd-prev-button">Previous 25</button><span class="lebd-page-indicator"></span><button type="button" class="lebd-next-button">Next 25</button></div>
      {{-- Server paginator retained; top and bottom controls link to its previous/next pages. --}}
      {{ $students->appends(request()->except('page'))->links() }}
      <p style="font-size:12px;color:#64748b;margin:10px 0 0">@if(request()->filled('exam_category'))Done চাপলে নির্বাচিত Active Student-দের সংশ্লিষ্ট Exam Section Category/Sub-category-তে Save করবে। @else এটি সাধারণ Student List; এখানে Done শুধু নির্বাচন সংখ্যা নিশ্চিত করবে। Exam Section থেকে Add Extra Student দিয়ে এলে Done-এর মাধ্যমে Save করা যাবে। @endif Shuffle করলে এই Page-এ লোড হওয়া ইতিমধ্যে নির্বাচিত Student আর দেখাবে না। সম্পূর্ণ Database থেকে নতুন Random Student আনতে Controller-এর Server-side Shuffle প্রয়োজন।</p>
      @else
      <div style="padding:22px;border:1px solid #d7e5fa;border-radius:6px;background:#f5f9ff;color:#315274">উপরে Civil, Electrical, Mechanical বা অন্য কোনো Department নির্বাচন করুন। Department নির্বাচন না করা পর্যন্ত Student List দেখানো হবে না।</div>
      @endif
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
 const table=document.getElementById('lebd-extra-table'); if(!table)return;
 const tbody=table.tBodies[0];
 const rows=Array.from(tbody.querySelectorAll('tr.lebd-extra-student-row'));
 const size=25;let page=0;let shuffleMode=false;
 const all=document.getElementById('lebd-extra-select-all');
 const counter=document.getElementById('lebd-extra-selection-count');
 const done=document.getElementById('lebd-extra-done');
 const clear=document.getElementById('lebd-extra-clear');
 const category=@json(request('exam_category'));
 const subcategory=@json(request('exam_subcategory'));
 const context=category && /^[1-9]\d*$/.test(String(category)) && (!subcategory || /^[1-9]\d*$/.test(String(subcategory)));
 const storeUrl=context ? @json(route('admin.exam-section.extra-students.store', ['category'=>'__CATEGORY__'])).replace('__CATEGORY__',encodeURIComponent(category)) : null;
 // Keep a separate selection per exam scope. Also persist when browsing department pages.
 const storageKey=context ? 'lebd-extra-picks:'+category+':'+(subcategory||'0') : 'lebd-extra-picks:standalone';
 let pickedIds=new Set();
 try { const v=JSON.parse(sessionStorage.getItem(storageKey)||'[]');if(Array.isArray(v))v.forEach(id=>{if(/^\d+$/.test(String(id)))pickedIds.add(String(id))});}catch(e){}
 function persist(){try{sessionStorage.setItem(storageKey,JSON.stringify([...pickedIds]))}catch(e){}}
 rows.forEach(row=>{const cb=row.querySelector('.extra-pick');cb.checked=pickedIds.has(cb.value)});
 const pageBars=[document.getElementById('lebd-extra-top-pages'),document.getElementById('lebd-extra-local-pages')].filter(Boolean);
 const serverPage=@json($students->currentPage());
 const serverLast=@json($students->lastPage());
 const serverNext=@json($students->appends(request()->except('page'))->nextPageUrl());
 const serverPrev=@json($students->appends(request()->except('page'))->previousPageUrl());
 const serverPerPage=@json($students->perPage());
 const serverMode=rows.length<=25;
 let displayRows=rows.slice();
 function currentRows(){return displayRows.slice(page*size,(page+1)*size)}
 function refresh(){
   // Shuffle only unselected students. Preserve selected IDs for Done and subsequent pages.
   displayRows=shuffleMode ? rows.filter(r=>!pickedIds.has(r.querySelector('.extra-pick').value)) : rows.slice();
   const totalPages=Math.max(1,Math.ceil(displayRows.length/size));page=Math.min(page,totalPages-1);
   const visible=new Set(currentRows());
   rows.forEach(row=>{const cb=row.querySelector('.extra-pick');cb.checked=pickedIds.has(cb.value);row.hidden=!visible.has(row)});
   const now=currentRows(),selectedHere=now.filter(row=>pickedIds.has(row.querySelector('.extra-pick').value)).length;
   all.checked=now.length>0&&selectedHere===now.length;
   all.indeterminate=selectedHere>0&&selectedHere<now.length;
   counter.textContent=pickedIds.size+' selected across pages'+(shuffleMode?' · selected hidden from Shuffle':'');
   done.textContent='✓ Done ('+pickedIds.size+')';done.disabled=pickedIds.size===0;
   pageBars.forEach(bar=>{
     const prev=bar.querySelector('.lebd-prev-button');
     const next=bar.querySelector('.lebd-next-button');
     const label=bar.querySelector('.lebd-page-indicator');
     label.textContent=serverMode ? ('Page '+serverPage+' of '+serverLast) : ('Page '+(page+1)+' of '+totalPages+' (loaded set)');
     prev.disabled=serverMode ? !serverPrev : page===0;
     next.disabled=serverMode ? !serverNext : page>=totalPages-1;
   });
 }
 all.addEventListener('change',()=>{currentRows().forEach(row=>{const cb=row.querySelector('.extra-pick');cb.checked=all.checked;if(cb.checked)pickedIds.add(cb.value);else pickedIds.delete(cb.value)});persist();refresh()});
 tbody.addEventListener('change',event=>{if(!event.target.matches('.extra-pick'))return;const cb=event.target;if(cb.checked)pickedIds.add(cb.value);else pickedIds.delete(cb.value);persist();refresh()});
 document.getElementById('lebd-extra-shuffle').addEventListener('click',()=>{
   shuffleMode=true;
   // Shuffle nonselected loaded rows; no duplicate of selected IDs is displayed.
   for(let i=rows.length-1;i>0;i--){const j=Math.floor(Math.random()*(i+1));[rows[i],rows[j]]=[rows[j],rows[i]];}
   rows.forEach(row=>tbody.appendChild(row));page=0;refresh();
 });
 clear.addEventListener('click',()=>{if(!confirm('Clear all selected students from this browser session?'))return;pickedIds.clear();persist();shuffleMode=false;page=0;refresh()});
 done.addEventListener('click',()=>{
   const ids=[...pickedIds];if(!ids.length)return;
   if(!context){alert('Selected: '+ids.length+'. Exam Section-এ Save করতে সংশ্লিষ্ট Category/Sub-category-এর Extra Student Button থেকে খুলুন।');return;}
   if(ids.length>200){alert('একবারে সর্বোচ্চ ২০০ জন Save করা যাবে। সংখ্যা কমিয়ে Done চাপুন।');return;}
   const form=document.createElement('form');form.method='POST';form.action=storeUrl;
   function field(name,value){const input=document.createElement('input');input.type='hidden';input.name=name;input.value=value;form.appendChild(input)}
   field('_token',@json(csrf_token()));if(subcategory)field('subcategory_id',subcategory);
   ids.forEach(id=>field('student_ids[]',id));document.body.appendChild(form);form.submit();
 });
 pageBars.forEach(bar=>{
   bar.querySelector('.lebd-prev-button').addEventListener('click',()=>{
     if(serverMode){if(serverPrev)window.location.assign(serverPrev)}else if(page>0){page--;refresh();}
   });
   bar.querySelector('.lebd-next-button').addEventListener('click',()=>{
     if(serverMode){if(serverNext)window.location.assign(serverNext)}else if((page+1)*size<displayRows.length){page++;refresh();}
   });
 });
 refresh();
});
</script>
@endsection
