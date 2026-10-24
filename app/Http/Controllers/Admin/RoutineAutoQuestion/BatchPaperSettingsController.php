<?php
namespace App\Http\Controllers\Admin\RoutineAutoQuestion;

use App\Http\Controllers\Controller;
use App\Services\RoutineAutoQuestion\BatchPaperPreviewService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BatchPaperSettingsController extends Controller
{
    private function context(int $courseId, int $batchId): array
    {
        $course=DB::table('courses')->where('id',$courseId)->first();
        abort_unless($course,404);
        // Keep preview/edit available for an expired batch so saved settings can be audited.
        $batch=DB::table('batches')->where('id',$batchId)->where('course_id',$courseId)->first();
        abort_unless($batch,404);
        return compact('course','batch');
    }

    public function show(int $courseId,int $batchId,BatchPaperPreviewService $preview)
    {
        $context=$this->context($courseId,$batchId);
        $settings=DB::table('routine_batch_paper_settings')->where('batch_id',$batchId)->first();
        $exams=$preview->exams($courseId,$batchId);
        $selected=(int)request()->query('exam', $exams->first()->exam_id ?? 0);
        $exam=$exams->first(fn($x)=>(int)$x->exam_id===$selected) ?? $exams->first();
        $effective=$settings ?: (object)[
            'question_limit'=>100,'mcq_format'=>'bn','running_minutes'=>40,
            'correct_mark'=>1,'negative_mark'=>0,'attempt_limit'=>'1',
            'random_questions'=>0,'question_display'=>'all','paid_exam'=>0,
            'email_result'=>0,'student_view_result'=>0,'header_extra'=>'',
            'details_extra'=>'','banner_path'=>null
        ];
        $info=$exam ? $preview->preview($exam,$effective) : null;
        return view('admin.routine-auto-question.batch-settings',array_merge($context,
            compact('settings','effective','exams','exam','info')));
    }

    public function save(Request $request,int $courseId,int $batchId)
    {
        $this->context($courseId,$batchId);
        $validated=$request->validate([
            'question_limit'=>'required|integer|min:1|max:10000',
            'mcq_format'=>['required',Rule::in(['bn','en-lower','en-upper','bn-number','roman'])],
            'running_minutes'=>'required|integer|min:1|max:1440',
            'correct_mark'=>'required|numeric|min:0|max:9999',
            'negative_mark'=>'required|numeric|min:0|max:9999',
            'attempt_limit'=>['required',Rule::in(['1','2','3','multiple'])],
            'random_questions'=>'required|boolean',
            'question_display'=>['required',Rule::in(['all','one'])],
            'paid_exam'=>'required|boolean',
            'email_result'=>'required|boolean',
            'student_view_result'=>'required|boolean',
            'header_extra'=>'nullable|string|max:10000',
            'details_extra'=>'nullable|string|max:10000',
            'banner'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        unset($validated['banner']);
        $old=DB::table('routine_batch_paper_settings')->where('batch_id',$batchId)->first();
        if ($request->hasFile('banner')) {
            $validated['banner_path']=$request->file('banner')->store('routine-batch-banners','public');
        }
        $validated['updated_at']=now();
        if ($old) {
            DB::table('routine_batch_paper_settings')->where('batch_id',$batchId)->update($validated);
            if (isset($validated['banner_path']) && $old->banner_path) Storage::disk('public')->delete($old->banner_path);
        } else {
            $validated['batch_id']=$batchId;
            $validated['created_at']=now();
            DB::table('routine_batch_paper_settings')->insert($validated);
        }
        return redirect()->route('admin.routine-auto-question.batch-settings-preview',
            ['courseId'=>$courseId,'batchId'=>$batchId])->with('success','Batch settings saved for all exams and departments.');
    }
}
