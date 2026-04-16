<?php

declare(strict_types=1);

namespace Kalakotra\SchemaData;

/**
 * Implement this interface on your Page class so the module can
 * automatically inject JSON-LD into the page <head> tag.
 *
 * Example:
 *
 *   class MyPage extends Page implements SchemaProvider
 *   {
 *       public function getSchemaData(): array
 *       {
 *           return (new ProductSchema(
 *               title: $this->Title,
 *               price: (float) $this->Price,
 *               ...
 *           ))->getSchemaData();
 *       }
 *   }
 */
interface SchemaProvider
{
    /**
    * Returns an associative array ready for json_encode.
    * It must contain the '@context' and '@type' keys.
     *
     * @return array<string, mixed>
     */
    public function getSchemaData(): array;
}
