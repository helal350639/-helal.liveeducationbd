@extends('dashboard')

@section('title')
    {{ $item->exists ? 'Edit Success Item' : 'Add Success Item' }}
@endsection

@section('content')
<section class="content-header">
    <h1>{{ $item->exists ? 'Edit' : 'Add' }} Our Success Item</h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.home-success.index') }}"><i class="fa fa-trophy"></i> Our Success</a></li>
        <li class="active">{{ $item->exists ? 'Edit' : 'Add New' }}</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-10">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">{{ $item->exists ? 'Edit Success Item' : 'Create Success Item' }}</h3>
                </div>
                <form method="POST" enctype="multipart/form-data" action="{{ $item->exists ? route('admin.home-success.update', ['success' => $item->getKey()]) : route('admin.home-success.store') }}">
                    @csrf
                    @if($item->exists)
                        @method('PUT')
                    @endif
                    <div class="box-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <strong>Please correct these errors:</strong>
                                <ul>
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="success-type">Item Type *</label>
                            <select id="success-type" name="type" class="form-control" required>
                                <option value="info" {{ old('type', $item->type) === 'info' ? 'selected' : '' }}>Main Information Card</option>
                                <option value="photo" {{ old('type', $item->type) === 'photo' ? 'selected' : '' }}>Main Image</option>
                                <option value="feature" {{ old('type', $item->type) === 'feature' ? 'selected' : '' }}>Bottom Feature</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="success-title">Title *</label>
                            <input id="success-title" type="text" name="title" class="form-control" maxlength="180" required value="{{ old('title', $item->title) }}">
                        </div>
                        <div class="form-group">
                            <label for="success-description">Description</label>
                            <textarea id="success-description" name="description" rows="5" maxlength="2500" class="form-control">{{ old('description', $item->description) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="success-subtitle">Subtitle (Optional)</label>
                            <input id="success-subtitle" type="text" name="subtitle" maxlength="255" class="form-control" value="{{ old('subtitle', $item->subtitle) }}">
                        </div>
                        <div class="form-group">
                            <label for="success-icon">Font Awesome Icon</label>
                            <input id="success-icon" type="text" name="icon" maxlength="100" class="form-control" placeholder="fa-trophy" value="{{ old('icon', $item->icon) }}">
                            <small class="text-muted">Examples: fa-trophy, fa-book, fa-line-chart, fa-users. No HTML.</small>
                        </div>
                        <div class="form-group">
                            <label for="success-image">Image — JPG / PNG / WEBP, up to 4 MB (1672 X 941 )</label>
                            <input id="success-image" type="file" name="image" accept="image/png,image/jpeg,image/webp" class="form-control">
                            @if(!empty($item->image_path))
                                <div style="padding-top:10px">
                                    <img src="{{ asset($item->image_path) }}" alt="Existing success photo" style="max-width:300px;max-height:180px;object-fit:contain;border:1px solid #ddd;border-radius:8px;padding:4px">
                                </div>
                                <p class="help-block">Choose a new file to replace the existing picture.</p>
                            @endif
                        </div>
                        <div class="form-group">
                            <label for="success-sort">Sort Order</label>
                            <input id="success-sort" type="number" name="sort_order" min="0" max="99999" class="form-control" value="{{ old('sort_order', $item->sort_order ?? 0) }}">
                        </div>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}>
                                Active / Show on Homepage
                            </label>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Changes</button>
                        <a class="btn btn-default" href="{{ route('admin.home-success.index') }}">Cancel</a>
                    </div>
                </form>
                @if($item->exists && !empty($item->image_path))
                    <div class="box-footer">
                        <form action="{{ route('admin.home-success.delete-photo', ['success' => $item->getKey()]) }}" method="POST" onsubmit="return confirm('Remove this picture?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-warning"><i class="fa fa-trash"></i> Delete Photo Only</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
