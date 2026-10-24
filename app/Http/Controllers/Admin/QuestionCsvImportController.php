<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

/** LEBD-04 Fix41: separate MCQ CSV importer; never calls or alters manual QuestionCtrl. */
class QuestionCsvImportController extends Controller
{
    private const COLUMNS = [
        'title', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_answer', 'explanation',
    ];
    private const SESSION_KEY = 'lebd04_question_csv_preview';
    private const CONTEXT_KEY = 'lebd04_question_csv_context';
    private const RELATIONS = [
        'course_ids' => 'courses', 'department_ids' => 'departments',
        'semester_ids' => 'semesters', 'subject_ids' => 'subjects',
        'chapter_ids' => 'chapters', 'sub_filter_ids' => 'sub_filters',
    ];

    public function index()
    {
        return view('admin.question-csv.index', [
            'preview' => session(self::SESSION_KEY),
            'context' => session(self::CONTEXT_KEY),
            'report' => session('lebd04_csv_import_report'),
        ]);
    }

    /** Save selectors from Add Question; never save a Question in this step. */
    public function setContext(Request $request)
    {
        $context = [];
        $errors = [];
        foreach (self::RELATIONS as $field => $table) {
            $raw = $request->input($field, []);
            if (!is_array($raw)) $raw = [];
            $ids = [];
            foreach ($raw as $value) {
                if (!is_scalar($value) || !preg_match('/^[1-9][0-9]*$/D', (string) $value)) {
                    $errors[] = $field . ': invalid ID';
                    continue;
                }
                $ids[] = (int)$value;
            }
            $ids = array_values(array_unique($ids));
            if (count($ids) > 200) $errors[] = $field . ': too many selections';
            if (in_array($field, ['course_ids','department_ids'], true) && !$ids) {
                $errors[] = 'Please select Course and Department on Add Question.';
            }
            if ($ids && DB::table($table)->whereIn('id', $ids)->count() !== count($ids)) {
                $errors[] = $field . ': one or more selected IDs do not exist.';
            }
            $context[$field] = implode('|', $ids);
        }
        if ($context['sub_filter_ids'] !== '' && Schema::hasTable('lebd_filter_visibility_baselines')) {
            $cutoff = DB::table('lebd_filter_visibility_baselines')->where('id',1)->value('legacy_max_sub_filter_id');
            if ($cutoff !== null && DB::table('sub_filters')->whereIn('id',$this->ids($context['sub_filter_ids']))->where('id','<=',(int)$cutoff)->exists()) {
                $errors[] = 'Old hidden filter options cannot be used.';
            }
        }
        $rawLabels = $request->input('labels', []);
        $rawLabels = is_array($rawLabels) ? $rawLabels : [];
        $labels = [];
        foreach ($rawLabels as $value) {
            if (!is_string($value) || mb_strlen($value) > 255) { $errors[] = 'Invalid label'; continue; }
            if (trim($value) !== '') $labels[] = trim($value);
        }
        if (count($labels)>50) $errors[]='Too many labels';
        $context['labels'] = implode('|',array_unique($labels));
        if ($errors) return redirect()->route('question.create')->withErrors($errors);
        session([self::CONTEXT_KEY => $context]);
        session()->forget(self::SESSION_KEY);
        return redirect()->route('admin.question-csv.index');
    }

    public function template()
    {
        return response()->streamDownload(function () {
            $out=fopen('php://output','w');
            fwrite($out,"\xEF\xBB\xBF");
            fputcsv($out,self::COLUMNS);
            fputcsv($out,['বাংলাদেশের রাজধানী কোথায়?','ঢাকা','খুলনা','রাজশাহী','সিলেট','A','ঢাকা বাংলাদেশের রাজধানী।']);
            fclose($out);
        }, 'LEBD04_MCQ_Import_Template.csv', [
            'Content-Type'=>'text/csv; charset=UTF-8','Cache-Control'=>'no-store',
        ]);
    }

