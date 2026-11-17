@extends('dashboard')
@section('title', 'Social Links & App Preview')
@section('stylesheets')
<style>
.lebd-social-admin{padding:18px 18px 30px}.lebd-social-admin .page-title{font-size:27px;font-weight:800;color:#111827;margin:5px 0 18px}
.lebd-social-admin .panel-card{background:#fff;border:1px solid #e2e9f4;border-radius:13px;box-shadow:0 8px 24px rgba(20,45,90,.08);overflow:hidden;margin-bottom:18px}
.lebd-social-admin .panel-head{background:linear-gradient(90deg,#e8efff,#dce7fb);padding:14px 18px;color:#101d50;font-size:19px;font-weight:800}
.lebd-social-admin .panel-body{padding:18px 20px}.lebd-social-admin label{font-weight:700;color:#1e293b}
.lebd-social-admin .form-control{height:42px;border:1px solid #d4dce8;border-radius:7px;box-shadow:none}
.lebd-social-admin .platform-row{padding:15px;border:1px solid #e4eaf4;border-radius:10px;margin-bottom:12px;background:#fbfdff}
.lebd-social-admin .platform-row h4{margin:0 0 12px;font-size:16px;font-weight:800;color:#153b78}
.lebd-social-admin .toggle{display:flex;align-items:center;gap:8px;margin-top:8px;font-weight:600}
.lebd-social-admin .toggle input{width:18px;height:18px;accent-color:#1455df}
.lebd-social-admin .preview{width:100%;max-width:260px;height:300px;border:1px dashed #a8bce0;border-radius:10px;background:#f1f6ff;display:flex;align-items:center;justify-content:center;overflow:hidden;color:#7b91b5;margin:8px 0 12px}
.lebd-social-admin .preview img{width:100%;height:100%;object-fit:contain}
.lebd-social-admin .hint{font-size:12px;color:#64748b;margin:7px 0 14px}
.lebd-social-admin .actions{display:flex;justify-content:flex-end;gap:10px;margin-top:16px}
@media(max-width:767px){.lebd-social-admin{padding:12px 8px}.lebd-social-admin .page-title{font-size:22px}.lebd-social-admin .panel-body{padding:13px}.lebd-social-admin .actions{flex-direction:column}.lebd-social-admin .actions .btn{width:100%}}
</style>
@endsection
@section('content')
<div class="lebd-social-admin">
  <h1 class="page-title"><i class="fa fa-share-alt" style="color:#1749bd"></i> Homepage Social Links & App Preview</h1>
  @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="alert alert-danger"><strong>Please check:</strong><ul style="margin:6px 0 0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

  <form class="panel-card" method="POST" action="{{ route('settings.social-section.update') }}" enctype="multipart/form-data">
    @csrf
    <div class="panel-head"><i class="fa fa-share-alt"></i> Contact & Social Media Buttons</div>
    <div class="panel-body">
      <p class="text-muted">প্রতিটি বাটনের নাম, ছোট পরিচিতি ও লিংক নিজে পরিবর্তন করতে পারবেন। লিংক ফাঁকা থাকলে বাটনটি ক্লিকযোগ্য হবে না।</p>
      @php
        $platformLabels = ['facebook'=>'Facebook','youtube'=>'YouTube','instagram'=>'Instagram','linkedin'=>'LinkedIn','whatsapp'=>'WhatsApp'];
      @endphp
      @foreach($platformLabels as $key => $platformLabel)
        @php $item = $platforms[$key] ?? []; @endphp
        <div class="platform-row">
          <h4><i class="fa fa-link"></i> {{ $platformLabel }}</h4>
          <div class="row">
            <div class="col-md-4">
              <label>Button Name</label>
              <input class="form-control" type="text" name="platforms[{{ $key }}][name]" value="{{ old('platforms.'.$key.'.name', $item['name'] ?? '') }}" maxlength="100" required>
            </div>
            <div class="col-md-4">
              <label>Subtitle / পরিচিতি</label>
              <input class="form-control" type="text" name="platforms[{{ $key }}][label]" value="{{ old('platforms.'.$key.'.label', $item['label'] ?? '') }}" maxlength="100" required>
            </div>
            <div class="col-md-4">
              <label>Button Link (URL)</label>
              <input class="form-control" type="url" name="platforms[{{ $key }}][url]" value="{{ old('platforms.'.$key.'.url', $item['url'] ?? '') }}" placeholder="https://example.com">
            </div>
          </div>
          <label class="toggle"><input type="checkbox" name="platforms[{{ $key }}][enabled]" value="1" {{ old('platforms.'.$key.'.enabled', $item['enabled'] ?? true) ? 'checked' : '' }}> Homepage-এ এই বাটন দেখাবে</label>
        </div>
      @endforeach
    </div>

    <div class="panel-head"><i class="fa fa-mobile"></i> Three Mobile App Preview Images</div>
    <div class="panel-body">
      <p class="text-muted">তিনটি ছবিই আলাদাভাবে আপলোড/পরিবর্তন করতে পারবেন। নতুন ছবি আপলোড করলে আগের ছবিটি মুছে যাবে, ফলে অপ্রয়োজনীয় Storage জমবে না।</p>
      @php $previewDefaults = ['ক্লাস ও কোর্স','স্টুডেন্ট ড্যাশবোর্ড','পরীক্ষা ও প্রস্তুতি']; @endphp
      <div class="row">
        @foreach([0,1,2] as $i)
          @php $image = $settings['images'][$i] ?? []; @endphp
          <div class="col-md-4">
            <div class="platform-row">
              <h4>Preview {{ $i + 1 }}</h4>
              <div class="preview" id="socialPreview{{ $i }}">
                @if(!empty($image['path']))
                  <img src="{{ asset($image['path']) }}" alt="Current preview {{ $i + 1 }}">
                @else
                  <i class="fa fa-mobile fa-4x"></i>
                @endif
              </div>
              <label>Upload / Change Image</label>
              <input class="form-control social-preview-input" type="file" name="preview_images[{{ $i }}]" id="socialImage{{ $i }}" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
              <p class="hint">JPG, PNG, WebP | Maximum 5 MB</p>
              <p class="hint" style="margin-top:-8px;color:#1749bd;font-weight:700">
                Recommended Image Size: 700 × 800 px (7:8 ratio)
              </p>
              <label>Image Caption</label>
              <input class="form-control" type="text" name="captions[{{ $i }}]" value="{{ old('captions.'.$i, $image['caption'] ?? $previewDefaults[$i]) }}" maxlength="120">
              @if(!empty($image['path']))
                <label class="toggle"><input type="checkbox" name="remove_preview[{{ $i }}]" value="1"> Remove this image and use default preview</label>
              @endif
            </div>
          </div>
        @endforeach
      </div>
      <div class="actions">
        <a href="{{ route('homepage') }}" target="_blank" class="btn btn-default"><i class="fa fa-external-link"></i> View Homepage</a>
        <button type="submit" class="btn btn-primary btn-lg"><i class="fa fa-save"></i> Save Social Section</button>
      </div>
    </div>
  </form>
</div>
<script>
(function(){
  document.querySelectorAll('.social-preview-input').forEach(function(input){
    input.addEventListener('change',function(){
      var file=this.files&&this.files[0];if(!file)return;
      if(!/^image\/(jpeg|png|webp)$/.test(file.type)){alert('Please select JPG, PNG or WebP image.');this.value='';return;}
      if(file.size>5*1024*1024){alert('Maximum image size is 5 MB.');this.value='';return;}
      var box=document.getElementById('socialPreview'+this.name.match(/\[(\d+)\]/)[1]);
      var reader=new FileReader();reader.onload=function(e){box.innerHTML='';var img=document.createElement('img');img.src=e.target.result;img.alt='Selected preview';box.appendChild(img);};reader.readAsDataURL(file);
    });
  });
})();
</script>
@endsection
