{{-- LEBD-04: shared navigation across Main / Revision / Model. Does not alter form state. --}}
<div class="row" style="margin:0 0 16px 0;">
    <div class="col-md-12" style="padding-left:0;padding-right:0;">
        <div class="row">
            <div class="col-sm-4" style="margin-bottom:7px;">
                <a class="btn btn-block {{ ($activeRoutineMode ?? 'exam') === 'exam' ? 'btn-primary' : 'btn-default' }}"
                   href="{{ route('routine.add-item', ['id' => $routine->id, 'mode' => 'exam']) }}">
                    <i class="fa fa-file-text-o"></i> Exam
                </a>
            </div>
            <div class="col-sm-4" style="margin-bottom:7px;">
                <a class="btn btn-block {{ ($activeRoutineMode ?? 'exam') === 'revision' ? 'btn-warning' : 'btn-default' }}"
                   href="{{ route('routine.add-item', ['id' => $routine->id, 'mode' => 'revision']) }}">
                    <i class="fa fa-repeat"></i> Revision Exam
                </a>
            </div>
            <div class="col-sm-4" style="margin-bottom:7px;">
                <a class="btn btn-block {{ ($activeRoutineMode ?? 'exam') === 'model' ? 'btn-info' : 'btn-default' }}"
                   href="{{ route('routine.add-item', ['id' => $routine->id, 'mode' => 'model']) }}">
                    <i class="fa fa-list-alt"></i> Model Exam
                </a>
            </div>
        </div>
    </div>
</div>
