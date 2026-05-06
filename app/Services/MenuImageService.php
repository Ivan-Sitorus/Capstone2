<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MenuImageService
{
    public function convertAndStore(UploadedFile $file, ?string $oldPath = null): ?string
    {
        if (!$file->isValid()) {
            return null;
        }

        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $manager = new ImageManager(new Driver());
        $image = $manager->read($file);
        $image->scale(800);

        $filename = 'menu_' . time() . '_' . uniqid() . '.webp';
        $path = 'menus/' . $filename;

        $encoded = $image->toWebp(70)->encode();
        Storage::disk('public')->put($path, $encoded->toString());

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function getImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
