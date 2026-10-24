<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Syllabus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use ZipArchive;

class QuestionExportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function export(Request $request)
    {
        $validated = $request->validate([
            'export_format' => 'required|in:csv,word',
            'export_limit' => 'required|in:25,all',
            'type' => 'nullable|in:MCQ,Written,Video',
            'course_id' => 'nullable|integer|min:1',
            'department_id' => 'nullable|integer|min:1',
            'semester_id' => 'nullable|integer|min:1',
            'subject_id' => 'nullable|integer|min:1',
            'chapter_id' => 'nullable|integer|min:1',
            'created_by' => 'nullable|integer|min:1',
            'start' => 'nullable|date_format:Y-m-d',
            'end' => 'nullable|date_format:Y-m-d',
            'title' => 'nullable|string|max:1000',
            'syllabus' => 'nullable|integer|min:1',
            'filter_ids' => 'nullable|string|max:5000',
        ]);

        $q = Question::query()->with(['mcqitems', 'getlabels'])->orderByDesc('id')
            ->where('type', $request->input('type') ?: 'MCQ');
        foreach (['course_id' => 'courses', 'department_id' => 'departments',
                  'semester_id' => 'semesters', 'subject_id' => 'subjects',
                  'chapter_id' => 'chapters'] as $field => $relation) {
            if ($request->filled($field)) {
                $id = (int) $request->input($field);
                $q->whereHas($relation, function ($query) use ($field, $id) {
                    $query->where($field, $id);
                });
            }
        }
        if ($request->filled('created_by')) $q->where('created_by', (int) $request->input('created_by'));
        if ($request->filled('start')) $q->whereDate('created_at', '>=', $request->input('start'));
        if ($request->filled('end')) $q->whereDate('created_at', '<=', $request->input('end'));
        if ($request->filled('title')) $q->where('title', 'like', '%'.trim($request->input('title')).'%');
        if ($request->filled('syllabus')) {
            $s = Syllabus::find((int) $request->input('syllabus'));
            $q->whereIn('id', $s ? $s->questions()->pluck('questions.id') : []);
        }
        $ids = array_values(array_unique(array_filter(array_map('intval',
            explode(',', (string) $request->input('filter_ids', ''))), fn ($n) => $n > 0)));
        if ($ids) $q->whereHas('filters', function ($query) use ($ids) {
            $query->whereIn('sub_filter_id', $ids);
        });
        if ($request->input('export_limit') === '25') $q->limit(25);
        $questions = $q->get();
        $stamp = date('Ymd_His');
        if ($request->input('export_format') === 'csv') {
            return response()->streamDownload(function () use ($questions) {
                $out = fopen('php://output', 'wb');
                fwrite($out, "\xEF\xBB\xBF");
                fputcsv($out, ['question_id','type','title','option_a','option_b','option_c','option_d','option_e','correct_answer','explanation','labels']);
                foreach ($questions as $question) {
                    $items = $question->mcqitems->values();
                    $options = [];
                    for ($i=0; $i<5; $i++) $options[] = isset($items[$i]) ? $this->safeCsv($this->plain($items[$i]->item)) : '';
                    $correct = $items->search(fn ($item) => (bool)$item->correct_answer);
                    $answer = $correct === false ? '' : chr(65 + (int)$correct);
                    fputcsv($out, array_merge([
                        $question->id, $question->type, $this->safeCsv($this->plain($question->title))
                    ], $options, [$answer, $this->safeCsv($this->plain($question->explanation)),
                        $this->safeCsv($question->getlabels->pluck('label')->implode(' | '))]));
                }
                fclose($out);
            }, 'LEBD_Filtered_Questions_'.$stamp.'.csv', ['Content-Type'=>'text/csv; charset=UTF-8']);
        }
        return $this->word($questions, 'LEBD_Filtered_Questions_'.$stamp.'.docx');
    }

    private function plain($html): string
    {
        $s = (string) ($html ?? '');
        $s = preg_replace('~<br\s*/?>|</p>|</div>|</li>~i', "\n", $s);
        return trim(html_entity_decode(strip_tags($s), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function safeCsv($s): string
    {
        return preg_match('/^[\s]*[=+\-@\t\r]/u', $s) ? "'".$s : $s;
    }

    private function xml($s): string
    {
        $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$s);
        return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function paragraph($text, $bold = false, $color = '222222'): string
    {
        $rpr = '<w:rPr>'.($bold ? '<w:b/>' : '').'<w:color w:val="'.$color.'"/><w:sz w:val="23"/></w:rPr>';
        return '<w:p><w:pPr><w:spacing w:after="110"/></w:pPr><w:r>'.$rpr.'<w:t xml:space="preserve">'.$this->xml($text).'</w:t></w:r></w:p>';
    }

    private function word($questions, $name)
    {
        if (!class_exists(ZipArchive::class)) {
            return back()->withErrors(['export' => 'Word export needs PHP ZipArchive extension. Enable extension=zip in php.ini.']);
        }
        $body = $this->paragraph('Live Education BD - Filtered Question Export', true, '183153');
        $body .= $this->paragraph('Total questions: '.$questions->count());
        foreach ($questions as $i => $question) {
            $body .= $this->paragraph(($i+1).'. '.$this->plain($question->title), true);
            $items = $question->mcqitems->values();
            $correct = '';
            foreach ($items as $j => $item) {
                $letter = chr(65+$j);
                if ($item->correct_answer) $correct = $letter;
                $body .= $this->paragraph('    '.$letter.') '.$this->plain($item->item), (bool)$item->correct_answer,
                    $item->correct_answer ? '008000' : '222222');
            }
            $body .= $this->paragraph('Correct answer: '.($correct ?: 'Not specified'), true, '008000');
            if (trim((string)$question->explanation) !== '') {
                $body .= $this->paragraph('Explanation: '.$this->plain($question->explanation));
            }
            $labels = $question->getlabels->pluck('label')->implode(' | ');
            if ($labels !== '') $body .= $this->paragraph('Labels: '.$labels);
            $body .= '<w:p><w:pPr><w:spacing w:after="150"/></w:pPr></w:p>';
        }
        $document = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:body>'.$body.'<w:sectPr><w:pgSz w:w="11906" w:h="16838"/>'
            .'<w:pgMar w:top="1000" w:right="1000" w:bottom="1000" w:left="1000"/></w:sectPr></w:body></w:document>';
        $path = tempnam(sys_get_temp_dir(), 'lebd_word_');
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::OVERWRITE | ZipArchive::CREATE) !== true) {
            @unlink($path);
            return back()->withErrors(['export' => 'Unable to create Word document.']);
        }
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            .'</Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            .'</Relationships>');
        $zip->addFromString('word/document.xml', $document);
        $zip->close();
        return response()->download($path, $name, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ])->deleteFileAfterSend(true);
    }
}
