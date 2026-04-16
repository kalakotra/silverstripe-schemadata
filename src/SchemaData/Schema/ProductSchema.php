<?php

declare(strict_types=1);

namespace Kalakotra\SchemaData\Schema;

use Kalakotra\SchemaData\SchemaProvider;

/**
 * Schema.org/Product
 *
 * Example usage on ProductPage:
 *
 *   public function getSchemaData(): array
 *   {
 *       return (new ProductSchema(
 *           title:        $this->Title,
 *           description:  $this->Summary,
 *           url:          $this->AbsoluteLink(),
 *           price:        (float) $this->Price,
 *           currency:     'BAM',
 *           availability: $this->InStock ? 'in_stock' : 'out_of_stock',
 *           sku:          $this->SKU,
 *           imageUrl:     $this->Image()?->AbsoluteURL() ?? '',
 *       ))->getSchemaData();
 *   }
 */
final class ProductSchema implements SchemaProvider
{
    public function __construct(
        private readonly string $title,
        private readonly string $url,
        private readonly string $description  = '',
        private readonly float  $price        = 0.0,
        private readonly string $currency     = 'BAM',
        private readonly string $availability = 'in_stock', // 'in_stock'|'out_of_stock'|'preorder'
        private readonly string $sku          = '',
        private readonly string $imageUrl     = '',
        private readonly string $brand        = '',
    ) {}

    public function getSchemaData(): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type'    => 'Product',
            'name'     => $this->title,
            'url'      => $this->url,
        ];

        if ($this->description !== '') {
            $data['description'] = $this->description;
        }

        if ($this->sku !== '') {
            $data['sku'] = $this->sku;
        }

        if ($this->imageUrl !== '') {
            $data['image'] = $this->imageUrl;
        }

        if ($this->brand !== '') {
            $data['brand'] = ['@type' => 'Brand', 'name' => $this->brand];
        }

        if ($this->price > 0) {
            $data['offers'] = [
                '@type'         => 'Offer',
                'price'         => number_format($this->price, 2, '.', ''),
                'priceCurrency' => $this->currency,
                'availability'  => $this->resolveAvailability(),
                'url'           => $this->url,
            ];
        }

        return $data;
    }

    private function resolveAvailability(): string
    {
        return match ($this->availability) {
            'out_of_stock' => 'https://schema.org/OutOfStock',
            'preorder'     => 'https://schema.org/PreOrder',
            default        => 'https://schema.org/InStock',
        };
    }
}
