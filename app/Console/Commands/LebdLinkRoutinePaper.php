<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LebdLinkRoutinePaper extends Command
{
    protected $signature = 'lebd:link-routine-paper {routine_row_id : routine_item_rows.id} {paper_id : papers.id} {--force : Replace an existing explicit mapping}';
    protected $description = 'Manually link a verified exam paper to one routine row; never publish or change payment records.';

    public function handle(): int
    {
        foreach (['routine_item_rows','routines','papers'] as $table) {
            if (!Schema::hasTable($table)) { $this->error("Missing table: {$table}"); return self::FAILURE; }
        }
        foreach (['routine_item_rows.paper_id','routine_item_rows.routine_id','routines.batch_id','routines.department_id', 'papers.batch_id','papers.department_id'] as $ref) {
            [$table,$column] = explode('.', $ref, 2);
            if (!Schema::hasColumn($table,$column)) { $this->error("Missing column: {$ref}"); return self::FAILURE; }
        }
        $rowId=(int)$this->argument('routine_row_id'); $paperId=(int)$this->argument('paper_id');
        if ($rowId<=0 || $paperId<=0) { $this->error('Positive numeric IDs required.'); return self::FAILURE; }
        $row=DB::table('routine_item_rows as ri')->join('routines as r','r.id','=','ri.routine_id')
            ->where('ri.id',$rowId)->select('ri.id','ri.paper_id','ri.paper_name','ri.routine_id','r.batch_id','r.department_id')->first();
        $paper=DB::table('papers')->where('id',$paperId)->first();
        if (!$row || !$paper) { $this->error('Routine row or paper not found.'); return self::FAILURE; }
        // A paper without a batch cannot be safely linked to a batch-specific routine.
        if (!$row->batch_id || !$paper->batch_id || (int)$row->batch_id !== (int)$paper->batch_id) {
            $this->error('Batch IDs differ or are missing; linkage denied.'); return self::FAILURE;
        }
        if ($paper->department_id !== null) {
            $dept=(int)$paper->department_id;
            $allowed=($row->department_id !== null && (int)$row->department_id===$dept);
            if (!$allowed && Schema::hasTable('routine_department_columns') && Schema::hasColumn('routine_department_columns','routine_id') && Schema::hasColumn('routine_department_columns','department_id')) {
                $allowed=DB::table('routine_department_columns')->where('routine_id',$row->routine_id)->where('department_id',$dept)->exists();
            }
            if (!$allowed) { $this->error('Paper department is not mapped to this routine; linkage denied.'); return self::FAILURE; }
        }
        if ($row->paper_id && (int)$row->paper_id!==$paperId && !$this->option('force')) {
            $this->error('Routine already mapped. Use --force only after reviewing the previous link.'); return self::FAILURE;
        }
        $this->line("Routine row {$row->id} / {$row->paper_name} / batch {$row->batch_id} / department ".($row->department_id ?? 'shared'));
        $this->line("Paper {$paper->id} / {$paper->name} / batch {$paper->batch_id} / department ".($paper->department_id ?? 'shared')." / status {$paper->status}");
        if ((string)$paper->status !== 'Published') $this->warn('This paper is not Published. Linking will NOT publish it.');
        if (!$this->confirm('Have you manually verified the exam identity, timing and question set?', false)) {
            $this->warn('No changes made.'); return self::FAILURE;
        }
        DB::table('routine_item_rows')->where('id',$rowId)->update(['paper_id'=>$paperId]);
        $this->info('Mapping saved. Existing exam access and published/time guards remain enforced.');
        return self::SUCCESS;
    }
}
