@php
use \App\Http\Controllers\SourceCtrl;
$source = new SourceCtrl;
@endphp

@extends('dashboard')
@section('title', 'Batch Details')
@section('content')
  <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Batch Details</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Batches</a></li>
        <li class="active">Details</li>
      </ol>    
    </section>

    <!-- Main content -->
  <section class="content">
    <div class="row"><!-- left column -->
      <div class="col-md-8"><!-- general form elements -->
        <div class="box box-primary">
          <div class="box-header with-border">
            <h4 class="box-title">Batch Information</h4>
          </div>
          <div class="col-md-12 text-right toolbar-icon">
            <button id="text" title="Copy URL" class="btn btn-default btn-sm" onclick="handleClick();" href="{{route('home.course.show', $batch->id)}}"><i class="fa fa-copy"></i> Copy URL </button>
            <a href="{{route('batch.create')}}" title="Add New" class="label label-info"><i class="fa fa-plus"></i></a>
            <a href="{{route('batch.index')}}" title="List" class="label label-success"><i class="fa fa-list"></i></a>
            <a href="{{route('batch.edit', $batch->id)}}" class="label label-warning" title="Edit this"><i class="fa fa-edit"></i></a>

            <a href="{{route('batch.sms', $batch->id)}}" class="label label-primary" title="Send SMS"><i class="fa fa-envelope"></i></a>
            
          </div>
          <div class="col-md-12">
            <table class="table">
              <tbody>
                <tr>
                  <th style="width: 200px;">Title:</th>
                  <td>{{$batch->name}}</td>
                </tr>
                <tr>
                  <th>Sub-Title:</th>
                  <td>{{$batch->subtitle}}</td>
                </tr>
                <tr>
                  <th>Price: Tk.</th>
                  <td><b>{{$batch->price}}</b></td>
                </tr>
                <tr>
                  <th>Extra Fee: Tk.</th>
                  <td>{{$batch->fee}}
                    <p>{{$batch->extra_fee_note}}</p>
                  </td>
                </tr>
                <tr>
                  <th>Discount: Tk.</th>
                  <td>{{$batch->discount}}</td>
                </tr>
                <tr>
                  <th>Reference Discount: Tk.</th>
                  <td>{{$batch->reference_discount}}</td>
                </tr>
                <tr>
                  <th>Selling Price: Tk.</th>
                  <td>{{$batch->reference_discount}}</td>
                </tr>
                <tr>
                  <th>Referrer Bonus: Tk.</th>
                  <td>{{$batch->reference_bonus}}</td>
                </tr>
                <tr>
                  <th>Net Price: Tk.</th>
                  <td>{{$batch->net_price}}</td>
                </tr>
                <tr>
                  <th>Course:</th>
                  <td>
                    @if($batch->course)
                    <label class="label label-primary">{{$batch->course->name}}</label>
                    @endif
                  </td>
                </tr>
                <tr>
                  <th>Departments:</th>
                  <td>
                    @if($batch->departments)
                    @foreach($batch->departments as $value)
                    <label class="label label-info">{{$value->name}}</label>
                    @endforeach
                    @endif
                  </td>
                </tr>
                <tr>
                <tr>
                  <th>Syllabuses:</th>
                  <td>
                    @if($batch->syllabuses)
                    @foreach($batch->syllabuses as $value)
                    <label class="label label-info">{{$value->name}}</label>
                    @endforeach
                    @endif
                  </td>
                </tr>
                <tr>
                  <th>Papers:</th>
                  <td>
                    @if($batch->papers)
                    @foreach($batch->papers as $value)
                    <label class="label label-warning">{{$value->name}}</label>
                    @endforeach
                    @endif
                  </td>
                </tr>
                <tr>
                  <th>Students</th>
                  <td><b>{{$batch->students->count()}}</b></td>
                </tr>
                <tr>
                  <th>Teachers:</th>
                  <td></td>
                </tr>
                <tr>
                  <th>Video:</th>
                  <td>
                    @if($batch->video)
                    <p><iframe class="responsive-iframe" style="max-width: 300px; border:5px solid #fff" src="https://www.youtube.com/embed/{{$batch->video}}" allowfullscreen></iframe></p>
                    @endif
                  </td>
                </tr>
                <tr>
                  <th>Banner:</th>
                  <td><img src="{{$batch->banner}}" alt="" style="max-width:300px"></td>
                </tr>
                <tr>
                  <th>Details:</th>
                  <td>{!!$batch->details!!}</td>
                </tr>
                <tr>
                  <th>Status:</th>
                  <td>
                    <span class="label label-{{$batch->status == 'Active'? 'success':'danger'}}">{{$batch->status}}</span>
                  </td>
                </tr>
                <tr>
                  <th>Record Created On:</th>
                  <td>{{$source->dtformat($batch->created_at)}} </td>
                </tr>
                <tr>
                  <th>Record Updated On:</th>
                  <td>{{$source->dtformat($batch->updated_at)}} </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="clearfix"></div>
        </div>
      </div><!-- /.box -->
    </div><!--/.col (left) -->
  </section><!-- /.content -->

  <script>
    function copyToClipBoard(str){
    const element = document.createElement('textarea');
    element.value = str;
    document.body.appendChild(element);
    element.select();
    document.execCommand('copy');
    document.body.removeChild(element);
    alert('Link Copied to the clipboard');
  }

  function handleClick(){
    let text = document.querySelector('#text');
    copyToClipBoard(text.getAttribute('href'));
  }
  </script>
   
@endsection
