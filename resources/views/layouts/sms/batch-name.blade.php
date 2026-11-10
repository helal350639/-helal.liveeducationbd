@extends('dashboard')
@section('title', 'Create Batch Name')
@section('content')
<section class="content-header">
    <h1>Create Batch Name <small>SMS Management</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ url('/home') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li><a href="{{ route('sms.index') }}">SMS</a></li>
        <li class="active">Create Batch Name</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary sms-batch-name-box">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-list-alt text-primary"></i> Create Batch Name</h3>
        </div>
        <div class="box-body">
            <div class="form-group">
                <label for="sms_source_type">Select Category</label>
                <select id="sms_source_type" class="form-control">
                    <option value="">Select Category</option>
                    <option value="batch_list">Batch List</option>
                    <option value="exam_section">Exam Section List</option>
                </select>
            </div>

            <div id="batch_source_fields" class="sms-dependent-fields" style="display:none;">
                <div class="form-group">
                    <label for="sms_course_id">Select Course</label>
                    <select id="sms_course_id" class="form-control" disabled>
                        <option value="">Select Course</option>
                    </select>
                </div>
                <div class="form-group" id="sms_batch_group" style="display:none;">
                    <label for="sms_batch_id">Select Batch</label>
                    <select id="sms_batch_id" class="form-control" disabled>
                        <option value="">Select Batch</option>
                    </select>
                    <p class="help-block" id="sms_batch_meta"></p>
                </div>
            </div>

            <div id="exam_source_fields" class="sms-dependent-fields" style="display:none;">
                <div class="form-group">
                    <label for="sms_exam_category_id">Select Exam Section Category</label>
                    <select id="sms_exam_category_id" class="form-control" disabled>
                        <option value="">Select Category</option>
                    </select>
                </div>
                <div class="form-group" id="sms_exam_subcategory_group" style="display:none;">
                    <label for="sms_exam_subcategory_id">Select Subcategory</label>
                    <select id="sms_exam_subcategory_id" class="form-control" disabled>
                        <option value="">Select Subcategory</option>
                    </select>
                </div>
                <div class="alert alert-info" id="sms_no_subcategories" style="display:none;margin-bottom:0;">
                    এই Category-এর অধীনে কোনো Subcategory নেই।
                </div>
            </div>

            <form id="sms_batch_name_save_form" method="POST" action="{{ route('sms.batch-name.store') }}" style="display:none;margin-top:18px;">
                @csrf
                <input type="hidden" name="source_type" id="sms_save_source_type">
                <input type="hidden" name="course_id" id="sms_save_course_id">
                <input type="hidden" name="batch_id" id="sms_save_batch_id">
                <input type="hidden" name="category_id" id="sms_save_category_id">
                <input type="hidden" name="subcategory_id" id="sms_save_subcategory_id">
                <button type="submit" class="btn btn-success"><i class="fa fa-plus-circle"></i> Create</button>
            </form>
            <div id="sms_selector_feedback" class="text-muted small" style="margin-top:12px;" aria-live="polite"></div>
        </div>
    </div>
</section>

