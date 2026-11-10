@extends('student')
@section('title', 'Dashboard')
@section('content')
<br>
<br>
<br>
<br>
<br>

<form action="/students/success" method="post">
    <input type="text" name="transaction_id" value="MCQSVP3X71MKVO2RTAJKECKFAJID">
    <button type="submit">submit</button>
</form>
@endsection
