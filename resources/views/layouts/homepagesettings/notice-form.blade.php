@extends('dashboard')
@section('title', $notice->exists ? 'Edit Notice' : 'Add Notice')
@section('stylesheets')
<link href="/assets/summernote/summernote.min.css" rel="stylesheet">
<style>
.lebd-notice-form{padding:16px 18px 30px}.lebd-notice-form .form-title{font-size:28px;font-weight:800;color:#111827;margin:5px 0 18px}.lebd-notice-form .form-shell{background:#fff;border:1px solid #e3eaf5;border-radius:13px;box-shadow:0 9px 28px rgba(24,48,92,.12);padding:18px}.lebd-notice-form .form-card{border:1px solid #dce5f2;border-radius:11px;overflow:hidden;height:100%;background:#fff}.lebd-notice-form .form-card-head{background:linear-gradient(90deg,#e8efff,#dce7fb);padding:13px 18px;font-size:19px;font-weight:800;color:#101d50}.lebd-notice-form .form-card-body{padding:18px 20px}.lebd-notice-form label{font-weight:700;color:#18243b}.lebd-notice-form .form-control{height:43px;border:1px solid #d4dce8;border-radius:7px;box-shadow:none}.lebd-notice-form textarea.form-control{height:auto}.lebd-notice-form .form-group{margin-bottom:19px}.lebd-notice-form .required{color:#e11d48}.lebd-notice-form .type-choice{display:flex;flex-wrap:wrap;gap:25px;padding:5px 0}.lebd-notice-form .type-choice label{font-weight:600;cursor:pointer}.lebd-notice-form .type-choice input{width:18px;height:18px;vertical-align:middle;margin-right:7px;accent-color:#124fe0}.lebd-notice-form .upload-preview{width:158px;height:88px;border-radius:8px;border:1px dashed #a8bce0;background:#eef4ff;display:flex;align-items:center;justify-content:center;overflow:hidden;color:#7a9ad0;margin-bottom:8px}.lebd-notice-form .upload-preview img{max-width:100%;max-height:100%;object-fit:contain}.lebd-notice-form .hint{font-size:12px;color:#64748b;margin-top:7px}.lebd-notice-form .toggle-line{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 0}.lebd-notice-form .toggle-line input{width:20px;height:20px;accent-color:#124fe0}.lebd-notice-form .action-row{display:flex;justify-content:flex-end;gap:10px;margin-top:18px}.lebd-notice-form .summernote{height:150px}@media(max-width:767px){.lebd-notice-form{padding:12px 7px}.lebd-notice-form .form-title{font-size:23px}.lebd-notice-form .form-shell{padding:10px}.lebd-notice-form .form-card-body{padding:14px}.lebd-notice-form .action-row{flex-direction:column}.lebd-notice-form .action-row .btn{width:100%}}
</style>
@endsection
@section('content')
@php
  $editing = $notice->exists;
  $dateValue = function($value){ return $value ? \Carbon\Carbon::parse($value)->format('Y-m-d\\TH:i') : ''; };
@endphp
<div class="lebd-notice-form">
  <h1 class="form-title"><i class="fa fa-bullhorn" style="color:#1749bd"></i> {{ $editing ? 'Edit Notice' : 'Add Notice' }}</h1>
  @if($errors->any())<div class="alert alert-danger"><strong>Please check the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  <form class="form-shell" action="{{ $editing ? route('notice.update', $notice->id) : route('notice.store') }}" method="POST" enctype="multipart/form-data">
    @csrf @if($editing) @method('PUT') @endif
    <div class="row">
      <div class="col-lg-8">
        <div class="form-card">
          <div class="form-card-head"><i class="fa fa-puzzle-piece"></i> Notice Information</div>
          <div class="form-card-body">
            <div class="form-group"><label>Title <span class="required">*</span></label><input type="text" class="form-control" name="title" value="{{ old('title', $notice->title) }}" placeholder="Enter notice title..." required></div>
            <div class="form-group"><label>Notice Type <span class="required">*</span></label>
              <div class="type-choice">
                <label><input type="radio" name="notice_type" value="text" {{ old('notice_type', $notice->notice_type ?? 'text_image') === 'text' ? 'checked' : '' }}> Text</label>
                <label><input type="radio" name="notice_type" value="image" {{ old('notice_type', $notice->notice_type ?? 'text_image') === 'image' ? 'checked' : '' }}> Image Only</label>
                <label><input type="radio" name="notice_type" value="text_image" {{ old('notice_type', $notice->notice_type ?? 'text_image') === 'text_image' ? 'checked' : '' }}> Text + Image</label>
              </div>
            </div>
            <div class="form-group" id="noticeDetailsGroup"><label>Notice Details</label><textarea class="form-control editor" name="details" rows="6" placeholder="Write notice details here...">{{ old('details', $notice->details) }}</textarea></div>
            <div class="form-group" id="noticeImageGroup"><label>Upload Image</label>
              <div class="upload-preview" id="noticePreview">@if($notice->image)<img src="{{ asset($notice->image) }}" alt="Current notice image">@else<i class="fa fa-image fa-3x"></i>@endif</div>
              <input type="file" name="image" id="noticeImage" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
              @if($notice->image)<label style="font-weight:500;margin-top:8px"><input type="checkbox" name="remove_image" value="1"> Remove current image</label><input type="hidden" name="existing_image" value="1">@endif
              <p class="hint">Supported: JPG, JPEG, PNG, WebP | Maximum file size: 5 MB | Recommended: 1200 × 700 px</p>
            </div>
            <div class="form-group"><label><i class="fa fa-link"></i> URL Link (Optional)</label><input type="url" class="form-control" name="url" value="{{ old('url', $notice->url) }}" placeholder="https://example.com"><label style="font-weight:500;margin-top:9px"><input type="checkbox" name="open_new_tab" value="1" {{ old('open_new_tab', $notice->open_new_tab) ? 'checked' : '' }}> Open link in new tab</label></div>
          </div>
        </div>
      </div>
      <div class="col-lg-4" style="margin-top:0">
        <div class="form-card">
          <div class="form-card-head"><i class="fa fa-cog"></i> Display Settings</div>
          <div class="form-card-body">
            <div class="toggle-line"><label style="margin:0">Show on Website <span class="required">*</span></label><input type="checkbox" name="show_on_website" value="1" {{ old('show_on_website', $notice->show_on_website ?? true) ? 'checked' : '' }}></div>
            <hr>
            <div class="form-group"><label><i class="fa fa-clock-o"></i> Display Duration (Auto Close)</label><select class="form-control" name="display_duration" required>@foreach([2,5,10,15,30] as $seconds)<option value="{{ $seconds }}" {{ (int)old('display_duration', $notice->display_duration ?? 2) === $seconds ? 'selected' : '' }}>{{ $seconds }} Seconds</option>@endforeach</select></div>
            <div class="form-group"><label><i class="fa fa-calendar"></i> Validity Period</label><label style="font-weight:500">Start Date</label><input type="datetime-local" class="form-control" name="start_at" value="{{ old('start_at', $dateValue($notice->start_at)) }}"><label style="font-weight:500;margin-top:10px">End Date</label><input type="datetime-local" class="form-control" name="end_at" value="{{ old('end_at', $dateValue($notice->end_at)) }}"><p class="hint">Leave blank to start immediately / no expiry.</p></div>
            <div class="form-group"><label><i class="fa fa-circle" style="color:#22b14c"></i> Status</label><select class="form-control" name="status" required><option value="Published" {{ old('status', $notice->status ?? 'Published') === 'Published' ? 'selected' : '' }}>Published</option><option value="Unpublished" {{ old('status', $notice->status) === 'Unpublished' ? 'selected' : '' }}>Unpublished</option></select></div>
          </div>
        </div>
      </div>
    </div>
    <div class="action-row"><a href="{{ route('noitice.edit') }}" class="btn btn-default btn-lg"><i class="fa fa-times"></i> Cancel</a><button type="submit" class="btn btn-primary btn-lg"><i class="fa fa-save"></i> {{ $editing ? 'Update Notice' : 'Save Notice' }}</button></div>
  </form>
</div>
@endsection
@section('scripts')
<script src="/assets/summernote/summernote.min.js"></script>
<script>
$(function(){
  if($.fn.summernote){$('.editor').summernote({height:150,toolbar:[['font',['bold','italic','underline','clear']],['para',['ul','ol']],['insert',['link','picture']],['view',['codeview']] ]});}
  function syncType(){var type=$('input[name="notice_type"]:checked').val();$('#noticeDetailsGroup').toggle(type!=='image');$('#noticeImageGroup').toggle(type!=='text');}
  $('input[name="notice_type"]').on('change',syncType);syncType();
  $('#noticeImage').on('change',function(){var f=this.files&&this.files[0];if(!f)return;if(f.size>5*1024*1024){alert('Image must be 5 MB or smaller.');this.value='';return;}var reader=new FileReader();reader.onload=function(e){$('#noticePreview').html('<img src="'+e.target.result+'" alt="Preview">');};reader.readAsDataURL(f);});
});
</script>
@endsection
