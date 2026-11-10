@extends('dashboard')

@section('title', 'Admin Balance - ' . $section)

@section('content')
<section class="content-header">
    <h1>{{ $sectionBn }} <small>Admin Balance / {{ $section }}</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li class="active">Admin Balance / {{ $section }}</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">{{ $section }} Management</h3>
        </div>
        <div class="box-body">
            <p class="text-muted">
                এই অংশটি Admin Balance মডিউলের {{ $section }} পৃষ্ঠা।
                পরবর্তী ধাপে এখানে সংশ্লিষ্ট হিসাব, লেনদেন ও রিপোর্টের ফিচার যুক্ত করা হবে।
            </p>
        </div>
    </div>
</section>
@endsection