    public function preview(Request $request)
    {
        $context = session(self::CONTEXT_KEY);
        if (!$context || empty($context['course_ids']) || empty($context['department_ids'])) {
            return redirect()->route('admin.question-csv.index')->withErrors(['csv'=>'First choose Course, Department and Filters on Add Question, then click Bulk CSV Upload.']);
        }
        $request->validate(['csv' => 'required|file|mimes:csv,txt|max:2048']);
        $path = $request->file('csv')->getRealPath();
        $handle = fopen($path, 'rb');
        if (!$handle) return back()->withErrors(['csv' => 'CSV file cannot be read.']);

        $header = fgetcsv($handle);
        if ($header === false) { fclose($handle); return back()->withErrors(['csv' => 'CSV is empty.']); }
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]);
        $header = array_map(fn ($x) => strtolower(trim((string) $x)), $header);
        if (count($header) !== count(array_unique($header)) || array_diff(self::COLUMNS, $header)) {
            fclose($handle);
            return back()->withErrors(['csv' => 'CSV header is invalid. Download the template and preserve every column name.']);
        }
        $rows = [];
        $line = 1;
        $seen = [];
        while (($cells = fgetcsv($handle)) !== false) {
            $line++;
            if ($cells === [null] || count(array_filter($cells, fn ($x) => trim((string) $x) !== '')) === 0) continue;
            if (count($rows) >= 500) { fclose($handle); return back()->withErrors(['csv' => 'Maximum 500 MCQ rows per upload.']); }
            $data = array_combine($header, array_pad(array_slice($cells, 0, count($header)), count($header), ''));
            $data = array_map(fn ($x) => trim((string) $x), $data);
            $errors = [];
            if (count($cells) !== count($header)) $errors[] = 'Incorrect column count';
            if (strlen($data['title']) < 3 || strlen($data['title']) > 60000) $errors[] = 'Question title is required (3–60000 bytes)';
            foreach (['option_a','option_b','option_c','option_d'] as $key) {
                if ($data[$key] === '') $errors[] = strtoupper($key).' is required';
            }
            $correct = strtoupper($data['correct_answer']);
            if (!in_array($correct, ['A','B','C','D','1','2','3','4'], true)) $errors[] = 'Correct answer must be A/B/C/D or 1/2/3/4';
            $data['correct_answer'] = $correct;
            // All relationships are taken from Add Question page, not from CSV columns.
            foreach (self::RELATIONS as $field => $table) {
                $data[$field] = $context[$field] ?? '';
            }
            $data['labels'] = $context['labels'] ?? '';
            $key = mb_strtolower(trim(strip_tags($data['title'])), 'UTF-8');
            if (isset($seen[$key])) $errors[] = 'Duplicate question within this CSV (row '.$seen[$key].')';
            $seen[$key] = $line;
            if ($data['title'] !== '' && Question::where('type','MCQ')->where('title',$data['title'])->exists()) {
                $errors[] = 'Question already exists in database';
            }
            $rows[] = ['line' => $line, 'data' => $data, 'errors' => $errors];
        }
        fclose($handle);
        if (!$rows) return back()->withErrors(['csv' => 'CSV has no question rows.']);
        $preview = [
            'token' => (string) Str::uuid(), 'created_at' => time(), 'rows' => $rows, 'context' => $context,
            'valid_count' => count(array_filter($rows, fn ($r) => !$r['errors'])),
            'invalid_count' => count(array_filter($rows, fn ($r) => (bool) $r['errors'])),
        ];
        session([self::SESSION_KEY => $preview]);
        session()->forget('lebd04_csv_import_report');
        return redirect()->route('admin.question-csv.index');
    }

    public function import(Request $request)
    {
        $request->validate(['token' => 'required|string']);
        $preview = session(self::SESSION_KEY);
        if (!$preview || !hash_equals($preview['token'], $request->input('token')) || time() - $preview['created_at'] > 3600) {
            return back()->withErrors(['token' => 'Preview expired. Upload the CSV again.']);
        }
        if (($preview['context'] ?? null) !== session(self::CONTEXT_KEY)) {
            return back()->withErrors(['token' => 'Selection changed. Preview the CSV again.']);
        }
        // Consume token before writes to prevent accidental repeated imports.
        session()->forget(self::SESSION_KEY);
        $result = ['created' => 0, 'skipped' => 0, 'errors' => []];
        foreach ($preview['rows'] as $row) {
            if ($row['errors']) { $result['skipped']++; continue; }
            $d = $row['data'];
            try {
                DB::transaction(function () use ($d) {
                    if (Question::where('type','MCQ')->where('title',$d['title'])->exists()) {
                        throw new \RuntimeException('Duplicate question exists');
                    }
                    $now = now();
                    $id = DB::table('questions')->insertGetId([
                        'type' => 'MCQ', 'title' => $d['title'], 'explanation' => $d['explanation'] ?: null,
                        'status' => 'Active', 'created_by' => Auth::id(),
                        'created_at' => $now, 'updated_at' => null,
                    ]);
                    $correct = in_array($d['correct_answer'], ['A','B','C','D'], true)
                        ? ord($d['correct_answer']) - ord('A') : ((int)$d['correct_answer'] - 1);
                    foreach (['option_a','option_b','option_c','option_d'] as $index => $field) {
                        DB::table('mcq_items')->insert([
                            'question_id' => $id, 'item' => $d[$field],
                            'correct_answer' => $index === $correct ? '1' : null,
                            'image' => null, 'image_style' => null,
                        ]);
                    }
                    $map = [
                        'course_ids' => ['course_question','course_id'],
                        'department_ids' => ['department_question','department_id'],
                        'semester_ids' => ['question_semester','semester_id'],
                        'subject_ids' => ['question_subject','subject_id'],
                        'chapter_ids' => ['chapter_question','chapter_id'],
                        'sub_filter_ids' => ['question_sub_filter','sub_filter_id'],
                    ];
                    foreach ($map as $field => [$table,$column]) {
                        foreach ($this->ids($d[$field]) as $foreignId) {
                            DB::table($table)->insert(['question_id' => $id, $column => $foreignId]);
                        }
                    }
                    foreach (array_unique(array_filter(array_map('trim', explode('|', $d['labels'])))) as $label) {
                        if (mb_strlen($label) > 255) throw new \RuntimeException('Label too long');
                        DB::table('labels')->insert(['question_id' => $id, 'label' => $label]);
                    }
                });
                $result['created']++;
            } catch (Throwable $e) {
                $result['skipped']++;
                $result['errors'][] = 'CSV row '.$row['line'].': '.$e->getMessage();
            }
        }
        session()->flash('lebd04_csv_import_report', $result);
        return redirect()->route('admin.question-csv.index');
    }

    /** Pipe-separated positive integer IDs, unique; invalid text must not silently become an ID. */
    private function ids(string $raw): array
    {
        if ($raw === '') return [];
        $parts = array_map('trim', explode('|', $raw));
        foreach ($parts as $part) if (!preg_match('/^[1-9][0-9]*$/D', $part)) return [];
        return array_values(array_unique(array_map('intval', $parts)));
    }
}
