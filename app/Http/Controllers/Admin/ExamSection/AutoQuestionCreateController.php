<?php
namespace App\Http\Controllers\Admin\ExamSection;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Isolated Exam Section question composer. No mutation of the original question bank. */
class AutoQuestionCreateController extends Controller
{
    private function context(int $category, int $routine, string $exam)
    {
        $cat=DB::table('lebd_exam_section_categories')->where('id',$category)->where('status','Active')->first(); abort_unless($cat,404);
        $r=DB::table('lebd_exam_section_routines')->where('id',$routine)->where('category_id',$category)->first(); abort_unless($r,404);
        $hasSubs=DB::table('lebd_exam_section_subcategories')->where('category_id',$category)->where('status','Active')->exists();
        $sub=$r->subcategory_id ? DB::table('lebd_exam_section_subcategories')->where('id',$r->subcategory_id)->where('category_id',$category)->where('status','Active')->first() : null;
        abort_unless(!$hasSubs || $sub,404);
        $known=DB::table('lebd_exam_section_routine_labels')->where('routine_id',$routine)->where('kind','exam')->where('value',$exam)->exists();
        $item=DB::table('lebd_exam_section_routine_items')->where('routine_id',$routine)->where('type','exam')->where('exam_name',$exam)->first();
        abort_unless($known || $item,404);
        return [$cat,$sub,$r,$item];
    }
    private function rows($item):array
    {
        if(!$item)return [];
        $topics=DB::table('lebd_exam_section_routine_item_topics')->where('item_id',$item->id)->orderBy('id')->get();
        $rows=[];
        foreach($topics as $t){
            if(!preg_match('/^(department|subject|chapter):([1-9][0-9]*)$/',(string)$t->topic_key,$match))continue;
            $level=$match[1];$id=(int)$match[2];
            $table=['department'=>'lebd_exam_routine_departments','subject'=>'lebd_exam_routine_subjects','chapter'=>'lebd_exam_routine_chapters'][$level];
            $node=DB::table($table)->where('id',$id)->first();if(!$node)continue;
            $dept=$level==='department'?$node:($level==='subject'?DB::table('lebd_exam_routine_departments')->where('id',$node->department_id)->first():null);
            $subj=$level==='subject'?$node:($level==='chapter'?DB::table('lebd_exam_routine_subjects')->where('id',$node->subject_id)->first():null);
            if($level==='chapter')$dept=$subj?DB::table('lebd_exam_routine_departments')->where('id',$subj->department_id)->first():null;
            $ids=DB::table('lebd_exam_routine_question_links as l')->join('questions as q','q.id','=','l.question_id')
                ->where('l.topic_level',$level)->where('l.topic_id',$id)->where('q.type','MCQ')->where('q.status','Active')
                ->distinct()->orderBy('l.question_id')->pluck('l.question_id')->map(fn($n)=>(int)$n)->all();
            // Range is 1-based POSITION in sorted linked MCQ IDs, not a question ID range.
            $start=$t->question_start ? max(1,(int)$t->question_start):1;
            $end=$t->question_end ? (int)$t->question_end:count($ids);
            $eligible=array_slice($ids,$start-1,max(0,$end-$start+1));
            $rows[]=['key'=>(string)$t->topic_key,'kind'=>$dept->kind??'','department'=>$dept->name??'—','subject'=>$subj->name??'—','chapter'=>$level==='chapter'?$node->name:'—',
                'range'=>$start.'–'.$end,'available'=>count($eligible),'ids'=>$eligible];
        }
        return $rows;
    }
    public function create(Request $request,int $category,int $routine)
    {
        $exam=trim((string)$request->query('exam',''));abort_if($exam==='',404);
        [$cat,$sub,$r,$item]=$this->context($category,$routine,$exam);
        $rows=$this->rows($item);
        $saved=DB::table('lebd_exam_section_generated_questions')->where('routine_id',$routine)->where('exam_name',$exam)->orderBy('position')->get();
        $counts=$saved->groupBy('topic_key')->map->count()->all();
        $groupTotal=0;
        $settings=DB::table('lebd_exam_section_paper_settings')->where('scope_key',($sub?'sub:'.$sub->id:'cat:'.$cat->id))->first();
        if($settings){$json=json_decode($settings->settings_json,true)?:[];$groupTotal=(int)($json['question_limit']??0);}
        return view('admin.exam-section.auto-question-create',compact('cat','sub','r','item','exam','rows','saved','counts','groupTotal'));
    }
    /** Unique and non-consecutive order where the pool permits it. */
    private function shuffledOrder(array $ids):array
    {
        $ids=array_values(array_unique(array_map('intval',$ids)));
        if(count($ids)<2)return $ids;
        $best=$ids;
        for($attempt=0;$attempt<80;$attempt++){
            shuffle($ids);
            $bad=0;
            for($i=1;$i<count($ids);$i++)if(abs($ids[$i]-$ids[$i-1])===1)$bad++;
            if($bad===0)return $ids;
            if($attempt===0||$bad<$bestBad){$best=$ids;$bestBad=$bad;}
        }
        return $best;
    }

    public function details(Request $request,int $category,int $routine)
    {
        $exam=trim((string)$request->query('exam',''));abort_if($exam==='',404);
        [$cat,$sub,$r,$item]=$this->context($category,$routine,$exam);
        $links=DB::table('lebd_exam_section_generated_questions as g')
            ->join('questions as q','q.id','=','g.question_id')
            ->where('g.routine_id',$routine)->where('g.exam_name',$exam)
            ->orderBy('g.position')->select('g.position','g.topic_key','q.id','q.title','q.image','q.answer','q.explanation')->get();
        $options=$links->isEmpty()?collect():DB::table('mcq_items')->whereIn('question_id',$links->pluck('id')->all())
            ->select('question_id','item','correct_answer','image')->orderBy('id')->get()->groupBy('question_id');
        return view('admin.exam-section.auto-question-details',compact('cat','sub','r','exam','item','links','options'));
    }

