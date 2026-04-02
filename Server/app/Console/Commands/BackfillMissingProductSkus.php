<?php

namespace App\Console\Commands;

use App\Models\ProductStock;
use App\Utility\SkuUtility;
use Illuminate\Console\Command;

class BackfillMissingProductSkus extends Command
{
    protected $signature = 'sku:backfill-product-stocks {--dry-run : Preview missing SKU updates without saving}';

    protected $description = 'Generate SKUs for existing product stock rows where SKU is missing';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $processed = 0;
        $updated = 0;
        $skipped = 0;

        ProductStock::with('product')
            ->where(function ($query) {
                $query->whereNull('sku')
                    ->orWhereRaw("TRIM(sku) = ''");
            })
            ->orderBy('id')
            ->chunkById(100, function ($stocks) use ($dryRun, &$processed, &$updated, &$skipped) {
                foreach ($stocks as $stock) {
                    $processed++;

                    if (!$stock->product) {
                        $skipped++;
                        $this->warn("Skipped stock #{$stock->id}: product not found.");
                        continue;
                    }

                    $sku = SkuUtility::forStock($stock->product, $stock->variant, null, $stock->id);

                    if (!$dryRun) {
                        $stock->sku = $sku;
                        $stock->save();
                    }

                    $updated++;
                    $this->line(($dryRun ? '[dry-run] ' : '') . "Stock #{$stock->id} => {$sku}");
                }
            });

        $this->info("Processed: {$processed}, updated: {$updated}, skipped: {$skipped}");

        return self::SUCCESS;
    }
}
