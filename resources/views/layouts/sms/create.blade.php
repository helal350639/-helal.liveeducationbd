@extends('dashboard')
@section('title', 'Send SMS')
@section('content')
<section class="content-header">
  <h1>Send SMS</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> SMS</a></li>
    <li class="active">Send SMS</li>
</ol>
</section>

<!-- Main content -->
<section class="content">
  <style>
    .sms-workflow-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;margin:0 0 20px}
    .sms-workflow-card{background:#fff;border:1px solid #dce6f2;border-radius:12px;overflow:hidden;box-shadow:0 4px 15px rgba(28,55,90,.07);transition:all .2s ease}
    .sms-workflow-card:hover{box-shadow:0 8px 22px rgba(28,55,90,.11);transform:translateY(-2px)}
    .sms-workflow-card.mcq{border-top:4px solid #2474d2}
    .sms-workflow-card.student{border-top:4px solid #15965d}
    .sms-workflow-head{display:flex;align-items:center;gap:14px;padding:18px 20px;background:linear-gradient(110deg,#f4f8ff,#eaf3ff)}
    .sms-workflow-card.student .sms-workflow-head{background:linear-gradient(110deg,#f2fbf6,#e7f8ef)}
    .sms-workflow-icon{width:52px;height:52px;display:flex;align-items:center;justify-content:center;border-radius:12px;font-size:23px;background:#dcecff;color:#1768c2;flex:0 0 52px}
    .sms-workflow-card.student .sms-workflow-icon{background:#d8f5e5;color:#15965d}
    .sms-workflow-head strong{display:block;font-size:18px;color:#203955;margin-bottom:4px}
    .sms-workflow-head small{font-size:12px;color:#788ba3}
    .sms-workflow-body{padding:20px}
    .sms-workflow-label{display:block;font-size:14px;font-weight:600;color:#334b68;margin-bottom:8px}
    .sms-workflow-select{height:44px;border:1px solid #cbd8e8;border-radius:7px;font-size:14px;box-shadow:none}
    .sms-workflow-select:focus{border-color:#428bca;box-shadow:0 0 0 3px rgba(66,139,202,.12)}
    .sms-other-wrap{display:none;margin-top:14px}
    .sms-other-message{width:100%;min-height:94px;border:1px solid #cbd8e8;border-radius:7px;padding:10px 12px;resize:vertical}
    .sms-other-count{text-align:right;color:#8291a5;font-size:12px;margin-top:5px}
    .sms-create-action{display:block;width:100%;margin-top:18px;padding:12px 15px;border:0;border-radius:7px;color:#fff;font-size:15px;font-weight:700;background:#1456bb;transition:all .2s}
    .sms-workflow-card.student .sms-create-action{background:#128b55}
    .sms-create-action:hover{filter:brightness(1.06);box-shadow:0 4px 12px rgba(20,86,187,.2)}
    .sms-create-action:disabled{opacity:.45;cursor:not-allowed;box-shadow:none}
    .sms-form-back{margin-left:auto}
    @media(max-width:767px){.sms-workflow-grid{grid-template-columns:1fr}.sms-workflow-body{padding:16px}.sms-workflow-head{padding:15px}}

    .sms-routine-option{cursor:pointer}
    .sms-routine-option:hover{border-color:#9db9dc!important;background:#f2f7ff!important}
    .sms-routine-option input{margin:0;flex:0 0 auto}
  </style>

  <div class="sms-workflow-grid" id="smsEntryShortcuts">
    <div class="sms-workflow-card mcq" data-workflow="mcq" @if(($sourceType ?? "batch_list") === "exam_section") style="display:none" @elseif(request()->has("batch_id")) style="display:block" @endif>
      <div class="sms-workflow-head">
        <div class="sms-workflow-icon"><i class="fa fa-book"></i></div>
        <div>
          <strong>MCQ Batch</strong>
          <small>MCQ Batch-এর SMS পাঠানোর ব্যবস্থাপনা</small>
        </div>
      </div>
      <div class="sms-workflow-body">
        <label class="sms-workflow-label" for="mcq_sms_type">SMS Type নির্বাচন করুন</label>
        <select id="mcq_sms_type" class="form-control sms-workflow-select" onchange="smsTypeChanged('mcq', this)">
          <option value="">Select SMS Type</option>
          <option value="Exam Alert">Exam Alert</option>
          <option value="Exam Link">Exam Link</option>
          <option value="Others">Others</option>
        </select>
        <div class="sms-other-wrap" id="mcq_other_wrap">
          <label class="sms-workflow-label" for="mcq_other_message">নিজের SMS লিখুন</label>
          <textarea id="mcq_other_message" class="sms-other-message" maxlength="1000" placeholder="আপনার SMS লিখুন..." oninput="smsOtherCount('mcq')"></textarea>
          <div class="sms-other-count"><span id="mcq_other_count">0</span>/1000 characters</div>
        </div>
        <button type="button" class="sms-create-action" id="mcq_create_btn" disabled onclick="openSmsDetails('mcq')">
          <i class="fa fa-paper-plane"></i> Create SMS
        </button>
      </div>
    </div>

    <div class="sms-workflow-card student" data-workflow="student" @if(($sourceType ?? "batch_list") !== "exam_section") style="display:none" @endif>
      <div class="sms-workflow-head">
        <div class="sms-workflow-icon"><i class="fa fa-graduation-cap"></i></div>
        <div>
          <strong>Student Dashboard</strong>
          <small>Student Dashboard-এর SMS পাঠানোর ব্যবস্থাপনা</small>
        </div>
      </div>
      <div class="sms-workflow-body">
        <label class="sms-workflow-label" for="student_sms_type">SMS Type নির্বাচন করুন</label>
        <select id="student_sms_type" class="form-control sms-workflow-select" onchange="smsTypeChanged('student', this)">
          <option value="">Select SMS Type</option>
          <option value="Exam Alert">Exam Alert</option>
          <option value="Exam Link">Exam Link</option>
          <option value="Others">Others</option>
        </select>
        <div class="sms-other-wrap" id="student_other_wrap">
          <label class="sms-workflow-label" for="student_other_message">নিজের SMS লিখুন</label>
          <textarea id="student_other_message" class="sms-other-message" maxlength="1000" placeholder="আপনার SMS লিখুন..." oninput="smsOtherCount('student')"></textarea>
          <div class="sms-other-count"><span id="student_other_count">0</span>/1000 characters</div>
        </div>
        <button type="button" class="sms-create-action" id="student_create_btn" disabled onclick="openSmsDetails('student')">
          <i class="fa fa-paper-plane"></i> Create SMS
        </button>
      </div>
    </div>
  </div>
  <div id="smsFormPanel" style="display:none;">
  <div class="row"> <!-- left column -->
    <div class="col-md-8"> <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border" style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
            <h3 style="color: #800;margin:0;" class="box-title">SMS Details</h3>
            <button type="button" class="btn btn-default btn-sm sms-form-back" onclick="backToSmsOptions()">
              <i class="fa fa-arrow-left"></i> Back
            </button>
        </div>
        <form action="{{route('sms.store')}}" method="POST" id="smsDetailsForm">
            @csrf
            <input type="hidden" name="sms_workflow" id="sms_workflow" value="">
            <input type="hidden" name="sms_type" id="sms_type" value="">
            <input type="hidden" name="sms_source_type" id="sms_source_type" value="{{ $sourceType ?? 'batch_list' }}">
            <input type="hidden" name="sms_source_id" id="sms_source_id" value="{{ $sourceId ?? 0 }}">
            <div class="box-body">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" class="form-control" name="title" id="title"/>
                    </div>
                </div>
                @if(($sourceType ?? 'batch_list') === 'exam_section' && !empty($smsSource))
                <div class="col-md-12">
                    <div class="alert alert-success" style="margin-bottom:12px">
                        <strong>Student Dashboard SMS</strong> — {{ $smsSource->name }}
                        <small style="display:block">Exam Section-এর নির্বাচিত Category / Subcategory-এর Published Routine ও Exam অনুযায়ী SMS তৈরি হবে।</small>
                    </div>
                </div>
                @endif
                @if(($sourceType ?? 'batch_list') === 'exam_section' && !empty($smsSource))
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Exam Section Category / Subcategory</label>
                            <div class="form-control" style="height:auto;min-height:38px;background:#f4fbf7">
                                <strong>{{ $smsSource->category_name ?? $smsSource->name }}</strong>
                                @if(!empty($smsSource->subcategory_name))
                                    <span class="text-muted"> / {{ $smsSource->subcategory_name }}</span>
                                @endif
                                @if(!empty($smsSource->course_names) && $smsSource->course_names->isNotEmpty())
                                    <small class="text-muted" style="display:block">Course: {{ $smsSource->course_names->implode(', ') }}</small>
                                @endif
                            </div>
                            <small class="text-muted">Routine: {{ $smsSource->routine_count }} | Total Exam: {{ $smsSource->exam_count }}</small>
                        </div>
                        @foreach($batches as $recipientBatch)
                            <input type="hidden" name="batch_id[]" value="{{ $recipientBatch->id }}">
                        @endforeach
                        @foreach($selectedRoutineIds as $routineId)
                            <input type="hidden" name="exam_section_routine_ids[]" value="{{ $routineId }}">
                        @endforeach
                        @foreach($selectedExamIds as $examId)
                            <input type="hidden" name="exam_section_exam_ids[]" value="{{ $examId }}">
                        @endforeach
                    </div>
                @else
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="name">Batch Name</label>
                            <select class="form-control select2" name="batch_id[]" id="batch_id" multiple>
                                <option value=""></option>
                                @foreach($batches as $batch)
                                <option value="{{$batch->id}}" {{ (isset($selectedBatchId) && (int) $selectedBatchId === (int) $batch->id) ? "selected" : "" }}>{{$batch->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="routine_picker">Routine / Exam নির্বাচন</label>
                        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                            @if(($sourceType ?? 'batch_list') !== 'exam_section')
                            <button type="button" class="btn btn-default" id="addRoutineBtn" onclick="goToRoutineSelection()">
                                <i class="fa fa-plus-circle"></i> Add Routine / Select Exam
                            </button>
                            @else
                            <span class="label label-success"><i class="fa fa-check-circle"></i> Exam Section থেকে সব Published Routine ও Exam লোড হয়েছে</span>
                            @endif
                            <span class="text-muted" id="selectedRoutineCount">
                                @if($selectedExamIds) {{ count($selectedExamIds) }}টি Exam নির্বাচন করা হয়েছে @else কোনো Exam নির্বাচন করা হয়নি @endif
                            </span>
                        </div>
                        <div id="selectedRoutineList" style="margin-top:10px">
                            @forelse($selectedExams as $exam)
                                <div class="sms-selected-routine" style="display:inline-flex;align-items:center;gap:6px;padding:6px 10px;margin:0 6px 6px 0;border:1px solid #dce6f2;border-radius:5px;background:#f7faff">
                                    <i class="fa fa-check-circle text-success"></i>
                                    <span>{{ $exam->paper_name }}</span>
                                    <small class="text-muted">{{ $exam->exam_at ? \Carbon\Carbon::parse($exam->exam_at)->timezone('Asia/Dhaka')->format('d-m-Y h:i A') : '' }}</small>
                                </div>
                            @empty
                                <span class="text-muted">কোনো Exam নির্বাচন করা হয়নি</span>
                            @endforelse
                        </div>
                        <div id="selectedRoutineHidden">
                            @if(($sourceType ?? 'batch_list') !== 'exam_section')
                                @foreach($selectedRoutineIds as $routineId)
                                    <input type="hidden" name="routine_ids[]" value="{{ $routineId }}">
                                @endforeach
                                @foreach($selectedExamIds as $examId)
                                    <input type="hidden" name="exam_ids[]" value="{{ $examId }}">
                                @endforeach
                            @endif
                        </div>
                <div class="clearfix"></div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="details">SMS Details</label>
                        <textarea class="form-control" name="details" id="details" rows="5" maxlength="150" required oninput="charCount(this)"></textarea>
                        <p style="color: red">Maximum 150 Characters = 1 SMS | <b>Character Count: <span id="char_count">0</span>/150</b> | SMS Count: <span id="sms_count">1</span></p>
                        <p id="examAlertAutoNote" class="text-info" style="display:none;margin-top:-5px">
                            <i class="fa fa-clock-o"></i> Save Draft করলে পরে Edit করতে পারবেন। Confirm & Schedule All করলে প্রতিটি Exam-এর জন্য আলাদা SMS তৈরি হবে এবং Exam শুরুর ১২ ঘণ্টা আগে পাঠানোর সময় নির্ধারিত হবে।
                        </p>
                    </div>
                </div>
                <div id="manualScheduleFields">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" class="form-control" name="start_date" id="start_date" value="{{ old('start_date') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" class="form-control" name="end_date" id="end_date" value="{{ old('end_date') }}">
                    </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                      <label>Status:</label><br>
                      <select name="status" id="status" class="form-control" required onchange="checkStatus(this)">
                        <option value="">Select One</option>
                        <option value="Scheduled">Scheduled</option>
                        <option value="Sent">Sent</option>
                        <option value="Unsent">Unsent</option>
                      </select>
                  </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label for="">Date & Time:</label>
                  <input type="datetime-local" name="datetime" class="form-control" id="datetime">
                </div>
              </div>
              </div><!-- /#manualScheduleFields -->
                <div class="col-md-12">
                    <div class="form-group">
                        <input type="hidden" name="is_active" value="0">
                        <label style="display:flex;align-items:center;gap:9px">
                            <input type="checkbox" name="is_active" value="1" checked>
                            Active রাখুন
                        </label>
                    </div>
                </div>
            </div> <!-- /.box body -->
            <div class="box-footer" style="display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap">
                <button type="submit" class="btn btn-primary" id="sentBtn"><i class="fa fa-save"></i> Save</button>
                <div id="examAlertActions" style="display:none;gap:10px">
                    <button type="submit" name="exam_action" value="save_draft" class="btn btn-default" id="saveExamDraftBtn"><i class="fa fa-floppy-o"></i> Save Draft</button>
                    <button type="submit" name="exam_action" value="confirm_schedule" class="btn btn-success" id="confirmExamScheduleBtn" onclick="return confirm('নির্বাচিত সব Exam-এর জন্য আলাদা SMS তৈরি করে Exam শুরুর ১২ ঘণ্টা আগে Schedule করবেন?')"><i class="fa fa-calendar-check-o"></i> Confirm &amp; Schedule All</button>
                </div>
            </div>
        </form>
    </div> <!-- /.box -->
    </div> <!--/.col (left) -->
</div> <!-- /.row -->
  </div> <!-- /#smsFormPanel -->
</section> <!-- /.content -->
@endsection
@section('scripts')
<script>
  var smsSelectedExams = @json($selectedExams->values());

  function formatExamDateTime(value, format) {
    if (!value) return '';
    var date = new Date(String(value).replace(' ', 'T'));
    if (isNaN(date.getTime())) return '';
    var day = String(date.getDate()).padStart(2, '0');
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var year = String(date.getFullYear()).slice(-2);
    var hours = date.getHours();
    var minutes = String(date.getMinutes()).padStart(2, '0');
    var suffix = hours >= 12 ? 'PM' : 'AM';
    var hour12 = hours % 12 || 12;
    return format === 'date' ? day + '-' + month + '-' + year :
      String(hour12).padStart(2, '0') + ':' + minutes + ' ' + suffix;
  }

  function examAlertMessage(exam) {
    return [
      'Exam Alert',
      'Live Education BD',
      String(exam.batch_name_en || exam.batch_name || 'Live Education BD Batch').replace(/\s*\([^)]*\)\s*/g, ' ').trim(),
      'Exam No: ' + (exam.paper_name || ''),
      'Exam Date: ' + formatExamDateTime(exam.exam_at, 'date'),
      'Time: ' + formatExamDateTime(exam.exam_at, 'time'),
      'Call: 01718778184'
    ].join('\n');
  }

  function applyExamAlertPreview() {
    var exam = smsSelectedExams.length ? smsSelectedExams[0] : null;
    var details = document.getElementById('details');
    var title = document.getElementById('title');
    var status = document.getElementById('status');
    var datetime = document.getElementById('datetime');
    var note = document.getElementById('examAlertAutoNote');

    if (!exam) return false;

    details.value = examAlertMessage(exam);
    details.readOnly = true;
    if (title) title.value = 'Exam Alert — ' + (exam.paper_name || 'Routine Exam');
    if (status) {
      status.value = 'Scheduled';
      status.dispatchEvent(new Event('change'));
    }
    if (datetime && exam.exam_at) {
      var examDate = new Date(String(exam.exam_at).replace(' ', 'T'));
      examDate.setHours(examDate.getHours() - 12);
      var localValue = new Date(examDate.getTime() - examDate.getTimezoneOffset() * 60000)
        .toISOString().slice(0, 16);
      datetime.value = localValue;
      datetime.readOnly = true;
    }
    if (note) note.style.display = 'block';
    if (typeof charCount === 'function') charCount(details);
    return true;
  }

  function clearExamAlertPreview() {
    var details = document.getElementById('details');
    var datetime = document.getElementById('datetime');
    var note = document.getElementById('examAlertAutoNote');
    if (details) details.readOnly = false;
    if (datetime) datetime.readOnly = false;
    if (note) note.style.display = 'none';
  }

  function smsTypeChanged(workflow, select) {
    var value = select.value;
    var otherWrap = document.getElementById(workflow + '_other_wrap');
    var createButton = document.getElementById(workflow + '_create_btn');
    var message = document.getElementById(workflow + '_other_message');

    otherWrap.style.display = value === 'Others' ? 'block' : 'none';
    var isExamAlert = value === 'Exam Alert';
    var manualFields = document.getElementById('manualScheduleFields');
    var examActions = document.getElementById('examAlertActions');
    var normalSave = document.getElementById('sentBtn');
    if (manualFields) manualFields.style.display = isExamAlert ? 'none' : '';
    ['start_date','end_date','status','datetime'].forEach(function(id) {
      var field = document.getElementById(id);
      if (field) field.required = !isExamAlert && id === 'status';
    });
    if (examActions) examActions.style.display = isExamAlert ? 'flex' : 'none';
    if (normalSave) normalSave.style.display = isExamAlert ? 'none' : '';
    clearExamAlertPreview();
    // Exam Alert opens the details page first; routine exams are selected there.
    createButton.disabled = !value ||
      (value === 'Others' && !message.value.trim());
  }

  function smsOtherCount(workflow) {
    var message = document.getElementById(workflow + '_other_message');
    var count = document.getElementById(workflow + '_other_count');
    var type = document.getElementById(workflow + '_sms_type').value;
    count.textContent = message.value.length;
    document.getElementById(workflow + '_create_btn').disabled =
      !type || (type === 'Others' && !message.value.trim());
  }

  function openSmsDetails(workflow) {
    var type = document.getElementById(workflow + '_sms_type').value;
    if (!type) return;

    if (type === 'Exam Alert') {
      // Exam selection is performed inside the details page.
      // Populate the preview only when the user has already selected exams.
      if (smsSelectedExams.length) {
        applyExamAlertPreview();
      } else {
        clearExamAlertPreview();
        var alertDetails = document.getElementById('details');
        var alertTitle = document.getElementById('title');
        if (alertDetails) alertDetails.value = '';
        if (alertTitle && !alertTitle.value.trim()) alertTitle.value = 'Exam Alert — MCQ Batch';
        var alertNote = document.getElementById('examAlertAutoNote');
        if (alertNote) alertNote.style.display = 'block';
      }
    } else {
      clearExamAlertPreview();
    }

    if (type === 'Others') {
      var customMessage = document.getElementById(workflow + '_other_message').value.trim();
      if (!customMessage) {
        alert('অনুগ্রহ করে SMS লিখুন।');
        return;
      }
      document.getElementById('details').value = customMessage;
      if (typeof charCount === 'function') charCount(document.getElementById('details'));
    }

    document.getElementById('sms_workflow').value = workflow;
    document.getElementById('sms_type').value = type;
    var isExamAlertMode = type === 'Exam Alert';
    document.getElementById('manualScheduleFields').style.display = isExamAlertMode ? 'none' : '';
    ['start_date','end_date','status','datetime'].forEach(function(id) {
      var field = document.getElementById(id);
      if (field) field.required = !isExamAlertMode && id === 'status';
    });
    document.getElementById('examAlertActions').style.display = isExamAlertMode ? 'flex' : 'none';
    document.getElementById('sentBtn').style.display = isExamAlertMode ? 'none' : '';

    var title = document.getElementById('title');
    if (title && !title.value.trim()) {
      title.value = type + ' — ' + (workflow === 'mcq' ? 'MCQ Batch' : 'Student Dashboard');
    }

    var panel = document.getElementById('smsFormPanel');
    var shortcuts = document.getElementById('smsEntryShortcuts');
    shortcuts.style.display = 'none';
    panel.style.display = 'block';

    var heading = document.querySelector('#smsFormPanel .box-title');
    if (heading) {
      heading.textContent = 'SMS Details — ' + type + ' — ' +
        (workflow === 'mcq' ? 'MCQ Batch' : 'Student Dashboard');
    }

    panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function backToSmsOptions() {
    var panel = document.getElementById('smsFormPanel');
    var shortcuts = document.getElementById('smsEntryShortcuts');

    panel.style.display = 'none';
    shortcuts.style.display = 'grid';
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function goToRoutineSelection() {
    var form = document.getElementById('smsDetailsForm');
    var batchSelect = document.getElementById('batch_id');
    var batchIds = Array.from(batchSelect.selectedOptions)
      .map(function(option) { return option.value; })
      .filter(Boolean);

    if (!batchIds.length) {
      alert('প্রথমে Batch নির্বাচন করুন।');
      batchSelect.focus();
      return;
    }

    // Keep any SMS details already typed while moving to the Routine list.
    var saved = {};
    Array.from(form.elements).forEach(function(element) {
      if (!element.name || element.name === '_token' || element.name === 'routine_ids[]' || element.name === 'exam_ids[]') return;
      if (element.type === 'checkbox' || element.type === 'radio') {
        saved[element.name] = element.checked;
      } else if (element.multiple) {
        saved[element.name] = Array.from(element.selectedOptions).map(function(option) { return option.value; });
      } else {
        saved[element.name] = element.value;
      }
    });
    saved.__workflow = document.getElementById('sms_workflow').value ||
      (document.getElementById('mcq_sms_type').value ? 'mcq' : 'student');
    saved.__smsType = document.getElementById(saved.__workflow + '_sms_type').value;
    saved.__otherMessage = document.getElementById(saved.__workflow + '_other_message').value;
    try { sessionStorage.setItem('sms_create_form_state', JSON.stringify(saved)); } catch (e) {}

    var url = new URL(@json(route('sms.routines.select')), window.location.origin);
    batchIds.forEach(function(id) { url.searchParams.append('batch_ids[]', id); });
    window.location.href = url.toString();
  }

  function restoreSmsFormState() {
    var raw = null;
    try {
      raw = sessionStorage.getItem('sms_create_form_state');
      if (!raw) return;
      sessionStorage.removeItem('sms_create_form_state');
    } catch (e) { return; }

    var saved;
    try { saved = JSON.parse(raw); } catch (e) { return; }
    var form = document.getElementById('smsDetailsForm');
    if (!form) return;

    Object.keys(saved).forEach(function(name) {
      var elements = form.querySelectorAll('[name="' + CSS.escape(name) + '"]');
      if (!elements.length) return;
      var element = elements[0];

      if (element.multiple && Array.isArray(saved[name])) {
        Array.from(element.options).forEach(function(option) {
          option.selected = saved[name].indexOf(option.value) !== -1;
        });
        if (window.jQuery && $(element).hasClass('select2')) $(element).trigger('change.select2');
      } else if (element.type === 'checkbox' || element.type === 'radio') {
        element.checked = !!saved[name];
      } else {
        element.value = saved[name] == null ? '' : saved[name];
      }
    });

    if (saved.__workflow && saved.__smsType) {
      var workflow = saved.__workflow;
      var typeSelect = document.getElementById(workflow + '_sms_type');
      var otherMessage = document.getElementById(workflow + '_other_message');
      if (typeSelect) {
        typeSelect.value = saved.__smsType;
        if (typeof smsTypeChanged === 'function') smsTypeChanged(workflow, typeSelect);
      }
      if (otherMessage && saved.__otherMessage !== undefined) {
        otherMessage.value = saved.__otherMessage;
        if (typeof smsOtherCount === 'function') smsOtherCount(workflow);
      }
      openSmsDetails(workflow);
    }

    if (saved.__smsType === 'Exam Alert') {
      applyExamAlertPreview();
    }

    if (document.getElementById('details') && typeof charCount === 'function') {
      charCount(document.getElementById('details'));
    }
  }

  function charCount(e)
  {
      // One SMS is limited to 150 characters in this project.
      if (!e.readOnly && e.value.length > 150) {
        e.value = e.value.substring(0, 150);
      }

      var charCountElement = document.getElementById('char_count');
      var smsCountElement = document.getElementById('sms_count');
      var length = e.value.length;

      if (charCountElement) charCountElement.textContent = length;
      if (smsCountElement) smsCountElement.textContent = length > 0 ? '1' : '0';
  }
    
  const datatime = document.getElementById('datetime')
  const sentBtn = document.getElementById('sentBtn')
  function checkStatus(e)
  {
    if (document.getElementById('sms_type') && document.getElementById('sms_type').value === 'Exam Alert') {
      e.value = 'Scheduled';
      return;
    }
    const option = e.options[e.selectedIndex];
    if(option.value == 'Sent')
    {
      sentBtn.innerHTML = '<i class="fa fa-send"></i> Send';
    }
    else
    {
      sentBtn.innerHTML = '<i class="fa fa-save"></i> Save';
    }

    if(option.value == 'Scheduled')
    {
      datetime.setAttribute('required', 'required');
    }
    else
    {
      datetime.removeAttribute('required');
    }
  }

    function getStudents(e)
    {
        let ids = Array.from(e.selectedOptions).map(({value}) => value);

        if(ids.length > 0)
        {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                type: 'GET', //THIS NEEDS TO BE GET
                url: '{{route("sms.batch.student", "")}}/' + ids,
                success: function (data) {

                    var obj = JSON.parse(JSON.stringify(data));
                    var options = '';

                    $.each(obj['students'], function (key, val) {
                        options += '<option value="'+val.student_id+'">'+val.student_name+'</option>';
                    });

                    if ($('#student_id').length) {
                        if(options != '') { $('#student_id').html(options); }
                        else { $('#student_id').html(''); }
                    }
                },
                error: function(data) { 
                        console.log('data error');
                }
            });
        }

        
    }

    //select 2
    $(function (){ if ($('.select2').length) $('.select2').select2(); restoreSmsFormState(); });
</script>
@endsection