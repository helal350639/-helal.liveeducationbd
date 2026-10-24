{{-- LEBD-04 shared routine navigation; context-aware and route-guarded. --}}
@php
    $lebdNavRoutineId = null;
    if (isset($routine) && is_object($routine) && isset($routine->id)) {
        $lebdNavRoutineId = (int) $routine->id;
    } elseif (isset($item) && is_object($item) && isset($item->routine_id)) {
        $lebdNavRoutineId = (int) $item->routine_id;
    } elseif (request()->routeIs('routine.item.show', 'routine.add-item', 'routine.derived.list')) {
        $lebdNavRoutineId = (int) request()->route('id');
    }
    $lebdNavLinks = [];
    if (\Illuminate\Support\Facades\Route::has('routine.index')) {
        $lebdNavLinks[] = ['Routine List', route('routine.index'), 'fa-list', 'btn-default'];
    }
    if (\Illuminate\Support\Facades\Route::has('routine.create')) {
        $lebdNavLinks[] = ['New Routine', route('routine.create'), 'fa-plus-circle', 'btn-default'];
    }
    if ($lebdNavRoutineId) {
        if (\Illuminate\Support\Facades\Route::has('routine.edit')) {
            $lebdNavLinks[] = ['Edit Routine', route('routine.edit', $lebdNavRoutineId), 'fa-pencil', 'btn-default'];
        }
        if (\Illuminate\Support\Facades\Route::has('routine.item.show')) {
            $lebdNavLinks[] = ['Show Routine Items', route('routine.item.show', $lebdNavRoutineId), 'fa-table', 'btn-success'];
        }
        if (\Illuminate\Support\Facades\Route::has('routine.add-item')) {
            $lebdNavLinks[] = ['Select Exam Type', route('routine.add-item', ['id' => $lebdNavRoutineId]), 'fa-th-large', 'btn-default'];
            $lebdNavLinks[] = ['Add Exam', route('routine.add-item', ['id' => $lebdNavRoutineId, 'mode' => 'exam']), 'fa-file-text-o', 'btn-primary'];
            $lebdNavLinks[] = ['Add Revision', route('routine.add-item', ['id' => $lebdNavRoutineId, 'mode' => 'revision']), 'fa-repeat', 'btn-warning'];
            $lebdNavLinks[] = ['Add Model', route('routine.add-item', ['id' => $lebdNavRoutineId, 'mode' => 'model']), 'fa-list-alt', 'btn-info'];
        }
        if (\Illuminate\Support\Facades\Route::has('routine.derived.list')) {
            $lebdNavLinks[] = ['Revision / Model List', route('routine.derived.list', ['id' => $lebdNavRoutineId]), 'fa-link', 'btn-default'];
        }
    }
@endphp
<div class="lebd-routine-navigation" style="margin:0 0 15px 0;padding:10px 12px;background:#fff;border:1px solid #dce4ec;border-radius:4px;">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:7px;">
        <strong style="color:#444"><i class="fa fa-compass"></i> Routine Navigation</strong>
        @if($lebdNavRoutineId)
            <small class="text-muted">Routine #{{ $lebdNavRoutineId }}</small>
        @else
            <small class="text-muted">Open a Routine to access its Exam, Revision and Model pages.</small>
        @endif
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:6px;">
        @foreach($lebdNavLinks as $lebdNavLink)
            <a href="{{ $lebdNavLink[1] }}" class="btn btn-sm {{ $lebdNavLink[3] }}" style="margin:0;white-space:nowrap">
                <i class="fa {{ $lebdNavLink[2] }}"></i> {{ $lebdNavLink[0] }}
            </a>
        @endforeach
    </div>
</div>
