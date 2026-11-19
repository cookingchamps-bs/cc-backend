<?php

/**
 * Remove media files under public/storage and storage/app/public that are not referenced
 * in the database or app/config/resources code (vendor/ excluded from scan).
 * Run: php scripts/prune-unused-public-storage.php
 */

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$storageRoots = array_filter([
    realpath(__DIR__.'/../public/storage'),
    realpath(__DIR__.'/../storage/app/public'),
]);
if ($storageRoots === []) {
    fwrite(STDERR, "No storage directories found.\n");
    exit(1);
}

$extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico', 'mp4', 'webm', 'mov'];

$used = [];

$add = static function (string $path) use (&$used): void {
    $path = trim($path);
    if ($path === '' || strcasecmp($path, 'null') === 0) {
        return;
    }
    $norm = normalizeStoragePath($path);
    if ($norm !== '') {
        $used[$norm] = true;
    }
};

function normalizeStoragePath(string $path): string
{
    $path = str_replace('\\', '/', trim($path));
    $path = preg_replace('#^https?://[^/]+/#', '', $path) ?? $path;
    $path = preg_replace('#^.*?/public/storage/#', '', $path) ?? $path;
    $path = preg_replace('#^public/storage/#', '', $path) ?? $path;
    $path = preg_replace('#^storage/app/public/#', '', $path) ?? $path;
    $path = preg_replace('#^storage/#', '', $path) ?? $path;

    return ltrim($path, '/');
}

// Always keep common static assets referenced in layouts / defaults
$alwaysKeep = [
    'logo.png',
    'vendor_image.jpg',
    'default_user.png',
    'default_image.png',
    '95.png',
];
foreach ($alwaysKeep as $f) {
    $used[$f] = true;
}

// --- Database: known path columns ---
$dbColumns = [
    'products' => ['image'],
    'product_images' => ['images'],
    'product_categories' => ['image'],
    'categories' => ['image'],
    'monthly_challenges' => ['banner_image'],
    'growing_activities' => ['image'],
    'banners' => ['image'],
    'stories' => ['image'],
    'story_images' => ['images'],
    'users' => ['image'],
    'kids' => ['kid_image'],
    'recipes' => ['banner_image'],
    'recipe_tools' => ['tool_image'],
    'about_us' => ['image'],
    'cooking_champs_intros' => ['image'],
    'admins' => ['image'],
    'school_videos' => ['video_path', 'thumbnail_image'],
];

foreach ($dbColumns as $table => $columns) {
    if (! Schema::hasTable($table)) {
        continue;
    }
    foreach ($columns as $col) {
        if (! Schema::hasColumn($table, $col)) {
            continue;
        }
        $rows = DB::table($table)->whereNotNull($col)->distinct()->pluck($col);
        foreach ($rows as $val) {
            $add((string) $val);
        }
    }
}

// --- Database: extract paths from HTML/text fields (Summernote, etc.) ---
$htmlColumns = [
    'products' => ['description'],
    'recipes' => ['description'],
    'about_us' => ['description'],
    'cooking_champs_intros' => ['description'],
    'help_and_supports' => ['description'],
    'school_videos' => ['description'],
];

$htmlPathPattern = '#(?:public/)?storage/([a-zA-Z0-9_\-./]+\.(?:jpg|jpeg|png|gif|webp|svg|bmp|mp4|webm|mov))#i';

foreach ($htmlColumns as $table => $columns) {
    if (! Schema::hasTable($table)) {
        continue;
    }
    foreach ($columns as $col) {
        if (! Schema::hasColumn($table, $col)) {
            continue;
        }
        $rows = DB::table($table)->whereNotNull($col)->pluck($col);
        foreach ($rows as $html) {
            if (! is_string($html) || $html === '') {
                continue;
            }
            if (preg_match_all($htmlPathPattern, $html, $m)) {
                foreach ($m[1] as $p) {
                    $add($p);
                }
            }
        }
    }
}

// --- Codebase: resources + app (exclude vendor, storage/framework views) ---
$scanDirs = [
    realpath(__DIR__.'/../resources'),
    realpath(__DIR__.'/../app'),
    realpath(__DIR__.'/../config'),
];
$scanDirs = array_filter($scanDirs);

$filePattern = '/(?:public\/storage\/|storage\/|asset\([\'"]storage\/|asset\([\'"]public\/storage\/)([a-zA-Z0-9_\-\/\.]+)/';

foreach ($scanDirs as $dir) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if (! $file->isFile()) {
            continue;
        }
        $ext = strtolower($file->getExtension());
        if (! in_array($ext, ['php', 'blade.php', 'js', 'json', 'css', 'env', 'xml'], true)) {
            continue;
        }
        $content = @file_get_contents($file->getPathname());
        if ($content === false) {
            continue;
        }
        if (preg_match_all($filePattern, $content, $matches)) {
            foreach ($matches[1] as $p) {
                $add($p);
            }
        }
    }
}

// --- Normalize used keys: also add basename-only if duplicate risk (skip) ---

$deleted = 0;
$skipped = 0;
$bytes = 0;

foreach ($storageRoots as $storageRoot) {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($storageRoot, FilesystemIterator::SKIP_DOTS));

    foreach ($rii as $file) {
        if (! $file->isFile()) {
            continue;
        }
        $ext = strtolower($file->getExtension());
        if (! in_array($ext, $extensions, true)) {
            continue;
        }
        $rel = ltrim(str_replace($storageRoot, '', $file->getPathname()), '/');
        $rel = str_replace('\\', '/', $rel);

        if (isset($used[$rel])) {
            $skipped++;

            continue;
        }

        $bytes += $file->getSize();
        @unlink($file->getPathname());
        $deleted++;
    }
}

echo "Prune storage complete (".implode(', ', $storageRoots).").\n";
echo "Kept references (unique paths): ".count($used)."\n";
echo "Files skipped (still used): {$skipped}\n";
echo "Files deleted: {$deleted}\n";
echo "Approx. bytes freed: {$bytes}\n";
