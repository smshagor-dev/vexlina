<?php

namespace App\Utility;

use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Support\Str;

class SkuUtility
{
    protected const MAX_LENGTH = 32;

    public static function preview(?string $productName, ?string $variant = null): string
    {
        $segments = [self::sanitize($productName, 'PRODUCT')];

        if (filled($variant)) {
            $segments[] = self::sanitize($variant, 'VARIANT');
        }

        return implode('-', array_filter($segments));
    }

    public static function forStock(Product $product, ?string $variant = null, ?string $requestedSku = null, ?int $ignoreStockId = null): string
    {
        $base = filled($requestedSku)
            ? self::sanitize($requestedSku, self::defaultBase($product, $variant))
            : self::defaultBase($product, $variant);

        return self::unique($base, $ignoreStockId, $product->id);
    }

    protected static function defaultBase(Product $product, ?string $variant = null): string
    {
        $segments = [
            self::sanitize($product->name, 'PRODUCT'),
            'P' . $product->id,
        ];

        if (filled($variant)) {
            $segments[] = self::sanitize($variant, 'VARIANT');
        }

        return implode('-', array_filter($segments));
    }

    protected static function sanitize(?string $value, string $fallback = 'SKU'): string
    {
        $value = Str::upper(Str::ascii((string) $value));
        $value = preg_replace('/[^A-Z0-9]+/', '-', $value);
        $value = trim((string) $value, '-');
        $value = preg_replace('/-+/', '-', $value);

        if ($value === '') {
            $value = $fallback;
        }

        return Str::limit($value, self::MAX_LENGTH, '');
    }

    protected static function unique(string $base, ?int $ignoreStockId = null, ?int $productId = null): string
    {
        $candidate = $base;
        $suffix = $productId ? '-' . $productId : '';
        $counter = 1;

        while (self::skuExists($candidate, $ignoreStockId)) {
            $counterSuffix = $suffix . ($counter > 1 ? '-' . $counter : '');
            $candidate = Str::limit($base, self::MAX_LENGTH - strlen($counterSuffix), '') . $counterSuffix;
            $counter++;
        }

        return $candidate;
    }

    protected static function skuExists(string $sku, ?int $ignoreStockId = null): bool
    {
        return ProductStock::where('sku', $sku)
            ->when($ignoreStockId, function ($query) use ($ignoreStockId) {
                $query->where('id', '!=', $ignoreStockId);
            })
            ->exists();
    }
}
