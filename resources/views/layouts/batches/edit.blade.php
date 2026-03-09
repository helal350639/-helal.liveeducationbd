@php
$department_ids = $teacher_ids = [];
foreach($batch->departments as $val)
{
    array_push($department_ids, $val->id);
}
foreach($batch->teachers as $val)
{
    array_push($teacher_ids, $val->id);
}
@endphp

@extends('dashboard')
@section('title', 'Edit Batch')
@section('stylesheets')
<link href="/assets/summernote/summernote.min.css" rel="stylesheet">
@endsection
@section('content')
<section class="content-header">
  <h1>Edit Batch</h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Batches</a></li>
    <li class="active">Edit Batch</li>
</ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row"> <!-- left column -->
    <div class="col-md-10"> <!-- general form elements -->
      <div class="box box-primary">
        <div class="box-header with-border">
            <h3 style="color: #800" class="box-title">Batch Details</h3>
        </div>
        <form action="{{route('batch.update', $batch)}}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="box-body">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Course Name (*)</label>
                        <select class="form-control select2" name="course_id" id="course_id" required  onchange="getDepartments(this)">
                            <option value="">Select One</option>
                            @foreach($courses as $val)
                            <option value="{{$val->id}}" {{$batch->course_id == $val->id? 'selected':''}}>{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name">Batch Title</label>
                        <input type="text" class="form-control" name="name" id="name" required value="{{$batch->name}}">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="subtitle">Batch Sub-Title</label>
                        <input type="text" class="form-control" name="subtitle" id="subtitle" required value="{{$batch->subtitle}}">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name">Department Name (*)</label>
                        <select class="form-control select2" multiple name="department_id[]" id="department_id" required>
                            @foreach($departments as $val)
                            <option value="{{$val->id}}"{{in_array($val->id, $department_ids)? 'selected':''}}>{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="price">Price</label>
                        <input type="number" class="form-control" name="price" id="price" required value="{{$batch->price}}" step="0.01" placeholder="0.00 Tk." onkeyup="calcPrice()">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="fee">Extra Fee</label>
                        <input type="number" class="form-control" name="fee" id="fee" required value="{{$batch->fee}}" step="0.01" placeholder="0.00 Tk." onkeyup="calcPrice()">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="discount">Discount</label>
                        <input type="number" class="form-control" name="discount" id="discount" value="{{$batch->discount}}" step="0.01" placeholder="0.00 Tk." onkeyup="calcPrice()">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="reference_discount">Reference Discount</label>
                        <input type="number" class="form-control" name="reference_discount" id="reference_discount" value="{{$batch->reference_discount}}" step="0.01" placeholder="0.00 Tk." onkeyup="calcPrice()">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="selling_price">Selling Price</label>
                        <input type="number" class="form-control" name="selling_price" id="selling_price" value="{{$batch->selling_price}}" step="0.01" placeholder="0.00 Tk.">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="referrer_bonus">Referrer Bonus</label>
                        <input type="number" class="form-control" name="referrer_bonus" id="referrer_bonus" required value="{{$batch->referrer_bonus}}" step="0.01" placeholder="0.00 Tk." onkeyup="calcPrice()">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="net_price">Net Price</label>
                        <input type="number" class="form-control" name="net_price" id="net_price" value="{{$batch->net_price}}" step="0.01" placeholder="0.00 Tk.">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="extra_fee_note">Note for Extra Fee</label>
                        <input type="text" class="form-control" name="extra_fee_note" id="extra_fee_note" value="{{$batch->extra_fee_note}}" placeholder="Write a why added the extra fee.">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="start_at">Start Date</label>
                        <input type="date" class="form-control" name="start_at" id="start_at" value="{{$batch->start_at}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="end_at">End Date</label>
                        <input type="date" class="form-control" name="end_at" id="end_at" value="{{$batch->end_at}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="reg_end_at">Registration Time</label>
                        <input type="datetime-local" class="form-control" name="reg_end_at" id="reg_end_at" value="{{$batch->reg_end_at}}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="offer_end_at">Offer Time</label>
                        <input type="datetime-local" class="form-control" name="offer_end_at" id="offer_end_at" value="{{$batch->offer_end_at}}">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="teacher_id">Teachers (*)</label>
                        <select class="form-control select2" multiple name="teacher_id[]" id="teacher_id" required>
                            @foreach($teachers as $val)
                            <option value="{{$val->id}}"{{in_array($val->id, $teacher_ids)? 'selected':''}}>{{$val->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="extra_student">Extra Students (Optional)</label>
                        <input class="form-control" type="number" name="extra_student" value="{{$batch->extra_student}}">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                      <label><input type="checkbox" name="status" id="status" value="{{$batch->status}}" style="width: 15px; height:15px" {{$batch->status == 'Active' ?'checked':''}}> <span style="margin-top:-10px">Status</span></label>

                        &nbsp;
                    
                      <label>
                        <input type="checkbox" name="web" id="web" value="{{$batch->web}}" style="width: 15px; height:15px" {{$batch->web == 'Yes' ? 'checked':''}}> <span style="margin-top:-10px">Web</span>
                      </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="subtitle">Youtube Video URL</label>
                        <input type="url" class="form-control" name="video" id="video" value=""/>
                    </div>
                    @if($batch->video)
                    <p><iframe class="responsive-iframe" style="max-width: 200px; border:5px solid #fff" src="https://www.youtube.com/embed/{{$batch->video}}" allowfullscreen></iframe></p>
                    @endif
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="banner">Banner</label>
                        <input type="file" class="form-control" name="banner" id="banner" onchange="showImg(this)" />
                        <p style="color:red;padding:5px 0">Image size: 4:6 / 400px X 600px</p>
                    </div>
                    <a target="_blank" href="{{$batch->banner}}">
                        <img src="{{$batch->banner}}" alt="" style="max-width:200px">
                    </a>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="short">Short Description</label>
                        <textarea class="form-control editor" name="short" id="short" rows="5">{{$batch->short}}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="details">Details</label>
                        <textarea class="form-control editor" name="details" id="details" rows="5">{{$batch->details}}</textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="what_is">What is in this course</label>
                        <textarea class="form-control editor" name="what_is" id="what_is" rows="5">{{$batch->what_is}}</textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="learn">What you will learn by doing the course</label>
                        <textarea class="form-control editor" name="learn" id="learn" rows="5">{{$batch->learn}}</textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="routine">Class routine</label>
                        <textarea class="form-control editor" name="routine" id="routine" rows="5">{{$batch->routine}}</textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="faq">Frequently Ask Question</label>
                        <textarea class="form-control editor" name="faq" id="faq" rows="5">{{$batch->faq}}</textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="refund">Re-fund Policy</label>
                        <textarea class="form-control editor" name="refund" id="refund" rows="5">{{$batch->refund}}</textarea>
                    </div>
                </div>
            </div> <!-- /.box body -->
            <div class="box-footer">
                <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> Save</button>
            </div>
        </form>
    </div> <!-- /.box -->
    </div> <!--/.col (left) -->
</div> <!-- /.row -->
</section> <!-- /.content -->
@endsection

@section('scripts')
<script src="/assets/summernote/summernote.min.js"></script>
<script type="text/javascript">
//this script for text editor
    $(document).ready(function() {
        $('.editor').summernote({
            height: 150
        });
    });
</script>
<script type="text/javascript">
    // on upload show image
    function showImg(e)
     {
         if(e.parentNode.firstElementChild.tagName == 'IMG')
         {
             e.parentNode.firstElementChild.src = window.URL.createObjectURL(e.files[0]);
         }
         else
         {
             let img = document.createElement('img');
             img.src = window.URL.createObjectURL(e.files[0]);
             img.setAttribute('style', 'width:100%;max-width:400px; margin-top:15px');
             img.alt = '';
             e.parentNode.appendChild(img);
         }
         
     }
function calcPrice()
{
    let price = document.getElementById('price');
    let fee = document.getElementById('fee');
    let discount = document.getElementById('discount');
    let referrer_bonus = document.getElementById('referrer_bonus');
    let reference_discount = document.getElementById('reference_discount');
    let selling_price = document.getElementById('selling_price');
    let net_price = document.getElementById('net_price');

    selling_price.value = (Number(price.value) + Number(fee.value)) - (Number(discount.value) + Number(reference_discount.value)).toFixed(2);

    net_price.value = Number(selling_price.value) - Number(referrer_bonus.value);
}

    // get department on select the course
    function getDepartments(e)
{
    let ids = Array.from(e.selectedOptions).map(({value}) => value);

    $.ajax({
        type: 'GET', //THIS NEEDS TO BE GET
        url: '/get_departments/' + ids,
        success: function (data) {

            var obj = JSON.parse(JSON.stringify(data));
            var options = '';

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
        },
        error: function(data) { 
                console.log('data error');
        }
    });
}

$(function(){$('.select2').select2()});
</script>
@endsection