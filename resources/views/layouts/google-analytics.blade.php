@extends('dashboard')
@section('title', 'Dashboard')
@section('content')
<div class="content">
  <div class="row">
    <div class="col-m-12">
{{--       
      <div id="auth-button"></div>
<div id="view-selector"></div>
<div id="chart-container"></div>

<script>
  gapi.analytics.ready(function() {
    // Authorize
    gapi.analytics.auth.authorize({
      container: 'auth-button',
      clientid: 'YOUR_CLIENT_ID.apps.googleusercontent.com'
    });

    // View selector
    var viewSelector = new gapi.analytics.ViewSelector({
      container: 'view-selector'
    });

    viewSelector.execute();

    // Chart
    var chart = new gapi.analytics.googleCharts.DataChart({
      query: {
        metrics: 'ga:sessions',
        dimensions: 'ga:date',
        'start-date': '30daysAgo',
        'end-date': 'yesterday'
      },
      chart: {
        container: 'chart-container',
        type: 'LINE',
        options: {
          width: '100%'
        }
      }
    });

    viewSelector.on('change', function(ids) {
      chart.set({query: {ids: ids}}).execute();
    });
  });
</script> --}}

    </div>
    <div class="col-md-12">
      <div class="card card-info">
        <div class="card-body">
          {{-- <canvas id="analyticsChart"></canvas> --}}


          <div id="auth-button"></div>
          <div id="view-selector"></div>
          <div id="chart-container"></div>

        </div>
      </div>
      
      <div class="clearfix"></div>
    </div>
  </div>
</div>

<script>
  gapi.analytics.ready(function() {
    // Authorize
    gapi.analytics.auth.authorize({
      container: 'auth-button',
      clientid: '969384107348-50g614u1pr8ilkjstavpej2ku1soke4b.apps.googleusercontent.com'
    });

    // View selector
    var viewSelector = new gapi.analytics.ViewSelector({
      container: 'view-selector'
    });

    viewSelector.execute();

    // Chart
    var chart = new gapi.analytics.googleCharts.DataChart({
      query: {
        metrics: 'ga:sessions',
        dimensions: 'ga:date',
        'start-date': '30daysAgo',
        'end-date': 'yesterday'
      },
      chart: {
        container: 'chart-container',
        type: 'LINE',
        options: {
          width: '100%'
        }
      }
    });

    viewSelector.on('change', function(ids) {
      chart.set({query: {ids: ids}}).execute();
    });
  });
</script>

@endsection

@section('scripts')
{{-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ctx = document.getElementById('analyticsChart');

  const chart = new Chart(ctx, {
      type: 'line',
      data: {
          labels: @json($labels),
          datasets: [{
              label: 'Visitors',
              data: @json($visits),
              borderColor: 'red',
              backgroundColor: 'rgba(0, 123, 255, 0.2)',
              tension: 0.4
          }]
      },
      options: { responsive: true }
  });
</script> --}}
@endsection