    public function reset(Request $request,int $category,int $routine)
    {
        $data=$request->validate(['exam'=>'required|string|max:190']);
        [$cat,$sub,$r,$item]=$this->context($category,$routine,$data['exam']);
        $deleted=DB::table('lebd_exam_section_generated_questions')->where('routine_id',$routine)->where('exam_name',$data['exam'])->delete();
        return redirect()->route('admin.exam-section.auto-question.exams',['category'=>$category]+($sub?['subcategory_id'=>$sub->id]:[]))
            ->with('success',$deleted.' question links reset for '.$data['exam'].'. Original MCQ bank unchanged.');
    }

    public function store(Request $request,int $category,int $routine)
    {
        $data=$request->validate(['exam'=>'required|string|max:190','quantities'=>'required|array','quantities.*'=>'required|integer|min:0|max:10000','mode'=>'required|in:save,done']);
        [$cat,$sub,$r,$item]=$this->context($category,$routine,$data['exam']);
        if(!$item)return back()->withErrors(['exam'=>'This Exam has no saved Routine Item. First add its Topic/Question Range under Routine.'])->withInput();
        $rows=$this->rows($item);if(!$rows)return back()->withErrors(['quantities'=>'Routine has no valid Topic selections.'])->withInput();
        // Scope-wide no-reuse: an original Question ID belongs to at most one saved Exam
        // within the SAME Category/Sub-category, even across distinct Routines.
        $scopeRoutines=DB::table('lebd_exam_section_routines')->where('category_id',$category)
            ->where('subcategory_id',$r->subcategory_id)->pluck('id')->all();
        $usedElsewhere=DB::table('lebd_exam_section_generated_questions')
            ->whereIn('routine_id',$scopeRoutines)
            ->where(function($q)use($routine,$data){$q->where('routine_id','!=',$routine)->orWhere('exam_name','!=',$data['exam']);})
            ->pluck('question_id')->map(fn($id)=>(int)$id)->flip()->all();
        $picked=[];$claimed=[];$errors=[];
        foreach($rows as $row){
            $key=$row['key'];$wanted=(int)($data['quantities'][$key]??0);
            $available=array_values(array_filter(array_unique($row['ids']),fn($id)=>!isset($usedElsewhere[$id])&&!isset($claimed[$id])));
            if($wanted>count($available)){
                $errors['quantities.'.$key]='Only '.count($available).' unused unique questions available for '.$key.' (requested '.$wanted.'). Questions already used in other exams are excluded.';
                continue;
            }
            // Random sampling uses cryptographically sound Fisher-Yates shuffle. No ascending-ID sequence.
            for($i=count($available)-1;$i>0;$i--){$j=random_int(0,$i);[$available[$i],$available[$j]]=[$available[$j],$available[$i]];}
            foreach(array_slice($available,0,$wanted) as $id){$claimed[$id]=true;$picked[]=['routine_id'=>$routine,'exam_name'=>$data['exam'],'topic_key'=>$key,'question_id'=>$id,'created_at'=>now(),'updated_at'=>now()];}
        }
        if($errors)throw ValidationException::withMessages($errors);
        if(!$picked)throw ValidationException::withMessages(['quantities'=>'Enter a question quantity greater than zero.']);
        $scope=$sub?'sub:'.$sub->id:'cat:'.$cat->id;
        $settings=DB::table('lebd_exam_section_paper_settings')->where('scope_key',$scope)->first();
        $limit=$settings?(int)((json_decode($settings->settings_json,true)?:[])['question_limit']??0):0;
        if($limit>0 && count($picked)>$limit)throw ValidationException::withMessages(['quantities'=>'Selected '.count($picked).' questions exceeds shared Paper Settings limit of '.$limit.'.']);
        $ordered=$this->shuffledOrder($pickedIds=array_column($picked,'question_id'));
        $index=array_column($picked,null,'question_id');
        $picked=array_map(function($id)use($index){return $index[$id];},$ordered);
        foreach($picked as $i=>&$link)$link['position']=$i+1;
        unset($link);
        DB::transaction(function()use($routine,$data,$picked,$scopeRoutines){
            // Serialize concurrent generators for this scope to prevent cross-exam reuse.
            DB::table('lebd_exam_section_routines')->whereIn('id',$scopeRoutines)->orderBy('id')->lockForUpdate()->get(['id']);
            $collisions=DB::table('lebd_exam_section_generated_questions')->whereIn('routine_id',$scopeRoutines)
                ->whereIn('question_id',array_column($picked,'question_id'))
                ->where(function($q)use($routine,$data){$q->where('routine_id','!=',$routine)->orWhere('exam_name','!=',$data['exam']);})->exists();
            if($collisions)throw ValidationException::withMessages(['quantities'=>'Questions have just been used by another Exam. Reload and try again.']);
            DB::table('lebd_exam_section_generated_questions')->where('routine_id',$routine)->where('exam_name',$data['exam'])->delete();
            foreach(array_chunk($picked,150) as $chunk)DB::table('lebd_exam_section_generated_questions')->insert($chunk);
        });
        if($data['mode']==='done')return redirect()->route('admin.exam-section.auto-question.exams',['category'=>$category]+($sub?['subcategory_id'=>$sub->id]:[]))->with('success',count($picked).' questions linked to '.$data['exam'].'.');
        return redirect()->route('admin.exam-section.auto-question.create',['category'=>$category,'routine'=>$routine,'exam'=>$data['exam']])->with('success',count($picked).' questions saved.');
    }
}
