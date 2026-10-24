<style>
.emcq-wrap{max-width:1300px;margin:15px auto 38px;padding:0 16px;font-size:14px;color:#183456}
.emcq-panel{background:#fff;border:1px solid #d9e6f6;border-radius:14px;padding:20px;margin-bottom:17px;box-shadow:0 5px 17px #153e6d0b}
.emcq-h{font-size:23px;font-weight:800;color:#113d7a;margin:2px 0 17px}
.emcq-row{display:flex;gap:12px;flex-wrap:wrap;align-items:end}.emcq-field{flex:1 1 185px;min-width:150px}
.emcq-field label{display:block;font-size:13px;font-weight:700;color:#365575;margin-bottom:5px}.emcq-field .form-control{height:40px;font-size:13px}
.emcq-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}.emcq-actions .btn{font-size:13px;padding:9px 12px;border-radius:6px}
.emcq-card{border:1px solid #dfebf8;padding:15px;border-radius:10px;margin:10px 0;background:#fbfdff}.emcq-meta{font-size:13px;color:#607998}
.emcq-q{font-size:16px;line-height:1.85;overflow-wrap:anywhere;color:#1c304c}.emcq-q img,.emcq-opt img{max-width:180px;max-height:120px;object-fit:contain}
.emcq-breadcrumb{display:flex;gap:9px;flex-wrap:wrap;margin:7px 0 17px}.emcq-breadcrumb a{display:inline-flex;align-items:center;padding:9px 17px;border-radius:8px;border:1px solid #cfe2fc;background:#fff;color:#1263ba!important;font-weight:800;text-decoration:none!important;font-size:14px}
.emcq-breadcrumb a.active{background:#0875df;color:#fff!important;border-color:#0875df}.emcq-msg{padding:12px;background:#e7fbf0;color:#087747;border-radius:8px;margin-bottom:12px}
.emcq-qnum{font-size:15px;font-weight:850;color:#076acc;margin:0 0 7px}.emcq-opts{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;margin-top:12px}
.emcq-opt{padding:11px 12px;border:1px solid #e3ebf4;border-radius:9px;background:white;font-size:14px;line-height:1.65;overflow-wrap:anywhere}
.emcq-opt.correct{background:#e4f8ee;border-color:#b0e9c9;color:#076b42}.emcq-topline{display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center}
.emcq-tree{display:flex;flex-wrap:wrap;gap:8px;margin:10px 0}.emcq-tree a{border-radius:7px;border:1px solid #caddf3;background:#edf6ff;color:#0a559d;padding:7px 10px;text-decoration:none;font-size:13px;font-weight:700}.emcq-tree a.active{background:#0b73d3;color:#fff}
.emcq-count{display:inline-block;margin-left:6px;padding:3px 8px;background:#e6f2ff;color:#155eae;border-radius:7px;font-size:12px}
@media(max-width:700px){.emcq-wrap{padding:0 9px}.emcq-panel{padding:13px}.emcq-field{flex:1 1 100%}.emcq-h{font-size:20px}.emcq-opts{grid-template-columns:1fr}.emcq-q{font-size:15px}}
</style>
<div class="emcq-wrap">
@if(session('success'))<div class="emcq-msg">{{session('success')}}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $err)<div>{{$err}}</div>@endforeach</div>@endif
<nav class="emcq-breadcrumb" aria-label="MCQ for Exam Categories">
<a class="{{ $kind === 'department' ? 'active' : '' }}" href="{{route('admin.exam-mcq.index',['kind'=>'department'])}}"><i class="fa fa-building"></i>&nbsp; Department</a>
<a class="{{ $kind === 'non-department' ? 'active' : '' }}" href="{{route('admin.exam-mcq.index',['kind'=>'non-department'])}}"><i class="fa fa-folder-open"></i>&nbsp; Non-Department</a>
</nav>
