<div class="lebd62-line">
    <span class="lebd62-label">{{ ucfirst($level) }} #{{ $entry->id }}</span>

    <form class="lebd62-form"
          action="{{ route('admin.exam-mcq.manage.update', ['kind' => $kind, 'level' => $level, 'id' => $entry->id]) }}"
          method="POST">
        @csrf
        @method('PATCH')
        <input class="form-control input-sm" type="text" name="name"
               value="{{ $entry->name }}" maxlength="180" required aria-label="Name">
        <input class="form-control input-sm" type="number" name="sort_order"
               value="{{ $entry->sort_order ?? 0 }}" min="0" max="999999"
               required aria-label="Sort order">
        <select class="form-control input-sm" name="status" aria-label="Status">
            <option value="Active" @selected($entry->status === 'Active')>Active</option>
            <option value="Inactive" @selected($entry->status === 'Inactive')>Inactive</option>
        </select>
        <button class="btn btn-primary btn-sm" type="submit">
            <i class="fa fa-pencil"></i> Save Edit
        </button>
    </form>

    <form action="{{ route('admin.exam-mcq.manage.destroy', ['kind' => $kind, 'level' => $level, 'id' => $entry->id]) }}"
          method="POST"
          onsubmit="return confirm('Delete this {{ $level }}? This is blocked when child records, linked MCQs or Routine selections exist.');">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger btn-sm" type="submit">
            <i class="fa fa-trash"></i> Delete
        </button>
    </form>
</div>
