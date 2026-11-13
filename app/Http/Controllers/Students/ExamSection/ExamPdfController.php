<?php
namespace App\Http\Controllers\Students\ExamSection;

use App\Http\Controllers\Controller;
use App\Services\ExamSectionMonthlyAccess;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;

/** Routine-driven Exam Section PDF papers. One cached PDF per scheduled Routine exam. */
class ExamPdfController extends Controller
{
    private function scope(int $category, Request $request): array
    {
        $cat = DB::table('lebd_exam_section_categories')->where('id', $category)->where('status', 'Active')->first();
        abort_unless($cat, 404);
        $hasSubs = DB::table('lebd_exam_section_subcategories')->where('category_id', $category)->where('status', 'Active')->exists();
        $sub = null;
        if ($hasSubs) {
            $subId = (int) $request->query('subcategory_id', 0);
            $sub = DB::table('lebd_exam_section_subcategories')->where('id', $subId)
                ->where('category_id', $category)->where('status', 'Active')->first();
            abort_unless($sub, 404);
        } elseif ($request->filled('subcategory_id')) {
            abort(404);
        }
        return [$cat, $sub];
    }

    public function index(Request $request, int $category)
    {
        [$cat, $sub] = $this->scope($category, $request);
        $items = DB::table('lebd_exam_section_routine_items as i')
            ->join('lebd_exam_section_routines as r', 'r.id', '=', 'i.routine_id')
            ->where('r.category_id', $category)->where('r.subcategory_id', $sub?->id)
            ->where('r.status', 'Published')->where('i.type', 'exam')
            ->select('i.id','i.routine_id','i.exam_name','i.exam_start_at','i.exam_end_at','i.notes','r.name as routine_name')
            ->orderBy('i.exam_start_at')->orderBy('i.id')->get();

        $rows = $items->map(function ($item) use ($category, $sub) {
            $record = $this->ensurePdf($item, $category, $sub?->id ? (int)$sub->id : null);
            $topics = $this->topicLabels((int)$item->id);
            return (object)[
                'item' => $item,
                'record' => $record,
                'topics' => $topics,
                'published' => $record && $record->status === 'Published' && $record->pdf_path && Storage::disk('local')->exists($record->pdf_path),
            ];
        });
        $total = $rows->count();
        $published = $rows->filter(fn($r) => $r->published)->count();
        $pending = $total - $published;
        $upcoming = $items->filter(fn($i) => $i->exam_start_at && Carbon::parse($i->exam_start_at, 'Asia/Dhaka')->isFuture())->count();
        return view('student.exam-section.exam-pdf', compact('cat','sub','rows','total','published','pending','upcoming'));
    }

    private function topicLabels(int $itemId): array
    {
        if (!Schema::hasTable('lebd_exam_section_routine_item_topics')) return [];
        $topics = DB::table('lebd_exam_section_routine_item_topics')->where('item_id',$itemId)->orderBy('id')->get();
        $labels = [];
        foreach ($topics as $topic) {
            if (!preg_match('/^(department|subject|chapter):([1-9][0-9]*)$/', (string)$topic->topic_key, $m)) continue;
            $id=(int)$m[2]; $level=$m[1];
            if ($level==='department') $name=DB::table('lebd_exam_routine_departments')->where('id',$id)->value('name');
            elseif ($level==='subject') $name=DB::table('lebd_exam_routine_subjects')->where('id',$id)->value('name');
            else $name=DB::table('lebd_exam_routine_chapters')->where('id',$id)->value('name');
            if ($name) $labels[]=(string)$name;
        }
        return array_values(array_unique($labels));
    }

