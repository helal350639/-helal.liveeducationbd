{{-- LEBD-3: Modular E-Book homepage section. Single include only. --}}
@php
    $lebdHomeBookCategories = \Illuminate\Support\Facades\DB::table('lebd_ebook_categories')
        ->where('status', 'Active')
        ->whereIn('kind', ['department', 'non-department'])
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get();
    $lebdDepartmentBooks = $lebdHomeBookCategories->where('kind', 'department');
    $lebdNonDepartmentBooks = $lebdHomeBookCategories->where('kind', 'non-department');
@endphp
<link rel="stylesheet" href="{{ asset('web/css/lebd-home-ebook.css') }}">
<div class="lebd-ebook-home" aria-label="E-Book and PDF categories">
    @include('homepage.partials.ebook.department', ['ebookCategories' => $lebdDepartmentBooks])
    @include('homepage.partials.ebook.non-department', ['ebookCategories' => $lebdNonDepartmentBooks])
</div>
