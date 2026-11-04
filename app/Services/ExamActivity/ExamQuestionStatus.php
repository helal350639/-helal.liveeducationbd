<?php
namespace App\Services\ExamActivity;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;

/** Read only: never create/reset questions or change paper/attempt records. */
final class ExamQuestionStatus
{
    public static function forRow(string $source, array $item): array
    {
        $out = ['added'=>null,'limit'=>null,'remaining'=>null,'ready'=>false,'setting_url'=>null,'count_note'=>null];
        $routineId = (int)($item['routine_id'] ?? 0);
        $examName = trim((string)($item['exam'] ?? ''));
        if (!$routineId || $examName === '') return $out;

        if ($source === 'exam_section') {
            if (!Schema::hasTable('lebd_exam_section_routines')) return $out;
            $routine = DB::table('lebd_exam_section_routines')->where('id',$routineId)
                ->first(['category_id','subcategory_id']);
            if (!$routine) return $out;
            $categoryId = (int)$routine->category_id;
            $subcategoryId = $routine->subcategory_id === null ? null : (int)$routine->subcategory_id;
            // Only the exact settings scope is authoritative (no category fallback for subcategory).
            $scopeKey = $subcategoryId === null ? 'cat:'.$categoryId : 'sub:'.$subcategoryId;
            if (Schema::hasTable('lebd_exam_section_paper_settings')) {
                $setting = DB::table('lebd_exam_section_paper_settings')->where('scope_key',$scopeKey)->first();
                if ($setting) {
                    $settings = json_decode((string)$setting->settings_json,true);
                    $limit = is_array($settings) ? ($settings['question_limit'] ?? null) : null;
                    if (is_numeric($limit) && (int)$limit > 0) $out['limit'] = (int)$limit;
                }
            }
            if (Schema::hasTable('lebd_exam_section_generated_questions')) {
                $out['added'] = DB::table('lebd_exam_section_generated_questions')
                    ->where('routine_id',$routineId)->where('exam_name',$examName)
                    ->distinct()->count('question_id');
            }
            $settingRoutes = [
                'admin.exam-section.auto-question.paper-settings',
                'admin.exam-section.paper-settings',
                'admin.exam-section.auto-question.settings',
            ];
            foreach ($settingRoutes as $routeName) {
                if (Route::has($routeName)) {
                    $params = ['category'=>$categoryId];
                    if ($subcategoryId !== null) $params['subcategory_id']=$subcategoryId;
                    $out['setting_url']=route($routeName,$params);
                    break;
                }
            }
            // If no exact setting-route name matched, take the user to the existing
            // auto-question group rather than inventing an endpoint.
            if (!$out['setting_url'] && Route::has('admin.exam-section.auto-question.exams')) {
                $params=['category'=>$categoryId];
                if ($subcategoryId !== null) $params['subcategory_id']=$subcategoryId;
                $out['setting_url']=route('admin.exam-section.auto-question.exams',$params);
            }
        } elseif ($source === 'batch') {
            if (!Schema::hasTable('routines')) return $out;
            $routine=DB::table('routines')->where('id',$routineId)->first(['course_id','batch_id']);
            if (!$routine) return $out;
            $batchId=(int)($routine->batch_id ?? 0);
            if ($batchId && Schema::hasTable('routine_batch_paper_settings')) {
                $limit=DB::table('routine_batch_paper_settings')->where('batch_id',$batchId)->value('question_limit');
                if (is_numeric($limit) && (int)$limit>0) $out['limit']=(int)$limit;
            }
            if (Schema::hasTable('routine_exams') && Schema::hasTable('routine_auto_question_plan_questions')) {
                $examId=DB::table('routine_exams')->where('routine_id',$routineId)->where('name',$examName)->value('id');
                if ($examId) {
                    $out['added']=DB::table('routine_auto_question_plan_questions')
                        ->where('exam_id',$examId)->distinct()->count('question_id');
                } else {
                    $out['count_note']='Routine Exam mapping পাওয়া যায়নি — নাম/Revision যাচাই করুন';
                }
            }
            if ($batchId && Route::has('admin.routine-auto-question.batch')) {
                $out['setting_url']=route('admin.routine-auto-question.batch',[
                    'courseId'=>(int)($routine->course_id ?? 0),'batchId'=>$batchId]);
            } elseif (Route::has('admin.routine-auto-question.index')) {
                $out['setting_url']=route('admin.routine-auto-question.index');
            }
        }
        if ($out['limit'] !== null && $out['added'] !== null) {
            $out['remaining']=max(0,$out['limit']-$out['added']);
            $out['ready']=$out['added'] >= $out['limit'];
        }
        return $out;
    }
}
