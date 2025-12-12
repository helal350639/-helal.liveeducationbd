<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishCKEditorPlugin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ckeditor:publish-plugin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish CKEditor MathType Plugin to Public Folder.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
      $from = base_path('node_modules/@wiris/');
      $to = public_path('ckeditor/plugins/ckeditor_wiris');

      File::ensureDirectoryExists($to);
      File::copyDirectory($from, $to);

      $this->info('CKEditor MathType plugin published successfully!');
    }
}
