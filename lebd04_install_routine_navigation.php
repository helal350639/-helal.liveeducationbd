<?php
/**
 * LEBD-04 routine navigation installer (project-root version).
 * Place this file next to artisan and run:
 * php lebd04_install_routine_navigation.php
 * Existing views are backed up before any modification.
 */

$projectRoot = __DIR__;
$views = $projectRoot . '/resources/views/layouts/routines';
$partial = $views . '/partials/all-pages-navigation.blade.php';

if (!is_file($partial)) {
    fwrite(STDERR, "ERROR: Navigation partial not found: {$partial}\n");
    fwrite(STDERR, "Extract resources/views/layouts/routines/partials/all-pages-navigation.blade.php from Step 3M ZIP first.\n");
    exit(1);
}

$pages = [
    'index.blade.php',
    'create.blade.php',
    'edit.blade.php',
    'show.blade.php',
    'show-item.blade.php',
    'add-item.blade.php',
    'edit-item.blade.php',
    'select-item-type.blade.php',
    'derived-add-item.blade.php',
    'derived-edit-item.blade.php',
    'derived-list.blade.php',
    'mcq-output.blade.php',
];

$include = "@include('layouts.routines.partials.all-pages-navigation')";
$backupRoot = $projectRoot . '/storage/lebd04_routine_nav_backups/' . date('Ymd_His');
$changed = 0;

foreach ($pages as $page) {
    $file = $views . '/' . $page;
    if (!is_file($file)) {
        echo "SKIP (not found): {$page}\n";
        continue;
    }

    $before = file_get_contents($file);
    if ($before === false) {
        fwrite(STDERR, "ERROR: Cannot read {$page}\n");
        exit(1);
    }

    if (strpos($before, $include) !== false) {
        echo "SKIP (already installed): {$page}\n";
        continue;
    }

    // Place the include directly after the Blade content section.
    // This avoids changing controllers, existing form markup, and scripts.
    $pattern = '/@section\s*\(\s*[\'\"]content[\'\"]\s*\)/';
    $after = preg_replace_callback($pattern, static function ($m) use ($include) {
        return $m[0] . "\n" . $include . "\n";
    }, $before, 1, $count);

    if ($after === null || $count !== 1) {
        echo "SKIP (no content section found): {$page}\n";
        continue;
    }

    if (!is_dir($backupRoot) && !mkdir($backupRoot, 0775, true) && !is_dir($backupRoot)) {
        fwrite(STDERR, "ERROR: Cannot create backup folder {$backupRoot}\n");
        exit(1);
    }

    if (!copy($file, $backupRoot . '/' . $page)) {
        fwrite(STDERR, "ERROR: Cannot back up {$page}. Stopping.\n");
        exit(1);
    }

    if (file_put_contents($file, $after, LOCK_EX) === false) {
        fwrite(STDERR, "ERROR: Cannot update {$page}. Restore from {$backupRoot}\n");
        exit(1);
    }

    $changed++;
    echo "UPDATED: {$page}\n";
}

echo "Finished. {$changed} Routine views updated.\n";
echo "Backups: {$backupRoot}\n";
echo "Next: php artisan view:clear\n";
