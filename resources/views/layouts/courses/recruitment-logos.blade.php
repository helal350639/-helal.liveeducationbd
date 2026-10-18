@extends('dashboard')
@section('title','Government Logo Library')
@section('content')
<section class="content-header"><h1>Government Recruitment Logo Library</h1></section>
<section class="content"><div class="box box-primary"><div class="box-header with-border"><h3 class="box-title">Add Verified Official Logo</h3></div>
<form action="{{route('course.recruitment-logos.store')}}" method="post" enctype="multipart/form-data">@csrf<div class="box-body row" style="margin:0">
<div class="col-md-4 form-group"><label>Full organization name</label><input name="name" required class="form-control"></div>
<div class="col-md-2 form-group"><label>Short name</label><input name="short_name" required class="form-control"></div>
<div class="col-md-3 form-group"><label>Official website (source)</label><input type="url" name="website" class="form-control"></div>
<div class="col-md-3 form-group"><label>Official Logo Image</label><input type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="form-control"></div>
<div class="col-md-3 form-group"><label>Status</label><select name="status" class="form-control"><option>Active</option><option>Inactive</option></select></div>
<div class="col-md-3 form-group"><label>Sort order</label><input type="number" name="sort_order" value="0" min="0" class="form-control"></div>
<div class="col-md-6 form-group"><label>&nbsp;</label><button class="btn btn-success form-control" type="submit">Add Logo</button></div>
</div></form></div>
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{$error}}</div>@endforeach</div>@endif
<div class="box box-default"><div class="box-header with-border"><h3 class="box-title">Organizations · Upload only genuine official images</h3></div>
<div class="box-body table-responsive"><table class="table table-bordered table-striped"><thead><tr><th>Logo</th><th>Organization</th><th>Short Name</th><th>Website</th><th>Update</th></tr></thead><tbody>
@foreach($logos as $logo)<tr><td style="width:95px">@if($logo->logo_path)<img src="{{asset($logo->logo_path)}}" alt="{{$logo->short_name}}" width="65" height="65" style="object-fit:contain">@else<small>Not uploaded</small>@endif</td>
<td colspan="4"><form action="{{route('course.recruitment-logos.update',$logo->id)}}" method="post" enctype="multipart/form-data">@csrf @method('PUT')
<div class="row"><div class="col-md-3"><input name="name" class="form-control" value="{{$logo->name}}" required></div><div class="col-md-1"><input name="short_name" class="form-control" value="{{$logo->short_name}}" required></div>
<div class="col-md-3"><input name="website" type="url" class="form-control" value="{{$logo->website}}"></div><div class="col-md-2"><input type="file" class="form-control" name="logo" accept="image/png,image/jpeg,image/webp"></div>
<div class="col-md-1"><input type="number" name="sort_order" class="form-control" value="{{$logo->sort_order}}"></div>
<div class="col-md-1"><select name="status" class="form-control"><option value="Active" {{$logo->status==='Active'?'selected':''}}>Active</option><option value="Inactive" {{$logo->status==='Inactive'?'selected':''}}>Inactive</option></select></div>
<div class="col-md-1"><button type="submit" class="btn btn-primary">Save</button></div></div></form></td></tr>@endforeach
</tbody></table>{{$logos->links()}}</div></div></section>
@endsection
