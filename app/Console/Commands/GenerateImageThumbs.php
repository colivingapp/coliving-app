<?php

namespace App\Console\Commands;

use App\Services\ImageStorage;
use Illuminate\Console\Command;

class GenerateImageThumbs extends Command
{
    protected $signature = 'images:generate-thumbs {--force : Recreate thumbs even if they already exist}';

    protected $description = 'Create small JPEG/PNG thumbs for existing space and profile photos';

    public function handle(ImageStorage $images): int
    {
        ini_set('memory_limit', '1024M');

        $force = (bool) $this->option('force');

        foreach ([ImageStorage::SPACE_DIR, ImageStorage::MATE_DIR] as $kind) {
            $result = $images->generateAllThumbs($kind, $force);

            $this->info(sprintf(
                '%s: created %d thumbs, skipped %d%s',
                $result['kind'],
                $result['created'],
                $result['skipped'],
                $force ? ' (forced)' : ''
            ));

            foreach ($result['failed'] as $failure) {
                $this->error('  failed: ' . $failure);
            }
        }

        $this->comment('Thumbs are in storage/app/public/spaces/thumbs and storage/app/public/mates/thumbs.');

        return self::SUCCESS;
    }
}
