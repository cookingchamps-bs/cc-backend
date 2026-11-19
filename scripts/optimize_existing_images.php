<?php

declare(strict_types=1);

$basePath = __DIR__.'/../storage/app/public';
$targetDirs = [
    'image',
    'story_images',
    'recipes',
    'product_categories',
    'monthly_challenge',
    'kid_image',
    'growing_activity',
    'recipe_tool',
    'aboutus',
];

$maxWidth = 1280;
$maxHeight = 1280;
$processed = 0;
$skipped = 0;
$failed = 0;
$totalBefore = 0;
$totalAfter = 0;

foreach ($targetDirs as $dir) {
    $directory = $basePath.'/'.$dir;
    if (! is_dir($directory)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $fileInfo) {
        if (! $fileInfo->isFile()) {
            continue;
        }

        $path = $fileInfo->getPathname();
        $extension = strtolower($fileInfo->getExtension());

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            continue;
        }

        $beforeSize = filesize($path);
        if ($beforeSize === false) {
            $failed++;
            continue;
        }

        $totalBefore += $beforeSize;

        $imageInfo = @getimagesize($path);
        if (! $imageInfo || empty($imageInfo['mime'])) {
            $skipped++;
            $totalAfter += $beforeSize;
            continue;
        }

        [$width, $height] = $imageInfo;
        $mime = strtolower((string) $imageInfo['mime']);

        $isAlreadyOptimized = preg_match('/_(full|medium|thumb)\.(webp|png|jpe?g)$/i', $path) === 1;
        if ($isAlreadyOptimized) {
            $skipped++;
            $totalAfter += $beforeSize;
            continue;
        }

        if ($width <= $maxWidth && $height <= $maxHeight && $beforeSize <= (1024 * 1024)) {
            $skipped++;
            $totalAfter += $beforeSize;
            continue;
        }

        $source = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            default => null,
        };

        if (! $source) {
            $failed++;
            $totalAfter += $beforeSize;
            continue;
        }

        $scale = min($maxWidth / $width, $maxHeight / $height, 1);
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        if (in_array($mime, ['image/png', 'image/webp'], true)) {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
        } else {
            $white = imagecolorallocate($resized, 255, 255, 255);
            imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $white);
        }

        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        $saved = match ($mime) {
            'image/jpeg', 'image/jpg' => imagejpeg($resized, $path, 82),
            'image/png' => imagepng($resized, $path, 7),
            'image/webp' => function_exists('imagewebp') ? imagewebp($resized, $path, 80) : false,
            default => false,
        };

        if (! $saved) {
            $failed++;
            $totalAfter += $beforeSize;
            continue;
        }

        clearstatcache(true, $path);
        $afterSize = filesize($path);
        $totalAfter += ($afterSize === false ? $beforeSize : $afterSize);
        $processed++;
    }
}

$beforeMb = round($totalBefore / 1024 / 1024, 2);
$afterMb = round($totalAfter / 1024 / 1024, 2);
$savedMb = round(($totalBefore - $totalAfter) / 1024 / 1024, 2);

echo "Processed: {$processed}\n";
echo "Skipped: {$skipped}\n";
echo "Failed: {$failed}\n";
echo "Size before: {$beforeMb} MB\n";
echo "Size after: {$afterMb} MB\n";
echo "Saved: {$savedMb} MB\n";