<style>
.sms-batch-name-box{border-top:3px solid #1685c5;border-radius:4px;box-shadow:0 2px 8px rgba(25,49,78,.05)}
.sms-batch-name-box>.box-header{padding:14px 18px;background:#fff}
.sms-batch-name-box .box-title{font-size:16px;font-weight:700;color:#263d58}
.sms-batch-name-box .box-body{padding:22px 24px}
.sms-batch-name-box .form-group{max-width:760px;margin-bottom:20px}
.sms-batch-name-box label{display:block;color:#344a64;font-weight:600;margin-bottom:7px}
.sms-batch-name-box .form-control{height:42px;border:1px solid #d5deea;border-radius:5px;box-shadow:none}
.sms-batch-name-box .form-control:focus{border-color:#3c8dbc;box-shadow:0 0 0 2px rgba(60,141,188,.10)}
.sms-batch-name-box .help-block{font-size:12px;margin:7px 0 0;color:#71839a}
.sms-batch-name-box .alert-info{max-width:760px}
@media(max-width:767px){.sms-batch-name-box .box-body{padding:16px}.sms-batch-name-box .form-group{max-width:100%}}
</style>

<script>
(function () {
    'use strict';

    var urls = {
        courses: @json(route('sms.batch-name.courses')),
        batches: @json(url('sms/create-batch-name/data/courses')),
        categories: @json(route('sms.batch-name.exam-categories')),
        subcategories: @json(url('sms/create-batch-name/data/exam-categories'))
    };

    var sourceType = document.getElementById('sms_source_type');
    var batchFields = document.getElementById('batch_source_fields');
    var examFields = document.getElementById('exam_source_fields');
    var courseSelect = document.getElementById('sms_course_id');
    var batchSelect = document.getElementById('sms_batch_id');
    var batchGroup = document.getElementById('sms_batch_group');
    var batchMeta = document.getElementById('sms_batch_meta');
    var categorySelect = document.getElementById('sms_exam_category_id');
    var subcategorySelect = document.getElementById('sms_exam_subcategory_id');
    var subcategoryGroup = document.getElementById('sms_exam_subcategory_group');
    var noSubcategories = document.getElementById('sms_no_subcategories');
    var feedback = document.getElementById('sms_selector_feedback');
    var saveForm = document.getElementById('sms_batch_name_save_form');
    var saveSourceType = document.getElementById('sms_save_source_type');
    var saveCourseId = document.getElementById('sms_save_course_id');
    var saveBatchId = document.getElementById('sms_save_batch_id');
    var saveCategoryId = document.getElementById('sms_save_category_id');
    var saveSubcategoryId = document.getElementById('sms_save_subcategory_id');

    function hideSave() {
        saveForm.style.display = 'none';
        saveSourceType.value = saveCourseId.value = saveBatchId.value = '';
        saveCategoryId.value = saveSubcategoryId.value = '';
    }
    function showSave(type, values) {
        saveSourceType.value = type;
        saveCourseId.value = values.course_id || '';
        saveBatchId.value = values.batch_id || '';
        saveCategoryId.value = values.category_id || '';
        saveSubcategoryId.value = values.subcategory_id || '';
        saveForm.style.display = 'block';
    }

    function setOptions(select, placeholder, rows) {
        select.innerHTML = '';
        var first = document.createElement('option');
        first.value = '';
        first.textContent = placeholder;
        select.appendChild(first);
        (rows || []).forEach(function (row) {
            var option = document.createElement('option');
            option.value = row.id;
            option.textContent = row.name;
            select.appendChild(option);
        });
        select.disabled = !(rows && rows.length);
    }

    function getJson(url) {
        return fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'}})
            .then(function (response) {
                if (!response.ok) throw new Error('তথ্য লোড করা যায়নি। পেজটি Refresh করে আবার চেষ্টা করুন।');
                return response.json();
            });
    }

    function resetBatchFields() {
        setOptions(courseSelect, 'Select Course', []);
        setOptions(batchSelect, 'Select Batch', []);
        batchGroup.style.display = 'none';
        batchMeta.textContent = '';
    }

    function resetExamFields() {
        setOptions(categorySelect, 'Select Category', []);
        setOptions(subcategorySelect, 'Select Subcategory', []);
        subcategoryGroup.style.display = 'none';
        noSubcategories.style.display = 'none';
    }

    sourceType.addEventListener('change', function () {
        batchFields.style.display = 'none';
        examFields.style.display = 'none';
        feedback.textContent = '';
        hideSave();
        resetBatchFields();
        resetExamFields();

        if (this.value === 'batch_list') {
            batchFields.style.display = 'block';
            feedback.textContent = 'প্রথমে Course নির্বাচন করুন, এরপর সেই Course-এর Batch দেখানো হবে।';
            getJson(urls.courses).then(function (result) {
                setOptions(courseSelect, 'Select Course', result.data || []);
                if (!result.data || !result.data.length) feedback.textContent = 'কোনো Course পাওয়া যায়নি।';
            }).catch(function (error) { feedback.textContent = error.message; });
        } else if (this.value === 'exam_section') {
            examFields.style.display = 'block';
            feedback.textContent = 'Exam Section Dashboard-এর Category নির্বাচন করুন।';
            getJson(urls.categories).then(function (result) {
                setOptions(categorySelect, 'Select Category', result.data || []);
                if (!result.data || !result.data.length) feedback.textContent = 'কোনো Exam Section Category পাওয়া যায়নি।';
            }).catch(function (error) { feedback.textContent = error.message; });
        }
    });

    courseSelect.addEventListener('change', function () {
        var courseId = this.value;
        setOptions(batchSelect, 'Select Batch', []);
        batchGroup.style.display = 'none';
        batchMeta.textContent = '';
        hideSave();
        if (!courseId) return;
        getJson(urls.batches + '/' + encodeURIComponent(courseId) + '/batches').then(function (result) {
            var rows = result.data || [];
            setOptions(batchSelect, 'Select Batch', rows);
            batchGroup.style.display = 'block';
            batchMeta.textContent = rows.length ? rows.length + 'টি বর্তমানে মেয়াদ থাকা Active Batch পাওয়া গেছে।' : 'এই Course-এর অধীনে বর্তমানে মেয়াদ থাকা Batch নেই।';
        }).catch(function (error) { feedback.textContent = error.message; });
    });

    batchSelect.addEventListener('change', function () {
        hideSave();
        if (this.value && courseSelect.value) {
            showSave('batch_list', {course_id: courseSelect.value, batch_id: this.value});
        }
    });

    categorySelect.addEventListener('change', function () {
        var categoryId = this.value;
        setOptions(subcategorySelect, 'Select Subcategory', []);
        subcategoryGroup.style.display = 'none';
        noSubcategories.style.display = 'none';
        hideSave();
        if (!categoryId) return;
        getJson(urls.subcategories + '/' + encodeURIComponent(categoryId) + '/subcategories').then(function (result) {
            var rows = result.data || [];
            if (result.has_subcategories && rows.length) {
                setOptions(subcategorySelect, 'Select Subcategory', rows);
                subcategoryGroup.style.display = 'block';
            } else {
                noSubcategories.style.display = 'none';
                showSave('exam_section', {category_id: categoryId});
            }
        }).catch(function (error) { feedback.textContent = error.message; });
    });

    subcategorySelect.addEventListener('change', function () {
        hideSave();
        if (this.value && categorySelect.value) {
            showSave('exam_section', {category_id: categorySelect.value, subcategory_id: this.value});
        }
    });
})();
</script>
@endsection
