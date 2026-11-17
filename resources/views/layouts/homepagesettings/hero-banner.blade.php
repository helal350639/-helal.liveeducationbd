@extends('dashboard')
@section('title', 'Homepage Hero Banner')
@section('stylesheets')
<style>
.lebd-hero-admin{padding:18px 18px 30px}.lebd-hero-admin .page-title{font-size:28px;font-weight:800;color:#111827;margin:5px 0 18px}.lebd-hero-admin .panel-card{background:#fff;border:1px solid #e2e9f4;border-radius:13px;box-shadow:0 8px 24px rgba(20,45,90,.08);overflow:hidden;margin-bottom:18px}.lebd-hero-admin .panel-head{background:linear-gradient(90deg,#e8efff,#dce7fb);padding:14px 18px;color:#101d50;font-size:19px;font-weight:800}.lebd-hero-admin .panel-body{padding:18px 20px}.lebd-hero-admin label{font-weight:700;color:#1e293b}.lebd-hero-admin .form-control{height:43px;border:1px solid #d4dce8;border-radius:7px;box-shadow:none}.lebd-hero-admin .preview{width:100%;max-width:520px;height:210px;border:1px dashed #a8bce0;border-radius:10px;background:#f1f6ff;display:flex;align-items:center;justify-content:center;overflow:hidden;color:#7b91b5;margin:8px 0 12px}.lebd-hero-admin .preview img{width:100%;height:100%;object-fit:contain}.lebd-hero-admin #mobilePreview{aspect-ratio:7/4;height:auto;min-height:0;max-height:300px}.lebd-hero-admin .hint{font-size:12px;color:#64748b;margin:7px 0 14px}.lebd-hero-admin .toggle{display:flex;align-items:center;gap:10px;padding:12px 14px;background:#f4f7fd;border:1px solid #e2eaf7;border-radius:9px}.lebd-hero-admin .toggle input{width:19px;height:19px;accent-color:#1455df}.lebd-hero-admin .actions{display:flex;justify-content:flex-end;gap:10px;margin-top:16px}@media(max-width:767px){.lebd-hero-admin{padding:12px 8px}.lebd-hero-admin .page-title{font-size:23px}.lebd-hero-admin .panel-body{padding:14px}.lebd-hero-admin .preview{height:165px}.lebd-hero-admin .actions{flex-direction:column}.lebd-hero-admin .actions .btn{width:100%}}
</style>
@endsection
@section('content')
<div class="lebd-hero-admin">
  <h1 class="page-title"><i class="fa fa-picture-o" style="color:#1749bd"></i> Homepage Hero Banner</h1>
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="alert alert-danger"><strong>Please check:</strong><ul style="margin:6px 0 0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  <form class="panel-card" method="POST" action="{{ route('settings.hero-banner.update') }}" enctype="multipart/form-data">
    @csrf
    <div class="panel-head"><i class="fa fa-desktop"></i> Banner Image Settings</div>
    <div class="panel-body">
      <div class="row">
        <div class="col-md-6">
          <label>Desktop Image</label>
          <div class="preview" id="desktopPreview">@if(!empty($settings['desktop_url']))<img src="{{ $settings['desktop_url'] }}" alt="Current desktop banner">@else<i class="fa fa-image fa-4x"></i>@endif</div>
          <input class="form-control" type="file" name="desktop_image" id="desktopImage" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
          <p class="hint">Supported: JPG, JPEG, PNG, WebP | Maximum: 5 MB | Recommended: 1200 × 700 px</p>
          @if(!empty($settings['desktop_url']))<label style="font-weight:500"><input type="checkbox" name="remove_desktop_image" value="1"> Remove desktop image</label>@endif
        </div>
        <div class="col-md-6">
          <label>Mobile Image (Optional)</label>
          <div class="preview" id="mobilePreview">@if(!empty($settings['mobile_url']))<img src="{{ $settings['mobile_url'] }}" alt="Current mobile banner">@else<i class="fa fa-mobile fa-4x"></i>@endif</div>
          <input class="form-control" type="file" name="mobile_image" id="mobileImage" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
          <p class="hint">Supported: JPG, JPEG, PNG, WebP | Maximum: 5 MB | Recommended: 700 × 400 px. Blank রাখলে Desktop image-ই ব্যবহার হবে।</p>
          @if(!empty($settings['mobile_url']))<label style="font-weight:500"><input type="checkbox" name="remove_mobile_image" value="1"> Remove mobile image</label>@endif
        </div>
      </div>
      <hr>
      <label class="toggle"><input type="checkbox" name="show_banner" value="1" {{ old('show_banner', $settings['show_banner'] ?? true) ? 'checked' : '' }}> Show Hero Banner image on Homepage</label>
      <div class="actions">
        <a href="{{ route('homepage') }}" target="_blank" class="btn btn-default"><i class="fa fa-external-link"></i> View Homepage</a>
        <button type="submit" class="btn btn-primary btn-lg"><i class="fa fa-save"></i> Save Banner</button>
      </div>
    </div>
  </form>
  <div class="alert alert-info"><i class="fa fa-info-circle"></i> নতুন ছবি Save করলে আগের আপলোড করা Banner file মুছে যাবে। Homepage-এর লেখা, বাটন, রং, কোর্স ও অন্যান্য সেকশন অপরিবর্তিত থাকবে।</div>
</div>
<script>
(function(){
  function preview(inputId, previewId){var input=document.getElementById(inputId),box=document.getElementById(previewId);if(!input||!box)return;input.addEventListener('change',function(){var file=this.files&&this.files[0];if(!file)return;if(!/^image\/(jpeg|png|webp)$/.test(file.type)){alert('Please select JPG, PNG or WebP image.');this.value='';return;}if(file.size>5*1024*1024){alert('Maximum image size is 5 MB.');this.value='';return;}var reader=new FileReader();reader.onload=function(e){box.innerHTML='';var img=document.createElement('img');img.src=e.target.result;img.alt='Selected banner preview';box.appendChild(img);};reader.readAsDataURL(file);});}
  preview('desktopImage','desktopPreview');preview('mobileImage','mobilePreview');
})();
</script>
@endsection
