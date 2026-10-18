{{-- LEBD Step 12: real Database-backed Category Management. Existing dashboard layout preserved. --}}
@extends('dashboard')
@section('title', 'Daily Update Categories | Live Education BD')
@section('content')
<style>
.lebd-du-categories{--duc-text:#1b395f;--duc-muted:#7790ac;--duc-border:#e1eaf6;--duc-primary:#1166d9;max-width:1460px;margin:0 auto;padding:16px 16px 40px;color:var(--duc-text);font:inherit;font-size:13px}
.lebd-du-categories,.lebd-du-categories *,.lebd-du-categories *::before,.lebd-du-categories *::after{box-sizing:border-box}
.lebd-du-categories button,.lebd-du-categories input,.lebd-du-categories select{font:inherit}
.lebd-du-categories button{cursor:pointer}.lebd-du-categories a{text-decoration:none}
.lebd-du-categories .dc-head{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;margin-bottom:16px}
.lebd-du-categories .dc-head h1{font-size:23px;font-weight:800;margin:0;color:#163861}
.lebd-du-categories .dc-crumb{font-size:11px;margin:4px 0 0;color:#7890a9}
.lebd-du-categories .dc-head-actions{display:flex;flex-wrap:wrap;gap:9px}
.lebd-du-categories .dc-button{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:39px;padding:9px 15px;border:1px solid transparent;border-radius:9px;font-size:12px;font-weight:800;white-space:nowrap}
.lebd-du-categories .dc-button.primary{background:linear-gradient(130deg,#1b86fa,#0a54cf);color:white;box-shadow:0 6px 15px #1868d525}
.lebd-du-categories .dc-button.neutral{background:#fff;border-color:#d8e5f5;color:#365b81}
.lebd-du-categories .dc-note{border:1px solid #f4dda7;border-left:4px solid #e9aa31;background:#fffaf0;border-radius:10px;padding:11px 14px;color:#805b21;line-height:1.65;font-size:12px;margin-bottom:15px}
.lebd-du-categories .dc-notify{display:none;padding:10px 13px;background:#e8f8ef;border:1px solid #bce9d1;border-radius:9px;color:#11734e;margin-bottom:14px;font-size:12px;font-weight:700}
.lebd-du-categories .dc-notify.active{display:block}
.lebd-du-categories .dc-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:15px}
.lebd-du-categories .dc-stat{background:white;border:1px solid var(--duc-border);border-radius:14px;padding:17px;display:flex;gap:12px;align-items:center;box-shadow:0 5px 20px #254f8508}
.lebd-du-categories .dc-stat-icon{width:47px;height:47px;display:grid;place-items:center;border-radius:12px;font-size:22px;font-weight:900;background:#e6f0ff;color:#1166d9;flex:none}
.lebd-du-categories .dc-stat-icon.green{background:#e5f8ee;color:#0a9566}.lebd-du-categories .dc-stat-icon.gray{background:#f0eaff;color:#8154d4}
.lebd-du-categories .dc-stat small{display:block;color:#7d91ae;font-size:11px;font-weight:700}
.lebd-du-categories .dc-stat strong{display:block;color:#183d68;font-size:25px;line-height:1.25;margin-top:3px;font-variant-numeric:tabular-nums}
.lebd-du-categories .dc-panel{background:white;border:1px solid var(--duc-border);border-radius:15px;overflow:hidden;box-shadow:0 8px 25px #194c880d}
.lebd-du-categories .dc-panel-top{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:18px 18px 14px;flex-wrap:wrap}
.lebd-du-categories .dc-panel-top h2{font-size:16px;font-weight:850;color:#1e426a;margin:0}.lebd-du-categories .dc-panel-top p{color:var(--duc-muted);font-size:11px;margin:4px 0 0}
.lebd-du-categories .dc-filters{display:flex;gap:9px;padding:0 18px 15px;flex-wrap:wrap}
.lebd-du-categories textarea.dc-field{min-height:82px;resize:vertical}.lebd-du-categories .dc-field{border:1px solid #d8e5f4;border-radius:9px;background:#fafdff;padding:10px 12px;outline:none;color:#234b78;font-size:12px!important;min-height:41px}
.lebd-du-categories .dc-field:focus{border-color:#428bef;box-shadow:0 0 0 3px #3b86f020}
.lebd-du-categories .dc-filters input{flex:1 1 260px;min-width:160px}.lebd-du-categories .dc-filters select{flex:0 1 190px}
.lebd-du-categories .dc-table-scroll{overflow-x:auto}
.lebd-du-categories .dc-table{width:100%;min-width:710px;border-collapse:collapse;font-size:12px}
.lebd-du-categories .dc-table th{background:#f3f7fd;color:#5f7697;font-size:11px;font-weight:850;text-align:left;white-space:nowrap}
.lebd-du-categories .dc-table td,.lebd-du-categories .dc-table th{padding:12px 16px;border-bottom:1px solid #edf2f9;vertical-align:middle}
.lebd-du-categories .dc-table tr:last-child td{border-bottom:0}
.lebd-du-categories .dc-cat-name{display:flex;align-items:center;gap:9px;font-weight:800;color:#193f69;white-space:nowrap}
.lebd-du-categories .dc-cat-icon{width:33px;height:33px;border-radius:9px;background:#edf4ff;display:grid;place-items:center;font-size:17px;flex:none}
.lebd-du-categories .dc-slug{font-family:ui-monospace,Consolas,monospace;font-size:11px;color:#5b7696}
.lebd-du-categories .dc-pill{display:inline-block;border-radius:7px;padding:5px 10px;font-size:10px;font-weight:800}.lebd-du-categories .dc-pill.active{background:#e2f8eb;color:#09885a}.lebd-du-categories .dc-pill.inactive{background:#fff0e8;color:#b76832}
.lebd-du-categories .dc-actions{display:flex;gap:6px;flex-wrap:wrap;align-items:center}.lebd-du-categories .dc-actions form{display:inline-flex;margin:0}
.lebd-du-categories .dc-action{border-radius:7px;padding:7px 10px;font-size:11px;font-weight:800;border:1px solid #cfe0f6;background:#edf5ff;color:#1362bc;white-space:nowrap}
.lebd-du-categories .dc-action.delete{border-color:#ffd4d3;background:#fff1f0;color:#c43e3e}
.lebd-du-categories .dc-action.switch{background:#f5f8fd;color:#5e7191}
.lebd-du-categories .dc-empty{padding:25px;text-align:center;color:#7790aa}
.lebd-du-categories .dc-footer{padding:11px 18px;font-size:11px;color:#8197b0;background:#fcfdff;border-top:1px solid #f0f3f8}
.lebd-du-categories .dc-overlay{position:fixed;inset:0;z-index:100010;display:none;align-items:center;justify-content:center;padding:12px;background:#102847a8}
.lebd-du-categories .dc-overlay.open{display:flex}
.lebd-du-categories .dc-modal{width:min(485px,100%);max-height:90vh;overflow:auto;border-radius:17px;background:#fff;padding:22px;box-shadow:0 30px 80px #071e4550}
.lebd-du-categories .dc-modal-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:15px}
.lebd-du-categories .dc-modal h3{font-size:19px;margin:0;color:#173b69;font-weight:850}
.lebd-du-categories .dc-close{border:1px solid #dfe9f5;background:#f7fbff;color:#3c5d80;border-radius:8px;font-size:20px;line-height:1;padding:5px 11px}
.lebd-du-categories .dc-form-row{margin-bottom:13px}.lebd-du-categories .dc-label{display:block;font-size:12px;font-weight:800;color:#315476;margin:0 0 7px}
.lebd-du-categories .dc-form-row .dc-field{width:100%}.lebd-du-categories .dc-help{margin:5px 0 0;color:#8395ad;font-size:10px;line-height:1.5}
.lebd-du-categories .dc-required{color:#d95455}
.lebd-du-categories .dc-modal-actions{display:flex;gap:8px;justify-content:flex-end;padding-top:12px;border-top:1px solid #edf2f9;margin-top:18px}
.lebd-du-categories .dc-error{display:none;color:#bd3333;background:#fff1f0;padding:9px 11px;border-radius:8px;font-size:11px;margin:10px 0}
.lebd-du-categories .dc-error.show{display:block}
@media(max-width:700px){.lebd-du-categories{padding:10px 9px 28px}.lebd-du-categories .dc-head h1{font-size:19px}.lebd-du-categories .dc-stats{gap:8px}.lebd-du-categories .dc-stat{padding:12px;gap:8px}.lebd-du-categories .dc-stat-icon{width:34px;height:34px;font-size:18px}.lebd-du-categories .dc-stat strong{font-size:20px}.lebd-du-categories .dc-filters{padding:0 11px 12px}.lebd-du-categories .dc-filters input,.lebd-du-categories .dc-filters select{flex:1 1 100%}.lebd-du-categories .dc-panel-top{padding:14px 12px}.lebd-du-categories .dc-modal{padding:17px}.lebd-du-categories .dc-modal-actions .dc-button{flex:1}}
</style>
<style>
.lebd-du-categories .dc-notify.error{background:#fff1f0;border-color:#ffd2ce;color:#a6322a}
.lebd-du-categories .dc-note.live{border-color:#c7e5db;border-left-color:#159a6b;background:#f1fcf6;color:#23795c}
.lebd-du-categories .dc-cat-name{white-space:normal}
.lebd-du-categories .dc-table tbody tr[hidden]{display:none}
.lebd-du-categories .dc-form-row .dc-field{font-size:13px!important}
.lebd-du-categories .dc-modal{width:min(535px,100%)}
.lebd-du-categories .dc-filter-result{padding:0 18px 11px;color:#7790ac;font-size:11px}
@media(max-width:700px){.lebd-du-categories .dc-actions{gap:4px}.lebd-du-categories .dc-action{padding:7px 9px}}
</style>
<main class="lebd-du-categories" id="lebdDuCategoryRoot"
      data-store-url="{{ route('admin.daily-update.categories.store') }}"
      data-old-had-errors="{{ $errors->any() ? '1' : '0' }}"
      data-old-edit-id="{{ old('_form_edit_id', '') }}"
      data-old-name="{{ old('name', '') }}"
      data-old-slug="{{ old('slug', '') }}"
      data-old-icon="{{ old('icon', '') }}"
      data-old-description="{{ old('description', '') }}"
      data-old-status="{{ old('status', '') }}"
      data-old-sort-order="{{ old('sort_order', '') }}">
    <header class="dc-head">
        <div>
            <h1>▤ Category Management</h1>
            <p class="dc-crumb">Admin &nbsp;›&nbsp; Daily Update &nbsp;›&nbsp; Categories</p>
        </div>
        <div class="dc-head-actions">
            <a class="dc-button neutral" href="{{ route('admin.daily-update.index') }}">← Dashboard</a>
            <button class="dc-button primary" type="button" id="dcAddCategory">＋ Add New Category</button>
        </div>
    </header>

    <div class="dc-note live" role="note">
        <strong>Live Database Connected:</strong> এখানে Add, Edit, Active/Inactive ও Delete করলে তথ্য
        <strong>MySQL Database-এ সংরক্ষিত হবে।</strong> Student Daily Update Page এবং Question Form-এর Demo Connection পরের ধাপে যুক্ত করা হবে।
    </div>

    @if(session('success'))
        <div class="dc-notify active" role="status">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="dc-notify active error" role="alert">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="dc-notify active error" role="alert">
            <strong>তথ্য সংরক্ষণ হয়নি:</strong>
            <ul style="margin:5px 0 0;padding-left:18px">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="dc-stats" aria-label="Database category summary">
        <div class="dc-stat"><span class="dc-stat-icon">▤</span><div><small>Total Categories</small><strong>{{ $categories->count() }}</strong></div></div>
        <div class="dc-stat"><span class="dc-stat-icon green">✓</span><div><small>Active Categories</small><strong>{{ $categories->where('is_active', true)->count() }}</strong></div></div>
        <div class="dc-stat"><span class="dc-stat-icon gray">◌</span><div><small>Inactive Categories</small><strong>{{ $categories->where('is_active', false)->count() }}</strong></div></div>
    </div>

    <section class="dc-panel" aria-labelledby="dcCategoriesTitle">
        <div class="dc-panel-top">
            <div><h2 id="dcCategoriesTitle">All Categories</h2><p>Category Name, Slug, Icon, Status এবং মোট Updates</p></div>
            <span class="dc-pill active">Live Data</span>
        </div>
        <div class="dc-filters">
            <input class="dc-field" id="dcSearch" type="search" aria-label="Search categories" placeholder="নাম অথবা Slug দিয়ে খুঁজুন..." autocomplete="off">
            <select class="dc-field" id="dcStatusFilter" aria-label="Filter by status">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="dc-filter-result" id="dcVisibleCount" role="status" aria-live="polite"></div>
        <div class="dc-table-scroll">
            <table class="dc-table">
                <thead><tr><th>#</th><th>Category Name</th><th>Slug</th><th>Icon</th><th>Status</th><th>Updates</th><th>Action</th></tr></thead>
                <tbody id="dcCategoryRows">
                    @forelse($categories as $category)
                        <tr class="dc-data-row"
                            data-name="{{ $category->name }}"
                            data-slug="{{ $category->slug }}"
                            data-status="{{ $category->is_active ? 'active' : 'inactive' }}">
                            <td>{{ $loop->iteration }}</td>
                            <td><span class="dc-cat-name"><span class="dc-cat-icon">{{ $category->icon ?: '📰' }}</span><span>{{ $category->name }}</span></span></td>
                            <td class="dc-slug">{{ $category->slug }}</td>
                            <td>{{ $category->icon ?: '—' }}</td>
                            <td><span class="dc-pill {{ $category->is_active ? 'active' : 'inactive' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td>{{ $category->updates_count }}</td>
                            <td>
                                <div class="dc-actions">
                                    <button class="dc-action dc-edit" type="button"
                                            data-edit-id="{{ $category->id }}"
                                            data-url="{{ route('admin.daily-update.categories.update', $category->id) }}"
                                            data-name="{{ $category->name }}"
                                            data-slug="{{ $category->slug }}"
                                            data-icon="{{ $category->icon }}"
                                            data-description="{{ $category->description }}"
                                            data-status="{{ $category->is_active ? 'active' : 'inactive' }}"
                                            data-sort-order="{{ $category->sort_order }}">Edit</button>
                                    <form method="POST" action="{{ route('admin.daily-update.categories.toggle', $category->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="dc-action switch" aria-label="{{ $category->is_active ? 'Deactivate' : 'Activate' }} {{ $category->name }}">
                                            {{ $category->is_active ? 'Disable' : 'Enable' }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.daily-update.categories.destroy', $category->id) }}" class="dc-delete-form" data-category-name="{{ $category->name }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dc-action delete">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="dc-empty" colspan="7">Database-এ এখনো কোনো Category নেই। উপরের <strong>Add New Category</strong> দিয়ে শুরু করুন।</td></tr>
                    @endforelse
                    @if($categories->isNotEmpty())
                        <tr id="dcNoResults" hidden><td class="dc-empty" colspan="7">এই Filter-এ কোনো Category পাওয়া যায়নি।</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
        <div class="dc-footer">এই তালিকা <strong>lebd_daily_update_categories</strong> Table থেকে আসছে। Question Save ও Student Page-এর Live Display পরের ধাপে হবে।</div>
    </section>

    <div class="dc-overlay" id="dcModal" role="presentation">
        <div class="dc-modal" role="dialog" aria-modal="true" aria-labelledby="dcModalTitle">
            <div class="dc-modal-head">
                <h3 id="dcModalTitle">Add New Category</h3>
                <button id="dcCloseModal" class="dc-close" type="button" aria-label="Close">×</button>
            </div>
            <form id="dcCategoryForm" method="POST" action="{{ route('admin.daily-update.categories.store') }}">
                @csrf
                <input type="hidden" name="_method" value="PATCH" id="dcMethod" disabled>
                <input type="hidden" name="_form_edit_id" id="dcEditId" value="">
                <div class="dc-form-row">
                    <label class="dc-label" for="dcCategoryName">Category Name <span class="dc-required">*</span></label>
                    <input class="dc-field" id="dcCategoryName" name="name" maxlength="150" required placeholder="যেমন: কারেন্ট অ্যাফেয়ার্স">
                </div>
                <div class="dc-form-row">
                    <label class="dc-label" for="dcCategorySlug">Slug <span class="dc-required">*</span></label>
                    <input class="dc-field" id="dcCategorySlug" name="slug" maxlength="160" required pattern="[a-z0-9]+(-[a-z0-9]+)*" placeholder="যেমন: current-affairs">
                    <p class="dc-help">ইংরেজি ছোট হাতের অক্ষর, সংখ্যা ও মাঝখানে Hyphen (-) ব্যবহার করুন। বাংলা নাম হলে Slug নিজে লিখুন।</p>
                </div>
                <div class="dc-form-row">
                    <label class="dc-label" for="dcCategoryIcon">Icon</label>
                    <select class="dc-field" id="dcCategoryIcon" name="icon">
                        <option value="">Default Icon</option>
                        <option value="📰">📰 Newspaper</option><option value="📆">📆 Calendar</option>
                        <option value="💡">💡 General Knowledge</option><option value="🌐">🌐 International</option>
                        <option value="🔬">🔬 Science</option><option value="📚">📚 Books</option>
                        <option value="📝">📝 Notes</option>
                    </select>
                </div>
                <div class="dc-form-row">
                    <label class="dc-label" for="dcCategoryDescription">Description (Optional)</label>
                    <textarea class="dc-field" id="dcCategoryDescription" name="description" rows="3" maxlength="5000" placeholder="ক্যাটাগরি সম্পর্কে ছোট বিবরণ"></textarea>
                </div>
                <div class="dc-form-row">
                    <label class="dc-label" for="dcSortOrder">Sort Order</label>
                    <input class="dc-field" id="dcSortOrder" type="number" name="sort_order" min="0" max="65535" value="0">
                </div>
                <div class="dc-form-row">
                    <label class="dc-label" for="dcCategoryStatus">Status</label>
                    <select class="dc-field" id="dcCategoryStatus" name="status">
                        <option value="active">Active</option><option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="dc-modal-actions">
                    <button class="dc-button neutral" type="button" id="dcCancelModal">Cancel</button>
                    <button class="dc-button primary" type="submit" id="dcSaveCategory">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</main>
<script>
(function () {
  'use strict';
  const root = document.getElementById('lebdDuCategoryRoot');
  if (!root) return;
  const find = id => document.getElementById(id);
  const form = find('dcCategoryForm');
  const method = find('dcMethod');
  const rows = Array.from(root.querySelectorAll('.dc-data-row'));
  let previousFocus = null;
  let editing = false;

  function selectIcon(value) {
    const select = find('dcCategoryIcon');
    let temporary = select.querySelector('[data-temporary="1"]');
    if (temporary) temporary.remove();
    if (value && !Array.from(select.options).some(option => option.value === value)) {
      const option = new Option(value + ' (Existing)', value);
      option.dataset.temporary = '1';
      select.add(option);
    }
    select.value = value || '';
  }

  function openModal(button) {
    previousFocus = document.activeElement;
    editing = !!button;
    method.disabled = !editing;
    form.action = editing ? button.dataset.url : root.dataset.storeUrl;
    find('dcEditId').value = editing ? button.dataset.editId : '';
    find('dcModalTitle').textContent = editing ? 'Edit Category' : 'Add New Category';
    find('dcSaveCategory').textContent = editing ? 'Update Category' : 'Save Category';
    find('dcCategoryName').value = editing ? button.dataset.name : '';
    find('dcCategorySlug').value = editing ? button.dataset.slug : '';
    selectIcon(editing ? button.dataset.icon : '');
    find('dcCategoryDescription').value = editing ? button.dataset.description : '';
    find('dcCategoryStatus').value = editing ? button.dataset.status : 'active';
    find('dcSortOrder').value = editing ? button.dataset.sortOrder : '0';
    find('dcCategorySlug').dataset.touched = editing ? 'yes' : 'no';
    find('dcModal').classList.add('open');
    find('dcCategoryName').focus();
  }

  function closeModal() {
    find('dcModal').classList.remove('open');
    if (previousFocus && typeof previousFocus.focus === 'function') previousFocus.focus();
  }

  function filterRows() {
    const term = find('dcSearch').value.trim().toLocaleLowerCase();
    const status = find('dcStatusFilter').value;
    let matches = 0;
    rows.forEach(row => {
      const match = (status === 'all' || row.dataset.status === status) &&
        (!term || row.dataset.name.toLocaleLowerCase().includes(term) || row.dataset.slug.toLowerCase().includes(term));
      row.hidden = !match;
      if (match) matches++;
    });
    const noResults = find('dcNoResults');
    if (noResults) noResults.hidden = matches !== 0;
    find('dcVisibleCount').textContent = rows.length + ' Categories থেকে ' + matches + 'টি দেখানো হচ্ছে';
  }

  find('dcAddCategory').addEventListener('click', () => openModal(null));
  find('dcCloseModal').addEventListener('click', closeModal);
  find('dcCancelModal').addEventListener('click', closeModal);
  find('dcModal').addEventListener('click', ev => {
    if (ev.target === find('dcModal')) closeModal();
  });
  document.addEventListener('keydown', ev => {
    if (ev.key === 'Escape' && find('dcModal').classList.contains('open')) closeModal();
  });
  root.querySelectorAll('.dc-edit').forEach(button => button.addEventListener('click', () => openModal(button)));
  root.querySelectorAll('.dc-delete-form').forEach(deleteForm => {
    deleteForm.addEventListener('submit', ev => {
      if (!window.confirm('"' + deleteForm.dataset.categoryName + '" Category মুছে ফেলবেন? এটি Database থেকেও Delete হবে।')) {
        ev.preventDefault();
      }
    });
  });
  find('dcSearch').addEventListener('input', filterRows);
  find('dcStatusFilter').addEventListener('change', filterRows);
  find('dcCategoryName').addEventListener('input', function () {
    if (editing || find('dcCategorySlug').dataset.touched === 'yes') return;
    const value = this.value.trim();
    if (/^[a-z0-9\s-]+$/i.test(value)) {
      find('dcCategorySlug').value = value.toLowerCase().replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-|-$/g, '');
    }
  });
  find('dcCategorySlug').addEventListener('input', function () {
    this.dataset.touched = 'yes';
  });
  form.addEventListener('submit', function (ev) {
    const slug = find('dcCategorySlug').value.trim().toLowerCase();
    find('dcCategorySlug').value = slug;
    if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slug)) {
      ev.preventDefault();
      find('dcCategorySlug').setCustomValidity('Slug-এ a-z, 0-9 ও Hyphen ব্যবহার করুন।');
      find('dcCategorySlug').reportValidity();
      return;
    }
    find('dcCategorySlug').setCustomValidity('');
    find('dcSaveCategory').disabled = true;
    find('dcSaveCategory').textContent = 'Saving...';
  });
  find('dcCategorySlug').addEventListener('input', function () { this.setCustomValidity(''); });

  // On server-side validation error: restore the previously submitted form.
  if (root.dataset.oldHadErrors === '1') {
    const oldId = root.dataset.oldEditId;
    const editButton = oldId ? Array.from(root.querySelectorAll('.dc-edit')).find(btn => btn.dataset.editId === oldId) : null;
    openModal(editButton || null);
    find('dcCategoryName').value = root.dataset.oldName || '';
    find('dcCategorySlug').value = root.dataset.oldSlug || '';
    selectIcon(root.dataset.oldIcon || '');
    find('dcCategoryDescription').value = root.dataset.oldDescription || '';
    find('dcCategoryStatus').value = root.dataset.oldStatus || 'active';
    find('dcSortOrder').value = root.dataset.oldSortOrder || '0';
    find('dcCategorySlug').dataset.touched = 'yes';
  }
  filterRows();
})();
</script>
@endsection
