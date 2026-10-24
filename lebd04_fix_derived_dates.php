<?php
/**
 * LEBD-04 derived exam creation dates diagnostic/backfill.
 * Preview: php lebd04_fix_derived_dates.php 28
 * Apply:   php lebd04_fix_derived_dates.php 28 --apply
 * No tables are created or dropped. Main exams are not modified.
 */
$root = __DIR__;
require $root . '/vendor/autoload.php';
$app = require $root . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$id = $argv[1] ?? null;
$apply = in_array('--apply', $argv, true);
if (!ctype_digit((string) $id) || (int) $id < 1) {
    fwrite(STDERR, "Usage: php lebd04_fix_derived_dates.php ROUTINE_ID [--apply]\n");
    exit(1);
}
try {
    $service = $app->make(App\Services\RoutineDerivedDateService::class);
    if ($apply) {
        $changes = Illuminate\Support\Facades\DB::transaction(
            fn () => $service->recalculate((int) $id, true)
        );
    } else {
        $changes = $service->recalculate((int) $id, false);
    }
    echo ($apply ? 'APPLIED' : 'PREVIEW ONLY') . ': Routine #' . $id . PHP_EOL;
    foreach ($changes as $row) {
        echo $row['exam_name'] . ' [ID ' . $row['id'] . ']: '
            . 'PDF ' . ($row['before_pdf'] ?? 'NULL') . ', '
            . 'Question ' . ($row['before_question'] ?? 'NULL')
            . ' => ' . ($row['after'] ?? 'NULL (previous end unavailable)') . PHP_EOL;
    }
    echo count($changes) . " row(s) " . ($apply ? 'updated' : 'would change') . ".\n";
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
