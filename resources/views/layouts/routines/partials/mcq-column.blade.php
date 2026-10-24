{{-- Reusable selector for one EXISTING Routine Subject/Department column. --}}
@php
    $selectorId = 'routine-mcq-' . $columnKind . '-' . $columnId;
    $savedPaths = [];
    if (isset($mcqSelections)) {
        foreach (($mcqSelections[$columnKind . ':' . $columnId] ?? []) as $saved) {
            $savedPaths[] = [
                'kind' => $saved->mcq_kind,
                'category_id' => (int) $saved->category_id,
                'subject_id' => $saved->subject_id ? (int) $saved->subject_id : null,
                'chapter_id' => $saved->chapter_id ? (int) $saved->chapter_id : null,
                'available_question_count' => (int) \Illuminate\Support\Facades\DB::table('exam_mcq_question_links as l')->join('questions as q','q.id','=','l.question_id')->where('l.category_id',$saved->category_id)->where('l.level',$saved->chapter_id ? 'chapter' : ($saved->subject_id ? 'subject' : 'category'))->where('l.entity_id',$saved->chapter_id ?: ($saved->subject_id ?: $saved->category_id))->where('q.type','MCQ')->distinct()->count('q.id'),
                'range_from' => $saved->range_from,
                'range_to' => $saved->range_to,
                'label' => ($saved->mcq_kind === 'department' ? 'Department' : 'Non-Department')
                    . ' > ' . ($saved->mcq_category_name ?: 'Category #' . $saved->category_id)
                    . ($saved->subject_id ? ' > ' . ($saved->mcq_subject_name ?: 'Subject #' . $saved->subject_id) : '')
                    . ($saved->chapter_id ? ' > ' . ($saved->mcq_chapter_name ?: 'Chapter #' . $saved->chapter_id) : ''),
            ];
        }
    }
    $savedPaths = old('mcq_selections.' . $columnKind . '.' . $columnId)
        ? (json_decode(old('mcq_selections.' . $columnKind . '.' . $columnId), true) ?: [])
        : $savedPaths;
@endphp
<div class="routine-mcq-selector" id="{{ $selectorId }}"
     data-initial='@json($savedPaths)' data-url="{{ route('routine.mcq.options') }}">
    {{-- Source kind is fixed per column and is not an editable filter. --}}
        <div style="display:none;">
            {{-- Fixed source: Subject columns use Non-Department, Department columns use Department. --}}
            <select class="form-control input-sm mcq-kind" style="display:none" tabindex="-1" aria-hidden="true">
                @if($columnKind === 'subject')
                    <option value="non-department" selected>Non-Department</option>
                @else
                    <option value="department" selected>Department</option>
                @endif
            </select>
        </div>
    <div class="row" style="margin-left:-4px;margin-right:-4px;">
        <div class="col-xs-12" style="padding:2px 4px;">
            <select class="form-control input-sm mcq-category"><option value="">Select Category</option></select>
        </div>
        <div class="col-xs-12" style="padding:2px 4px;">
            <select class="form-control input-sm mcq-subject" multiple data-placeholder="Select one or more Subjects"></select>
        </div>
        <div class="col-xs-12" style="padding:2px 4px;">
            <select class="form-control input-sm mcq-chapter" multiple data-placeholder="Select one or more Chapters"></select>
        </div>
    </div>
    <div style="margin:5px 0;">
        <small class="mcq-available" style="color:#555;">Available Questions: —</small>
        <button type="button" class="btn btn-xs btn-success pull-right mcq-add" disabled>
            <i class="fa fa-plus"></i> Add
        </button>
        <div class="clearfix"></div>
    </div>
    <div class="mcq-selected" style="margin:5px 0;"></div>
    <input type="hidden" class="mcq-selection-input"
           name="mcq_selections[{{ $columnKind }}][{{ $columnId }}]"
           value="{{ json_encode($savedPaths) }}">
    {{-- Preserve existing Routine Row content and field names. No manual item entry. --}}
    <textarea readonly rows="3" class="form-control routine-item-preview" name="{{ $columnKind === 'subject' ? 'subject_item[]' : 'department_item[]' }}"
              style="font-size:12px;background:#fff;resize:vertical;">{{ $existingText }}</textarea>
</div>
