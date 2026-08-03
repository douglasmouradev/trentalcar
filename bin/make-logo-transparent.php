<?php

declare(strict_types=1);

$in = __DIR__ . '/../public/assets/img/logo.jpeg';
$out = __DIR__ . '/../public/assets/img/logo.png';

$src = imagecreatefromjpeg($in);
if ($src === false) {
    fwrite(STDERR, "fail load jpeg\n");
    exit(1);
}

$w = imagesx($src);
$h = imagesy($src);

// Sample corners to detect solid light background (white or lavender panel).
$samples = [
    imagecolorat($src, 2, 2),
    imagecolorat($src, $w - 3, 2),
    imagecolorat($src, 2, $h - 3),
    imagecolorat($src, $w - 3, $h - 3),
];
$bgRs = [];
$bgGs = [];
$bgBs = [];
foreach ($samples as $rgb) {
    $bgRs[] = ($rgb >> 16) & 0xFF;
    $bgGs[] = ($rgb >> 8) & 0xFF;
    $bgBs[] = $rgb & 0xFF;
}
$bgR = (int) round(array_sum($bgRs) / count($bgRs));
$bgG = (int) round(array_sum($bgGs) / count($bgGs));
$bgB = (int) round(array_sum($bgBs) / count($bgBs));

$dst = imagecreatetruecolor($w, $h);
imagealphablending($dst, false);
imagesavealpha($dst, true);
$transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
imagefilledrectangle($dst, 0, 0, $w, $h, $transparent);

$tolerance = 42;
for ($y = 0; $y < $h; $y++) {
    for ($x = 0; $x < $w; $x++) {
        $rgb = imagecolorat($src, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        $dist = abs($r - $bgR) + abs($g - $bgG) + abs($b - $bgB);
        $nearWhite = $r >= 245 && $g >= 245 && $b >= 245;
        if ($nearWhite || $dist <= $tolerance) {
            continue;
        }

        // Soft edge against background
        if ($dist <= $tolerance + 28) {
            $alpha = (int) min(127, (int) round((($tolerance + 28 - $dist) / 28) * 127));
            $col = imagecolorallocatealpha($dst, $r, $g, $b, $alpha);
        } else {
            $col = imagecolorallocatealpha($dst, $r, $g, $b, 0);
        }
        imagesetpixel($dst, $x, $y, $col);
    }
}

imagealphablending($dst, false);
imagesavealpha($dst, true);
if (!imagepng($dst, $out, 6)) {
    fwrite(STDERR, "fail write png\n");
    exit(1);
}

echo "ok {$w}x{$h} -> {$out}\n";
