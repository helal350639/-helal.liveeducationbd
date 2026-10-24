@extends('dashboard')

@section('title', 'Select Routine Exam Type')

@section('content')
@include('layouts.routines.partials.all-pages-navigation')

<section class="content-header">
    <h1>Add a Routine Item</h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('routine.index') }}"><i class="fa fa-dashboard"></i> Routines</a></li>
        <li class="active">Select Exam Type</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title" style="color:#800">{{ $routine->name }}</h3>
                    <div class="toolbar-icon pull-right text-right">
                        <a href="{{ route('routine.item.show', $routine->id) }}" title="Back to Routine" class="label label-success">
                            <i class="fa fa-list"></i>
                        </a>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row" style="margin-bottom:16px">
                        <div class="col-md-12">
                            <h4 style="margin-top:0">Select Exam Type</h4>
                            <p class="text-muted">Choose an exam type to continue.</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4" style="margin-bottom:12px">
                            <a class="btn btn-primary btn-block btn-lg" href="{{ route('routine.add-item', ['id' => $routine->id, 'mode' => 'exam']) }}">
                                <i class="fa fa-file-text-o"></i> Exam
                            </a>
                        </div>
                        <div class="col-sm-4" style="margin-bottom:12px">
                            <a class="btn btn-warning btn-block btn-lg" href="{{ route('routine.add-item', ['id' => $routine->id, 'mode' => 'revision']) }}">
                                <i class="fa fa-repeat"></i> Revision Exam
                            </a>
                        </div>
                        <div class="col-sm-4" style="margin-bottom:12px">
                            <a class="btn btn-info btn-block btn-lg" href="{{ route('routine.add-item', ['id' => $routine->id, 'mode' => 'model']) }}">
                                <i class="fa fa-list-alt"></i> Model Exam
                            </a>
                        </div>
                    </div>
                    @if($selectedMode === 'revision' || $selectedMode === 'model')
                        <div class="alert alert-info" style="margin-top:15px; margin-bottom:0">
                            <strong>{{ $selectedMode === 'revision' ? 'Revision Exam' : 'Model Exam' }}</strong>
                            <p style="margin:8px 0 0">This is a separate page placeholder. The new exam-add form will be built in the next step. No routine data has been changed.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
