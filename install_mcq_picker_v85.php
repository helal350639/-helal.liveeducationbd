<?php
// Run once in Laravel root: php install_mcq_picker_v85.php
$root=__DIR__;
$picker=$root.'/resources/views/admin/exam-mcq/picker.blade.php';
$web=$root.'/routes/web.php';
if(!is_file($picker)||!is_file($web)){fwrite(STDERR,"Missing picker.blade.php or routes/web.php. No changes made.\n");exit(1);}
$old=file_get_contents($picker);$current=file_get_contents($web);
if(strpos($old,'@endsection')===false){fwrite(STDERR,"Picker lacks @endsection. No changes made.\n");exit(1);}
$include="@include('admin.exam-mcq.bulk-actions-v85')";
$require="require __DIR__.'/exam_mcq_bulk_v85.php';";
if(strpos($old,$include)===false){
    copy($picker,$picker.'.backup-before-v85');
    $last=strrpos($old,'@endsection');
    $new=substr($old,0,$last).$include."\n".substr($old,$last);
    if(file_put_contents($picker,$new)===false){fwrite(STDERR,"Picker update failed.\n");exit(1);}
}
if(strpos($current,'exam_mcq_bulk_v85.php')===false){
    copy($web,$web.'.backup-before-v85');
    if(file_put_contents($web,rtrim($current)."\n\n".$require."\n")===false){fwrite(STDERR,"Routes update failed.\n");exit(1);}
}
echo "v85 installed. Existing picker HTML/CSS retained.\n";
