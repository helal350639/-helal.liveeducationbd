@php

use \App\Http\Controllers\SourceCtrl;

$source = New SourceCtrl;

$correct = '';

$format = ['a)', 'b)', 'c)', 'd)', 'e)'];

$url = route('paper.addtopaper');

if(Session::get('_paper') && Session::get('_paper')->type == 'syllabus')

{

  $url = route('syllabus.addtopaper');

}

else if(Session::get('_paper') && Session::get('_paper')->type == 'book'){

  $url = route('book.addtopaper');

}

  $book_chapters = $session = [];

  if(!is_null(Session::get('_paper')))

  {

    $session = Session::get('_paper');

    $book_chapters = $session->chapters;

  }

  else

  {

    $book_chapters = $chapters;

  }

@endphp

@extends('dashboard')

@section('title', 'View Questions')

@section('content')

<style>

  .scroll ::-webkit-scrollbar{width: 5px;}

  .scroll ::-webkit-scrollbar-thumb{background-color: #ddd}

  ul.list li{padding: 10px 0; list-style: none;}

  .hide{display: none}

  .filter{padding-left: 10px;}

  .filter .item{list-style: none;font-weight: bold;padding-bottom: 25px}

  .filter li:last-child{padding:0}

  .filter .item a{display: inline-block;font-size: 15px;color: #000}

  .filter .item .drop-down{position: absolute; right: 20px}

  .filter-group{clear:top;padding-left: 10px;}

  .filter-group .item{padding-bottom: 10px}

  .filter-group .item a{border:1px solid #ddd; padding: 5px;display: block;font-weight: normal}

  .loading{position:fixed; top:0;bottom:0;left:0;right:0; background:rgba(0,0,0,0.2); text-align: center; padding-top:15%}

  .panel-heading{background:none}

  .paper_panel{padding: 15px}

  .paper_panel label{border:1px solid #ddd; display: block;padding:10px; margin-bottom:0}

  @media screen and (min-width:480px) {

    .filter-parent{max-width:400px; max-height: 600px; overflow:auto;}

    .sticky{position: fixed;top:10px;}

  }

  @media screen and (max-width:481px){

    .filter-parent{width:100%}

    .sticky{position: relative;}

  }

  .iframe-container {

  position: relative;

  overflow: hidden;

  width: 100%;

  padding-top: 56.25%;

  margin: 15px 0

}

.iframe-container iframe {

  position: absolute;

  top: 0;

  left: 0;

  width: 100%;

  height: 100%;

}

  /* Fix48: show the complete 11-stage sidebar without clipping. */
  #filter-parent.filter-parent { max-height:none !important; overflow:visible !important; width:100%; }
  #filter-parent.filter-parent.sticky { position:relative !important; top:auto !important; }
  #filter-parent .panel-body { overflow:visible; }
  .lebd-view-fresh-filters { padding-bottom:10px; }
  .lebd-view-fresh-filters .lebd-option-search { padding:7px 4px 9px; }
  .lebd-view-fresh-filters .lebd-option-search input { width:100%; height:33px; font-size:12px; }
  .lebd-view-fresh-filters .lebd-option-search-hint { color:#777;font-size:11px;margin-top:3px;display:block; }
  /* Fix47: compact searchable filters; keep the existing view/AJAX/exports. */
  .lebd-view-fresh-filters .lebd-stage{padding-bottom:5px;border-bottom:1px solid #eee;}
  .lebd-view-fresh-filters .lebd-stage>p{position:relative;margin:0;padding:10px 27px 10px 3px;}
  .lebd-view-fresh-filters .lebd-stage>p>a{font-size:13px;font-weight:600;cursor:pointer;}
  .lebd-view-fresh-filters .lebd-stage>p>.drop-down{right:8px;top:11px;cursor:pointer;}
  .lebd-view-fresh-filters .filter-group{padding:0 4px 7px 4px;margin:0;list-style:none;}
  .lebd-view-fresh-filters .filter-group .item{padding:2px 0;}
  .lebd-view-fresh-filters .lebd-view-option{display:flex;align-items:center;justify-content:space-between;gap:8px;font-weight:400;font-size:13px;margin:0;padding:7px;border:1px solid #eee;border-radius:3px;cursor:pointer;}
  .lebd-view-fresh-filters .lebd-view-option input{margin:0;flex-shrink:0;}
  .lebd-view-fresh-filters .lebd-view-option:has(input:checked){background:#e6f6fd;border-color:#3c8dbc;}
</style>

<!-- Content Header (Page header) -->

<section class="content-header">

  <h1>View Questions</h1>

  <ol class="breadcrumb">

    <li><a href="#"><i class="fa fa-dashboard"></i>Dashboard</a></li>

    {{-- <li><a href="#">Tables</a></li> --}}

    <li class="active">View Questions</li>

  </ol>

</section>

<!-- Main content -->

<section class="content">

  <div class="row">

    <div class="col-xs-12">

      <div class="box">

        <div class="box-header">

          <h3 class="box-title">Categories</h3>

          <div class="box-tools">

            <a href="{{route('question.create')}}" class="btn btn-info">

              <i class="fa fa-plus"></i> Add Question

            </a>

            {{-- <div class="input-group input-group-sm" style="float:right; width: 150px;margin-left:15px">

              <input type="text" name="table_search" class="form-control pull-right" placeholder="Search">

              <div class="input-group-btn">

                <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>

              </div>

            </div> --}}

          </div>

        </div>

        <div class="box-body">

          <div class="col-md-12">

            <form action="{{route('question.view.post')}}" method="POST" id="filter_form" onsubmit="event.preventDefault(); ajaxFilter()">

              @csrf

              <div class="row">

                <div class="col-md-2">

                  <div class="form-group">

                    <label for="">Course</label>

                    <select name="course_id" id="course_id" class="form-control select2" onchange="getDepartments(this); getBatch(this)">

                      <option value="">Select One</option>

                      @foreach($courses as $value)

                      <option value="{{$value->id}}" {{($cat['course_id'] ?? null) == $value->id ? 'selected':''}}>{{$value->name}}</option>

                      @endforeach

                    </select>

                  </div>

                </div>

                <div class="col-md-2">

                  <div class="form-group">

                    <label for="">Department</label>

                    <select name="department_id" id="department_id" class="form-control select2" onchange="getSemesters(this); getSubjects(this);">

                      <option value="">Select One</option>

                      @foreach($departments as $value)

                      <option value="{{$value->id}}" {{($cat['department_id'] ?? null) == $value->id ? 'selected':''}}>{{$value->name}}</option>

                      @endforeach

                    </select>

                  </div>

                </div>

                <div class="col-md-2" id="semester">

                  <div class="form-group">

                    <label for="">Semester</label>

                    <select name="semester_id" id="semester_id" class="form-control select2" onchange="getSubjects(this)">

                      <option value="">Select One</option>

                      @foreach($semesters as $value)

                      <option value="{{$value->id}}" {{($cat['semester_id'] ?? null) == $value->id ? 'selected':''}}>{{$value->name}}</option>

                      @endforeach

                    </select>

                  </div>

                </div>

                <div class="col-md-2">

                  <div class="form-group">

                    <label for="">Subjects</label>

                    <select name="subject_id" id="subject_id" class="form-control select2" onchange="{{$session && $session->type == 'book'? '':'getChapters(this)'}}">

                      <option value="">Select One</option>

                      @foreach($subjects as $value)

                      <option value="{{$value->id}}" {{($cat['subject_id'] ?? null) == $value->id ? 'selected':''}}>{{$value->name}}</option>

                      @endforeach

                    </select>

                  </div>

                </div>

                <div class="col-md-2">

                  <div class="form-group">

                    <label for="">Chapter</label>

                    <select name="chapter_id" id="chapter_id" class="form-control select2">

                      <option value="">Select One</option>

                      @if($book_chapters)

                        @foreach($book_chapters as $value)

                        <option value="{{$value->id}}" {{($cat['chapter_id'] ?? null) == $value->id ? 'selected':''}}>{{$value->name}}</option>

                        @endforeach

                      @endif

                    </select>

                  </div>

                </div>

                <div class="col-md-2">

                  <div class="form-group">

                    <label for="">Type</label>

                    <select name="type" id="" class="form-control">

                      {{-- <option value="">Select One</option> --}}

                      <option value="MCQ" {{($cat['type'] ?? null) == 'MCQ' ? 'selected' : '' }}>MCQ</option>

                      <option value="Written" {{($cat['type'] ?? null) == 'Written' ? 'selected' : '' }}>Written</option>

                      <option value="Video" {{($cat['type'] ?? null) == 'Video' ? 'selected' : '' }}>Video</option>

                    </select>

                  </div>

                </div>

                <div class="col-md-2">

                  <div class="form-group">

                    <label>Created By</label>

                    <select name="created_by" id="created_by" class="form-control select2">

                      <option value="">Select One</option>

                      @foreach($users as $value)

                      <option value="{{$value->id}}" {{($cat['created_by'] ?? null) == $value->id ? 'selected':''}}>{{$value->name}}</option>

                      @endforeach

                    </select>

                  </div>

                </div>

                <div class="col-md-2">

                  <div class="form-group">

                    <label for="">Start Date</label>

                    <input type="date" name="start" id="start" class="form-control">

                  </div>

                </div>

                <div class="col-md-2">

                  <div class="form-group">

                    <label for="">End Date</label>

                    <input type="date" name="end" id="end" class="form-control">

                  </div>

                </div>

                <div class="col-md-2">

                  <div class="form-group">

                    <label for="">Question Title</label>

                    <input name="title" id="title" class="form-control" placeholder="Search by question's title">

                  </div>

                </div>

                <div class="col-md-2">

                  <div class="form-group">

                    <label for="">Select Batch</label>

                    <select name="batch" id="batch" class="form-control" onchange="getSyllabus(this)">

                      <option value="">Select One</option>

                    </select>

                  </div>

                </div>

                <div class="col-md-2">

                  <div class="form-group">

                    <label for="">Select Syllabus</label>

                    <select name="syllabus" id="syllabus" class="form-control">

                      <option value="">Select One</option>

                    </select>

                  </div>

                </div>

                <div class="col-md-2">

                  <div class="form-group">

                    <br>

                    <input class="btn btn-primary btn-block" type="submit" value="Submit">

                  </div>

                </div>

              </div>

            </form>

          </div>

        </div>

      </div>

      <div class="col-md-4 scroll" style="padding-left: 0">

        <div class="box filter-parent" id="filter-parent">

          @if(!is_null(Session::get('_paper')))

            @php

            $paper = Session::get('_paper');

            @endphp

          <div class="box-header">

            <div class="paper_panel">

              <div class="form-group">

                <div class="input-group">

                  <label class="label-default">{{$paper->name}}</label>

                  <span class="input-group-addon" style="font-weight: bold">

                    <span id="qcount">{{count($paper->questions)}}</span>/

                    <span id="maxq">{{$paper->max}}</span>

                  </span>

                </div>

              </div>

              <div class="form-group">

                @if($paper->type == 'syllabus')

                <a class="btn btn-info pull-right" href="{{route('syllabus.view', $paper->id)}}">

                  <i class="fa fa-check"></i> Done

                </a>

                @elseif($paper->type == 'book')

                <a class="btn btn-info pull-right" href="{{route('book.view', $paper->id)}}">

                  <i class="fa fa-check"></i> Done

                </a>

                @else

                <a class="btn btn-info pull-right" href="{{route('paper.view', $paper->id)}}">

                  <i class="fa fa-check"></i> Done

                </a>

                @endif

              </div>

            </div>

          </div>

          @endif

          <div class="box-body">

            <div class="panel" style="margin-bottom:0;box-shadow:none">

              <div class="panel-heading" style="border-bottom:1px solid #ddd; border-top:1px solid #ddd">

                <h4> <i class="fa fa-sliders"> </i> Filter</h4>

              </div>

              <div class="panel-body">

                {{-- LEBD-04 Fix47: match the 11 fresh filter stages on Add Question. --}}
                @php
                  $lebdStageNames = [
                    'প্রশ্ন টাইপ', 'কোর্সের নাম', 'প্রশ্ন টপিক', 'ডিপার্টমেন্ট নাম',
                    'বুক প্রকাশনির নাম', 'সাবজেক্ট নাম', 'চ্যাপ্টার নাম',
                    'প্রশ্ন সোর্স', 'সাব-সোর্স', 'পরীক্ষার সন', 'Question Quality filter'
                  ];
                  $lebdMainStages = \App\Models\Filter::query()
                    ->where('status', 'Active')->orderBy('order_number')->orderBy('id')
                    ->limit(11)->get(['id', 'name']);
                  $lebdBaselineCutoff = \Illuminate\Support\Facades\Schema::hasTable('lebd_filter_visibility_baselines')
                    ? (int) (\Illuminate\Support\Facades\DB::table('lebd_filter_visibility_baselines')
                        ->where('id', 1)->value('legacy_max_sub_filter_id') ?? 18)
                    : 18;
                  $lebdStageOptions = \App\Models\SubFilter::query()
                    ->where('status', 'Active')->where('id', '>', $lebdBaselineCutoff)
                    ->whereIn('filter_id', $lebdMainStages->pluck('id')->all())
                    ->orderBy('id')->get(['id', 'filter_id', 'name', 'parent_id'])->groupBy('filter_id');
                  $lebdConnectionMap = [];
                  if (\Illuminate\Support\Facades\Schema::hasTable('sub_filter_connections')) {
                    foreach (\Illuminate\Support\Facades\DB::table('sub_filter_connections')->whereIn('child_sub_filter_id', $lebdStageOptions->flatten()->pluck('id')->all())->get(['child_sub_filter_id', 'parent_sub_filter_id']) as $lebdLink) {
                      $lebdConnectionMap[(int)$lebdLink->child_sub_filter_id][] = (int)$lebdLink->parent_sub_filter_id;
                    }
                  }
                @endphp
                <div class="lebd-fresh-search" style="margin-bottom:12px;">
                  <input id="lebd_sidebar_search" type="search" class="form-control" placeholder="Filter / Option খুঁজুন..." aria-label="Search filters" oninput="lebdSearchViewFilters(this.value)">
                  <small class="text-muted">Add Question-এর ১১টি নতুন Filter</small>
                </div>
                <ul class="filter lebd-view-fresh-filters" id="lebd_view_filter_list">
                  @foreach($lebdStageNames as $lebdIndex => $lebdStageLabel)
                    @php
                      $lebdStage = $lebdMainStages->get($lebdIndex);
                      $lebdStageItems = $lebdStage ? $lebdStageOptions->get($lebdStage->id, collect()) : collect();
                    @endphp
                    <li class="item lebd-stage" data-stage-index="{{ $lebdIndex }}" data-stage="{{ $lebdStageLabel }}">
                      <p>
                        <a role="button" tabindex="0" onclick="showHideSubFilter(this.nextElementSibling)" onkeydown="if(event.key==='Enter'){showHideSubFilter(this.nextElementSibling)}">{{ $lebdStageLabel }} <small class="text-muted">({{ $lebdStageItems->count() }})</small></a>
                        <span class="drop-down" role="button" tabindex="0" aria-label="Toggle {{ $lebdStageLabel }}" onclick="showHideSubFilter(this)" onkeydown="if(event.key==='Enter'){showHideSubFilter(this)}">
                          <i class="fa fa-chevron-down"></i>
                        </span>
                      </p>
                      <ul class="filter-group hide" id="lebd-stage-{{ $lebdIndex }}">
                        @if(in_array($lebdIndex, [7, 8, 9], true))
                          <li class="lebd-option-search">
                            <input type="search" class="form-control lebd-stage-search-input" placeholder="{{ $lebdStageLabel }} খুঁজুন..." aria-label="{{ $lebdStageLabel }} Search" oninput="lebdSearchStageOptions(this)" onclick="event.stopPropagation()">
                            <small class="lebd-option-search-hint">নাম লিখে Option খুঁজুন; Tick করলে Filter হবে।</small>
                          </li>
                        @endif
                        @forelse($lebdStageItems as $lebdOption)
                          @php
                            $lebdParents = $lebdConnectionMap[(int)$lebdOption->id] ?? [];
                            if ($lebdOption->parent_id && !in_array((int)$lebdOption->parent_id, $lebdParents, true)) $lebdParents[] = (int)$lebdOption->parent_id;
                          @endphp
                          <li class="item lebd-stage-option" data-option="{{ $lebdOption->name }}" data-parents="{{ implode(',', $lebdParents) }}">
                            <label class="lebd-view-option"><span>{{ $lebdOption->name }}</span>
                              <input type="checkbox" name="sub_filter" value="{{ $lebdOption->id }}" onchange="lebdCascadeChange(this)" data-id="{{ $lebdOption->id }}">
                            </label>
                          </li>
                        @empty
                          <li class="text-muted" style="padding:8px;">এখন কোনো নতুন Option নেই</li>
                        @endforelse
                      </ul>
                    </li>
                  @endforeach
                </ul>

              </div>

            </div>

          </div><!-- /.box body -->

        </div><!--/.box for fitler -->

      </div>

      <div class="col-md-8 no-padding">

        {{-- LEBD-04 Fix45: Export current matching questions, independent from pagination. --}}
        <div class="box" style="margin-bottom:12px;border-top:3px solid #3c8dbc;">
          <div class="box-body">
            <div class="row" style="display:flex;flex-wrap:wrap;align-items:end;gap:8px;">
              <div style="min-width:175px;flex:1;max-width:260px;">
                <label for="lebd_export_limit" style="display:block;">Download how many?</label>
                <select id="lebd_export_limit" class="form-control">
                  <option value="25">25 Questions</option>
                  <option value="all">All Results (all filtered questions)</option>
                </select>
              </div>
              <button type="button" id="lebd_download_csv" class="btn btn-success"><i class="fa fa-file-excel-o"></i> Download CSV</button>
              <button type="button" id="lebd_download_word" class="btn btn-primary"><i class="fa fa-file-word-o"></i> Download Word</button>
            </div>
            <small class="text-muted" style="display:block;margin-top:7px;">Exports use the selected filters, not just the visible pagination page. Word includes questions, options, correct answers and explanations.</small>
          </div>
        </div>
        <div class="box" id="question_area">

            @include('layouts.questions.paginate')

        </div><!-- /.box -->

      </div>

      <div id="loading" class="loading hide">

        <img src="/img/loading.gif" alt="">

      </div>

      {{-- <div class="alert hi/de" style="background:#fff;right:0">///</div> --}}

<script>

  // assign loading variable for every where use

  const loading = document.getElementById('loading');

  let maxq = document.getElementById('maxq');

  let qcount = document.getElementById('qcount');

  let counter = 0;

  //checked all if max item is checked

  function checkAllchecked()

  {

    let checked = 0;

    let unchecked = 0;

    if(qcount)

    {

      counter = Number(qcount.innerHTML);

    }

    let allQ = document.querySelectorAll('.check');

    allQ.forEach(e => {

      if(e.checked == true)

      {

        checked ++;

      }

      else

      {

        unchecked ++;

      }

    });

    let checkAll = document.getElementById('checkAll');

    // maxq && Number(counter) >= Number(maxq.innerHTML) || 

    if(checkAll) {
      checkAll.checked = allQ.length > 0 && unchecked === 0;
      checkAll.indeterminate = checked > 0 && unchecked > 0;
    }

  }

  function getBatch(e)

  {

    const batch = document.getElementById('batch');

    $.ajax({

      type: 'GET',

      url: '{{route("batch.course-batch", "")}}',

      data: {course: e.options[e.selectedIndex].value},

      success: function(data){

        // console.log(data);

        let option = '<option value="">Select One</option>';

        data.batches.forEach((b) => {

          option += '<option value="'+b.id+'">'+b.name+'</option>';

        });

        batch.innerHTML = option;

      },

      error: function(data){

        console.error(data);

      }

    });

  }

  function getDepartments(e)

  {

    if(e.options[e.selectedIndex].value != '')

    {

      loading.classList.remove('hide');

      let ids = Array.from(e.selectedOptions).map(({value}) => value);

      $.ajax({

          type: 'GET', //THIS NEEDS TO BE GET

          url: '/get_departments/' + ids,

          success: function (data) {

            var obj = JSON.parse(JSON.stringify(data));

            var options = '<option value="">Select One</option>';

            $.each(obj['data'], function (key, val) {

              options += '<option value="'+val.id+'">'+val.name+'</option>';

            });

            if(options != ""){

              $("#department_id").html(options)

            }else{

              $("#department_id").html('')

              $("#semester_id").html('')

              $("#subject_id").html('')

              $("#chapter_id").html('')

            }

            //hide loading image

            loading.classList.add('hide');

          },

          error: function(data) {

            console.log('data error');

          loading.classList.add('hide');

          }

      });

    }

  }

  function getSemesters(e)

  {

    if(e.options[e.selectedIndex].value != '')

    {

      loading.classList.remove('hide');

      let ids = Array.from(e.selectedOptions).map(({value}) => value);

      let department = document.getElementById('department_id');

      let preElm = department.parentNode.parentNode;

      $.ajax({

          type: 'GET', //THIS NEEDS TO BE GET

          url: '/get_semesters/' + ids,

          success: function (data) {

              var obj = JSON.parse(JSON.stringify(data));

              var options = '<option value="">Select One</option>';

              $.each(obj['data'], function (key, val) {

                  options += '<option value="'+val.id+'">'+val.name+'</option>';

              });

              if(options != "")

              {

                  if(preElm.nextElementSibling.id == 'semester')

                  {

                      $("#semester_id").html(options)

                  }

                  else

                  {

                      let elm = document.createElement('div');

                      elm.setAttribute('class', 'col-md-3');

                      elm.setAttribute('id', 'semester');

                      elm.innerHTML = '<div class="form-group">'+

                              '<label for="name">Semester Name</label>'+

                              '<select class="form-control select2" multiple name="semester_id[]" id="semester_id" onchange="getSemsSubjects(this)">'+ options +

                                  '</select>'+

                              '</div>';

                      preElm.after(elm);

                  }

                  // select2 execution

                  $(function(){ $('.select2').select2(); });

              }else{

                  $("#semester_id").html('')

                  $("#subject_id").html('')

                  $("#chapter_id").html('')

                  if(preElm.nextElementSibling.id == 'semester')

                  {

                      preElm.nextElementSibling.remove()

                  }

              }

          //hide loading image

          loading.classList.add('hide');

          },

          error: function(data) { 

                  console.log('data error');

          loading.classList.add('hide');

          }

      });

    }

  }

  function getSubjects(e)

  {

    if(e.options[e.selectedIndex].value != '')

    {

      //loading image view

      loading.classList.remove('hide');

      let ids = Array.from(e.selectedOptions).map(({value}) => value);

      $.ajax({

          type: 'GET', //THIS NEEDS TO BE GET

          url: '/get_subjects/' + ids,

          success: function (data) {

              var obj = JSON.parse(JSON.stringify(data));

              var options = '<option value="">Select One</option>';

              $.each(obj['data'], function (key, val) {

                  options += '<option value="'+val.id+'">'+val.name+'</option>';

              });

              if(options != ""){

                  $("#subject_id").html(options)

              }else{

                  $("#subject_id").html('')

                  $("#chapter_id").html('')

              }

            //hide loading image

            loading.classList.add('hide');

          },

          error: function(data) { 

                  console.log('data error');

          loading.classList.add('hide');

          }

      });

    }

  }

  function getSemsSubjects(e)

  {

    //loading image view

    loading.classList.remove('hide');

      let ids = Array.from(e.selectedOptions).map(({value}) => value);

      $.ajax({

          type: 'GET', //THIS NEEDS TO BE GET

          url: '/get_sems_subjects/' + ids,

          success: function (data) {

              var obj = JSON.parse(JSON.stringify(data));

              var options = '<option value="">Select One</option>';

              $.each(obj['data'], function (key, val) {

                  options += '<option value="'+val.id+'">'+val.name+'</option>';

              });

              if(options != ""){

                  $("#subject_id").html(options)

              }else{

                  $("#subject_id").html('')

                  $("#chapter_id").html('')

              }

          //hide loading image

          loading.classList.add('hide');

          },

          error: function(data) { 

                  console.log('data error');

          loading.classList.add('hide');

          }

      });

  }

  function getChapters(e)

  {

    if(e.options[e.selectedIndex].value != '')

    {

    //loading image view

    loading.classList.remove('hide');

      let ids = Array.from(e.selectedOptions).map(({value}) => value);

      $.ajax({

          type: 'GET', //THIS NEEDS TO BE GET

          url: '/get_chapters/' + ids,

          success: function (data) {

              var obj = JSON.parse(JSON.stringify(data));

              var options = '<option value="">Select One</option>';

              $.each(obj['data'], function (key, val) {

                  options += '<option value="'+val.id+'">'+val.name+'</option>';

              });

              if(options != ""){

                  $("#chapter_id").html(options)

              }else{

                  $("#chapter_id").html('')

              }

          //hide loading image

          loading.classList.add('hide');

          },

          error: function(data) { 

            console.log('data error');

          loading.classList.add('hide');

          }

      });

    }

  }

  function showHideAnswer(e)

  {

    let item = e.parentNode.nextElementSibling;

    item.classList.toggle('hide');

  }

  // Fix49: the exact parent links used by Add Question (including connection table)
  // determine whether a later-stage option belongs to the selected hierarchy.
  // Question Quality (last stage) is independent of this hierarchy.
  function lebdCascadeRefresh() {
    const stages = Array.from(document.querySelectorAll('#lebd_view_filter_list .lebd-stage'));
    const selectedAncestors = [];
    let previousStageHasSelection = true; // Stage 1 is always available.

    stages.forEach(function(stage, index) {
      const quality = index === 10; // Independent difficulty/quality stage.
      const eligible = index === 0 || previousStageHasSelection;
      const options = Array.from(stage.querySelectorAll('.lebd-stage-option'));
      const currentSelected = [];

      options.forEach(function(option) {
        const cb = option.querySelector('input[type="checkbox"]');
        const parents = (option.getAttribute('data-parents') || '').split(',').filter(Boolean);
        // Every step requires a checked option in the preceding step.
        // Every earlier chosen ancestor must also belong to the option's saved connections.
        const allowed = quality || (eligible && (index === 0 ||
          selectedAncestors.every(function(id) { return parents.includes(id); })));
        option.dataset.allowed = allowed ? '1' : '0';
        if (!allowed && cb.checked) cb.checked = false;
        if (allowed && cb.checked && !quality) currentSelected.push(String(cb.value));
      });

      if (!quality) {
        previousStageHasSelection = currentSelected.length > 0;
        currentSelected.forEach(function(id) { selectedAncestors.push(id); });
      }
    });

    lebdSearchViewFilters(document.getElementById('lebd_sidebar_search')?.value || '');
  }

  // Fix51: a chosen option closes only its own stage; search inputs remain intact.
  // Source, Sub-source and Exam Year retain their dedicated search fields.
  function lebdCascadeChange(input) {
    lebdCascadeRefresh();
    if (input && input.checked) {
      const stage = input.closest('.lebd-stage');
      if (stage) {
        const group = stage.querySelector('.filter-group');
        if (group) group.classList.add('hide');
        const arrow = stage.querySelector('p .drop-down i');
        if (arrow) {
          arrow.classList.remove('fa-chevron-up');
          arrow.classList.add('fa-chevron-down');
        }
      }
    }
    ajaxFilter(input);
  }

  function lebdSearchStageOptions(input) {
    lebdSearchViewFilters(document.getElementById('lebd_sidebar_search')?.value || '');
  }

  function lebdSearchViewFilters(value) {
    const query = String(value || '').trim().toLocaleLowerCase();
    document.querySelectorAll('#lebd_view_filter_list .lebd-stage').forEach(function(stage) {
      const nameMatches = (stage.getAttribute('data-stage') || '').toLocaleLowerCase().includes(query);
      const localInput = stage.querySelector('.lebd-stage-search-input');
      const localQuery = localInput ? localInput.value.trim().toLocaleLowerCase() : '';
      let anyMatch = false;
      stage.querySelectorAll('.lebd-stage-option').forEach(function(option) {
        const name = (option.getAttribute('data-option') || '').toLocaleLowerCase();
        const matches = option.dataset.allowed !== '0' &&
          (!query || nameMatches || name.includes(query)) &&
          (!localQuery || name.includes(localQuery));
        option.style.display = matches ? '' : 'none';
        if (matches) anyMatch = true;
      });
      stage.style.display = (!query || nameMatches || anyMatch) ? '' : 'none';
      const count = stage.querySelector('p small');
      if (count) count.textContent = '(' + Array.from(stage.querySelectorAll('.lebd-stage-option')).filter(function(o){return o.dataset.allowed !== '0';}).length + ')';
      const group = stage.querySelector('.filter-group');
      if (group && query && (nameMatches || anyMatch)) group.classList.remove('hide');
    });
  }
  document.addEventListener('DOMContentLoaded', lebdCascadeRefresh);

  function showHideSubFilter(e)

  {

    let sub = e.parentNode.nextElementSibling;

    sub.classList.toggle('hide');

    e.firstElementChild.classList.toggle('fa-chevron-down');

    e.firstElementChild.classList.toggle('fa-chevron-up');

  }

  //make full width the layout

  document.body.classList.add('sidebar-collapse');

  //globaly declare the question area variable

  let question_area = document.getElementById('question_area');

  //globaly declare the filter form

  const form = document.getElementById('filter_form');

  //assign sub-filters for every where use

  let filters = document.getElementsByName('sub_filter');

  let lebdFilterRequest = null;

  let lebdPageRequest = null;

  function ajaxFilter(elm)

  {

    //loading image view

    if (lebdFilterRequest) lebdFilterRequest.abort();

      if (lebdPageRequest) lebdPageRequest.abort();

      loading.classList.remove('hide');

    let ids = [];

    filters.forEach(e => {

      if(e.checked == true){

        ids.push(e.value);  

      }  

    });

    // console.log(ids);

    const formData = new FormData(form);

    formData.append('filter_ids', ids);

    lebdFilterRequest = $.ajax({

      url: '{{route("question.view.post")}}',

      type: 'POST',

      data:formData,

      cache: false,

      contentType: false,

      processData: false,

      success:function(data)

      {

        question_area.innerHTML = data;

        loading.classList.add('hide');

        mathImgConverter();

        prevNext();

        checkAllchecked();

      },

      error:function(data)

      {

        console.error('Question request failed', data);

        loading.classList.add('hide');

      }

    });

    const subsource = document.getElementById('sub-source');

    if(elm && elm.dataset && elm.dataset.id && elm.checked && subsource)

    {

      $.ajax({

      type: 'GET',

      url: '{{route("subfilter.subsource", "")}}/'+elm.dataset.id,

      success: function(data){

        let options = '';

        data.subsource.forEach(s => 

        {

          options += '<li class="item">'+

                        '<div class="input-group">'+

                          '<a>'+s.name+'</a>'+

                          '<span class="input-group-addon">'+

                            '<input type="checkbox" name="sub_filter" value="'+s.id+'" onchange="ajaxFilter(this)">'+

                          '</span>'+

                        '</div>'+

                      '</li>';

        });

        if(options)

        {

          subsource.innerHTML = options;

        }

        else

        {

          subsource.innerHTML = 'Sub Source Not Found!';

        }

      },

      error: function(data){

        console.error(data);

      }

    });

    }

  }

  window.onscroll = function(){

    let filterParent = document.getElementById('filter-parent');

    if(document.body.scrollTop >= 500 || document.documentElement.scrollTop >= 500)

    {

      filterParent.classList.add('sticky');

    }

    else

    {

      filterParent.classList.remove('sticky');

    }

  }

  // delete question by ajax request

  function del(e)

  {

    let item = e.parentNode.parentNode.parentNode.parentNode;

    console.log(item);

    $.ajaxSetup({

      headers: {

        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

      }

    });

    $.ajax({

      url: '{{route("question.destroy", '')}}/'+e.value,

      type:'POST',

      data:{_method:'DELETE'},

      beforeSend:function(){

        return confirm('Are you sure, you want to delete this?');

      },

      success:function(data){

        //

      },

      success:function(data){}

    });

    //remove item from page

    item.remove();

  }

  function count(e)

  {

    let maxq = document.getElementById('maxq');

    //question id/ids

    let ids = e.value;

    if(maxq)

    {

      if(e.checked == true)

      {

        if(Number(maxq.innerHTML) != 0 && Number(qcount.innerHTML) >= Number(maxq.innerHTML))

        {

          alert('Maximum Question Entry Limit Exceeded!');

          e.checked = false;

        }

        else

        {

          // qcount.innerHTML = Number(qcount.innerHTML) + 1;

          counter ++;

          qcount.innerHTML = counter;

          addToPaper(ids, 'add');

        }

      }

      else

      {

        // qcount.innerHTML = Number(qcount.innerHTML) - 1;

        counter --;

        qcount.innerHTML = counter;

        addToPaper(ids, 'remove');

      }

    }

  }

  // Fix46: one-page selection; never modifies the database.
  // Existing paper workflow retains its original addToPaper behavior.
  function lebdSelectVisibleQuestions(elm) {
    const area = document.getElementById('question_area');
    if (!area) return;
    const boxes = area.querySelectorAll('input.check[type="checkbox"]');
    const hasPaper = !!document.getElementById('maxq');
    if (hasPaper) {
      checkAll(elm);
    } else {
      boxes.forEach(function(box) { box.checked = elm.checked; });
    }
    checkAllchecked();
  }

  function lebdHandleRowSelect(elm) {
    count(elm);
    checkAllchecked();
  }

  // check all add to the question paper

  function checkAll(elm)

  {

    if(elm.checked == true)

    {

      let ids = [];

      let allQ = document.querySelectorAll('.check');

      for (let i = 0; i < allQ.length; i++)

      {

        let e = allQ[i];

        if (!e.checked) {

            e.checked = true;

            ids.push(e.value);

            counter++;

        }

        if (maxq.innerHTML && counter >= Number(maxq.innerHTML))

        {

          qcount.innerHTML = counter;

          break;

        }

        else

        {

          qcount.innerHTML = counter;

        }

      }

      //call to the function for add to the paper.

      if(ids.length > 0)

      {

        addToPaper(ids, 'add');

      }

      // console.log(ids);

    }

    else if(elm.checked == false)

    {

      let ids = [];

      let allQ = document.querySelectorAll('.check');

      allQ.forEach(e => {

        if(e.checked == true)

        {

          e.checked = false;

          ids.push(e.value);

          counter--;

          qcount.innerHTML = counter;

        }

      });

      //call to the function for add to the paper

      if(ids.length > 0)

      {

        addToPaper(ids, 'remove')

      }

      // console.log(ids);

    }

  }

  // add questions to paper post

  function addToPaper(ids, action)

  {

    let formData = new FormData();

    formData.append('question', ids);

    formData.append('action', action);

    // console.log(...formData);

    $.ajaxSetup({

      headers: {

        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

        }

      });

    $.ajax({

      url: '{{$url}}',

      type: 'POST',

      data: formData,

      cache: false,

      contentType: false,

      processData: false,

      success: function(data){

        // console.log(data);

        // qcount.innerHTML = data.qcount.attached.length;

        // loading.classList.add('hide');

      },

      error: function(data){

        console.error('Question request failed', data);

        loading.classList.add('hide');

      },

    });

  }

</script>

</section> <!-- /.content -->

@endsection

@section('scripts')
<script>
(function () {
  function exportFilteredQuestions(format) {
    var sourceForm = document.getElementById('filter_form');
    if (!sourceForm) return;
    var payload = new FormData(sourceForm);
    var checked = Array.prototype.slice.call(document.getElementsByName('sub_filter'))
      .filter(function (c) { return c.checked; })
      .map(function (c) { return c.value; });
    payload.set('filter_ids', checked.join(','));
    payload.set('export_format', format);
    payload.set('export_limit', document.getElementById('lebd_export_limit').value);
    var temp = document.createElement('form');
    temp.method = 'POST';
    temp.action = @json(route('question.filtered.export'));
    temp.style.display = 'none';
    payload.forEach(function (value, key) {
      if (value instanceof File) return;
      var hidden = document.createElement('input');
      hidden.type = 'hidden'; hidden.name = key; hidden.value = value;
      temp.appendChild(hidden);
    });
    document.body.appendChild(temp);
    temp.submit();
    setTimeout(function () { if (temp.parentNode) temp.parentNode.removeChild(temp); }, 2000);
  }
  document.addEventListener('DOMContentLoaded', function () {
    ['csv','word'].forEach(function (type) {
      var b = document.getElementById('lebd_download_' + type);
      if (b) b.addEventListener('click', function () { exportFilteredQuestions(type); });
    });
  });
})();
</script>

@include('shared.mathjax-renderer')

  <script>

  // get syllabuses

  function getSyllabus(e)

  {

    const syllabus = document.getElementById('syllabus');

    const department = document.getElementById('department_id');

    const batch_id = e.options[e.selectedIndex];

    const department_id = department.options[department.selectedIndex];

    $.ajax({

      type: 'GET',

      url: '{{route("syllabus.get-syllabus")}}',

      data: {batch_id: batch_id.value, department_id: department_id.value},

      success: function(data){

        let option = '<option value="">Select One</option>';

        data.syllabus.forEach((s) => {

          option += '<option value="'+s.id+'">'+s.name+'</option>';

        });

        syllabus.innerHTML = option;

      },

      error: function(data){

        console.error(data);

      }

    });

  }

  </script>

<script>

  $(function(){$('.select2').select2();});

  // change pagination symble to name

  function prevNext()

    {

      let paginate = document.getElementsByClassName('pagination');

      if(paginate.length > 0)

      {

        const firstLink = paginate[0].querySelector('li:first-child a');

        if (firstLink) firstLink.innerHTML = '&laquo; Previous';

        const lastLink = paginate[0].querySelector('li:last-child a');

        if (lastLink) lastLink.innerHTML = 'Next &raquo;';

      }

    }

  $(document).ready(function()

  {

    // click handle on pagination item

    $(document).on('click', '.pagination li a', function(event)

    {

      //prevent default get method

      event.preventDefault();

      var href = $(this).attr('href');

      if (!href || href === '#') return;

      var page = new URL(href, window.location.href).searchParams.get('page');

      if (!page || !/^\d+$/.test(page)) return;

      fetch_data(page);

    });

    // request by pagination page

    function fetch_data(page)

    {

      let data = '';

      //loading image view

      if (lebdFilterRequest) lebdFilterRequest.abort();

        if (lebdPageRequest) lebdPageRequest.abort();

        loading.classList.remove('hide');

      let ids = [];

      filters.forEach(e => {

        if(e.checked == true){

          ids.push(e.value);  

        }  

      });

      const formData = new FormData(form);

      formData.append('filter_ids', ids);

      lebdPageRequest = $.ajax({

        url: '{{route("question.view.post")}}?page='+page,

        type: 'POST',

        data:formData,

        cache: false,

        contentType: false,

        processData: false,

        success:function(data)

        {

          question_area.innerHTML = data;

            mathImgConverter();

          loading.classList.add('hide');

          prevNext();

          checkAllchecked();

        },

        error: function(data){

          if (data.statusText !== 'abort') console.error('Pagination request failed', data);

            loading.classList.add('hide');

        }

      });

      question_area.scrollIntoView({ behavior: "smooth" });

    }

  });

    prevNext();
    if (window.LEBDTypesetMath) window.LEBDTypesetMath(question_area);
    // math image converter

    function mathImgConverter()

    {

      if (typeof com !== 'undefined' && typeof com.wiris !== 'undefined' && typeof com.wiris.js !== 'undefined' && typeof com.wiris.js.JsPluginViewer !== 'undefined')

        {

          com.wiris.js.JsPluginViewer.parseElement(question_area);

        }

        // MathLive saves LaTeX in the question title and MCQ items.
    // Re-typeset after every AJAX filter/pagination HTML replacement.
    if (window.LEBDTypesetMath) {
        window.LEBDTypesetMath(question_area).catch(function (err) {
            console.warn('MathJax render failed', err);
        });
    }
}

</script>

{{-- LEBD-3 v9: Optional E-Book selection, only on ebook_kind + ebook_chapter request. --}}

@include('layouts.questions.ebook-selection-bridge')

@endsection