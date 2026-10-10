<?php

namespace App\Http;

use App\Models\File;
use Illuminate\Support\Facades\Storage;

class FileThumbnail
{
    public function path(File $file): ?string
    {
        $disk = Storage::disk('local');
        $key = 'thumbnails/'.$file->uuid.'/preview-v2.jpg';
        if ($disk->exists($key)) {
            return $disk->path($key);
        }
        if (! extension_loaded('gd') || $file->category !== 'image') {
            return null;
        }
        $sourcePath = $disk->path($file->storage_key);
        $dimensions = @getimagesize($sourcePath);
        if ($dimensions === false || $dimensions[0] * $dimensions[1] > 16000000) {
            return null;
        }
        $decoder = match ($dimensions[2]) {
            IMAGETYPE_JPEG => 'imagecreatefromjpeg', IMAGETYPE_PNG => 'imagecreatefrompng',
            IMAGETYPE_GIF => 'imagecreatefromgif', IMAGETYPE_WEBP => 'imagecreatefromwebp',
            IMAGETYPE_AVIF => 'imagecreatefromavif', default => null,
        };
        if ($decoder === null || ! function_exists($decoder)) {
            return null;
        }
        $source = @$decoder($sourcePath);
        if ($source === false) {
            return null;
        }
        try {
            if ($dimensions[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
                $metadata = @exif_read_data($sourcePath);
                $orientation = $metadata['Orientation'] ?? 1;
                if (in_array($orientation, [2, 4, 5, 7], true)) {
                    imageflip($source, IMG_FLIP_HORIZONTAL);
                }
                $angle = match ($orientation) {
                    3, 4 => 180, 5, 6 => -90, 7, 8 => 90, default => 0,
                };
                if ($angle !== 0) {
                    $rotated = imagerotate($source, $angle, 0);
                    if ($rotated !== false) {
                        imagedestroy($source);
                        $source = $rotated;
                    }
                }
            }
            $scale = min(1, 160 / max(imagesx($source), imagesy($source)));
            $width = max(1, (int) round(imagesx($source) * $scale));
            $height = max(1, (int) round(imagesy($source) * $scale));
            do {
                $thumbnail = imagecreatetruecolor($width, $height);
                try {
                    imagefill($thumbnail, 0, 0, imagecolorallocate($thumbnail, 245, 247, 250));
                    imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));
                    ob_start();
                    try {
                        imagejpeg($thumbnail, null, 25);
                        $bytes = ob_get_contents();
                    } finally {
                        ob_end_clean();
                    }
                } finally {
                    imagedestroy($thumbnail);
                }
                $width = max(1, (int) floor($width * 0.75));
                $height = max(1, (int) floor($height * 0.75));
            } while (strlen($bytes) > 1024 && ($width > 1 || $height > 1));
            if (! $disk->put($key, $bytes)) {
                return null;
            }
        } finally {
            imagedestroy($source);
        }

        return $disk->path($key);
    }
}
