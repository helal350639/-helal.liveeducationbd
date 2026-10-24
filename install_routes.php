<?php
$project = __DIR__;
$target = $project.'/routes/web.php';
if (!is_file($target)) {fwrite(STDERR,"Missing routes/web.php\n");exit(1);}
$contents=file_get_contents($target);
$line="require __DIR__.'/exam_mcq.php';";
if (strpos($contents,"exam_mcq.php")===false) {
    if(file_put_contents($target,rtrim($contents)."\n\n".$line."\n")===false) {fwrite(STDERR,"Unable to update routes/web.php\n");exit(1);}
    echo "Registered exam_mcq routes.\n";
} else echo "Routes already registered.\n";
