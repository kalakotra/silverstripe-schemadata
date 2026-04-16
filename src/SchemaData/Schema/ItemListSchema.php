<?php

declare(strict_types=1);

namespace Kalakotra\SchemaData\Schema;

use Kalakotra\SchemaData\SchemaProvider;

/**
 * Schema.org/ItemList
 *
 * Use this on listing/holder pages that display heterogeneous items
 * (for example SeminarHolder listing multiple different seminars).
 *
 * Example usage on SeminarHolder:
 *
 *   public function getSchemaData(): array
 *   {
 *       return (new ItemListSchema(
 *           name: $this->Title,
 *           url:  $this->AbsoluteLink(),
 *           items: $this->Children()->map(
 *               fn($page) => [
 *                   'url'  => $page->AbsoluteLink(),
 *                   'name' => $page->Title,        // optional
 *               ]
 *           )->toArray(),
 *       ))->getSchemaData();
 *   }
 *
 * Example usage on any listing page:
 *
 *   public function getSchemaData(): array
 *   {
 *       return (new ItemListSchema(
 *           name:  'Our products',
 *           url:   $this->AbsoluteLink(),
 *           items: $this->Products()->map(
 *               fn($p) => ['url' => $p->Link(), 'name' => $p->Title]
 *           )->toArray(),
 *       ))->getSchemaData();
 *   }
 */
final class ItemListSchema implements SchemaProvider
{
    /**
     * @param array<int, array{url: string, name?: string, description?: string, imageUrl?: string}> $items
    *   Each item must include 'url'. 'name', 'description', and 'imageUrl' are optional.
     */
    public function __construct(
        private readonly string $name,
        private readonly string $url,
        private readonly array  $items,
        private readonly string $description = '',
    ) {}

    public function getSchemaData(): array
    {
        $data = [
            '@context'        => 'https://schema.org',
            '@type'           => 'ItemList',
            'name'            => $this->name,
            'url'             => $this->url,
            'numberOfItems'   => count($this->items),
            'itemListElement' => $this->buildElements(),
        ];

        if ($this->description !== '') {
            $data['description'] = $this->description;
        }

        return $data;
    }

    /** @return array<int, array<string, mixed>> */
    private function buildElements(): array
    {
        $elements = [];

        foreach (array_values($this->items) as $position => $item) {
            if (empty($item['url'])) {
                continue;
            }

            $element = [
                '@type'    => 'ListItem',
                'position' => $position + 1,
                'url'      => $item['url'],
            ];

            // If name/description/image exists, use a Thing wrapper
            if (!empty($item['name'])) {
                $thing = [
                    '@type' => 'Thing',
                    'url'   => $item['url'],
                    'name'  => $item['name'],
                ];

                if (!empty($item['description'])) {
                    $thing['description'] = $item['description'];
                }

                if (!empty($item['imageUrl'])) {
                    $thing['image'] = $item['imageUrl'];
                }

                $element['item'] = $thing;
                unset($element['url']); // url moves inside item, not directly on ListItem
            }

            $elements[] = $element;
        }

        return $elements;
    }
}
