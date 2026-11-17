@extends('dashboard')
@section('title', 'Add Notice')

@section('stylesheets')
<link href="/assets/summernote/summernote.min.css" rel="stylesheet">
<style>
.lebd-notice-admin{padding:18px 18px 30px;color:#172554}
.lebd-notice-admin *{box-sizing:border-box}
.lebd-notice-admin .page-heading{display:flex;align-items:center;gap:12px;margin:0 0 18px;color:#101828;font-size:29px;font-weight:800}
.lebd-notice-admin .page-heading i{color:#1749c5;font-size:28px}
.lebd-notice-admin .notice-form-shell,.lebd-notice-admin .notice-list-shell{background:#fff;border:1px solid #e0e7f2;border-radius:13px;box-shadow:0 7px 22px rgba(25,55,105,.09);padding:18px;margin-bottom:22px}
.lebd-notice-admin .notice-layout{display:grid;grid-template-columns:minmax(0,1.8fr) minmax(300px,.9fr);gap:24px;align-items:stretch}
.lebd-notice-admin .notice-card{border:1px solid #dce5f5;border-radius:12px;overflow:hidden;background:#fff;min-width:0}
.lebd-notice-admin .notice-card-heading{padding:14px 18px;background:linear-gradient(100deg,#e6edfc,#eaf0fb);color:#0e1b53;font-size:20px;font-weight:800;border-bottom:1px solid #dce5f5;display:flex;align-items:center;gap:12px}
.lebd-notice-admin .notice-card-heading i{color:#123eb4}
.lebd-notice-admin .notice-card-body{padding:19px 20px}
.lebd-notice-admin label{display:block;font-weight:700;color:#202938;margin-bottom:8px}
.lebd-notice-admin .required{color:#ef4444}
.lebd-notice-admin .form-control{height:44px;border-radius:8px;border:1px solid #cbd5e1;box-shadow:none;font-size:15px;color:#172033;padding:9px 13px}
.lebd-notice-admin textarea.form-control{height:auto}
.lebd-notice-admin .form-control:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.10)}
.lebd-notice-admin .form-group{margin-bottom:20px}
.lebd-notice-admin .type-options{display:flex;flex-wrap:wrap;gap:27px;padding:7px 0 2px}
.lebd-notice-admin .type-options label{font-weight:600;display:flex;align-items:center;gap:9px;cursor:pointer;margin:0;color:#111827}
.lebd-notice-admin .type-options input{width:21px;height:21px;margin:0;accent-color:#164bd8}
.lebd-notice-admin .image-upload-box{border:1px dashed #c5d4ed;border-radius:10px;background:#f8fbff;padding:14px;display:flex;align-items:center;gap:18px;flex-wrap:wrap;min-height:116px}
.lebd-notice-admin .image-preview{width:158px;height:88px;border-radius:8px;background:#e5edfb;object-fit:contain;border:1px solid #d5e0f2}
.lebd-notice-admin .upload-actions{min-width:220px;flex:1}
.lebd-notice-admin .upload-help{font-size:12px;color:#475569;line-height:1.75;margin-top:8px}
.lebd-notice-admin .upload-help strong{color:#172554}
.lebd-notice-admin .btn-choose{display:inline-block;background:#2fa84f;color:#fff;border:0;border-radius:7px;padding:10px 15px;font-weight:700;cursor:pointer;margin:0 7px 0 0}
.lebd-notice-admin .btn-remove-image{background:#e32636;color:#fff;border:0;border-radius:7px;padding:10px 14px;font-weight:700}
.lebd-notice-admin .file-input{display:none}
.lebd-notice-admin .url-row{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.lebd-notice-admin .url-field{flex:1;min-width:220px;position:relative}
.lebd-notice-admin .url-field .url-icon{position:absolute;left:13px;top:13px;color:#64748b}
.lebd-notice-admin .url-field input{padding-left:40px}
.lebd-notice-admin .new-tab-label{display:flex;align-items:center;gap:8px;font-weight:500;white-space:nowrap;margin:0}
.lebd-notice-admin .new-tab-label input{width:18px;height:18px;margin:0;accent-color:#164bd8}
.lebd-notice-admin .display-card-body{padding:18px 20px}
.lebd-notice-admin .display-row{padding:2px 0 17px;margin-bottom:17px;border-bottom:1px solid #e8edf5}
.lebd-notice-admin .display-row:last-child{border-bottom:0;margin-bottom:0;padding-bottom:0}
.lebd-notice-admin .display-label{font-size:16px;font-weight:800;color:#111827;margin-bottom:10px;display:flex;align-items:center;gap:9px}
.lebd-notice-admin .display-label i{color:#142e73}
.lebd-notice-admin .switch-line{display:flex;align-items:center;justify-content:space-between;gap:12px}
.lebd-notice-admin .switch-line label{margin:0;font-size:16px;font-weight:800;color:#111827}
.lebd-notice-admin .switch-control{position:relative;display:inline-block;width:70px;height:34px;flex:0 0 70px;margin:0}
.lebd-notice-admin .switch-control input{opacity:0;width:0;height:0}
.lebd-notice-admin .switch-slider{position:absolute;inset:0;border-radius:30px;background:#cbd5e1;cursor:pointer;transition:.2s}
.lebd-notice-admin .switch-slider:before{content:"";position:absolute;width:26px;height:26px;left:4px;top:4px;background:#fff;border-radius:50%;transition:.2s;box-shadow:0 1px 4px rgba(0,0,0,.2)}
.lebd-notice-admin .switch-control input:checked+.switch-slider{background:#164bd8}
.lebd-notice-admin .switch-control input:checked+.switch-slider:before{transform:translateX(36px)}
.lebd-notice-admin .switch-state{font-size:13px;font-weight:800;color:#fff;position:absolute;left:10px;top:8px;pointer-events:none}
.lebd-notice-admin .switch-control input:not(:checked)+.switch-slider .switch-state{left:auto;right:9px;color:#fff}
.lebd-notice-admin .display-card-body .form-group{margin-bottom:17px}
.lebd-notice-admin .display-card-body .form-group:last-child{margin-bottom:0}
.lebd-notice-admin .field-hint{display:block;color:#64748b;font-size:12px;line-height:1.55;margin-top:6px}
.lebd-notice-admin .form-actions{display:flex;justify-content:flex-end;gap:10px;border-top:1px solid #e6edf7;margin-top:18px;padding-top:17px}
.lebd-notice-admin .btn-save{background:#155eef;color:#fff;border:0;border-radius:8px;padding:12px 21px;font-weight:800;font-size:15px}
.lebd-notice-admin .btn-save:hover{background:#1049c4;color:#fff}
.lebd-notice-admin .btn-cancel{background:#64748b;color:#fff;border:0;border-radius:8px;padding:12px 19px;font-weight:700}
.lebd-notice-admin .notice-table{width:100%;min-width:760px;margin:0}
.lebd-notice-admin .notice-table th{background:#edf3ff;color:#18356d;font-weight:800}
.lebd-notice-admin .notice-table td,.lebd-notice-admin .notice-table th{padding:11px 12px;vertical-align:middle;border-bottom:1px solid #e6edf7}
.lebd-notice-admin .notice-thumb{width:62px;height:46px;object-fit:cover;border-radius:6px;border:1px solid #dbe4f2}
.lebd-notice-admin .status-pill{display:inline-block;padding:4px 9px;border-radius:20px;font-size:11px;font-weight:800;background:#dcfce7;color:#15803d}
.lebd-notice-admin .status-pill.off{background:#fee2e2;color:#b91c1c}
.lebd-notice-admin .list-heading{display:flex;align-items:center;justify-content:space-between;gap:10px}
@media(max-width:991px){.lebd-notice-admin .notice-layout{grid-template-columns:1fr}.lebd-notice-admin .display-card-body{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px 22px}.lebd-notice-admin .display-row{margin:0;padding:0 0 14px}.lebd-notice-admin .display-row:last-child{padding:0}}
@media(max-width:767px){.lebd-notice-admin{padding:12px 7px 24px}.lebd-notice-admin .page-heading{font-size:22px;margin-bottom:13px}.lebd-notice-admin .page-heading i{font-size:22px}.lebd-notice-admin .notice-form-shell,.lebd-notice-admin .notice-list-shell{padding:10px;border-radius:10px}.lebd-notice-admin .notice-layout{gap:13px}.lebd-notice-admin .notice-card-heading{font-size:16px;padding:11px 13px}.lebd-notice-admin .notice-card-body{padding:13px}.lebd-notice-admin .display-card-body{display:block;padding:13px}.lebd-notice-admin .display-row{margin-bottom:14px;padding-bottom:13px}.lebd-notice-admin .type-options{gap:13px 18px}.lebd-notice-admin .type-options label{font-size:13px}.lebd-notice-admin .image-preview{width:112px;height:78px}.lebd-notice-admin .image-upload-box{gap:11px;padding:10px}.lebd-notice-admin .upload-actions{min-width:0}.lebd-notice-admin .url-field{min-width:100%}.lebd-notice-admin .new-tab-label{white-space:normal}.lebd-notice-admin .form-actions{position:static}.lebd-notice-admin .btn-save,.lebd-notice-admin .btn-cancel{padding:10px 14px;font-size:13px}}
</style>
@endsection

@section('content')
@php
    $editing = $notice ?? null;
    $notices = $notices ?? collect();
    $formAction = $editing ? route('notice.update', $editing->id) : route('notice.store');
    $type = old('notice_type', $editing->notice_type ?? 'both');
@endphp
<section class="content lebd-notice-admin">
    <h1 class="page-heading"><i class="fa fa-bullhorn"></i> {{ $editing ? 'Edit Notice' : 'Add Notice' }}</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>দয়া করে নিচের তথ্যগুলো ঠিক করুন:</strong>
            <ul style="margin:7px 0 0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="notice-form-shell">
        <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data">
            @csrf
            @if($editing) @method('PUT') @endif
            <div class="notice-layout">
                <div class="notice-card">
                    <div class="notice-card-heading"><i class="fa fa-puzzle-piece"></i> Notice Information</div>
                    <div class="notice-card-body">
                        <div class="form-group">
                            <label for="title">Title <span class="required">*</span></label>
                            <input id="title" type="text" name="title" class="form-control" required maxlength="255"
                                   value="{{ old('title', $editing->title ?? '') }}" placeholder="Enter notice title...">
                        </div>

                        <div class="form-group">
                            <label>Notice Type <span class="required">*</span></label>
                            <div class="type-options">
                                <label><input type="radio" name="notice_type" value="text" {{ $type === 'text' ? 'checked' : '' }}> Text</label>
                                <label><input type="radio" name="notice_type" value="image" {{ $type === 'image' ? 'checked' : '' }}> Image Only</label>
                                <label><input type="radio" name="notice_type" value="both" {{ $type === 'both' ? 'checked' : '' }}> Text + Image</label>
                            </div>
                        </div>

                        <div class="form-group" id="noticeDetailsGroup">
                            <label for="details">Notice Details</label>
                            <textarea id="details" name="details" class="form-control editor" rows="6" placeholder="Write notice details here...">{{ old('details', $editing->details ?? '') }}</textarea>
                        </div>

                        <div class="form-group" id="noticeImageGroup">
                            <label>Upload Image</label>
                            <div class="image-upload-box">
                                <img id="noticeImagePreview" class="image-preview"
                                     src="{{ !empty($editing?->image) ? asset('storage/'.$editing->image) : 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22150%22 height=%22100%22 viewBox=%220 0 150 100%22%3E%3Crect width=%22150%22 height=%22100%22 rx=%2210%22 fill=%22%23e5edfb%22/%3E%3Cpath d=%22M30 75l28-30 18 18 14-15 30 27z%22 fill=%22%2393b4e8%22/%3E%3Ccircle cx=%2253%22 cy=%2230%22 r=%229%22 fill=%22%2393b4e8%22/%3E%3C/svg%3E' }}"
                                     alt="Notice image preview">
                                <div class="upload-actions">
                                    <label for="noticeImageInput" class="btn-choose"><i class="fa fa-upload"></i> Choose Image</label>
                                    <button type="button" id="removeImagePreview" class="btn-remove-image"><i class="fa fa-trash"></i> Remove</button>
                                    <input type="file" name="image" id="noticeImageInput" class="file-input" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                    <div class="upload-help">
                                        <strong>Recommended: 1200 × 800 pixels</strong><br>
                                        Maximum file size: <strong>5 MB</strong><br>
                                        Supported formats: JPG, JPEG, PNG, WebP
                                    </div>
                                    @if(!empty($editing?->image))
                                        <label style="font-weight:500;margin-top:8px"><input type="checkbox" name="remove_image" value="1"> Remove current saved image</label>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom:0">
                            <label for="url"><i class="fa fa-link"></i> URL Link (Optional)</label>
                            <div class="url-row">
                                <div class="url-field">
                                    <i class="fa fa-link url-icon"></i>
                                    <input id="url" type="url" name="url" class="form-control" style="width:100%" value="{{ old('url', $editing->url ?? '') }}" placeholder="https://example.com">
                                </div>
                                <label class="new-tab-label"><input type="checkbox" name="open_new_tab" value="1" {{ old('open_new_tab', $editing->open_new_tab ?? true) ? 'checked' : '' }}> Open link in new tab</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="notice-card">
                    <div class="notice-card-heading"><i class="fa fa-cog"></i> Display Settings</div>
                    <div class="display-card-body">
                        <div class="display-row">
                            <div class="switch-line">
                                <label for="show_on_website">Show on Website <span class="required">*</span></label>
                                <label class="switch-control" aria-label="Show on Website">
                                    <input id="show_on_website" type="checkbox" name="show_on_website" value="1" {{ old('show_on_website', $editing->show_on_website ?? true) ? 'checked' : '' }}>
                                    <span class="switch-slider"><span class="switch-state">Yes</span></span>
                                </label>
                            </div>
                        </div>

                        <div class="display-row">
                            <div class="display-label"><i class="fa fa-clock-o"></i> Display Duration (Auto Close)</div>
                            <select id="auto_close_seconds" name="auto_close_seconds" class="form-control">
                                @foreach([1,2,3,5,10,15,30,60] as $seconds)
                                    <option value="{{ $seconds }}" {{ (int)old('auto_close_seconds', $editing->auto_close_seconds ?? 2) === $seconds ? 'selected' : '' }}>{{ $seconds }} {{ $seconds === 1 ? 'Second' : 'Seconds' }}</option>
                                @endforeach
                            </select>
                            <small class="field-hint">Close button becomes available after the selected time.</small>
                        </div>

                        <div class="display-row">
                            <div class="display-label"><i class="fa fa-calendar"></i> Validity Period</div>
                            <div class="form-group">
                                <label for="start_at">Start Date</label>
                                <input id="start_at" type="datetime-local" name="start_at" class="form-control"
                                       value="{{ old('start_at', $editing && $editing->start_at ? $editing->start_at->format('Y-m-d\\TH:i') : '') }}">
                            </div>
                            <div class="form-group" style="margin-bottom:0">
                                <label for="end_at">End Date</label>
                                <input id="end_at" type="datetime-local" name="end_at" class="form-control"
                                       value="{{ old('end_at', $editing && $editing->end_at ? $editing->end_at->format('Y-m-d\\TH:i') : '') }}">
                                <small class="field-hint">After the end date, the notice image is automatically deleted from storage and its database path is cleared.</small>
                            </div>
                        </div>

                        <div class="display-row">
                            <div class="display-label"><i class="fa fa-circle" style="color:#22b34a"></i> Status</div>
                            <select id="status" name="status" class="form-control" required>
                                <option value="Published" {{ old('status', $editing->status ?? 'Published') === 'Published' ? 'selected' : '' }}>Published</option>
                                <option value="Unpublished" {{ old('status', $editing->status ?? '') === 'Unpublished' ? 'selected' : '' }}>Unpublished</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                @if($editing)
                    <a href="{{ route('noitice.edit') }}" class="btn btn-cancel"><i class="fa fa-times"></i> Cancel</a>
                @endif
                <button type="submit" class="btn btn-save"><i class="fa fa-save"></i> {{ $editing ? 'Update Notice' : 'Save Notice' }}</button>
            </div>
        </form>
    </div>

    <div class="notice-list-shell">
        <div class="list-heading" style="margin-bottom:12px">
            <h3 style="margin:0;color:#12265a;font-weight:800"><i class="fa fa-list"></i> Existing Notices</h3>
            <span class="badge" style="background:#1d4ed8;padding:6px 10px">{{ $notices->count() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table notice-table">
                <thead><tr><th>Title</th><th>Type</th><th>Image</th><th>Validity</th><th>Status</th><th width="150">Action</th></tr></thead>
                <tbody>
                @forelse($notices as $item)
                    <tr>
                        <td><strong>{{ $item->title }}</strong><br><small class="text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($item->details), 75) }}</small></td>
                        <td>{{ ucfirst($item->notice_type ?: 'text') }}</td>
                        <td>
                            @if($item->image)
                                <img class="notice-thumb" src="{{ asset('storage/'.$item->image) }}" alt="Notice">
                            @else
                                <span class="text-muted">No image</span>
                            @endif
                        </td>
                        <td><small>From: {{ $item->start_at ? $item->start_at->format('d M Y, h:i A') : 'Immediately' }}<br>To: {{ $item->end_at ? $item->end_at->format('d M Y, h:i A') : 'No end date' }}</small></td>
                        <td><span class="status-pill {{ strtolower($item->status) !== 'published' ? 'off' : '' }}">{{ $item->status }}</span></td>
                        <td>
                            <a href="{{ route('noitice.edit') }}?edit={{ $item->id }}" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> Edit</a>
                            <form action="{{ route('notice.destroy', $item->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this notice?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted" style="padding:25px">এখনো কোনো Notice তৈরি করা হয়নি।</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script src="/assets/summernote/summernote.min.js"></script>
<script>
$(function () {
    $('.editor').summernote({height: 170});
    var detailsGroup = document.getElementById('noticeDetailsGroup');
    var imageGroup = document.getElementById('noticeImageGroup');
    function updateType() {
        var selected = document.querySelector('input[name="notice_type"]:checked');
        var type = selected ? selected.value : 'both';
        detailsGroup.style.display = type === 'image' ? 'none' : '';
        imageGroup.style.display = type === 'text' ? 'none' : '';
    }
    document.querySelectorAll('input[name="notice_type"]').forEach(function (radio) {
        radio.addEventListener('change', updateType);
    });
    updateType();

    var input = document.getElementById('noticeImageInput');
    var preview = document.getElementById('noticeImagePreview');
    var removePreview = document.getElementById('removeImagePreview');
    var defaultPreview = preview ? preview.src : '';
    if (input && preview) {
        input.addEventListener('change', function () {
            if (!this.files || !this.files[0]) return;
            var file = this.files[0];
            if (file.size > 5 * 1024 * 1024) {
                alert('ছবির সাইজ সর্বোচ্চ 5 MB হতে পারবে।');
                this.value = '';
                return;
            }
            preview.src = URL.createObjectURL(file);
        });
    }
    if (removePreview && input && preview) {
        removePreview.addEventListener('click', function () {
            input.value = '';
            preview.src = defaultPreview;
        });
    }
    var websiteToggle = document.getElementById('show_on_website');
    if (websiteToggle) {
        websiteToggle.addEventListener('change', function () {
            var state = this.parentElement.querySelector('.switch-state');
            if (state) state.textContent = this.checked ? 'Yes' : 'No';
        });
        var state = websiteToggle.parentElement.querySelector('.switch-state');
        if (state) state.textContent = websiteToggle.checked ? 'Yes' : 'No';
    }
});
</script>
@endsection
