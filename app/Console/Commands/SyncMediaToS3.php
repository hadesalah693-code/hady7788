<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use SplFileInfo;
use Throwable;

class SyncMediaToS3 extends Command
{
    protected $signature = 'media:sync-to-s3
                            {--dry-run : عرض الملفات التي ستُرفع دون رفعها}';

    protected $description = 'رفع صور العقارات والأفاتار والشعارات من التخزين المحلي إلى أقراص S3.';

    public function handle(): int
    {
        $targets = [
            'property-images' => storage_path('app/property-images'),
            'public' => storage_path('app/public'),
        ];

        $failed = false;

        foreach ($targets as $diskName => $localRoot) {
            if (config("filesystems.disks.{$diskName}.driver") !== 's3') {
                $this->warn("تخطي القرص [{$diskName}]: لم يُضبط على s3 (راجع PROPERTY_IMAGE_DRIVER / PUBLIC_DISK_DRIVER).");

                continue;
            }

            if (! File::isDirectory($localRoot)) {
                $this->line("لا يوجد مجلد محلي للقرص [{$diskName}].");

                continue;
            }

            $disk = Storage::disk($diskName);

            $files = collect(File::allFiles($localRoot))
                ->reject(fn (SplFileInfo $file): bool => str_starts_with($file->getFilename(), '.'));

            $this->info("القرص [{$diskName}]: ".$files->count().' ملف محلي.');

            $uploaded = 0;
            $skipped = 0;

            foreach ($files as $file) {
                $relative = str_replace('\\', '/', $file->getRelativePathname());

                if ($this->option('dry-run')) {
                    $this->line("  - {$relative}");

                    continue;
                }

                if ($disk->exists($relative)) {
                    $skipped++;

                    continue;
                }

                $stream = fopen($file->getPathname(), 'rb');

                if ($stream === false) {
                    $this->error("  ✗ تعذّر قراءة {$relative}");
                    $failed = true;

                    continue;
                }

                try {
                    $disk->writeStream($relative, $stream);
                    $uploaded++;
                    $this->line("  ✓ {$relative}");
                } catch (Throwable $exception) {
                    $failed = true;
                    $this->error("  ✗ {$relative}: {$exception->getMessage()}");
                } finally {
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }
            }

            if (! $this->option('dry-run')) {
                $this->line("  تم الرفع: {$uploaded} — موجود مسبقًا: {$skipped}.");
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
