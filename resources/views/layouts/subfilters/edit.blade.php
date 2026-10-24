@php
  // Display labels only. Real existing Filter IDs and stored connections are unchanged.
  $fixedMainFilterNames = ['প্রশ্ন টাইপ', 'কোর্সের নাম', 'প্রশ্ন টপিক', 'ডিপার্টমেন্ট নাম', 'বুক প্রকাশনির নাম', 'সাবজেক্ট নাম', 'চ্যাপ্টার নাম', 'প্রশ্ন সোর্স', 'সাব-সোর্স', 'পরীক্ষার সন', 'Question Quality filter'];
@endphp
@extends('dashboard')
@section('title', 'Edit Sub-filter')
@section('content')
<section class="content-header"><h1>Edit Sub-filter</h1></section>
<section class="content">
  <div class="row"><div class="col-md-9"><div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">Edit your option and all earlier-filter connections</h3>
      <a class="btn btn-default btn-sm pull-right" href="{{ route('filter.index') }}"><i class="fa fa-arrow-left"></i> Filter Management</a>
    </div>
    <form action="{{ route('sub-filter.update', $subfilter->id) }}" method="POST">
      @csrf
      @method('PUT')
      <div class="box-body">
        @if($errors->any())
          <div class="alert alert-danger"><strong>Please correct the following:</strong><ul>
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
          </ul></div>
        @endif
        <div class="form-group">
          <label for="filter_id">Main Filter / Step <span class="text-danger">*</span></label>
          <select class="form-control" name="filter_id" id="filter_id" required>
            <option value="">Select a filter</option>
            @foreach($filters as $filter)
              <option value="{{ $filter->id }}" {{ (string) (old('filter_id', $subfilter->filter_id)) === (string) $filter->id ? 'selected' : '' }}>
                {{ $loop->iteration }} — {{ $fixedMainFilterNames[$loop->index] ?? $filter->name }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label for="name">Sub-filter Name <span class="text-danger">*</span></label>
          <input class="form-control" name="name" id="name" type="text" maxlength="255" value="{{ old('name', $subfilter->name) }}" required>
        </div>
        <div class="well well-sm" id="parent_connection_panel">
          <h4><i class="fa fa-link"></i> Connections to All Previous Main Filters</h4>
          <p class="help-block">প্রতিটি Main Filter-এর নিচে তার নতুন Sub-filter থেকে একটি বা একাধিক নির্বাচন করুন। ঠিক আগের ধাপ থেকে অন্তত একটি নির্বাচন প্রয়োজন।</p>
          @php $selectedParents = array_map('intval', (array) (old('parent_connections', $connectionIds))); @endphp
          <div id="previous_filter_groups">
            @foreach($filters as $parentFilter)
              <div class="form-group previous-filter-group" data-step-filter="{{ $parentFilter->id }}" style="display:none; border:1px solid #ddd; border-radius:4px; padding:12px; background:white">
                <label for="parent_filter_{{ $parentFilter->id }}">
                  {{ $loop->iteration }}. {{ $fixedMainFilterNames[$loop->index] ?? $parentFilter->name }}
                  <span class="text-danger immediate-required" style="display:none">* (required)</span>
                </label>
                <select id="parent_filter_{{ $parentFilter->id }}" name="parent_connections[]" class="form-control parent-step-select"
                        data-parent-filter="{{ $parentFilter->id }}" multiple style="width:100%" disabled>
                  @foreach($subfilters->where('filter_id', $parentFilter->id) as $candidate)
                    <option value="{{ $candidate->id }}" {{ in_array((int)$candidate->id, $selectedParents, true) ? 'selected' : '' }}>{{ $candidate->name }} (ID {{ $candidate->id }})</option>
                  @endforeach
                </select>
                @if($subfilters->where('filter_id', $parentFilter->id)->isEmpty())
                  <small class="text-muted">এই Main Filter-এ এখনো নতুন Sub-filter তৈরি করা হয়নি।</small>
                @endif
              </div>
            @endforeach
          </div>
          <div class="alert alert-info" id="no_parent_message">Filter 1 has no parent connections.</div>
          <span class="label label-info" id="parent_count">0 connected parents</span>
        </div>
        <div class="form-group">
          <label for="details">Details</label>
          <textarea class="form-control" name="details" id="details" rows="4">{{ old('details', $subfilter->details) }}</textarea>
        </div>
      </div>
      <div class="box-footer">
        <a class="btn btn-default" href="{{ route('sub-filter.index') }}">Cancel</a>
        <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> Save Sub-filter &amp; Connections</button>
      </div>
    </form>
  </div></div></div>
</section>
@endsection
@section('scripts')
<script>
(function () {
  'use strict';
  var stages = @json($filters->map(function ($f) { return ['id' => (int)$f->id, 'name' => $f->name]; })->values());
  var filterSelect = $('#filter_id');
  var groups = $('.previous-filter-group');
  var selectors = $('.parent-step-select');

  selectors.each(function () {
    $(this).select2({ width: '100%', placeholder: 'Sub-filter-এর নাম বা ID দিয়ে খুঁজুন', closeOnSelect: true });
  });

  function updateCount() {
    var count = 0;
    selectors.filter(':enabled').each(function () { count += ($(this).val() || []).length; });
    $('#parent_count').text(count + (count === 1 ? ' connected parent' : ' connected parents'));
  }

  function updateStages() {
    var selected = Number(filterSelect.val());
    var index = stages.findIndex(function (step) { return step.id === selected; });
    groups.hide();
    selectors.prop('disabled', true).prop('required', false);
    groups.find('.immediate-required').hide();
    var independent = index === 10;
    if (index > 0 && !independent) {
      for (var i = 0; i < index; i++) {
        var container = groups.filter('[data-step-filter="' + stages[i].id + '"]');
        container.show();
        var field = container.find('select');
        field.prop('disabled', false);
        if (i === index - 1) {
          field.prop('required', true);
          container.find('.immediate-required').show();
        }
        field.trigger('change.select2');
      }
    }
    $('#parent_connection_panel').toggle(index >= 0 && !independent);
    $('#no_parent_message').toggle(index <= 0)
      .text(index === 0 ? 'Filter 1: no parent required.' : 'Choose a Main Filter above to view its earlier filters.');
    updateCount();
  }
  filterSelect.on('change', updateStages);
  selectors.on('change', updateCount);
  updateStages();
})();
</script>
@endsection
