<?php
namespace App\Http\Controllers\EBook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentPremiumController extends Controller
{
    private function checkedKind(string $kind): void { abort_unless(in_array($kind,['department','non-department'],true),404); }
    private function category(string $kind, int $category): object
    {
        $this->checkedKind($kind);
        return DB::table('lebd_ebook_categories')->where('kind',$kind)->where('id',$category)->where('status','Active')->first() ?? abort(404);
    }
    private function chapter(string $kind, int $category, int $chapter): object
    {
        return DB::table('lebd_ebook_chapters')->where('category_id',$category)->where('id',$chapter)->where('status','Active')->first() ?? abort(404);
    }
    private function paid(int $category): bool
    {
        if (!auth()->check()) return false;
        return DB::table('lebd_ebook_entitlements')->where('user_id',auth()->id())->where('category_id',$category)->where('status','Active')->where('expires_at','>',now())->exists();
    }
    private function content(int $chapter, bool $paid): array
    {
        // IMPORTANT: guest/free users receive only a limited set of blocks server-side.
        $query=DB::table('lebd_ebook_blocks')->where('chapter_id',$chapter)->orderBy('sort_order')->orderBy('id');
        $blocks=$paid?$query->get():$query->limit(8)->get();
        $ids=$blocks->where('block_type','database_mcq')->pluck('question_id')->filter()->all();
        $qs=DB::table('questions')->whereIn('id',$ids)->get()->keyBy('id');
        $opts=DB::table('mcq_items')->whereIn('question_id',$ids)->orderBy('id')->get()->groupBy('question_id');
        return compact('blocks','qs','opts');
    }
    public function categories(string $kind)
    {
        $this->checkedKind($kind);
        $categories=DB::table('lebd_ebook_categories')->where('kind',$kind)->where('status','Active')->orderBy('sort_order')->orderBy('id')->get();
        return view('ebook.categories',compact('kind','categories'));
    }
    public function chapters(string $kind,int $category)
    {
        $cat=$this->category($kind,$category);
        $chapters=DB::table('lebd_ebook_chapters')->where('category_id',$category)->where('status','Active')->orderBy('sort_order')->orderBy('id')->get();
        $paid=$this->paid($category);
        $packages=DB::table('lebd_ebook_packages as p')->join('lebd_ebook_package_categories as pc','pc.package_id','=','p.id')->where('pc.category_id',$category)->where('p.status','Active')->orderBy('p.price')->select('p.*')->get();
        return view('ebook.student-category',compact('kind','cat','chapters','paid','packages'));
    }
    public function read(string $kind,int $category,int $chapter)
    {
        $cat=$this->category($kind,$category);
        $ch=$this->chapter($kind,$category,$chapter);
        $paid=$this->paid($category);
        if(!$paid){
            $first=(int)DB::table('lebd_ebook_chapters')->where('category_id',$category)->where('status','Active')->orderBy('sort_order')->orderBy('id')->value('id');
            if($first!==$chapter) return redirect()->route('student.ebook.chapters',compact('kind','category'))->with('ebook_notice','এই অধ্যায় পড়তে Premium Package প্রয়োজন।');
        }
        $preview=false;
        $freePreview=!$paid;
        return view('ebook.student-read',array_merge(compact('kind','cat','ch','preview','freePreview'),$this->content($chapter,$paid)));
    }
    public function fullBook(string $kind,int $category)
    {
        $cat=$this->category($kind,$category);
        if(!$this->paid($category))return redirect()->route('student.ebook.chapters',compact('kind','category'))->with('ebook_notice','সম্পূর্ণ বই পড়তে Premium Package প্রয়োজন। প্রথম অধ্যায়ের Free Preview দেখতে পারো।');
        $chapters=DB::table('lebd_ebook_chapters')->where('category_id',$category)->where('status','Active')->orderBy('sort_order')->orderBy('id')->get();
        $bookChapters=[];
        foreach($chapters as $ch){$data=$this->content((int)$ch->id,true);$bookChapters[]=['ch'=>$ch,'blocks'=>$data['blocks'],'qs'=>$data['qs'],'opts'=>$data['opts']];}
        $preview=false;
        return view('ebook.full-book',compact('kind','cat','bookChapters','preview'));
    }
}
