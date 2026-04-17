<?php

declare(strict_types=1);

namespace Kalakotra\SchemaData\Schema;

use Kalakotra\SchemaData\SchemaProvider;

/**
 * Schema.org/BreadcrumbList
 *
 * Recommended on all pages deeper than the root page.
 * Google uses this to display breadcrumb trails directly in search results.
 *
 * Example usage - automatic from the SilverStripe page hierarchy:
 *
 *   public function getSchemaData(): array
 *   {
 *       return (new BreadcrumbSchema(
 *           items: BreadcrumbSchema::fromPage($this),
 *       ))->getSchemaData();
 *   }
 *
 * Example of manual construction:
 *
 *   public function getSchemaData(): array
 *   {
 *       return (new BreadcrumbSchema(
 *           items: [
 *               ['name' => 'Home',  'url' => 'https://kalakotra.ba/'],
 *               ['name' => 'Seminari', 'url' => 'https://kalakotra.ba/seminari/'],
 *               ['name' => 'SilverStripe architecture'],  // last item has no URL
 *           ],
 *       ))->getSchemaData();
 *   }
 */
final class BreadcrumbSchema implements SchemaProvider
{
    /**
     * @param array<int, array{name: string, url?: string}> $items
     *   Each item must include 'name'. 'url' is optional (the last item usually has none).
     */
    public function __construct(
        private readonly array $items,
    ) {}

    public function getSchemaData(): array
    {
        if (empty($this->items)) {
            return [];
        }

        $elements = [];

        foreach (array_values($this->items) as $position => $item) {
            if (empty($item['name'])) {
                continue;
            }

            $element = [
                '@type'    => 'ListItem',
                'position' => $position + 1,
                'name'     => $item['name'],
            ];

            if (!empty($item['url'])) {
                $element['item'] = $item['url'];
            }

            $elements[] = $element;
        }

        return [
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => $elements,
        ];
    }

     /**
      * Helper that automatically builds a breadcrumb array from the SilverStripe page hierarchy.
      *
      * Usage: BreadcrumbSchema::fromPage($this)
      *
      * @return array<int, array{name: string, url?: string}>
      */
    public static function fromPage(\SilverStripe\CMS\Model\SiteTree $page): array
    {
        $items = [];
        $ancestors = array_reverse($page->getAncestors()->toArray());

        // Root / Home
        $items[] = [
            'name' => 'Home',
            'url'  => \SilverStripe\Control\Director::absoluteBaseURL(),
        ];

        // Ancestor pages
        foreach ($ancestors as $ancestor) {
            $items[] = [
                'name' => (string) $ancestor->MenuTitle,
                'url'  => $ancestor->AbsoluteLink(),
            ];
        }

        // Current page (without URL - last element)
        $items[] = [
            'name' => (string) $page->MenuTitle,
        ];

        return $items;
    }
}
