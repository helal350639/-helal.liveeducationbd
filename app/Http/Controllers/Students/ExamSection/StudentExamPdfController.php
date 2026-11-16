<?php

namespace App\Http\Controllers\Students\ExamSection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\ExamSectionMonthlyAccess;

/** Student-facing, click-to-create PDF for questions already generated from Routine topics. */
class StudentExamPdfController extends Controller
{
    public function index(Request $request, int $category)
    {
        $cat = DB::table('lebd_exam_section_categories')->where('id', $category)->first();
        abort_unless($cat && strcasecmp((string) $cat->status, 'Active') === 0, 404);

        $hasSubs = DB::table('lebd_exam_section_subcategories')
            ->where('category_id', $category)->where('status', 'Active')->exists();
        $sub = null;
        if ($hasSubs) {
            $subId = (int) $request->query('subcategory_id', 0);
            $sub = DB::table('lebd_exam_section_subcategories')
                ->where('category_id', $category)->where('id', $subId)->where('status', 'Active')->first();
            abort_unless($sub, 404);
        }

        $student = Auth::guard('student')->user();
        $hasPdfAccess = $student && ExamSectionMonthlyAccess::premium((int) $student->id, (int) $category, $sub?->id ? (int) $sub->id : null);

        $routines = DB::table('lebd_exam_section_routines')
            ->where('category_id', $category)
            ->where('subcategory_id', $sub?->id)
            ->where('status', 'Published')
            ->orderBy('id')
            ->get(['id', 'name', 'created_at']);
        $routineIds = $routines->pluck('id')->all();

        $items = $routineIds
            ? DB::table('lebd_exam_section_routine_items')
                ->whereIn('routine_id', $routineIds)
                ->where('type', 'exam')
                ->orderBy('exam_start_at')->orderBy('id')->get()
            : collect();

        // First exam becomes available at Routine creation; each following exam
        // becomes available when the previous exam ends. All times use Asia/Dhaka.
        $previousEndByRoutine = [];
        foreach ($items as $scheduledItem) {
            $routine = $routines->firstWhere('id', $scheduledItem->routine_id);
            $scheduledAt = $scheduledItem->pdf_create_at
                ?: ($previousEndByRoutine[$scheduledItem->routine_id] ?? ($routine->created_at ?? null));
            $scheduledItem->pdf_create_at = $scheduledAt;
            $previousEndByRoutine[$scheduledItem->routine_id] = $scheduledItem->exam_end_at;
            $scheduledItem->pdf_publish_timestamp = null;
            $scheduledItem->pdf_is_due = false;
            if ($scheduledAt) {
                try {
                    $publishMoment = \Carbon\Carbon::parse($scheduledAt, 'Asia/Dhaka')->timezone('Asia/Dhaka');
                    $scheduledItem->pdf_publish_timestamp = $publishMoment->timestamp * 1000;
                    $scheduledItem->pdf_is_due = $publishMoment->lessThanOrEqualTo(\Carbon\Carbon::now('Asia/Dhaka'));
                } catch (\Throwable $e) {
                    $scheduledItem->pdf_publish_timestamp = null;
                    $scheduledItem->pdf_is_due = false;
                }
            }
        }

        $counts = collect();
        if ($routineIds) {
            $counts = DB::table('lebd_exam_section_generated_questions')
                ->whereIn('routine_id', $routineIds)
                ->select('routine_id', 'exam_name', DB::raw('COUNT(DISTINCT question_id) AS question_count'))
                ->groupBy('routine_id', 'exam_name')
                ->get()
                ->keyBy(fn ($row) => $row->routine_id . '|' . $row->exam_name);
        }

        $items = $items->map(function ($item) use ($routines, $counts) {
            $item->routine_name = $routines->firstWhere('id', $item->routine_id)->name ?? 'Routine';
            $countRow = $counts->get($item->routine_id . '|' . $item->exam_name);
            $item->question_count = (int) ($countRow->question_count ?? 0);
            $manifestPath = storage_path('app/exam-section-pdf-cache/' . (int) $item->routine_id . '/' . (int) $item->id . '/shared.json');
            $manifest = is_file($manifestPath) ? json_decode((string) @file_get_contents($manifestPath), true) : null;
            $sharedFile = is_array($manifest) && !empty($manifest['file']) ? basename((string) $manifest['file']) : null;
            $sharedPath = $sharedFile ? dirname($manifestPath) . DIRECTORY_SEPARATOR . $sharedFile : null;
            $item->shared_pdf_ready = $sharedPath && is_file($sharedPath) && filesize($sharedPath) > 0;
            $item->pdf_status = $item->shared_pdf_ready ? 'Ready' : ($item->question_count > 0 ? 'Not created' : 'Preparing');
            return $item;
        });

        return view('student.exam-section.exam-pdfs', compact('cat', 'sub', 'items', 'hasPdfAccess'));
    }

