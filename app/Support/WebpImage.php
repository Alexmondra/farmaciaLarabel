<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebpImage
{
    public static function store(UploadedFile $file, string $directory, string $disk = 'public', int $quality = 82): string
    {
        $image = self::createImage($file);

        $basename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'imagen';
        $path = trim($directory, '/') . '/' . $basename . '-' . Str::random(10) . '.webp';

        ob_start();
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
        imagewebp($image, null, $quality);
        $contents = ob_get_clean();

        imagedestroy($image);

        Storage::disk($disk)->put($path, $contents);

        return $path;
    }

    private static function createImage(UploadedFile $file)
    {
        return match (strtolower($file->getMimeType() ?: '')) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($file->getRealPath()),
            'image/png' => imagecreatefrompng($file->getRealPath()),
            'image/gif' => imagecreatefromgif($file->getRealPath()),
            'image/webp' => imagecreatefromwebp($file->getRealPath()),
            default => throw new \InvalidArgumentException('Formato de imagen no soportado para WebP.'),
        };
    }
}