    /** Generate/rebuild only when the saved exam question set or topic details change. */
    private function ensurePdf(object $item, int $categoryId, ?int $subcategoryId): ?object
    {
        if (!Schema::hasTable('lebd_exam_section_pdfs') || !class_exists(Mpdf::class)) return null;
        $settingsKey = $subcategoryId ? 'sub:'.$subcategoryId : 'cat:'.$categoryId;
        $settingsRow = DB::table('lebd_exam_section_paper_settings')->where('scope_key',$settingsKey)->first();
        $settings = $settingsRow ? (json_decode($settingsRow->settings_json,true) ?: []) : [];
        $limit = max(1,min(1000,(int)($settings['question_limit'] ?? 100)));
        $links = DB::table('lebd_exam_section_generated_questions as g')
            ->join('questions as q','q.id','=','g.question_id')
            ->where('g.routine_id',$item->routine_id)->where('g.exam_name',$item->exam_name)
            ->orderBy('g.position')->orderBy('g.id')
            ->select('g.question_id','g.topic_key','g.position','q.title','q.image')
            ->limit($limit)->get();
        $ids = $links->pluck('question_id')->map(fn($v)=>(int)$v)->all();
        $existing = DB::table('lebd_exam_section_pdfs')->where('routine_item_id',$item->id)->first();
        if (!$ids) {
            DB::table('lebd_exam_section_pdfs')->updateOrInsert(['routine_item_id'=>$item->id],[
                'category_id'=>$categoryId,'subcategory_id'=>$subcategoryId,'routine_id'=>$item->routine_id,
                'exam_name'=>$item->exam_name,'pdf_path'=>null,'content_hash'=>null,'question_count'=>0,
                'status'=>'Pending','published_at'=>null,'updated_at'=>now(),'created_at'=>$existing->created_at ?? now(),
            ]);
            return DB::table('lebd_exam_section_pdfs')->where('routine_item_id',$item->id)->first();
        }
        $options = DB::table('mcq_items')->whereIn('question_id',$ids)->orderBy('id')
            ->get(['question_id','item'])->groupBy('question_id');
        $questions = $links->map(function($q) use ($options) {
            return ['id'=>(int)$q->question_id,'title'=>(string)$q->title,'options'=>($options->get($q->question_id) ?? collect())->pluck('item')->values()->all()];
        })->values();
        $topics = $this->topicLabels((int)$item->id);
        // Include renderer/font version in the hash so PDFs created with the old
        // DejaVu-only setup are automatically regenerated with Bengali glyph support.
        $hash = hash('sha256', json_encode([
            'renderer' => 'mpdf-bengali-force-font-v3',
            'exam' => $item->exam_name,
            'topics' => $topics,
            'questions' => $questions->all(),
        ], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
        $existingFile = $existing && $existing->pdf_path && Storage::disk('local')->exists($existing->pdf_path);
        if ($existing && $existing->content_hash === $hash && $existing->status === 'Published' && $existingFile) return $existing;

        $relative = 'exam-section-pdf/'.$categoryId.'/'.($subcategoryId ?: 0).'/exam-'.$item->id.'-'.$hash.'.pdf';
        $absolute = storage_path('app/'.$relative);
        if (!is_dir(dirname($absolute))) mkdir(dirname($absolute),0775,true);
        $html = view('student.exam-section.exam-pdf-document',[
            'categoryName'=>DB::table('lebd_exam_section_categories')->where('id',$categoryId)->value('name'),
            'examName'=>$item->exam_name,'routineName'=>$item->routine_name,'examStart'=>$item->exam_start_at,
            'examEnd'=>$item->exam_end_at,'topics'=>$topics,'questions'=>$questions,
        ])->render();
        try {
            // Build the mPDF font configuration by extending its defaults.
            // Replacing fontDir/fontdata outright can prevent mPDF from resolving
            // registered custom fonts on some installed mPDF versions.
            $fontPath = resource_path('fonts');
            $fontFile = $fontPath . '/SolaimanLipi.ttf';

            $configVariables = (new \Mpdf\Config\ConfigVariables())->getDefaults();
            $fontVariables = (new \Mpdf\Config\FontVariables())->getDefaults();
            $fontDirs = $configVariables['fontDir'];
            $fontData = $fontVariables['fontdata'];

            if (is_file($fontFile)) {
                $fontDirs[] = $fontPath;
                $fontData['lebdsolaiman'] = [
                    'R' => 'SolaimanLipi.ttf',
                    'B' => 'SolaimanLipi.ttf',
                    'I' => 'SolaimanLipi.ttf',
                    'BI' => 'SolaimanLipi.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ];
                $defaultFont = 'lebdsolaiman';
            } else {
                // Fail loudly rather than silently generating a PDF with missing Bengali glyphs.
                throw new \RuntimeException('Bengali PDF font missing: ' . $fontFile);
            }

            $config = [
                'mode' => 'utf-8',
                'format' => 'A4',
                'default_font_size' => 12,
                'fontDir' => $fontDirs,
                'fontdata' => $fontData,
                'default_font' => $defaultFont,
                // Do not let mPDF silently switch Bengali text to FreeSerif.
                // The whole paper must use the explicitly registered Bengali font.
                'autoScriptToLang' => false,
                'autoLangToFont' => false,
                'useSubstitutions' => true,
                // A new temp directory avoids stale mPDF font metric caches.
                'tempDir' => storage_path('app/mpdf-bengali-v2'),
            ];
            if (!is_dir($config['tempDir'])) mkdir($config['tempDir'],0775,true);
            $pdf = new Mpdf($config);
            // Set the document default explicitly as well as in constructor config.
            $pdf->default_font = 'lebdsolaiman';
            $pdf->SetTitle($item->exam_name.' - Live Education BD');
            $pdf->SetWatermarkText('LiveEducationBD.com',0.08);
            $pdf->showWatermarkText = true;
            $pdf->WriteHTML($html);
            $pdf->Output($absolute,'F');
        } catch (\Throwable $e) {
            report($e);
            return $existing;
        }
        DB::table('lebd_exam_section_pdfs')->updateOrInsert(['routine_item_id'=>$item->id],[
            'category_id'=>$categoryId,'subcategory_id'=>$subcategoryId,'routine_id'=>$item->routine_id,
            'exam_name'=>$item->exam_name,'pdf_path'=>$relative,'content_hash'=>$hash,'question_count'=>$questions->count(),
            'status'=>'Published','published_at'=>now('Asia/Dhaka'),'updated_at'=>now(),'created_at'=>$existing->created_at ?? now(),
        ]);
        return DB::table('lebd_exam_section_pdfs')->where('routine_item_id',$item->id)->first();
    }

    private function authorizedPdf(int $itemId): object
    {
        $student=auth('student')->user(); abort_unless($student,403);
        $row=DB::table('lebd_exam_section_pdfs as p')
            ->join('lebd_exam_section_categories as c','c.id','=','p.category_id')
            ->where('p.routine_item_id',$itemId)->where('p.status','Published')->where('c.status','Active')
            ->select('p.*','c.status as category_status')->first();
        abort_unless($row && $row->pdf_path && Storage::disk('local')->exists($row->pdf_path),404);
        $scopeKey=$row->subcategory_id ? 'sub:'.$row->subcategory_id : 'cat:'.$row->category_id;
        $settingsRow=DB::table('lebd_exam_section_paper_settings')->where('scope_key',$scopeKey)->first();
        $settings=$settingsRow ? (json_decode($settingsRow->settings_json,true) ?: []) : [];
        $paid=($settings['paid_exam'] ?? 'paid')==='paid' || filter_var($settings['paid_exam'] ?? false,FILTER_VALIDATE_BOOLEAN);
        if ($paid) abort_unless(ExamSectionMonthlyAccess::premium((int)$student->id,(int)$row->category_id,$row->subcategory_id ? (int)$row->subcategory_id : null),403,'এই PDF দেখতে Premium Exam Package প্রয়োজন।');
        return $row;
    }

    public function view(int $item)
    {
        $row=$this->authorizedPdf($item);
        return response()->file(storage_path('app/'.$row->pdf_path),['Content-Type'=>'application/pdf','Content-Disposition'=>'inline; filename="'.rawurlencode($row->exam_name).'.pdf"']);
    }

    public function download(int $item)
    {
        $row=$this->authorizedPdf($item);
        return response()->download(storage_path('app/'.$row->pdf_path),preg_replace('/[^A-Za-z0-9._-]+/','_', $row->exam_name).'.pdf');
    }
}
