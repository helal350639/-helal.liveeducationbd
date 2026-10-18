<?php
namespace App\Http\Controllers\MCQJobPreparation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\McqAccessService;

class StudentJobPreparationController extends Controller
{
    private function accessService(): McqAccessService { return app(McqAccessService::class); }

    private function checkKind(string $kind): void
    {
        abort_unless(in_array($kind, ['department', 'non-department'], true), 404);
    }

    private function categoryRecord(string $kind, int $id)
    {
        $this->checkKind($kind);
        return DB::table('job_prep_categories')->where('kind',$kind)->where('status','Active')->where('id',$id)->first() ?? abort(404);
    }

    private function subjectRecord(int $category,int $id)
    {
        return DB::table('job_prep_subjects')->where('category_id',$category)->where('status','Active')->where('id',$id)->first() ?? abort(404);
    }

    private function chapterRecord(int $subject,int $id)
    {
        return DB::table('job_prep_chapters')->where('subject_id',$subject)->where('status','Active')->where('id',$id)->first() ?? abort(404);
    }

    private function countQuestions(string $level, int $id): int
    {
        return DB::table('job_prep_question_links as l')->join('questions as q','q.id','=','l.question_id')
            ->where('l.level',$level)->where('l.entity_id',$id)->where('q.type','MCQ')->where('q.status','Active')->count();
    }

    public function categories(string $kind)
    {
        $this->checkKind($kind);
        $categories=DB::table('job_prep_categories')->where('kind',$kind)->where('status','Active')->orderBy('sort_order')->orderBy('id')->paginate(30);
        return view('homepage.job-preparation.categories',compact('kind','categories'));
    }

    public function category(string $kind,int $category)
    {
        $cat=$this->categoryRecord($kind,$category);
        $subjects=DB::table('job_prep_subjects')->where('category_id',$cat->id)->where('status','Active')->orderBy('sort_order')->orderBy('id')->paginate(30);
        $directCount=$this->countQuestions('category',$cat->id);
        return view('homepage.job-preparation.category',compact('kind','cat','subjects','directCount'));
    }

    public function subject(string $kind,int $category,int $subject)
    {
        $cat=$this->categoryRecord($kind,$category);
        $sub=$this->subjectRecord($cat->id,$subject);
        $chapters=DB::table('job_prep_chapters')->where('subject_id',$sub->id)->where('status','Active')->orderBy('sort_order')->orderBy('id')->paginate(30);
        $directCount=$this->countQuestions('subject',$sub->id);
        return view('homepage.job-preparation.subject',compact('kind','cat','sub','chapters','directCount'));
    }

    public function chapter(string $kind,int $category,int $subject,int $chapter)
    {
        $cat=$this->categoryRecord($kind,$category);
        $sub=$this->subjectRecord($cat->id,$subject);
        $ch=$this->chapterRecord($sub->id,$chapter);
        return redirect()->route('student.jobprep.questions',['kind'=>$kind,'level'=>'chapter','entity'=>$ch->id]);
    }