    /**
     * First student creates the shared PDF for this exam item.
     * A single manifest per Routine Exam makes the same file available to every student.
     */
    public function build(Request $request, int $category, int $item)
    {
        $student = Auth::guard('student')->user();
        abort_unless($student, 403, 'Please log in as a student.');

        $settings = $request->validate([
            'columns' => 'required|integer|in:1,2,3',
            'per_page' => 'required|integer|in:10,15,20,25,30',
            'outer_border' => 'required|in:0,1',
        ]);
        $columns = (int) $settings['columns'];
        $perPage = (int) $settings['per_page'];
        $outerBorder = (string) $settings['outer_border'] === '1';

        $row = $this->examRow($category, $item);
        if (!ExamSectionMonthlyAccess::premium((int) $student->id, (int) $row->category_id, $row->subcategory_id ? (int) $row->subcategory_id : null)) {
            return response()->json([
                'success' => false,
                'message' => 'আপনার এই Category/Sub-category-এর জন্য কোনো সক্রিয় Exam Package নেই। PDF তৈরি ও Download করতে প্যাকেজ কিনুন।',
                'package_url' => route('student.exam-section.packages', ['notice' => 'pdf']),
            ], 403);
        }
        $publishAt = $this->scheduledPdfAt($row);
        if ($publishAt && $publishAt->isFuture()) {
            return response()->json([
                'success' => false,
                'message' => 'এই PDF এখনো প্রকাশের সময় হয়নি। নির্ধারিত সময়: ' . $publishAt->format('d M Y, h:i A'),
                'publish_at' => $publishAt->toIso8601String(),
            ], 423);
        }

        // Read the exact question selection saved by the Routine workflow.
        $generated = DB::table('lebd_exam_section_generated_questions')
            ->where('routine_id', $row->routine_id)
            ->where('exam_name', $row->exam_name)
            ->orderBy('position')->orderBy('id')
            ->get(['question_id', 'topic_key', 'position']);

        $unique = [];
        foreach ($generated as $entry) {
            $qid = (int) $entry->question_id;
            if ($qid > 0 && !isset($unique[$qid])) $unique[$qid] = $entry;
        }
        abort_if(!$unique, 404, 'এই Exam-এর প্রশ্ন এখনো তৈরি হয়নি।');

        $questions = DB::table('questions')
            ->whereIn('id', array_keys($unique))
            ->where('type', 'MCQ')
            ->where('status', 'Active')
            ->get(['id', 'title', 'image'])
            ->keyBy('id');

        $optionRows = DB::table('mcq_items')
            ->whereIn('question_id', array_keys($unique))
            ->orderBy('id')
            ->get(['question_id', 'item', 'image', 'correct_answer'])
            ->groupBy('question_id');

        $paperQuestions = [];
        foreach ($unique as $qid => $entry) {
            $question = $questions->get($qid);
            if (!$question) continue;

            $options = [];
            foreach (($optionRows->get($qid) ?? collect()) as $index => $option) {
                $options[] = [
                    'letter' => chr(65 + $index),
                    'text' => $this->pdfPlainText((string) $option->item),
                    'image' => $this->localImagePath($option->image),
                    'correct' => (function ($value) {
                        $value = strtolower(trim((string) ($value ?? '')));
                        return $value !== '' && !in_array($value, ['0', 'false', 'no', 'off', 'null', 'none'], true);
                    })($option->correct_answer),
                ];
            }

            $paperQuestions[] = [
                'id' => $qid,
                'title' => $this->pdfPlainText((string) $question->title),
                'image' => $this->localImagePath($question->image),
                'options' => $options,
                'answer' => collect($options)->firstWhere('correct', true)['letter'] ?? null,
                'answer_text' => collect($options)->firstWhere('correct', true)['text'] ?? null,
            ];
        }
        abort_if(!$paperQuestions, 404, 'এই Exam-এর Active প্রশ্ন পাওয়া যায়নি।');

        $contentFingerprint = hash('sha256', json_encode([
            'routine_id' => (int) $row->routine_id,
            'exam_item_id' => (int) $row->id,
            'exam_name' => (string) $row->exam_name,
            'render_version' => 5,
            'questions' => $paperQuestions,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE));
        $fingerprint = hash('sha256', json_encode([
            'content' => $contentFingerprint,
            'columns' => $columns,
            'per_page' => $perPage,
            'outer_border' => $outerBorder,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE));

        $cacheDirectory = storage_path('app/exam-section-pdf-cache/' . (int) $row->routine_id . '/' . (int) $row->id);
        if (!is_dir($cacheDirectory) && !mkdir($cacheDirectory, 0775, true) && !is_dir($cacheDirectory)) {
            throw new \RuntimeException('PDF cache directory তৈরি করা যায়নি।');
        }

        $pdfFile = $fingerprint . '.pdf';
        $pdfPath = $cacheDirectory . DIRECTORY_SEPARATOR . $pdfFile;
        $manifestPath = $cacheDirectory . DIRECTORY_SEPARATOR . 'shared.json';
        $lock = fopen($cacheDirectory . DIRECTORY_SEPARATOR . 'shared.lock', 'c');
        abort_unless($lock, 500, 'Shared PDF lock তৈরি করা যায়নি।');

        $startedAt = microtime(true);
        $created = false;
        $sharedManifest = null;

        try {
            if (!flock($lock, LOCK_EX)) {
                throw new \RuntimeException('Shared PDF lock পাওয়া যায়নি।');
            }

            $existing = is_file($manifestPath) ? json_decode((string) @file_get_contents($manifestPath), true) : null;
            $existingFile = is_array($existing) && !empty($existing['file']) ? basename((string) $existing['file']) : null;
            $existingPath = $existingFile ? $cacheDirectory . DIRECTORY_SEPARATOR . $existingFile : null;

            // If the same Routine Exam PDF already exists and its content is unchanged,
            // do not render it again for this student or any other student.
            if (is_array($existing)
                && ($existing['content_fingerprint'] ?? null) === $contentFingerprint
                && $existingPath && is_file($existingPath) && filesize($existingPath) > 0) {
                // The first student's selected layout is the shared layout for everyone.
                // A later student cannot silently replace it with a different layout.
                $pdfFile = $existingFile;
                $pdfPath = $existingPath;
                $sharedManifest = $existing;
            } else {
                // Reuse an old cached variant if it was generated earlier.
                if (!is_file($pdfPath) || filesize($pdfPath) < 1) {
                    $this->buildSharedPdf($pdfPath, $row, $paperQuestions, $columns, $perPage, $outerBorder);
                    $created = true;
                }

                $sharedManifest = [
                    'fingerprint' => $fingerprint,
                    'content_fingerprint' => $contentFingerprint,
                    'file' => $pdfFile,
                    'columns' => $columns,
                    'per_page' => $perPage,
                    'outer_border' => $outerBorder,
                    'question_count' => count($paperQuestions),
                    'created_at' => date('c'),
                ];
                $tmpManifest = $manifestPath . '.' . bin2hex(random_bytes(5)) . '.tmp';
                file_put_contents($tmpManifest, json_encode($sharedManifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
                rename($tmpManifest, $manifestPath);
            }
        } finally {
            if (is_resource($lock)) {
                @flock($lock, LOCK_UN);
                fclose($lock);
            }
        }

        $duration = round(microtime(true) - $startedAt, 2);

        return response()->json([
            'success' => true,
            'created' => $created,
            'shared' => true,
            'message' => $created
                ? 'PDF তৈরি সম্পূর্ণ হয়েছে। এখন এই Routine-এর সব শিক্ষার্থী Download করতে পারবে।'
                : 'এই Routine-এর PDF আগে থেকেই তৈরি আছে। সবাই একই PDF ব্যবহার করবে।',
            'duration' => $duration,
            'question_count' => count($paperQuestions),
            'download_url' => route('student.exam-section.exam-pdf.download', ['category' => $category, 'item' => $item]),
            'view_url' => route('student.exam-section.exam-pdf.view', ['category' => $category, 'item' => $item]),
        ]);
    }

    /**
     * Reset the shared PDF for one Routine Exam.
     * Only site administrators can clear the shared cache; students cannot reset it.
     */
    public function resetSharedPdf(Request $request, int $category, int $item)
    {
        $user = Auth::guard('web')->user();
        abort_unless($user && $user->authorizeRoles(['SuperAdmin', 'Admin']), 403,
            'শুধুমাত্র Admin বা SuperAdmin Shared PDF Reset করতে পারবেন।');

        $row = $this->examRow($category, $item);
        $directory = storage_path('app/exam-section-pdf-cache/' . (int) $row->routine_id . '/' . (int) $row->id);

        if (!is_dir($directory)) {
            return redirect()->back()->with('status', 'এই Exam-এর Shared PDF Cache আগে থেকেই খালি আছে।');
        }

        $lock = fopen($directory . DIRECTORY_SEPARATOR . 'shared.lock', 'c');
        abort_unless($lock, 500, 'Shared PDF lock তৈরি করা যায়নি।');

        try {
            if (!flock($lock, LOCK_EX)) {
                throw new \RuntimeException('Shared PDF lock পাওয়া যায়নি।');
            }

            foreach (glob($directory . DIRECTORY_SEPARATOR . '*.pdf') ?: [] as $pdfFile) {
                if (is_file($pdfFile)) @unlink($pdfFile);
            }

            $manifestPath = $directory . DIRECTORY_SEPARATOR . 'shared.json';
            if (is_file($manifestPath)) @unlink($manifestPath);
        } finally {
            if (is_resource($lock)) {
                @flock($lock, LOCK_UN);
                fclose($lock);
            }
        }

        return redirect()->back()->with('status', (string) $row->exam_name . ' এর Shared PDF Reset হয়েছে। এখন আবার Create PDF করতে পারবেন।');
    }

    /** Show the one shared PDF generated for this Routine Exam. */
    public function view(Request $request, int $category, int $item)
    {
        return $this->serveSharedPdf($category, $item, true);
    }

    /** Download the one shared PDF generated for this Routine Exam. */
    public function download(Request $request, int $category, int $item)
    {
        return $this->serveSharedPdf($category, $item, false);
    }

    private function serveSharedPdf(int $category, int $item, bool $inline)
    {
        $student = Auth::guard('student')->user();
        abort_unless($student, 403, 'Please log in as a student.');

        $row = $this->examRow($category, $item);
        if (!ExamSectionMonthlyAccess::premium((int) $student->id, (int) $row->category_id, $row->subcategory_id ? (int) $row->subcategory_id : null)) {
            return redirect()->route('student.exam-section.packages', ['notice' => 'pdf']);
        }
        $directory = storage_path('app/exam-section-pdf-cache/' . (int) $row->routine_id . '/' . (int) $row->id);
        $manifestPath = $directory . DIRECTORY_SEPARATOR . 'shared.json';
        $manifest = is_file($manifestPath) ? json_decode((string) @file_get_contents($manifestPath), true) : null;

        $file = is_array($manifest) && !empty($manifest['file']) ? basename((string) $manifest['file']) : null;
        $pdfPath = $file ? $directory . DIRECTORY_SEPARATOR . $file : null;
        abort_unless($pdfPath && is_file($pdfPath) && filesize($pdfPath) > 0, 404,
            'এই Routine-এর PDF এখনো তৈরি হয়নি। প্রথমে Create PDF চাপুন।');

        $safeExamName = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $row->exam_name);
        $downloadName = (trim($safeExamName, '-') ?: 'Exam') . '-Question-Paper.pdf';
        $headers = [
            'Content-Type' => 'application/pdf',
            'X-LEBD-PDF-CACHE' => 'SHARED',
            'Cache-Control' => 'private, max-age=300',
        ];

        if ($inline) {
            return response()->file($pdfPath, $headers + [
                'Content-Disposition' => 'inline; filename="' . $downloadName . '"',
            ]);
        }

        return response()->download($pdfPath, $downloadName, $headers);
    }

    private function examRow(int $category, int $item): object
    {
        $row = DB::table('lebd_exam_section_routine_items as i')
            ->join('lebd_exam_section_routines as r', 'r.id', '=', 'i.routine_id')
            ->join('lebd_exam_section_categories as c', 'c.id', '=', 'r.category_id')
            ->leftJoin('lebd_exam_section_subcategories as s', 's.id', '=', 'r.subcategory_id')
            ->where('i.id', $item)->where('i.type', 'exam')
            ->where('r.category_id', $category)->where('r.status', 'Published')
            ->where('c.status', 'Active')
            ->select('i.*', 'r.name as routine_name', 'r.created_at as routine_created_at',
                'r.category_id', 'r.subcategory_id', 'c.name as category_name',
                's.name as subcategory_name', 's.status as subcategory_status')
            ->first();

        abort_unless($row, 404);
        if ($row->subcategory_id) {
            abort_unless(strcasecmp((string) $row->subcategory_status, 'Active') === 0, 404);
        }

        return $row;
    }

    /**
     * Resolve the scheduled PDF publish time. First exam: Routine creation.
     * Later exams: preceding exam end time.
     */
    private function scheduledPdfAt(object $row): ?\Carbon\Carbon
    {
        $scheduled = $row->pdf_create_at ?? null;
        if (!$scheduled) {
            $previous = DB::table('lebd_exam_section_routine_items')
                ->where('routine_id', $row->routine_id)
                ->where('type', 'exam')
                ->where('id', '<>', $row->id)
                ->where(function ($query) use ($row) {
                    if (!empty($row->exam_start_at)) {
                        $query->where('exam_start_at', '<', $row->exam_start_at)
                            ->orWhere(function ($q) use ($row) {
                                $q->where('exam_start_at', $row->exam_start_at)
                                  ->where('id', '<', $row->id);
                            });
                    } else {
                        $query->where('id', '<', $row->id);
                    }
                })
                ->orderByDesc('exam_start_at')->orderByDesc('id')
                ->first(['exam_end_at']);
            $scheduled = $previous->exam_end_at ?? $row->routine_created_at ?? null;
        }
        if (!$scheduled) return null;
        try {
            return \Carbon\Carbon::parse($scheduled, 'Asia/Dhaka')->timezone('Asia/Dhaka');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Create the shared A4 PDF once for the selected exam/layout variant.
     * Uses the bundled Noto Sans Bengali Unicode font and appends the MCQ answer key.
     */
    private function buildSharedPdf(string $pdfPath, object $row, array $questions, int $columns, int $perPage, bool $outerBorder): void
    {
        $fontDirectory = resource_path('fonts');
        $fontRegular = $fontDirectory . DIRECTORY_SEPARATOR . 'NotoSansBengali-Regular.ttf';
        abort_unless(is_file($fontRegular), 500, 'Noto Sans Bengali Regular font পাওয়া যায়নি।');

        $tempDirectory = storage_path('app/mpdf');
        if (!is_dir($tempDirectory)) {
            mkdir($tempDirectory, 0775, true);
        }

        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 9,
            'margin_bottom' => 12,
            'default_font_size' => 9,
            'default_font' => 'notosansbengali',
            'fontDir' => array_merge($defaultConfig['fontDir'], [$fontDirectory]),
            'fontdata' => $defaultFontConfig['fontdata'] + [
                'notosansbengali' => [
                    // One known-good Bengali Unicode face for all styles; avoids missing glyphs in mPDF bold face.
                    'R' => 'NotoSansBengali-Regular.ttf',
                    'B' => 'NotoSansBengali-Regular.ttf',
                    'I' => 'NotoSansBengali-Regular.ttf',
                    'BI' => 'NotoSansBengali-Regular.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ],
            ],
            'tempDir' => $tempDirectory,
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'useSubstitutions' => true,
            'shrink_tables_to_fit' => 1,
        ]);

        $mpdf->SetWatermarkText('LiveEducationBD', 0.08);
        $mpdf->watermark_font = 'notosansbengali';
        $mpdf->showWatermarkText = true;
        $mpdf->SetHTMLFooter(
            '<div style="border-top:0.4pt solid #d5dfeb;padding-top:3pt;text-align:center;color:#718096;font-size:7pt;">LiveEducationBD.com &nbsp; | &nbsp; {PAGENO} / {nbpg}</div>'
        );

        $chunks = array_chunk($questions, $perPage);
        $total = count($questions);
        $pageCount = count($chunks);

        foreach ($chunks as $pageIndex => $chunk) {
            if ($pageIndex > 0) {
                $mpdf->AddPage();
            }

            $html = '<html><head><meta charset="UTF-8"><style>
                body{font-family:notosansbengali,sans-serif;color:#172b45;font-size:9pt;line-height:1.35}
                h1{font-family:notosansbengali,sans-serif;font-size:17pt;text-align:center;color:#123b70;margin:0 0 3pt;font-weight:normal}
                h2{font-family:notosansbengali,sans-serif;font-size:12pt;text-align:center;margin:0 0 4pt;font-weight:normal}
                .qtitle{font-family:notosansbengali,sans-serif;font-weight:normal!important;font-style:normal!important}
                .meta{text-align:center;color:#536b8a;font-size:8pt;margin:2pt 0}
                .headrule{border-bottom:1pt solid #173b66;margin:7pt 0 9pt}
                table{border-collapse:collapse}
                .question-table{width:100%;table-layout:fixed}
                .column-cell{vertical-align:top;padding:0 3pt}
                .question-box{border:0.55pt solid #c7d5e5;padding:4pt;margin:0 0 5pt;vertical-align:top}
                .qtitle{font-family:notosansbengali,sans-serif;font-size:8.7pt;font-weight:normal!important;font-style:normal!important;margin-bottom:3pt}
                .options{width:100%;table-layout:fixed}
                .options td{width:50%;vertical-align:top;padding:1.5pt 2pt 1.5pt 0;font-size:8.2pt}
                .letter{font-weight:bold;color:#174d85}
                img{max-width:95%;max-height:38mm}
                .page-frame{padding:5pt}
                </style></head><body>';

            if ($pageIndex === 0) {
                $html .= '<div style="text-align:center;">'
                    . '<h1>BCS Question Bank</h1>'
                    . '<h2 style="font-family:notosansbengali;font-weight:normal;">' . e($this->pdfPlainText((string) $row->exam_name)) . ' — প্রশ্নপত্র</h2>'
                    . '<div class="meta">Routine: ' . e((string) $row->routine_name)
                    . ' · মোট প্রশ্ন: ' . $total . ' টি</div>'
                    . (!empty($row->exam_start_at)
                        ? '<div class="meta">পরীক্ষার তারিখ: ' . e(\Carbon\Carbon::parse($row->exam_start_at, 'Asia/Dhaka')->format('d M Y, h:i A')) . '</div>'
                        : '')
                    . '</div><div class="headrule"></div>';
            } else {
                $html .= '<h2 style="font-family:notosansbengali;font-weight:normal;">' . e($this->pdfPlainText((string) $row->exam_name)) . ' — প্রশ্নপত্র (চলমান)</h2>'
                    . '<div class="meta">LiveEducationBD · ' . ($pageIndex + 1) . ' / ' . $pageCount . '</div>'
                    . '<div class="headrule"></div>';
            }

            $rows = (int) ceil(count($chunk) / $columns);
            $html .= '<div class="page-frame"' . ($outerBorder ? ' style="border:0.7pt solid #8fa9c7;"' : '') . '>';
            $html .= '<table class="question-table"><tbody>';
            for ($r = 0; $r < $rows; $r++) {
                $html .= '<tr>';
                for ($c = 0; $c < $columns; $c++) {
                    $index = $r + ($c * $rows);
                    $html .= '<td class="column-cell"' . ($c < $columns - 1 ? ' style="border-right:0.7pt solid #a9bad0;"' : '') . '>';
                    if (isset($chunk[$index])) {
                        $q = $chunk[$index];
                        $html .= '<div class="question-box">';
                        $html .= '<div class="qtitle" style="font-family:notosansbengali;font-weight:normal;">' . ($index + 1 + ($pageIndex * $perPage)) . '. ' . e((string) $q['title']) . '</div>';
                        if (!empty($q['image']) && is_file($q['image'])) {
                            $html .= '<div><img src="' . e($q['image']) . '" alt="Question image"></div>';
                        }
                        $opts = $q['options'];
                        $optionRows = (int) ceil(count($opts) / 2);
                        $html .= '<table class="options"><tbody>';
                        for ($oi = 0; $oi < $optionRows; $oi++) {
                            $html .= '<tr>';
                            foreach ([$oi, $oi + $optionRows] as $optionIndex) {
                                $html .= '<td>';
                                if (isset($opts[$optionIndex])) {
                                    $option = $opts[$optionIndex];
                                    $html .= '<span class="letter">' . e((string) $option['letter']) . '.</span> ' . e((string) $option['text']);
                                    if (!empty($option['image']) && is_file($option['image'])) {
                                        $html .= '<br><img src="' . e($option['image']) . '" alt="Option image">';
                                    }
                                }
                                $html .= '</td>';
                            }
                            $html .= '</tr>';
                        }
                        $html .= '</tbody></table></div>';
                    }
                    $html .= '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table></div></body></html>';
            $mpdf->WriteHTML($html);
        }

        // Append a separate Answer Key page to the shared question paper.
        $mpdf->AddPage();
        $answerHtml = '<html><head><meta charset="UTF-8"><style>
            body{font-family:notosansbengali,sans-serif;color:#172b45;font-size:10pt;line-height:1.45}
            h2{font-family:notosansbengali,sans-serif;text-align:center;color:#123b70;font-size:15pt;font-weight:normal;margin:0 0 10pt}
            table{width:100%;border-collapse:collapse}
            th,td{border:0.5pt solid #c7d5e5;padding:5pt 7pt}
            th{background:#edf4fc;font-weight:normal}
            .answer{color:#087b43}
            .missing{color:#b45309}
            </style></head><body>';
        $answerHtml .= '<h2>সঠিক উত্তরমালা (Answer Key)</h2>';
        $answerHtml .= '<p style="text-align:center;color:#536b8a;">'
            . e($this->pdfPlainText((string) $row->exam_name))
            . ' · মোট প্রশ্ন: ' . count($questions) . ' টি</p>';
        $answerHtml .= '<table><thead><tr><th>প্রশ্ন</th><th>সঠিক অপশন</th><th>সঠিক উত্তর</th></tr></thead><tbody>';
        foreach ($questions as $questionIndex => $question) {
            $hasAnswer = !empty($question['answer']);
            $answerHtml .= '<tr><td>' . ($questionIndex + 1) . '</td><td class="' . ($hasAnswer ? 'answer' : 'missing') . '">'
                . e((string) ($question['answer'] ?? 'নির্ধারিত নেই')) . '</td><td class="' . ($hasAnswer ? 'answer' : 'missing') . '">'
                . e((string) ($question['answer_text'] ?? 'উত্তর নির্ধারিত নেই')) . '</td></tr>';
        }
        $answerHtml .= '</tbody></table></body></html>';
        $mpdf->WriteHTML($answerHtml);

        $mpdf->Output($pdfPath, \Mpdf\Output\Destination::FILE);
        unset($mpdf);
        abort_unless(is_file($pdfPath) && filesize($pdfPath) > 0, 500, 'PDF তৈরি সম্পন্ন হয়নি।');
    }

    private function pdfSafeRichHtml(string $value): string
    {
        // Question titles are saved from the rich-text editor. Preserve basic
        // emphasis/line breaks, but discard all attributes and unsupported tags.
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = strip_tags($value, '<b><strong><i><em><u><sub><sup><br><p><div><span>');
        $value = preg_replace_callback(
            '/<(\\/?)(b|strong|i|em|u|sub|sup|br|p|div|span)\\b[^>]*>/i',
            static fn ($match) => '<' . $match[1] . strtolower($match[2]) . '>',
            $value
        );
        return $value;
    }

    private function pdfPlainText(string $value): string
    {
        // Question content may contain rich-text HTML/entities from the editor.
        // Keep the visible text, decode entities once, and normalize to UTF-8.
        $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = str_replace(["\xc2\xa0", "\u{00A0}"], ' ', $value);
        $value = trim(preg_replace('/[\t ]+/u', ' ', $value) ?? $value);

        if (function_exists('mb_check_encoding') && !mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }

        if (class_exists('Normalizer')) {
            $normalized = \Normalizer::normalize($value, \Normalizer::FORM_C);
            if (is_string($normalized)) $value = $normalized;
        }

        return $value;
    }

    private function localImagePath(?string $path): ?string
    {
        if (!$path) return null;
        $path = trim($path);
        if ($path === '' || preg_match('/^(https?:)?\/\//i', $path)) return null;
        if (str_contains($path, '..') || str_contains($path, '\\')) return null;
        $absolute = public_path(ltrim($path, '/'));
        return is_file($absolute) ? $absolute : null;
    }
}
