@extends('layouts.app')

@section('content')

<div class="container">

    <h2 class="mb-4">
        Withdraw Requests
    </h2>


    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif


    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif



    <div class="card">

        <div class="card-body">


            <table class="table table-bordered table-striped">

                <thead>
                    <tr>

                        <th>
                            #
                        </th>

                        <th>
                            Student
                        </th>

                        <th>
                            Amount
                        </th>

                        <th>
                            Method
                        </th>

                        <th>
                            Account Number
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>
                </thead>



                <tbody>


                @forelse($withdraws as $withdraw)

                    <tr>

                        <td>
                            {{ $loop->iteration }}
                        </td>


                        <td>
                            {{ $withdraw->student->name ?? 'N/A' }}

                            <br>

                            <small>
                            {{ $withdraw->student->email ?? '' }}
                            </small>
                        </td>



                        <td>
                            ৳ {{ number_format($withdraw->amount,2) }}
                        </td>



                        <td>
                            {{ ucfirst($withdraw->method) }}
                        </td>



                        <td>
                            {{ $withdraw->account_number }}

                            <br>

                            {{ $withdraw->account_name }}
                        </td>



                        <td>

                            @if($withdraw->status == 'pending')

                                <span class="badge badge-warning">
                                    Pending
                                </span>

                            @elseif($withdraw->status == 'approved')

                                <span class="badge badge-success">
                                    Approved
                                </span>

                            @else

                                <span class="badge badge-danger">
                                    Rejected
                                </span>

                            @endif

                        </td>



                        <td>


                            @if($withdraw->status == 'pending')


                            <a href="{{ route('admin.withdraw.approve',$withdraw->id) }}"
                            class="btn btn-success btn-sm"
                            onclick="return confirm('Approve this withdraw?')">

                                Approve

                            </a>



                            <a href="{{ route('admin.withdraw.reject',$withdraw->id) }}"
                            class="btn btn-danger btn-sm"
                            onclick="return confirm('Reject this withdraw?')">

                                Reject

                            </a>


                            @else

                                Completed

                            @endif


                        </td>


                    </tr>


                @empty


                    <tr>

                        <td colspan="7" class="text-center">

                            No Withdraw Request Found

                        </td>

                    </tr>


                @endforelse


                </tbody>


            </table>


        </div>

    </div>


</div>


@endsection