<?php
namespace App\Http\Controllers\MCQJobPreparation;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class QuestionShareController extends Controller
{
    private function question(string $kind,string $level,int $entity,int $question): array
    {
        abort_unless(in_array($kind,['department','non-department'],true),404);
        abort_unless(in_array($level,['category','subject','chapter'],true),404);
        $categoryId=$entity;
        if ($level==='subject') {
            $categoryId=DB::table('job_prep_subjects')->where('id',$entity)->where('status','Active')->value('category_id');
        } elseif($level==='chapter') {
            $subjectId=DB::table('job_prep_chapters')->where('id',$entity)->where('status','Active')->value('subject_id');
            $categoryId=DB::table('job_prep_subjects')->where('id',$subjectId)->where('status','Active')->value('category_id');
        }
        $category=DB::table('job_prep_categories')->where('id',$categoryId)->where('kind',$kind)->where('status','Active')->first();
        abort_unless($category,404);
        $linked=DB::table('job_prep_question_links')->where('level',$level)->where('entity_id',$entity)->where('question_id',$question)->exists();
        abort_unless($linked,404);
        $q=DB::table('questions')->where('id',$question)->where('type','MCQ')->where('status','Active')->first();
        abort_unless($q,404);
        $options=DB::table('mcq_items')->where('question_id',$question)->orderBy('id')->limit(6)->get();
        return [$q,$options,$category];
    }

    public function show(string $kind,string $level,int $entity,int $question)
    {
        [$q,$options,$category]=$this->question($kind,$level,$entity,$question);
        $shareUrl=route('student.jobprep.share.show',compact('kind','level','entity','question'));
        $imageUrl=route('student.jobprep.share.image',compact('kind','level','entity','question'));
        $readingUrl=route('student.jobprep.questions',compact('kind','level','entity')).'?question='.$question.'#question-'.$question;
        return view('homepage.job-preparation.share-question',compact('q','options','category','shareUrl','imageUrl','readingUrl'));
    }

    private function fontPath(): ?string
    {
        $candidates=[
            'C:/Windows/Fonts/Nirmala.ttf','C:/Windows/Fonts/vrinda.ttf',
            '/usr/share/fonts/truetype/noto/NotoSansBengali-Regular.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        ];
        foreach($candidates as $font){if(is_file($font))return $font;}
        return null;
    }

    private function writeLine($canvas,string $text,int $size,int $x,int $y,int $color,?string $font): void
    {
        if($font && function_exists('imagettftext')) {
            imagettftext($canvas,$size,0,$x,$y,$color,$font,$text);
        } else {
            imagestring($canvas,5,$x,$y-15,$text,$color);
        }
    }

    private function wrapLines(string $text,int $limit=66): array
    {
        $text=trim(preg_replace('/\s+/u',' ',strip_tags(html_entity_decode($text,ENT_QUOTES|ENT_HTML5,'UTF-8'))));
        if($text==='') return [];
        $words=preg_split('/\s+/u',$text);
        $lines=[];$line='';
        foreach($words as $word){
            $candidate=trim($line.' '.$word);
            if(mb_strlen($candidate,'UTF-8')>$limit && $line!==''){$lines[]=$line;$line=$word;}
            else{$line=$candidate;}
        }
        if($line!=='')$lines[]=$line;
        return $lines;
    }

    public function image(string $kind,string $level,int $entity,int $question)
    {
        [$q,$options,$category]=$this->question($kind,$level,$entity,$question);
        abort_unless(function_exists('imagecreatetruecolor') && function_exists('imagejpeg'),503,'PHP GD extension is required to create JPG cards.');
        $im=imagecreatetruecolor(1200,630);
        $white=imagecolorallocate($im,255,255,255);$navy=imagecolorallocate($im,13,44,94);
        $blue=imagecolorallocate($im,22,110,217);$gray=imagecolorallocate($im,78,97,123);
        $pale=imagecolorallocate($im,240,246,253);$line=imagecolorallocate($im,214,229,246);
        imagefill($im,0,0,$white);imagefilledrectangle($im,0,0,1200,112,$navy);
        $font=$this->fontPath();
        $this->writeLine($im,'LIVE EDUCATION BD',29,53,74,$white,$font);
        $this->writeLine($im,'MCQ JOB PREPARATION',16,850,72,$white,$font);
        $this->writeLine($im,'Category: '.$category->name,19,55,157,$blue,$font);
        $title=$this->wrapLines((string)$q->title,65);
        $y=205;
        foreach(array_slice($title,0,3) as $i=>$part){$this->writeLine($im,($i===0?'Q) ':'').$part,23,55,$y,$navy,$font);$y+=40;}
        $y=max($y+14,328);$letters=['A','B','C','D'];
        foreach($options->take(4) as $index=>$option){
            $text=$this->wrapLines((string)$option->item,28);$column=$index%2;$row=intdiv($index,2);
            $x=55+565*$column;$yy=$y+94*$row;
            imagefilledrectangle($im,$x,$yy,$x+542,$yy+82,$pale);
            imagerectangle($im,$x,$yy,$x+542,$yy+82,$line);
            $this->writeLine($im,($letters[$index]??'').') '.($text[0]??''),17,$x+17,$yy+33,$navy,$font);
            if(isset($text[1]))$this->writeLine($im,$text[1],15,$x+44,$yy+60,$gray,$font);
        }
        imagefilledrectangle($im,0,594,1200,630,$blue);
        $this->writeLine($im,'Practice more at liveeducationbd.com',14,52,619,$white,$font);
        ob_start();imagejpeg($im,null,88);$data=ob_get_clean();imagedestroy($im);
        return response($data,200,['Content-Type'=>'image/jpeg','Cache-Control'=>'public, max-age=3600','Content-Disposition'=>'inline; filename="liveeducationbd-mcq.jpg"']);
    }
}
