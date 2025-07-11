@php
use \App\Http\Controllers\SourceCtrl;
$source = New SourceCtrl;
$user = Auth::guard('student')->user();
$student = [];
if($user)
{
  $student = \App\Models\Student::find($user->id);
}
$value = $circular;
@endphp

@extends('homepage')
@section('title', 'নিয়োগ বিজ্ঞপ্তি')
@section('content')
<link rel="stylesheet" href="{{'/web/css/item-list.css'}}" />

    <!-- Main content -->
    <section class="our_courses">
      <div class="container">
        <div class="row">
          <!-- Content Header (Page header) -->
          <div class="title-area text-center">
            <h1> নিয়োগ বিজ্ঞপ্তি </h1>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12 no-padding">
            <div class="all-courses-area">
              <div class="row justify-content-center g-3">

                  <div class="col-md-12">
                    <div class="card card-default">
                      <div class="card-heading text-center p-3">
                        <h4>শিরোনামঃ {{$value->title}} </h4>
                      </div>
                      <div class="card-body">
                        <table class="table">
                          <tr>
                            <th>নিয়োগ বিজ্ঞপ্তি প্রকাশের তারিখঃ </th>
                            <td>{{$source->dformat($value->published_at)}}</td>
                          </tr>
                          <tr>
                            <th>প্রতিষ্ঠানের নামঃ </th>
                            <td>{{$value->admit_to}}</td>
                          </tr>
                          <tr>
                            <th>পদের নামঃ </th>
                            <td>{{$value->position}}</td>
                          </tr>
                          <tr>
                            <th>আবেদন ফিঃ</th>
                            <td>{{$value->fee}}</td>
                          </tr>
                          <tr>
                            <th>আবেদন শুরুঃ</th>
                            <td>{{$source->dformat($value->start_at)}}</td>
                          </tr>
                          <tr>
                            <th>আবেদনের শেষ তারিখঃ </th>
                            <td>{{$source->dformat($value->end_at)}}</td>
                          </tr>
                          <tr>
                            <th>আবেদন লিংকঃ </th>
                            <td><a target="_blank" href="{{$value->url}}">{{$value->url}}</a></td>
                          </tr>
                          <tr>
                            <th>নিয়োগ বিজ্ঞপ্তির pdf: </th>
                            <td><a target="_blank" href="{{$value->pdf}}"></a></td>
                          </tr>
                        </table>

                        <h4 class="text-center">নিয়োগ বিজ্ঞপ্তির ছবিঃ </h4>
                        <object data="{{$value->pdf}}" type="application/pdf" width="100%" height="600px">
                          <p>Your browser does not support PDFs.
                            <a href="path/to/file.pdf">Download the PDF</a>.
                          </p>
                        </object>
                      </div>
                    </div>
                  </div>
              </div>
            </div>
          </div>
        </div> <!-- /.row -->

        <div class="row p-5">
          <div class="container text-center">
            <p><button class="btn btn-warning btn-outline" onclick="window.history.back();"><i class="fa fa-long-arrow-left"></i> Back</button></p>
          </div>
        </div>

      </div> <!-- /.container -->
    </section> <!-- /.our_courses -->
@endsection