    public function questions(Request $request,string $kind,string $level,int $entity)
    {
        $this->checkKind($kind);
        abort_unless(in_array($level,['category','subject','chapter'],true),404);
        if ($level==='category') {
            $cat=$this->categoryRecord($kind,$entity); $title=$cat->name; $back=route('student.jobprep.category',compact('kind')+['category'=>$cat->id]);
        } elseif($level==='subject') {
            $sub=DB::table('job_prep_subjects')->where('id',$entity)->where('status','Active')->first() ?? abort(404);
            $cat=$this->categoryRecord($kind,$sub->category_id); $title=$cat->name.' / '.$sub->name;
            $back=route('student.jobprep.subject',compact('kind')+['category'=>$cat->id,'subject'=>$sub->id]);
        } else {
            $ch=DB::table('job_prep_chapters')->where('id',$entity)->where('status','Active')->first() ?? abort(404);
            $sub=DB::table('job_prep_subjects')->where('id',$ch->subject_id)->where('status','Active')->first() ?? abort(404);
            $cat=$this->categoryRecord($kind,$sub->category_id);$title=$cat->name.' / '.$sub->name.' / '.$ch->name;
            $back=route('student.jobprep.subject',compact('kind')+['category'=>$cat->id,'subject'=>$sub->id]);
        }
        $student=Auth::guard('student')->user();
        if (!$student) return redirect()->guest(route('students.login'));
        $access=$this->accessService()->access((int)$cat->id);
        $difficulty=$request->query('difficulty');
        $query=DB::table('job_prep_question_links as l')->join('questions as q','q.id','=','l.question_id')
            ->where('l.level',$level)->where('l.entity_id',$entity)->where('q.type','MCQ')->where('q.status','Active');
        if (in_array($difficulty,['easy','medium','difficult'],true)) {
            if (!$student) {
                $query->whereRaw('1=0');
            } else {
                $query->join('student_job_prep_readings as r',function($join) use($student) {
                    $join->on('r.question_id','=','q.id')->where('r.student_id','=',$student->id);
                })->where('r.difficulty',$difficulty);
            }
        }
        $rows=$query->select('l.id as link_id','q.id','q.title','q.image','q.answer','q.explanation','q.solution','q.details','q.status')
            ->orderByDesc('l.id')->paginate(20)->withQueryString();
        $allowed=$this->accessService()->reserveReading((int)$cat->id,$rows->getCollection()->pluck('id')->all(),$access);
        $rows->setCollection($rows->getCollection()->filter(fn($q)=>in_array((int)$q->id,$allowed,true))->values());
        $remainingReading=$this->accessService()->remainingReading((int)$student->id,$access);
        $ids=$rows->getCollection()->pluck('id')->all();
        $items=DB::table('mcq_items')->whereIn('question_id',$ids)->orderBy('id')->get()->groupBy('question_id');
        $marks=$student ? DB::table('student_job_prep_readings')->where('student_id',$student->id)->whereIn('question_id',$ids)->pluck('difficulty','question_id') : collect();
        $comments=DB::table('student_job_prep_comments')->whereIn('question_id',$ids)->orderBy('created_at','desc')->get()->groupBy('question_id');
        $reactionCounts=DB::table('student_job_prep_reactions')->whereIn('question_id',$ids)->select('question_id',DB::raw('COUNT(*) as total'))->groupBy('question_id')->pluck('total','question_id');
        $ratingStats=DB::table('student_job_prep_ratings')->whereIn('question_id',$ids)->select('question_id',DB::raw('AVG(rating) as average'),DB::raw('COUNT(*) as total'))->groupBy('question_id')->get()->keyBy('question_id');
        $myRatings=$student ? DB::table('student_job_prep_ratings')->where('student_id',$student->id)->whereIn('question_id',$ids)->pluck('rating','question_id') : collect();
        $myReactions=$student ? DB::table('student_job_prep_reactions')->where('student_id',$student->id)->whereIn('question_id',$ids)->pluck('question_id','question_id') : collect();
        return view('homepage.job-preparation.questions',compact('kind','level','entity','title','back','rows','items','marks','student','difficulty','comments','reactionCounts','myReactions','ratingStats','myRatings','access','remainingReading'));
    }

    public function saveReading(Request $request,string $kind,string $level,int $entity,int $question)
    {
        $this->checkKind($kind);
        abort_unless(in_array($level,['category','subject','chapter'],true),404);
        $data=$request->validate(['difficulty'=>['required','in:easy,medium,difficult,clear']]);
        $exists=DB::table('job_prep_question_links')->where('level',$level)->where('entity_id',$entity)->where('question_id',$question)->exists();
        abort_unless($exists,404);
        $student=Auth::guard('student')->user();
        if($data['difficulty']==='clear') {
            DB::table('student_job_prep_readings')->where('student_id',$student->id)->where('question_id',$question)->delete();
        } else {
            DB::table('student_job_prep_readings')->updateOrInsert(
                ['student_id'=>$student->id,'question_id'=>$question],
                ['difficulty'=>$data['difficulty'],'updated_at'=>now()]
            );
        }
        return back()->with('success','Reading level saved.');
    }

