@extends('dashboard')
@section('title', 'Dashboard')
@section('content')
<div class="content">
  <div class="row">
    <div class="col-md-12">
      <div class="card card-info">
        <div class="card-body">
          
          <canvas id="visitorsChart"></canvas>
      
          <div class="clearfix"></div>

        </div>
        <div class="clearfix"></div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <br>
      <br>
      <table class="table table-borderd" style="background: #fff">
        <tr>
          <th>Date</th>
          <th>Visitor's  IP</th>
        </tr>
        @foreach($all_visitors as $value)
        <tr>
          <td>{{$value->visited_at}}</td>
          <td>{{$value->ip_address}}</td>
        </tr>
        @endforeach
      </table>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const ctx = document.getElementById('visitorsChart').getContext('2d');

    const chart = new Chart(ctx, {
        type: 'line', // or 'bar'
        data: {
            labels: @json($dates),
            datasets: [{
                label: 'Visitors',
                data: @json($totals),
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>

@endsection