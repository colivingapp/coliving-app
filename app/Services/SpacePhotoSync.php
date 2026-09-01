<?php

namespace App\Services;

use App\Models\Photo;
use App\Models\Space;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SpacePhotoSync
{
    public const MAX_PHOTOS = 20;
    public const MAX_BYTES = 5 * 1024 * 1024;
    public const ALLOWED_EXTENSIONS = ['jpeg', 'jpg', 'png', 'jfif', 'webp'];

    public function __construct(private ImageStorage $imageStorage)
    {
    }

    /**
     * Persist the gallery in the exact order submitted.
     * Position 1 is always the cover photo.
     *
     * @param  array<int, array<string, mixed>>  $meta
     * @param  array<string, UploadedFile|null>  $files
     */
    public function sync(Space $space, array $meta, array $files): void
    {
        if (count($meta) > self::MAX_PHOTOS) {
            throw new InvalidArgumentException('Maximum 20 photos allowed.');
        }

        $normalized = [];
        $seenIds = [];

        foreach (array_values($meta) as $index => $item) {
            if (!is_array($item)) {
                throw new InvalidArgumentException('Invalid photo data.');
            }

            $key = isset($item['key']) ? (string) $item['key'] : '';
            if ($key === '' || !preg_match('/^[A-Za-z0-9_-]{1,40}$/', $key)) {
                throw new InvalidArgumentException('Invalid photo key.');
            }

            $id = isset($item['id']) && $item['id'] !== '' && $item['id'] !== null
                ? (int) $item['id']
                : null;

            if ($id && isset($seenIds[$id])) {
                $id = null;
            }
            if ($id) {
                $seenIds[$id] = true;
            }

            $file = $files[$key] ?? null;
            if (!$file instanceof UploadedFile) {
                $file = null;
            }
            if ($file) {
                $this->assertValidFile($file);
            }

            if (!$id && !$file) {
                throw new InvalidArgumentException('Each new photo needs an uploaded file.');
            }

            $normalized[] = [
                'key' => $key,
                'id' => $id,
                'file' => $file,
                'pos' => $index + 1,
            ];
        }

        $existing = Photo::query()
            ->where('space_uid', $space->uid)
            ->get()
            ->keyBy('id');

        $keepIds = [];
        foreach ($normalized as $index => $item) {
            if ($item['id'] && $existing->has($item['id'])) {
                $keepIds[$item['id']] = true;
            } else {
                $normalized[$index]['id'] = null;
                if (!$item['file']) {
                    throw new InvalidArgumentException('Each new photo needs an uploaded file.');
                }
            }
        }

        $storedFilenames = [];
        foreach ($normalized as $item) {
            if ($item['file']) {
                $storedFilenames[$item['key']] = $this->imageStorage->storeSpacePhoto($item['file']);
            }
        }

        $obsoleteFilenames = [];

        DB::transaction(function () use ($space, $normalized, $existing, $keepIds, $storedFilenames, &$obsoleteFilenames) {
            foreach ($existing as $photo) {
                if (!isset($keepIds[$photo->id])) {
                    if ($photo->filename) {
                        $obsoleteFilenames[] = $photo->filename;
                    }
                    $photo->delete();
                }
            }

            foreach ($normalized as $item) {
                $photo = $item['id'] ? $existing->get($item['id']) : null;
                $newFilename = $storedFilenames[$item['key']] ?? null;

                if ($newFilename) {
                    if ($photo) {
                        if ($photo->filename && $photo->filename !== $newFilename) {
                            $obsoleteFilenames[] = $photo->filename;
                        }
                        $photo->filename = $newFilename;
                        $photo->pos = $item['pos'];
                        $photo->space_id = $space->id;
                        $photo->save();
                    } else {
                        Photo::create([
                            'space_uid' => $space->uid,
                            'space_id' => $space->id,
                            'filename' => $newFilename,
                            'pos' => $item['pos'],
                        ]);
                    }
                    continue;
                }

                if ($photo) {
                    $photo->pos = $item['pos'];
                    $photo->space_id = $space->id;
                    $photo->save();
                }
            }

            $remaining = Photo::where('space_uid', $space->uid)->count();
            $space->photo = $remaining > 0 ? 1 : 0;
            $space->save();
        });

        foreach ($obsoleteFilenames as $filename) {
            $this->imageStorage->deleteSpacePhoto($filename);
        }
    }

    private function assertValidFile(UploadedFile $file): void
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new InvalidArgumentException('Invalid file format. Use JPEG, PNG, or WebP.');
        }

        if ($file->getSize() > self::MAX_BYTES) {
            throw new InvalidArgumentException('File size exceeds 5MB.');
        }
    }
}