    public function saveComment(Request $request,string $kind,string $level,int $entity,int $question)
    {
        $this->checkKind($kind);
        abort_unless(in_array($level,['category','subject','chapter'],true),404);
        $this->resolveScope($kind,$level,$entity);
        abort_unless(DB::table('job_prep_question_links')->where('level',$level)->where('entity_id',$entity)->where('question_id',$question)->exists(),404);
        $data=$request->validate(['comment'=>'required|string|max:1000']);
        DB::table('student_job_prep_comments')->insert(['student_id'=>Auth::guard('student')->id(),'question_id'=>$question,'comment'=>$data['comment'],'created_at'=>now(),'updated_at'=>now()]);
        return back()->with('success','Comment added.');
    }

    public function saveReaction(Request $request,string $kind,string $level,int $entity,int $question)
    {
        $this->resolveScope($kind,$level,$entity);
        abort_unless(DB::table('job_prep_question_links')->where('level',$level)->where('entity_id',$entity)->where('question_id',$question)->exists(),404);
        $studentId=Auth::guard('student')->id();
        abort_unless($studentId,403);
        $key=['student_id'=>$studentId,'question_id'=>$question];
        $already=DB::table('student_job_prep_reactions')->where($key)->exists();
        if($already){DB::table('student_job_prep_reactions')->where($key)->delete();}
        else{DB::table('student_job_prep_reactions')->insert($key+['created_at'=>now(),'updated_at'=>now()]);}
        return back()->with('success',$already?'Reaction removed.':'Reaction saved.');
    }

    public function saveRating(Request $request,string $kind,string $level,int $entity,int $question)
    {
        $this->resolveScope($kind,$level,$entity);
        abort_unless(DB::table('job_prep_question_links')->where('level',$level)->where('entity_id',$entity)->where('question_id',$question)->exists(),404);
        $studentId=Auth::guard('student')->id();
        abort_unless($studentId,403);
        $validated=$request->validate(['rating'=>['required','integer','between:1,5']]);
        DB::table('student_job_prep_ratings')->updateOrInsert(
            ['student_id'=>$studentId,'question_id'=>$question],
            ['rating'=>(int)$validated['rating'],'updated_at'=>now()]
        );
        return back()->with('success','Rating saved.');
    }

    private function resolveScope(string $kind,string $level,int $entity): array
    {
        $this->checkKind($kind);
        abort_unless(in_array($level,['category','subject','chapter'],true),404);
        if($level==='category'){$cat=$this->categoryRecord($kind,$entity);return [$cat,null,null];}
        if($level==='subject'){$sub=DB::table('job_prep_subjects')->where('id',$entity)->first()??abort(404);$cat=$this->categoryRecord($kind,$sub->category_id);$this->subjectRecord($cat->id,$sub->id);return [$cat,$sub,null];}
        $ch=DB::table('job_prep_chapters')->where('id',$entity)->first()??abort(404);
        $sub=DB::table('job_prep_subjects')->where('id',$ch->subject_id)->first()??abort(404);
        $cat=$this->categoryRecord($kind,$sub->category_id);$this->subjectRecord($cat->id,$sub->id);$this->chapterRecord($sub->id,$ch->id);return [$cat,$sub,$ch];
    }

    public function quizCreate(Request $request,string $kind,string $level,int $entity)
    {
        if (!Auth::guard('student')->check()) return redirect()->guest(route('students.login'));
        [$cat,$sub,$ch]=$this->resolveScope($kind,$level,$entity);
        $access=$this->accessService()->access((int)$cat->id);
        $remainingQuizzes=$this->accessService()->remainingQuizzes((int)Auth::guard('student')->id(),$access);
        $subjects=DB::table('job_prep_subjects')->where('category_id',$cat->id)->where('status','Active')->orderBy('sort_order')->get();
        $chapters=DB::table('job_prep_chapters')->whereIn('subject_id',$subjects->pluck('id')->all())->where('status','Active')->orderBy('sort_order')->get();
        $available=$this->quizLinks($cat->id,$sub?->id,$ch?->id)->distinct()->count('l.question_id');
        return view('homepage.job-preparation.quiz.create',compact('kind','level','entity','cat','sub','ch','subjects','chapters','available','access','remainingQuizzes'));
    }

