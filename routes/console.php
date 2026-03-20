<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use App\Models\Product;
use App\Models\ProductPriceHistory;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('products:recalc-selling-price {--dry-run}', function () {
    $dryRun = (bool) $this->option('dry-run');
    $checked = 0;
    $updated = 0;

    Product::query()
        ->select(['id', 'import_price', 'profit_margin', 'selling_price'])
        ->chunkById(200, function ($products) use (&$checked, &$updated, $dryRun) {
            foreach ($products as $product) {
                $checked++;

                $importPrice = (float) $product->import_price;
                $profitMargin = (float) $product->profit_margin;
                $currentSelling = (float) $product->selling_price;

                $nextSelling = round($importPrice * (1 + ($profitMargin / 100)), 2);
                if (abs(round($currentSelling, 2) - $nextSelling) <= 0.00001) {
                    continue;
                }

                $updated++;

                if ($dryRun) {
                    continue;
                }

                $product->update(['selling_price' => $nextSelling]);

                ProductPriceHistory::create([
                    'product_id' => $product->id,
                    'import_note_id' => null,
                    'import_price' => $importPrice,
                    'profit_margin' => $profitMargin,
                    'selling_price' => $nextSelling,
                ]);
            }
        });

    if (!$dryRun && $updated > 0) {
        if (!Cache::has('storefront:products:version')) {
            Cache::put('storefront:products:version', 1);
        } else {
            Cache::increment('storefront:products:version');
        }
    }

    $this->info("Checked: {$checked}. Updated: {$updated}." . ($dryRun ? ' (dry run)' : ''));
})->purpose('Recalculate selling_price for all products.');
