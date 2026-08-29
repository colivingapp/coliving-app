<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class ImageStorage
{
    public const SPACE_DIR = 'spaces';
    public const MATE_DIR = 'mates';

    public const SPACE_FULL_WIDTH = 1280;
    public const SPACE_THUMB_WIDTH = 480;
    public const MATE_FULL_WIDTH = 800;
    public const MATE_THUMB_WIDTH = 320;

    public const FULL_QUALITY = 80;
    public const THUMB_QUALITY = 72;

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'jfif', 'webp', 'gif'];

    public function storeSpacePhoto(UploadedFile $file): string
    {
        return $this->storeUpload(self::SPACE_DIR, $file, self::SPACE_FULL_WIDTH, self::SPACE_THUMB_WIDTH);
    }

    public function storeMatePhoto(UploadedFile $file): string
    {
        return $this->storeUpload(self::MATE_DIR, $file, self::MATE_FULL_WIDTH, self::MATE_THUMB_WIDTH);
    }

    public function deleteSpacePhoto(string $filename): void
    {
        $this->delete(self::SPACE_DIR, $filename);
    }

    public function deleteMatePhoto(?string $storedUrl): void
    {
        if (!$storedUrl) {
            return;
        }

        $filename = basename(parse_url($storedUrl, PHP_URL_PATH) ?: $storedUrl);
        $this->delete(self::MATE_DIR, $filename);
    }

    public function delete(string $kind, string $filename): void
    {
        if ($filename === '' || $filename === '.' || $filename === '..') {
            return;
        }

        Storage::delete('public/' . $kind . '/' . $filename);
        Storage::delete('public/' . $kind . '/thumbs/' . $filename);
    }

    public function generateThumb(string $kind, string $filename, bool $force = false): bool
    {
        $source = storage_path('app/public/' . $kind . '/' . $filename);
        $thumb = storage_path('app/public/' . $kind . '/thumbs/' . $filename);

        if (!is_file($source)) {
            return false;
        }

        if (!$force && is_file($thumb)) {
            return true;
        }

        $thumbWidth = $kind === self::MATE_DIR ? self::MATE_THUMB_WIDTH : self::SPACE_THUMB_WIDTH;
        $this->ensureDirectory($kind, true);
        $this->saveResized($source, $thumb, $thumbWidth, self::THUMB_QUALITY);

        return true;
    }

    /**
     * @return array{kind: string, created: int, skipped: int, failed: array<int, string>}
     */
    public function generateAllThumbs(string $kind, bool $force = false): array
    {
        ini_set('memory_limit', '1024M');

        $created = 0;
        $skipped = 0;
        $failed = [];

        $files = Storage::files('public/' . $kind);

        foreach ($files as $file) {
            $filename = basename($file);
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (!in_array($extension, self::IMAGE_EXTENSIONS, true)) {
                continue;
            }

            $thumb = storage_path('app/public/' . $kind . '/thumbs/' . $filename);
            if (!$force && is_file($thumb)) {
                $skipped++;
                continue;
            }

            try {
                $this->generateThumb($kind, $filename, true);
                $created++;
            } catch (\Throwable $e) {
                $failed[] = $filename . ' (' . $e->getMessage() . ')';
            }

            gc_collect_cycles();
        }

        return [
            'kind' => $kind,
            'created' => $created,
            'skipped' => $skipped,
            'failed' => $failed,
        ];
    }

    private function storeUpload(string $kind, UploadedFile $file, int $fullWidth, int $thumbWidth): string
    {
        $filename = uniqid() . '.' . strtolower($file->getClientOriginalExtension());
        $source = $file->getRealPath();

        $this->ensureDirectory($kind, false);
        $this->ensureDirectory($kind, true);

        $this->saveResized(
            $source,
            storage_path('app/public/' . $kind . '/' . $filename),
            $fullWidth,
            self::FULL_QUALITY
        );

        $this->saveResized(
            $source,
            storage_path('app/public/' . $kind . '/thumbs/' . $filename),
            $thumbWidth,
            self::THUMB_QUALITY
        );

        return $filename;
    }

    private function saveResized(string $source, string $destination, int $maxWidth, int $quality): void
    {
        $manager = new ImageManager(['driver' => 'gd']);
        $image = $manager->make($source)->orientate();

        $image->resize($maxWidth, $maxWidth, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $image->save($destination, $this->qualityForPath($destination, $quality));
        $image->destroy();
    }

    private function qualityForPath(string $path, int $jpegQuality): int
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'png') {
            return 8;
        }

        return $jpegQuality;
    }

    private function ensureDirectory(string $kind, bool $thumbs): void
    {
        $path = 'public/' . $kind . ($thumbs ? '/thumbs' : '');
        Storage::makeDirectory($path);
    }
}