    private function quizLinks(int $categoryId,?int $subjectId=null,?int $chapterId=null)
    {
        $q=DB::table('job_prep_question_links as l')->join('questions as q','q.id','=','l.question_id')->where('q.type','MCQ')->where('q.status','Active');
        if($chapterId){return $q->where('l.level','chapter')->where('l.entity_id',$chapterId);}
        if($subjectId){$chapIds=DB::table('job_prep_chapters')->where('subject_id',$subjectId)->where('status','Active')->pluck('id')->all();return $q->where(function($z)use($subjectId,$chapIds){$z->where(function($x)use($subjectId){$x->where('l.level','subject')->where('l.entity_id',$subjectId);})->orWhere(function($x)use($chapIds){$x->where('l.level','chapter')->whereIn('l.entity_id',$chapIds);});});}
        $subIds=DB::table('job_prep_subjects')->where('category_id',$categoryId)->where('status','Active')->pluck('id')->all();
        $chapterIds=DB::table('job_prep_chapters')->whereIn('subject_id',$subIds)->where('status','Active')->pluck('id')->all();
        return $q->where(function($z)use($categoryId,$subIds,$chapterIds){$z->where(function($x)use($categoryId){$x->where('l.level','category')->where('l.entity_id',$categoryId);})->orWhere(function($x)use($subIds){$x->where('l.level','subject')->whereIn('l.entity_id',$subIds);})->orWhere(function($x)use($chapterIds){$x->where('l.level','chapter')->whereIn('l.entity_id',$chapterIds);});});
    }

    public function quizStart(Request $request,string $kind,string $level,int $entity)
    {
        if (!Auth::guard('student')->check()) return redirect()->guest(route('students.login'));
        [$cat,$sub,$ch]=$this->resolveScope($kind,$level,$entity);
        $access=$this->accessService()->access((int)$cat->id);
        $data=$request->validate(['subject_id'=>'nullable|integer','chapter_id'=>'nullable|integer','count'=>'required|integer|min:1|max:100','minutes'=>'required|integer|min:1|max:180','order'=>'required|in:random,latest']);
        $subjectId=$data['subject_id']??($sub?->id); $chapterId=$data['chapter_id']??($ch?->id);
        if($sub && $subjectId!=$sub->id)abort(422);
        if($ch && $chapterId!=$ch->id)abort(422);
        if($subjectId){$this->subjectRecord($cat->id,(int)$subjectId);}
        if($chapterId){abort_unless($subjectId,422);$this->chapterRecord((int)$subjectId,(int)$chapterId);}
        // Each student's previous quiz questions are excluded as long as enough fresh questions exist.
        $subjectId=$subjectId ? (int)$subjectId : null;
        $chapterId=$chapterId ? (int)$chapterId : null;
        $base=$this->quizLinks($cat->id,$subjectId,$chapterId)->select('q.id')->distinct();
        $available=(clone $base)->count('q.id');
        if($available < $data['count'])return back()->withErrors(['count'=>'এই Filter-এ '.$data['count'].'টি MCQ নেই। পাওয়া গেছে '.$available.'টি।'])->withInput();
        $studentId=Auth::guard('student')->id();
        $seen=$studentId ? DB::table('student_job_prep_quiz_history')->where('student_id',$studentId)->whereIn('question_id',(clone $base)->pluck('q.id')->all())->pluck('question_id')->all() : (array)$request->session()->get('jp_quiz_seen',[]);
        $fresh=(clone $base)->whereNotIn('q.id',$seen);
        $orderQuery=function($query)use($data){return $data['order']==='random' ? $query->inRandomOrder() : $query->orderByDesc('q.id');};
        $ids=$orderQuery($fresh)->limit($data['count'])->pluck('q.id')->all();
        if(count($ids)<$data['count']) {
            $remaining=$data['count']-count($ids);
            $extra=$orderQuery((clone $base)->whereNotIn('q.id',$ids))->limit($remaining)->pluck('q.id')->all();
            $ids=array_merge($ids,$extra);
        }
        // Reserve an attempt before exposing quiz questions.
        $token=(string)\Illuminate\Support\Str::uuid();
        if (!$this->accessService()->reserveQuiz((int)$cat->id,$token,$access)) {
            return back()->withErrors(['quiz'=>'এই মাসের Quiz Limit শেষ। প্যাকেজ কিনুন।'])->withInput();
        }
        // Mark questions as seen when the quiz starts, not only after submission.
        if($studentId){
            $stamp=now();
            foreach($ids as $qid){DB::table('student_job_prep_quiz_history')->updateOrInsert(['student_id'=>$studentId,'question_id'=>$qid],['last_seen_at'=>$stamp]);}
        } else {
            $request->session()->put('jp_quiz_seen',array_values(array_unique(array_merge($seen,$ids))));
        }
        $request->session()->put('jp_quiz_'.$token,['student_id'=>(int)$studentId,'ids'=>$ids,'minutes'=>$data['minutes'],'started'=>time(),'kind'=>$kind,'title'=>$cat->name,'submitted'=>false,'level'=>$level,'entity'=>$entity,'category_id'=>$cat->id,'subject_id'=>$subjectId,'chapter_id'=>$chapterId]);
        return redirect()->route('student.jobprep.quiz.exam',['token'=>$token]);
    }

