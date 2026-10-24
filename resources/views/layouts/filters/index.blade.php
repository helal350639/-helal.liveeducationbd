@extends('dashboard')
@section('title','Filter Connection Management')
@section('content')
<section class="content-header">
  <h1>Filter Connection Management</h1>
  <p class="text-muted">Only eleven fixed filters are shown. Create fresh Sub-filters and connect each to the previous step.</p>
</section>
<section class="content">
<div class="box box-primary"><div class="box-header with-border">
  <h3 class="box-title"><i class="fa fa-sliders"></i> Main Filters</h3>
  <a class="btn btn-default btn-sm pull-right" href="{{ route('sub-filter.index') }}"><i class="fa fa-list"></i> Manage all Sub-filters</a>
</div><div class="box-body">
  <div class="alert alert-info" style="margin-bottom:15px">Only newly created Sub-filters appear here. Older Sub-filter records remain safely stored in the database.</div>
  @unless($visibilityConfigured)<div class="alert alert-warning">Before adding new Sub-filters, import the Fix 12 visibility checkpoint SQL in phpMyAdmin. Existing database records will not be deleted.</div>@endunless
  <div class="row">
    <div class="col-md-8">
      @foreach($stages as $stage)
      @php $parentFilter = $stage['filter']; @endphp
      <div class="panel panel-default lebd-stage" data-step="{{ $stage['position'] }}" data-filter="{{ $parentFilter ? $parentFilter->id : '' }}" style="margin-bottom:9px">
        <div class="panel-body" style="padding:11px 13px">
          <div class="row">
            <div class="col-sm-8">
              <label for="stage-{{ $stage['position'] }}" style="display:block;font-size:15px;margin-bottom:6px">{{ $stage['position'] }}. {{ $stage['label'] }}</label>
              @if($parentFilter)
                <select id="stage-{{ $stage['position'] }}" class="form-control lebd-stage-select" data-step="{{ $stage['position'] }}">
                  <option value="">Select {{ $stage['label'] }}</option>
                </select>
                <span class="text-muted" style="font-size:11px">{{ $parentFilter->name }} · Filter ID {{ $parentFilter->id }}</span>
              @else
                <p class="text-warning" style="margin:0">No existing Filter assigned to this serial. Edit existing filter serials first.</p>
              @endif
            </div>
            <div class="col-sm-4 text-right" style="padding-top:15px">
              @if($parentFilter)
                <a class="btn btn-primary btn-sm" href="{{ route('sub-filter.create', ['filter_id' => $parentFilter->id]) }}"><i class="fa fa-plus"></i> Create Sub-filter</a>
                <a class="btn btn-default btn-sm" style="margin-top:4px" href="{{ route('sub-filter.index', ['view'=>'manage', 'filter_id'=>$parentFilter->id]) }}"><i class="fa fa-search"></i> Manage / Edit</a>
              @else
                <span class="text-muted">Unavailable</span>
              @endif
            </div>
          </div>
        </div>
      </div>
      @endforeach
    </div>
    <div class="col-md-4">
      <div class="well well-sm">
        <h4>How the connection works</h4>
        <p>Choose an option in Question Type. Only linked options will appear in Course, then Topic, Department, and the next filters.</p>
        <p>Each Sub-filter can be linked to earlier filter options using Create/Edit. Question Quality is independent and needs no connections.</p>
        <p><strong>Note:</strong> Unconnected options are intentionally hidden after the first step. They can be linked through Manage / Edit.</p>
      </div>
    </div>
  </div>
</div></div>
</section>
@endsection
@section('scripts')
<script>
(function () {
  'use strict';
  var items = @json($optionPayload);
  var relations = @json($parentsByChild);
  var selects = Array.prototype.slice.call(document.querySelectorAll('.lebd-stage-select'));
  var byStage = {};
  selects.forEach(function(el) { byStage[Number(el.dataset.step)] = el; });
  var filters = {};
  Array.prototype.slice.call(document.querySelectorAll('.lebd-stage')).forEach(function(el) {
    filters[Number(el.dataset.step)] = Number(el.dataset.filter) || 0;
  });
  function resetFrom(step) {
    for (var n = step; n <= 11; n++) {
      var select = byStage[n];
      if (!select) continue;
      select.innerHTML = '';
      select.appendChild(new Option('Select an option', ''));
      select.disabled = n !== 1 && n !== 11;
    }
  }
  function populate(step, parentId) {
    var select = byStage[step];
    if (!select || !filters[step]) return;
    var matches = items.filter(function(item) {
      if (Number(item.filter_id) !== filters[step]) return false;
      if (step === 1 || step === 11) return true;
      return (relations[String(item.id)] || []).some(function(p) { return Number(p) === Number(parentId); });
    });
    matches.forEach(function(item) { select.appendChild(new Option(item.name, item.id)); });
    select.disabled = matches.length === 0;
    if (!matches.length && step > 1 && step !== 11) select.options[0].text = 'No connected options';
  }
  resetFrom(1);
  populate(1, null);
  populate(11, null); // Independent Question Quality options, even without any previous choice.
  selects.forEach(function(select) {
    select.addEventListener('change', function() {
      var step = Number(select.dataset.step);
      if (step === 11) return; // Quality selection must not affect other filters.
      resetFrom(step + 1);
      populate(11, null); // Keep independent Quality choices available on every change.
      if (select.value && step < 10) populate(step + 1, select.value);
    });
  });
})();
</script>
@endsection