    public function quizExam(Request $request,string $token)
    {
        $quiz=$request->session()->get('jp_quiz_'.$token);abort_unless(is_array($quiz) && Auth::guard('student')->id()==($quiz['student_id']??0),404);
        if(!empty($quiz['result']))return redirect()->route('student.jobprep.quiz.result',['token'=>$token]);
        $questions=DB::table('questions')->whereIn('id',$quiz['ids'])->get()->keyBy('id');
        $items=DB::table('mcq_items')->whereIn('question_id',$quiz['ids'])->orderBy('id')->get()->groupBy('question_id');
        $remaining=max(0,($quiz['started']+$quiz['minutes']*60)-time());
        return view('homepage.job-preparation.quiz.exam',compact('token','quiz','questions','items','remaining'));
    }

    public function quizSubmit(Request $request,string $token)
    {
        $quiz=$request->session()->get('jp_quiz_'.$token);abort_unless(is_array($quiz) && Auth::guard('student')->id()==($quiz['student_id']??0),404);
        if(!empty($quiz['result']))return redirect()->route('student.jobprep.quiz.result',['token'=>$token]);
        $ids=$quiz['ids'];
        $isExpired=time()>($quiz['started']+$quiz['minutes']*60+5);
        $selected=$isExpired?[]:(array)$request->input('answer',[]);
        $items=DB::table('mcq_items')->whereIn('question_id',$ids)->get()->groupBy('question_id');
        $correct=0;$wrong=0;$unanswered=0;$review=[];
        foreach($ids as $id){$options=$items->get($id,collect());$right=$options->first(fn($v)=>in_array(strtolower(trim((string)$v->correct_answer)),['1','yes','true','on'],true));$chosen=$selected[$id]??null;$valid=$options->contains(fn($v)=>(string)$v->id===(string)$chosen);if(!$valid){$chosen=null;$unanswered++;}elseif($right&&(string)$right->id===(string)$chosen){$correct++;}else{$wrong++;}$review[$id]=['chosen'=>$chosen,'correct'=>$right?->id];}
        $quiz['result']=['correct'=>$correct,'wrong'=>$wrong,'unanswered'=>$unanswered,'review'=>$review,'total'=>count($ids)];
        $request->session()->put('jp_quiz_'.$token,$quiz);
        return redirect()->route('student.jobprep.quiz.result',['token'=>$token]);
    }

    public function quizResult(Request $request,string $token)
    {
        $quiz=$request->session()->get('jp_quiz_'.$token);abort_unless(is_array($quiz)&&isset($quiz['result']) && Auth::guard('student')->id()==($quiz['student_id']??0),404);
        $questions=DB::table('questions')->whereIn('id',$quiz['ids'])->get()->keyBy('id');
        $items=DB::table('mcq_items')->whereIn('question_id',$quiz['ids'])->orderBy('id')->get()->groupBy('question_id');
        return view('homepage.job-preparation.quiz.result',compact('quiz','questions','items'));
    }

}

